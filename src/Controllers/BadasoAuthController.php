<?php

namespace Uasoft\Badaso\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use stdClass;
use Tymon\JWTAuth\Exceptions\JWTException;
use Uasoft\Badaso\Exceptions\SingleException;
use Uasoft\Badaso\Facades\Badaso;
use Uasoft\Badaso\Helpers\ApiResponse;
use Uasoft\Badaso\Helpers\AuthenticatedUser;
use Uasoft\Badaso\Helpers\CheckBase64;
use Uasoft\Badaso\Helpers\Config;
use Uasoft\Badaso\Mail\ForgotPassword;
use Uasoft\Badaso\Mail\SendUserVerification;
use Uasoft\Badaso\Middleware\BadasoAuthenticate;
use Uasoft\Badaso\Models\EmailReset;
use Uasoft\Badaso\Models\Role;
use Uasoft\Badaso\Models\User;
use Uasoft\Badaso\Models\UserRole;
use Uasoft\Badaso\Models\UserVerification;
use Uasoft\Badaso\Traits\FileHandler;

class BadasoAuthController extends Controller
{
    use FileHandler;

    public function __construct()
    {
        $this->middleware(BadasoAuthenticate::class, ['except' => ['login', 'register', 'forgetPassword', 'resetPassword', 'verify', 'reRequestVerification', 'validateTokenForgetPassword']]);
    }

    public function login(Request $request)
    {
        try {
            $remember = $request->get('remember', false);
            $credentials = [
                'email' => $request->email,
                'password' => $request->password,
            ];
            $request->validate([
                'email' => [
                    'required',
                    function ($attribute, $value, $fail) use ($credentials) {
                        if (!$token = auth()->attempt($credentials)) {
                            $fail(__('badaso::validation.auth.invalid_credentials'));
                        }
                    },
                ],
                'password' => ['required'],
            ]);

            $should_verify_email = Config::get('adminPanelVerifyEmail') == '1' ? true : false;
            if ($should_verify_email) {
                $user = auth()->user();
                if (is_null($user->email_verified_at)) {
                    return ApiResponse::success([]);
                }
            }

            $ttl = $this->getTTL($remember);
            $token = auth()->setTTL($ttl)->attempt($credentials);

            return $this->createNewToken($token, auth()->user(), $remember);
        } catch (JWTException $e) {
            return ApiResponse::failed($e);
        } catch (Exception $e) {
            return ApiResponse::failed($e);
        }
    }

    public function logout(Request $request)
    {
        try {
            auth()->logout();
            // auth()->invalidate();

            return ApiResponse::success();
        } catch (Exception $e) {
            return ApiResponse::failed($e);
        }
    }

    public function register(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6|confirmed',
            ]);

            $user = User::create([
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'password' => Hash::make($request->get('password')),
            ]);

            $role = $this->getCustomerRole();

            $user_role = new UserRole();
            $user_role->user_id = $user->id;
            $user_role->role_id = $role->id;
            $user_role->save();

            $should_verify_email = Config::get('adminPanelVerifyEmail') == '1' ? true : false;
            if (!$should_verify_email) {
                $ttl = $this->getTTL();
                $token = auth()->setTTL($ttl)->login($user);

                DB::commit();

                return $this->createNewToken($token, auth()->user());
            } else {
                $token = rand(111111, 999999);
                $token_lifetime = env('VERIFICATION_TOKEN_LIFETIME', 5);
                $expired_token = date('Y-m-d H:i:s', strtotime("+$token_lifetime minutes", strtotime(date('Y-m-d H:i:s'))));
                $data = [
                    'user_id' => $user->id,
                    'verification_token' => $token,
                    'expired_at' => $expired_token,
                    'count_incorrect' => 0,
                ];

                UserVerification::firstOrCreate($data);

                $this->sendVerificationToken(['user' => $user, 'token' => $token]);

                DB::commit();

                return ApiResponse::success([
                    'message' => __('badaso::validation.verification.email_sended'),
                ]);
            }
        } catch (Exception $e) {
            DB::rollBack();

            return ApiResponse::failed($e);
        }
    }

    public function refreshToken(Request $request)
    {
        try {
            $ttl = $this->getTTL();
            $token = auth()->setTTL($ttl)->refresh();

            return $this->createNewToken($token, auth()->user());

            return ApiResponse::success();
        } catch (Exception $e) {
            return ApiResponse::failed($e);
        }
    }

    public function getAuthenticatedUser()
    {
        try {
            if (!$user = AuthenticatedUser::getUser()) {
                throw new SingleException(__('badaso::validation.auth.user_not_found'));
            }

            $data['user'] = json_decode(json_encode($user));

            return ApiResponse::success($data);
        } catch (Exception $e) {
            return ApiResponse::failed($e);
        }
    }

    protected function createNewToken($token, $user, $remember = false)
    {
        $obj = new stdClass();
        $obj->access_token = $token;
        $obj->token_type = 'bearer';
        $obj->user = $user;
        $obj->expires_in = auth()->factory()->getTTL();

        return ApiResponse::success($obj);
    }

    public function sendVerificationToken($data)
    {
        return Mail::to($data['user']['email'])->queue(new SendUserVerification($data));
    }

    public function verify(Request $request)
    {
        try {
            $request->validate([
                'email' => ['required', 'exists:users'],
                'token' => ['required'],
            ]);

            $user = User::where('email', $request->email)->first();
            $user_verification = UserVerification::where('verification_token', $request->token)
                ->where('user_id', $user->id)
                ->first();

            if ($user_verification) {
                if (strtotime(date('Y-m-d H:i:s')) > strtotime($user_verification->expired_at)) {
                    // $user_verification->delete();
                    throw new SingleException('EXPIRED');
                }
                $user->email_verified_at = date('Y-m-d H:i:s');
                $user->save();

                $user_verification->delete();
            } else {
                throw new SingleException(__('badaso::validation.verification.invalid_verification_token'));
            }

            $ttl = $this->getTTL();
            $token = auth()->setTTL($ttl)->login($user);

            return $this->createNewToken($token, auth()->user());
        } catch (Exception $e) {
            return ApiResponse::failed($e);
        }
    }

    public function changePassword(Request $request)
    {
        try {
            if (!$user = auth()->user()) {
                throw new SingleException(__('badaso::validation.auth.user_not_found'));
            }

            $request->validate([
                'old_password' => [
                    'required',
                    function ($attribute, $value, $fail) use ($user) {
                        if (!Hash::check($value, $user->password)) {
                            $fail(__('badaso::validation.auth.wrong_old_password'));
                        }
                    },
                ],
                'new_password' => [
                    'required',
                    'confirmed',
                    'string',
                    'min:6',
                    'confirmed',
                    function ($attribute, $value, $fail) use ($user) {
                        if (Hash::check($value, $user->password)) {
                            $fail(__('badaso::validation.auth.password_not_changes'));
                        }
                    },
                ],
            ]);

            $user = User::find($user->id);
            $user->password = Hash::make($request->new_password);
            $user->save();

            return ApiResponse::success($user);
        } catch (Exception $e) {
            return ApiResponse::failed($e);
        }
    }

    public function forgetPassword(Request $request)
    {
        try {
            $request->validate([
                'email' => ['required', 'email', 'exists:users,email'],
            ]);

            $token = rand(111111, 999999);

            DB::table('password_resets')->insert([
                'email' => $request->email,
                'token' => $token,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $user = User::where('email', $request->email)->first();
            Mail::to($request->email)->send(new ForgotPassword($user, $token));

            return ApiResponse::success();
        } catch (Exception $e) {
            return ApiResponse::failed($e);
        }
    }

    public function validateTokenForgetPassword(Request $request)
    {
        try {
            $request->validate([
                'email' => ['required', 'email', 'exists:users,email'],
                'token' => [
                    'required',
                    'exists:password_resets,token',
                    function ($attribute, $value, $fail) use ($request) {
                        $password_resets = DB::SELECT('SELECT * FROM password_resets WHERE token = :token AND email = :email', [
                            'token' => $request->token,
                            'email' => $request->email,
                        ]);
                        $password_reset = collect($password_resets)->first();
                        if (is_null($password_reset)) {
                            $fail('Token or Email invalid');
                        }
                    },
                ],
            ]);

            return ApiResponse::success();
        } catch (Exception $e) {
            return ApiResponse::failed($e);
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            $request->validate([
                'email' => ['required', 'email', 'exists:users,email'],
                'token' => [
                    'required',
                    'exists:password_resets,token',
                    function ($attribute, $value, $fail) use ($request) {
                        $password_resets = DB::SELECT('SELECT * FROM password_resets WHERE token = :token AND email = :email', [
                            'token' => $request->token,
                            'email' => $request->email,
                        ]);
                        $password_reset = collect($password_resets)->first();
                        if (is_null($password_reset)) {
                            $fail('Token or Email invalid');
                        }
                    },
                ],
            ]);

            $password_resets = DB::SELECT('SELECT * FROM password_resets WHERE token = :token AND email = :email', [
                'token' => $request->token,
                'email' => $request->email,
            ]);

            $password_reset = collect($password_resets)->first();

            $request->validate([
                'token' => [
                    function ($attribute, $value, $fail) use ($password_reset) {
                        if (is_null($password_reset)) {
                            $fail('Token Invalid');
                        }
                    },
                ],
            ]);

            $user = User::where('email', $password_reset->email)->first();
            $user->password = Hash::make($request->password);
            $saved = $user->save();

            if ($saved) {
                DB::table('password_resets')->where('token', $request->token)->delete();
            }

            return ApiResponse::success();
        } catch (Exception $e) {
            return ApiResponse::failed($e);
        }
    }

    public function reRequestVerification(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'email' => 'required|string|email|max:255',
            ]);

            $user = User::where('email', $request->email)->first();
            $user_verification = UserVerification::where('user_id', $user->id)
                ->first();

            if (!$user_verification) {
                throw new SingleException(__('badaso::validation.verification.verification_not_found'));
            }

            $token = rand(111111, 999999);
            $token_lifetime = env('VERIFICATION_TOKEN_LIFETIME', 5);
            $expired_token = date('Y-m-d H:i:s', strtotime("+$token_lifetime minutes", strtotime(date('Y-m-d H:i:s'))));

            $user_verification->verification_token = $token;
            $user_verification->expired_at = $expired_token;
            $user_verification->save();

            $this->sendVerificationToken(['user' => $user, 'token' => $token]);

            DB::commit();

            return ApiResponse::success([
                'message' => __('badaso::validation.verification.email_sended'),
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return ApiResponse::failed($e);
        }
    }

    protected function getCustomerRole()
    {
        $role = Role::where('name', 'customer')->first();

        if (is_null($role)) {
            $role = new Role();
            $role->name = 'customer';
            $role->display_name = 'Customer';
            $role->save();
        }

        return $role;
    }

    public function updateProfile(Request $request)
    {
        DB::beginTransaction();
        try {
            if (!$user = auth()->user()) {
                throw new SingleException(__('badaso::validation.auth.user_not_found'));
            }

            $request->validate([
                'name' => 'required',
                'avatar' => [
                    function ($attribute, $value, $fail) {
                        $check = new CheckBase64($value);
                        if (!$check->isValid()) {
                            $fail($check->getMessage());
                        }
                    },
                ],
            ]);

            $user = User::find($user->id);

            $user->name = $request->name;
            $uploaded = null;
            if ($request->avatar && $request->avatar != '') {
                $extension = explode('/', explode(';', $request->avatar)[0])[1];
                $files = [];
                $files[] = [
                    'base64' => $request->avatar,
                    'name' => Str::slug($request->name).'.'.$extension,
                ];
                $uploaded = $this->handleUploadFiles($files, null, 'users');
                if (count($uploaded) > 0) {
                    $uploaded = $uploaded[0];
                    $this->handleDeleteFile($user->avatar);
                }
                $user->avatar = $uploaded;
            }
            $user->additional_info = $request->additional_info;
            $user->save();

            DB::commit();

            return ApiResponse::success($user);
        } catch (Exception $e) {
            DB::rollBack();

            return ApiResponse::failed($e);
        }
    }

    public function updateEmail(Request $request)
    {
        DB::beginTransaction();
        try {
            if (!$user = auth()->user()) {
                throw new SingleException(__('badaso::validation.auth.user_not_found'));
            }

            $request->validate([
                'email' => 'required|email|unique:users,email',
            ]);

            $user = User::find($user->id);

            $should_verify_email = Config::get('adminPanelVerifyEmail') == '1' ? true : false;
            if ($should_verify_email) {
                $token = rand(111111, 999999);
                $token_lifetime = env('VERIFICATION_TOKEN_LIFETIME', 5);
                $expired_token = date('Y-m-d H:i:s', strtotime("+$token_lifetime minutes", strtotime(date('Y-m-d H:i:s'))));
                $data = [
                    'user_id' => $user->id,
                    'email' => $request->email,
                    'verification_token' => $token,
                    'expired_at' => $expired_token,
                    'count_incorrect' => 0,
                ];

                EmailReset::firstOrCreate($data);

                $user->email = $request->email;

                $this->sendVerificationToken(['user' => $user, 'token' => $token]);

                DB::commit();

                return ApiResponse::success([
                    'should_verify_email' => true,
                    'message' => __('badaso::validation.verification.email_sended'),
                ]);
            } else {
                $user->email = $request->email;
                $user->save();
            }

            DB::commit();

            return ApiResponse::success([
                'should_verify_email' => false,
                'user' => $user,
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return ApiResponse::failed($e);
        }
    }

    public function verifyEmail(Request $request)
    {
        try {
            if (!$user = auth()->user()) {
                throw new SingleException(__('badaso::validation.auth.user_not_found'));
            }

            $request->validate([
                'email' => ['required', 'unique:users', 'email'],
                'token' => ['required'],
            ]);

            $emai_reset = EmailReset::where('verification_token', $request->token)
                ->where('user_id', $user->id)
                ->first();

            $user = User::find($user->id);

            if ($emai_reset) {
                if (strtotime(date('Y-m-d H:i:s')) > strtotime($emai_reset->expired_at)) {
                    // $user_verification->delete();
                    throw new SingleException('EXPIRED');
                }
                $user->email = $request->email;
                $user->email_verified_at = date('Y-m-d H:i:s');
                $user->save();

                $emai_reset->delete();
            } else {
                throw new SingleException(__('badaso::validation.verification.invalid_verification_token'));
            }

            $ttl = $this->getTTL();
            $token = auth()->setTTL($ttl)->login($user);

            return $this->createNewToken($token, auth()->user());
        } catch (Exception $e) {
            return ApiResponse::failed($e);
        }
    }

    private function getTTL($remember = false)
    {
        $remember_lifetime = 60 * 24 * 30; // a month
        $ttl = env('BADASO_AUTH_TOKEN_LIFETIME', Badaso::getDefaultJwtTokenLifetime());
        if ($ttl != '') {
            $ttl = (int) $ttl;
        } else {
            $ttl = Badaso::getDefaultJwtTokenLifetime();
        }
        if ($remember && $ttl < $remember_lifetime) {
            $ttl = $remember_lifetime;
        }

        return $ttl;
    }
}
