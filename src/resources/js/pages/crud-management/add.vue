<template>
  <div>
    <badaso-breadcrumb-row></badaso-breadcrumb-row>
    <vs-row v-if="$helper.isAllowed('add_crud_data')">
      <vs-col vs-lg="12">
        <vs-card>
          <div slot="header">
            <h3>
              {{
                $t("crud.add.title.table", {
                  tableName: $route.params.tableName,
                })
              }}
            </h3>
          </div>
          <vs-row>
            <badaso-text
              v-model="crudData.name"
              size="6"
              :label="$t('crud.add.field.tableName.title')"
              :placeholder="$t('crud.add.field.tableName.title')"
              required
              readonly
              :alert="errors.name"
            ></badaso-text>
            <badaso-switch
              size="3"
              v-model="crudData.generatePermissions"
              :label="$t('crud.add.field.generatePermissions')"
              :alert="errors.generatePermissions"
            ></badaso-switch>
            <badaso-switch
              size="3"
              v-model="crudData.serverSide"
              :label="$t('crud.add.field.serverSide')"
              :alert="errors.serverSide"
            ></badaso-switch>
          </vs-row>
          <vs-row>
            <badaso-text
              v-model="crudData.displayNameSingular"
              size="6"
              :label="$t('crud.add.field.displayNameSingular.title')"
              required
              :placeholder="
                $t('crud.add.field.displayNameSingular.placeholder')
              "
              :alert="errors.displayNameSingular"
            ></badaso-text>
            <badaso-text
              v-model="crudData.displayNamePlural"
              size="6"
              :label="$t('crud.add.field.displayNamePlural.title')"
              :placeholder="$t('crud.add.field.displayNamePlural.placeholder')"
              :alert="errors.displayNamePlural"
            ></badaso-text>
            <badaso-text
              v-model="crudData.slug"
              size="6"
              :label="$t('crud.add.field.urlSlug.title')"
              :placeholder="$t('crud.add.field.urlSlug.placeholder')"
              :alert="errors.slug"
            ></badaso-text>
            <badaso-text
              v-model="crudData.icon"
              size="6"
              :label="$t('crud.add.field.icon.title')"
              :placeholder="$t('crud.add.field.icon.placeholder')"
              :additionalInfo="
                $t('menu.builder.popup.edit.field.icon.description')
              "
              :alert="errors.icon"
            ></badaso-text>
            <badaso-text
              v-model="crudData.modelName"
              size="6"
              :label="$t('crud.add.field.modelName.title')"
              :placeholder="$t('crud.add.field.modelName.placeholder')"
              :alert="errors.modelName"
            ></badaso-text>
            <badaso-text
              v-model="crudData.controller"
              size="6"
              :label="$t('crud.add.field.controllerName.title')"
              :placeholder="$t('crud.add.field.controllerName.placeholder')"
              :alert="errors.controller"
            ></badaso-text>
            <badaso-select
              v-model="crudData.orderColumn"
              size="4"
              :label="$t('crud.add.field.orderColumn.title')"
              :placeholder="$t('crud.add.field.orderColumn.placeholder')"
              :items="fieldList"
              :alert="errors.orderColumn"
            ></badaso-select>
            <badaso-select
              v-model="crudData.orderDisplayColumn"
              size="4"
              :label="$t('crud.add.field.orderDisplayColumn.title')"
              :placeholder="$t('crud.add.field.orderDisplayColumn.placeholder')"
              :items="fieldList"
              :alert="errors.orderDisplayColumn"
              :additionalInfo="
                $t('crud.add.field.orderDisplayColumn.description')
              "
            ></badaso-select>
            <badaso-select
              v-model="crudData.orderDirection"
              size="4"
              :label="$t('crud.add.field.orderDirection.title')"
              :placeholder="$t('crud.add.field.orderDirection.placeholder')"
              :items="orderDirections"
              :alert="errors.orderDirection"
            ></badaso-select>
            <badaso-hidden
              v-model="crudData.defaultServerSideSearchField"
              size="3"
              :label="$t('crud.add.field.defaultServerSideSearchField.title')"
              :placeholder="
                $t('crud.add.field.defaultServerSideSearchField.placeholder')
              "
              :items="fieldList"
              :alert="errors.defaultServerSideSearchField"
            ></badaso-hidden>
            <badaso-textarea
              size="12"
              :label="$t('crud.add.field.description.title')"
              :placeholder="$t('crud.add.field.description.placeholder')"
              v-model="crudData.description"
              :alert="errors.description"
            >
            </badaso-textarea>
          </vs-row>
        </vs-card>
      </vs-col>
      <vs-col vs-lg="12">
        <vs-card>
          <div slot="header">
            <h3>
              {{
                $t("crud.add.title.field", {
                  tableName: $route.params.tableName,
                })
              }}
            </h3>
          </div>
          <vs-row class="hide-for-mobile">
            <vs-col col-lg="12" style="overflow-x: auto">
              <table class="table">
                <thead>
                  <th style="width: 1%; word-wrap: nowrap;"></th>
                  <th style="width: 1%; word-wrap: nowrap;">
                    {{ $t("crud.add.header.field") }}
                  </th>
                  <th style="width: 1%; word-wrap: nowrap;">
                    {{ $t("crud.add.header.visibility") }}
                  </th>
                  <th style="width: 1%; word-wrap: nowrap; min-width: 200px;">
                    {{ $t("crud.add.header.inputType") }}
                  </th>
                  <th style="min-width: 200px;">
                    {{ $t("crud.add.header.displayName") }}
                  </th>
                  <th style="min-width: 200px;">
                    {{ $t("crud.add.header.optionalDetails") }}
                  </th>
                </thead>
                <draggable v-model="crudData.rows" tag="tbody">
                  <tr
                    :key="index"
                    v-for="(field, index) in crudData.rows"
                    style="background-color: white"
                  >
                    <td>
                      <vs-icon
                        icon="drag_indicator"
                        class="drag_icon"
                      ></vs-icon>
                    </td>
                    <td :data="field.field">
                      <strong>{{ field.field }}</strong>
                      <br />
                      <span style="white-space: nowrap">
                        {{ $t("crud.add.body.type") }} {{ field.type }}
                      </span>
                      <br />
                      <span style="white-space: nowrap">
                        {{ $t("crud.add.body.required.title") }}
                        <span v-if="field.required">{{
                          $t("crud.add.body.required.yes")
                        }}</span
                        ><span v-else>{{
                          $t("crud.add.body.required.no")
                        }}</span>
                      </span>
                      <br />
                      <span class="text-danger" v-for="err in errors[`rows.${index}.field`]">{{err}}</span>
                    </td>
                    <td>
                      <vs-checkbox
                        v-model="field.browse"
                        class="mb-1"
                        style="justify-content: start;"
                        >{{ $t("crud.add.body.browse") }}</vs-checkbox
                      >
                      <vs-checkbox
                        v-model="field.read"
                        class="mb-1"
                        style="justify-content: start;"
                        >{{ $t("crud.add.body.read") }}</vs-checkbox
                      >
                      <vs-checkbox
                        v-model="field.edit"
                        class="mb-1"
                        style="justify-content: start;"
                        >{{ $t("crud.add.body.edit") }}</vs-checkbox
                      >
                      <vs-checkbox
                        v-model="field.add"
                        class="mb-1"
                        style="justify-content: start;"
                        >{{ $t("crud.add.body.add") }}</vs-checkbox
                      >
                      <vs-checkbox
                        v-model="field.delete"
                        class="mb-1"
                        style="justify-content: start;"
                        >{{ $t("crud.add.body.delete") }}</vs-checkbox
                      >
                    </td>
                    <td>
                      <badaso-select
                        size="12"
                        v-model="field.type"
                        :items="componentList"
                        :alert="errors[`rows.${index}.type`]"
                      ></badaso-select>
                    </td>
                    <td>
                      <badaso-text
                        :placeholder="$t('crud.add.body.displayName')"
                        v-model="field.displayName"
                        :alert="errors[`rows.${index}.displayName`]"
                      />
                    </td>
                    <td>
                      <badaso-code-editor
                        v-model="field.details"
                        v-if="field.type !== 'relation'"
                      >
                      </badaso-code-editor>
                      <vs-button
                        color="primary"
                        type="relief"
                        @click.stop
                        @click="openRelationSetup(field)"
                        v-else
                        >{{ $t("crud.add.body.setRelation") }}</vs-button
                      >
                      <vs-popup
                        class="holamundo"
                        :title="$t('crud.add.body.setRelation')"
                        :active.sync="field.setRelation"
                      >
                        <vs-row>
                          <badaso-select
                            size="12"
                            v-model="relation.relationType"
                            :items="relationTypes"
                            :label="$t('crud.add.body.relationType')"
                          ></badaso-select>
                          <vs-col vs-lg="12" class="mb-3">
                            <vs-select
                              :label="$t('crud.add.body.destinationTable')"
                              width="100%"
                              v-model="relation.destinationTable"
                              @input="changeTable"
                            >
                              <vs-select-item
                                :key="index"
                                :value="item.value ? item.value : item"
                                :text="item.label ? item.label : item"
                                v-for="(item, index) in destinationTables"
                              />
                            </vs-select>
                          </vs-col>
                          <badaso-select
                            size="12"
                            v-model="relation.destinationTableColumn"
                            :items="destinationTableColumns"
                            :label="$t('crud.add.body.destinationTableColumn')"
                          ></badaso-select>
                          <badaso-select
                            size="12"
                            v-model="relation.destinationTableDisplayColumn"
                            :items="destinationTableColumns"
                            :label="
                              $t('crud.add.body.destinationTableDisplayColumn')
                            "
                          ></badaso-select>
                        </vs-row>
                        <vs-row vs-type="flex" vs-justify="space-between">
                          <vs-col vs-lg="2" vs-type="flex" vs-align="flex-end">
                            <vs-button
                              class="btn-block"
                              color="danger"
                              @click="field.setRelation = false"
                              type="relief"
                              >{{
                                $t("crud.add.body.cancelRelation")
                              }}</vs-button
                            >
                          </vs-col>
                          <vs-col vs-lg="2" vs-type="flex" vs-align="flex-end">
                            <vs-button
                              class="btn-block"
                              color="primary"
                              @click="saveRelation(field)"
                              type="relief"
                              >{{ $t("crud.add.body.saveRelation") }}</vs-button
                            >
                          </vs-col>
                        </vs-row>
                      </vs-popup>
                    </td>
                  </tr>
                </draggable>
              </table>
            </vs-col>
          </vs-row>
          <vs-row class="show-for-mobile">
            <vs-col col-lg="12" style="overflow-x: auto">
              <draggable v-model="crudData.rows" tag="div">
                <vs-row
                  :key="index"
                  v-for="(field, index) in crudData.rows"
                  style="background-color: white; border: solid 1px #dedede; margin-bottom: 20px; padding: 10px;"
                >
                  <vs-col col-lg="12" class="mb-2">
                    <table class="table">
                      <tr>
                        <td>{{ $t("crud.add.header.field") }}</td>
                        <td>
                          <strong>{{ field.field }}</strong>
                        </td>
                      </tr>
                      <tr>
                        <td>{{ $t("crud.add.body.type") }}</td>
                        <td>{{ field.type }}</td>
                      </tr>
                      <tr>
                        <td>{{ $t("crud.add.body.required.title") }}</td>
                        <td>
                          <span v-if="field.required">{{
                            $t("crud.add.body.required.yes")
                          }}</span
                          ><span v-else>{{
                            $t("crud.add.body.required.no")
                          }}</span>
                        </td>
                      </tr>
                      <tr v-if="errors[`rows.${index}.field`]">
                        <td colspan="2">
                          <span
                            class="text-danger"
                            v-for="err in errors[`rows.${index}.field`]"
                            >{{ err }}</span
                          >
                        </td>
                      </tr>
                      <tr>
                        <td>{{ $t("crud.add.header.visibility") }}</td>
                        <td>
                          <vs-checkbox
                            v-model="field.browse"
                            class="mb-1"
                            style="justify-content: start;"
                            >{{ $t("crud.add.body.browse") }}</vs-checkbox
                          >
                          <vs-checkbox
                            v-model="field.read"
                            class="mb-1"
                            style="justify-content: start;"
                            >{{ $t("crud.add.body.read") }}</vs-checkbox
                          >
                          <vs-checkbox
                            v-model="field.edit"
                            class="mb-1"
                            style="justify-content: start;"
                            >{{ $t("crud.add.body.edit") }}</vs-checkbox
                          >
                          <vs-checkbox
                            v-model="field.add"
                            class="mb-1"
                            style="justify-content: start;"
                            >{{ $t("crud.add.body.add") }}</vs-checkbox
                          >
                          <vs-checkbox
                            v-model="field.delete"
                            class="mb-1"
                            style="justify-content: start;"
                            >{{ $t("crud.add.body.delete") }}</vs-checkbox
                          >
                        </td>
                      </tr>
                      <tr>
                        <td colspan="2">
                          {{ $t("crud.add.header.inputType") }}
                          <badaso-select
                            size="12"
                            v-model="field.type"
                            :items="componentList"
                            :alert="errors[`rows.${index}.type`]"
                          ></badaso-select>
                        </td>
                      </tr>
                      <tr>
                        <td colspan="2">
                          {{ $t("crud.add.body.displayName") }}
                          <badaso-text
                            :placeholder="$t('crud.add.body.displayName')"
                            v-model="field.displayName"
                            :alert="errors[`rows.${index}.displayName`]"
                          />
                        </td>
                      </tr>
                      <tr>
                        <td colspan="2">
                          {{ $t("crud.add.header.optionalDetails") }}
                          <badaso-code-editor
                            v-model="field.details"
                            v-if="field.type !== 'relation'"
                          >
                          </badaso-code-editor>
                          <vs-button
                            color="primary"
                            type="relief"
                            @click.stop
                            @click="openRelationSetup(field)"
                            v-else
                            >{{ $t("crud.add.body.setRelation") }}</vs-button
                          >
                          <vs-popup
                            class="holamundo"
                            :title="$t('crud.add.body.setRelation')"
                            :active.sync="field.setRelation"
                          >
                            <vs-row>
                              <badaso-select
                                size="12"
                                v-model="relation.relationType"
                                :items="relationTypes"
                                :label="$t('crud.add.body.relationType')"
                              ></badaso-select>
                              <vs-col vs-lg="12" class="mb-3">
                                <vs-select
                                  :label="$t('crud.add.body.destinationTable')"
                                  width="100%"
                                  v-model="relation.destinationTable"
                                  @input="changeTable"
                                >
                                  <vs-select-item
                                    :key="index"
                                    :value="item.value ? item.value : item"
                                    :text="item.label ? item.label : item"
                                    v-for="(item, index) in destinationTables"
                                  />
                                </vs-select>
                              </vs-col>
                              <badaso-select
                                size="12"
                                v-model="relation.destinationTableColumn"
                                :items="destinationTableColumns"
                                :label="
                                  $t('crud.add.body.destinationTableColumn')
                                "
                              ></badaso-select>
                              <badaso-select
                                size="12"
                                v-model="relation.destinationTableDisplayColumn"
                                :items="destinationTableColumns"
                                :label="
                                  $t(
                                    'crud.add.body.destinationTableDisplayColumn'
                                  )
                                "
                              ></badaso-select>
                            </vs-row>
                            <vs-row vs-type="flex" vs-justify="space-between">
                              <vs-col
                                vs-lg="2"
                                vs-type="flex"
                                vs-align="flex-end"
                              >
                                <vs-button
                                  color="primary"
                                  @click="saveRelation(field)"
                                  >{{
                                    $t("crud.add.body.saveRelation")
                                  }}</vs-button
                                >
                              </vs-col>
                              <vs-col
                                vs-lg="2"
                                vs-type="flex"
                                vs-align="flex-end"
                              >
                                <vs-button
                                  color="danger"
                                  @click="field.setRelation = false"
                                  >{{
                                    $t("crud.add.body.cancelRelation")
                                  }}</vs-button
                                >
                              </vs-col>
                            </vs-row>
                          </vs-popup>
                        </td>
                      </tr>
                    </table>
                  </vs-col>
                </vs-row>
              </draggable>
            </vs-col>
          </vs-row>
        </vs-card>
      </vs-col>
      <vs-col vs-lg="12">
        <vs-card class="action-card">
          <vs-row>
            <vs-col vs-lg="12">
              <vs-button color="primary" type="relief" @click="submitForm">
                <vs-icon icon="save"></vs-icon> {{ $t("crud.add.button") }}
              </vs-button>
            </vs-col>
          </vs-row>
        </vs-card>
      </vs-col>
    </vs-row>
    <vs-row v-else>
      <vs-col vs-lg="12">
        <vs-card>
          <vs-row>
            <vs-col vs-lg="12">
              <h3>{{ $t("crud.warning.notAllowed") }}</h3>
            </vs-col>
          </vs-row>
        </vs-card>
      </vs-col>
    </vs-row>
  </div>
</template>

<script>
import draggable from "vuedraggable";

export default {
  name: "CrudManagementAdd",
  components: {
    draggable,
  },
  data: () => ({
    errors: {},
    breadcrumb: [],
    errors: {},
    fieldList: [],
    tableColumns: [],
    orderDirections: [
      {
        label: "Ascending",
        value: "asc",
      },
      {
        label: "Descending",
        value: "desc",
      },
    ],
    crudData: {
      name: "",
      slug: "",
      displayNameSingular: "",
      displayNamePlural: "",
      icon: "",
      modelName: "",
      policyName: "",
      description: "",
      generatePermissions: true,
      serverSide: false,
      details: "",
      controller: "",
      orderColumn: "",
      orderDisplayColumn: "",
      orderDirection: "",
      rows: [],
    },
    relationTypes: [],
    destinationTables: [],
    destinationTableColumns: [],
    relation: {
      relationType: "",
      destinationTable: "",
      destinationTableColumn: "",
      destinationTableDisplayColumn: "",
    },
  }),
  computed: {
    componentList: {
      get() {
        return this.$store.getters["badaso/getComponent"];
      },
    },
  },
  mounted() {
    (this.orderDirections = [
      {
        label: this.$t("crud.edit.field.orderDirection.value.ascending"),
        value: "asc",
      },
      {
        label: this.$t("crud.edit.field.orderDirection.value.descending"),
        value: "desc",
      },
    ]),
      (this.crudData.name = this.$route.params.tableName);
    this.crudData.displayNameSingular = this.$helper.generateDisplayName(
      this.$route.params.tableName
    );
    this.crudData.displayNamePlural = this.$helper.generateDisplayNamePlural(
      this.$route.params.tableName
    );
    this.crudData.slug = this.$helper.generateSlug(
      this.$route.params.tableName
    );
    this.getTableDetail();
    this.getRelationTypes();
    this.getDestinationTables();
  },
  methods: {
    openRelationSetup(field) {
      field.setRelation = true;
      this.relation = {
        relationType: field.relationType ? field.relationType : "",
        destinationTable: field.destinationTable ? field.destinationTable : "",
        destinationTableColumn: field.destinationTableColumn
          ? field.destinationTableColumn
          : "",
        destinationTableDisplayColumn: field.destinationTableDisplayColumn
          ? field.destinationTableDisplayColumn
          : "",
      };
      if (field.destinationTable !== "") {
        this.getDestinationTableColumns(field.destinationTable);
      }
    },
    changeTable(table) {
      if (table) {
        this.relation.destinationTableColumn = "";
        this.relation.destinationTableDisplayColumn = "";
        this.getDestinationTableColumns(table);
      }
    },
    saveRelation(field) {
      field.relationType = this.relation.relationType;
      field.destinationTable = this.relation.destinationTable;
      field.destinationTableColumn = this.relation.destinationTableColumn;
      field.destinationTableDisplayColumn = this.relation.destinationTableDisplayColumn;
      this.relation = {};
      field.setRelation = false;
    },
    submitForm() {
      this.errors = {};
      this.$openLoader();
      this.$api.badasoCrud
        .add(this.crudData)
        .then((response) => {
          this.$closeLoader();
          this.$store.commit("badaso/FETCH_MENU");
          this.$store.commit("badaso/FETCH_USER");
          this.$router.push({ name: "CrudManagementBrowse" });
        })
        .catch((error) => {
          this.errors = error.errors;
          this.$closeLoader();
          this.$vs.notify({
            title: this.$t("alert.danger"),
            text: error.message,
            color: "danger",
          });
        });
    },
    getTableDetail() {
      this.$openLoader();
      this.$api.badasoTable
        .read({
          table: this.$route.params.tableName,
        })
        .then((response) => {
          let fieldList = response.data.tableFields;
          this.tableColumns = fieldList;
          this.fieldList = fieldList.map((field) => {
            return {
              label: field.name,
              value: field.name,
            };
          });
          this.crudData.rows = fieldList.map((field) => {
            return {
              field: field.name,
              type: field.type,
              displayName: this.$helper.generateDisplayName(field.name),
              required: field.isNotNull,
              browse: true,
              read: true,
              edit: false,
              add: false,
              delete: false,
              details: "{}",
              order: 1,
              setRelation: false,
            };
          });
          this.$closeLoader();
        })
        .catch((error) => {
          this.$closeLoader();
        });
    },
    getRelationTypes() {
      this.$openLoader();
      this.$api.badasoData
        .tableRelations()
        .then((response) => {
          this.$closeLoader();
          this.relationTypes = response.data.tableRelations;
        })
        .catch((error) => {
          this.$closeLoader();
          this.$vs.notify({
            title: this.$t("alert.danger"),
            text: error.message,
            color: "danger",
          });
        });
    },
    getDestinationTables() {
      this.$openLoader();
      this.$api.badasoTable
        .browse()
        .then((response) => {
          this.$closeLoader();
          this.destinationTables = response.data.tables;
        })
        .catch((error) => {
          this.$closeLoader();
          this.$vs.notify({
            title: this.$t("alert.danger"),
            text: error.message,
            color: "danger",
          });
        });
    },
    getDestinationTableColumns(table) {
      this.$openLoader();
      this.$api.badasoTable
        .read({
          table,
        })
        .then((response) => {
          this.$closeLoader();
          this.destinationTableColumns = response.data.tableFields;
        })
        .catch((error) => {
          this.$closeLoader();
          this.$vs.notify({
            title: this.$t("alert.danger"),
            text: error.message,
            color: "danger",
          });
        });
    },
  },
};
</script>

<style>
.drag_icon:hover {
  cursor: all-scroll;
}
</style>
