<template>
  <div class="flex column">
    <div id="_wrapper" class="pa-5">
      <v-main>
        <v-breadcrumbs :items="items">
          <template v-slot:item="{ item }">
            <v-breadcrumbs-item :to="item.link" :disabled="item.disabled">
              {{ item.text.toUpperCase() }}
            </v-breadcrumbs-item>
          </template>
        </v-breadcrumbs>
        <v-card>
          <v-card-title>
            Position Lists
            <v-spacer></v-spacer>
            <v-text-field
              v-model="search"
              append-icon="mdi-magnify"
              label="Search"
              single-line
              v-if="hasPermission('position-list')"
            ></v-text-field>
            <template>
              <v-toolbar flat>
                <v-spacer></v-spacer>
                <v-btn
                  color="primary"
                  fab
                  dark
                  class="mb-2"
                  @click="clear() + (dialog = true)"
                  v-if="hasPermission('position-create')"
                >
                  <v-icon>mdi-plus</v-icon>
                </v-btn>
                <v-dialog v-model="dialog" max-width="500px" persistent>
                  <v-card>
                    <v-card-title class="pa-4">
                      <span class="headline">{{ formTitle }}</span>
                    </v-card-title>
                    <v-divider class="mt-0"></v-divider>
                    <v-card-text>
                      <v-row>
                        <v-col>
                          <v-row>
                            <v-col class="my-0 py-0">
                              <v-text-field
                                name="position"
                                v-model="editedItem.name"
                                label="Position"
                                required
                                :error-messages="positionErrors + positionError.name"
                                @input="$v.editedItem.name.$touch() + (positionError.name = [])"
                                @blur="$v.editedItem.name.$touch()"
                              ></v-text-field>
                            </v-col>
                          </v-row>
                          <v-row>
                            <v-col class="my-0 mb-1 py-0">
                              <v-autocomplete
                                name="department"
                                :items="departments"
                                item-text="name"
                                item-value="id"
                                v-model="editedItem.department_id"
                                label="Department"
                                required
                                :error-messages="departmentErrors + positionError.department_id"
                                @input="$v.editedItem.department_id.$touch() + (positionError.department_id = [])"
                                @blur="$v.editedItem.department_id.$touch()"
                              ></v-autocomplete>
                            </v-col>
                          </v-row>
                        </v-col>
                      </v-row>
                    </v-card-text>
                    <v-divider class="mb-3 mt-0"></v-divider>
                    <v-card-actions class="pa-0">
                      <v-spacer></v-spacer>
                      <v-btn color="#E0E0E0" @click="close" class="mb-3">
                        Cancel
                      </v-btn>
                      <v-btn
                        color="primary"
                        @click="save"
                        class="mb-3 mr-4"
                        :disabled="disabled"
                      >
                        Save
                      </v-btn>
                    </v-card-actions>
                  </v-card>
                </v-dialog>
              </v-toolbar>
            </template>
          </v-card-title>
          
          <v-data-table
            :headers="headers"
            :items="positions"
            :search="search"
            :loading="loading"
            loading-text="Loading... Please wait"
            v-if="hasPermission('position-list')"
          >
            <template v-slot:item.actions="{ item }">
              <v-icon
                small
                class="mr-2"
                color="green"
                @click="editPosition(item)"
                v-if="hasPermission('position-edit')"
              >
                mdi-pencil
              </v-icon>
              <v-icon
                small
                color="red"
                @click="showConfirmAlert(item)"
                v-if="hasPermission('position-delete')"
              >
                mdi-delete
              </v-icon>
            </template>
          </v-data-table>
        </v-card>
      </v-main>
    </div>
  </div>
</template>
<style scoped>
  table {
    width: 100%;
  }

  thead, tbody, tr, td, th { display: block; }

  tr:after {
      content: ' ';
      display: block;
      visibility: hidden;
      clear: both;
  }

  tbody {
      height: calc(55vh - 135px);
      overflow-y: auto;
  }

  .employee-per-branch-table tbody {
      height: calc(65vh - 135px) !important;
      overflow-y: auto;
  }

  tbody td, thead th {
      float: left;
  }

  .centered-input >>> input {
    text-align: center
  }
</style>
<script>

import axios from "axios";
import { validationMixin } from "vuelidate";
import { required, maxLength, email } from "vuelidate/lib/validators";
import { mapState, mapGetters } from "vuex";
import LoginVue from '../../auth/Login.vue';

export default {

  mixins: [validationMixin],

  validations: {
    editedItem: {
      name: { required },
      department_id: { required },
    },
  },
  data() {
    return {
      search: "",
      headers: [
        { text: "Position", value: "name" },
        { text: "Department", value: "department.name" },
        { text: "Actions", value: "actions", sortable: false, width: "80px" },
      ],
      subordinate_headers: [
        { text: "Position", value: "name" },
      ],
      switch1: true,
      disabled: false,
      dialog: false,
      positions: [],
      branches: [],
      departments: [],
      editedIndex: -1,
      editedItem: {
        name: "",
        department_id: "",
      },
      defaultItem: {
        name: "",
        department_id: "",
      },
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/",
        },
        {
          text: "Position Lists",
          disabled: true,
        },
      ],
      loading: true,
      positionError: {
        name: [],
        department_id: [],
      },
      search_position: "",
    };
  },

  methods: {
    getPosition() {
      this.loading = true;
      axios.get(this.$apiBaseUrl + "/api/position/index").then(
        (response) => {
          this.loading = false;
          let data = response.data;

          this.positions = data.positions;
          this.branches = data.branches;
          this.departments = data.departments;
            
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    editPosition(item) {
      
      this.editedIndex = this.positions.indexOf(item);

      Object.assign(this.editedItem, item);
      
      this.dialog = true;
      this.switch1 = true;
      if(this.editedItem.active === 'N')
      {
        this.switch1 = false;
      }

    },

    deletePosition(position_id) {
      const data = { position_id: position_id };
      this.loading = true;
      axios.post(this.$apiBaseUrl + "/api/position/delete", data).then(
        (response) => {
          if (response.data.success) {
            // send data to Sockot.IO Server
            // this.$socket.emit("sendData", { action: "position-delete" });
          }
          this.loading = false;
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    showAlert() {
      this.$swal({
        position: "center",
        icon: "success",
        title: "Record has been saved",
        showConfirmButton: false,
        timer: 2500,
      });
    },

    showConfirmAlert(item) {
      this.$swal({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Delete record!",
      }).then((result) => {
        // <--

        if (result.value) {
          // <-- if confirmed

          const position_id = item.id;
          const index = this.positions.indexOf(item);

          //Call delete Position function
          this.deletePosition(position_id);

          //Remove item from array positions
          this.positions.splice(index, 1);

          this.$swal({
            position: "center",
            icon: "success",
            title: "Record has been deleted",
            showConfirmButton: false,
            timer: 2500,
          });
        }
      });
    },

    close() {
      this.dialog = false;
      this.clear();
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      });
    },

    save() {
      this.$v.$touch();
      this.positionError = {
        name: [],
        rank_id: [],
        cost_center: [],
        department_id: [],
      };

      let api = "";
      Object.assign(this.editedItem, { branchRequirement: this.branchRequirement });
      const data = this.editedItem;
      
      if (!this.$v.$error) {
        this.disabled = true;

        if (this.editedIndex > -1) {
          const position_id = this.editedItem.id;
          api = "/api/position/update/" + position_id;
        } else {
          api = "/api/position/store";
        }

        axios.post(this.$apiBaseUrl + api, data).then(
          (response) => {
            
            this.disabled = false;
            let data = response.data;
            console.log(data);
            
            if (data.success) {
              // send data to Sockot.IO Server
              // this.$socket.emit("sendData", { action: "position-create" });

              this.showAlert();
              this.close();

              if (this.editedIndex > -1) 
              {
                Object.assign(
                  this.positions[this.editedIndex],
                  data.position
                );
              }
              else
              {
                //push recently added data from database
                this.positions.push(data.position);
              }
              
            }
            else
            { 
              let errors = data;
              let errorNames = Object.keys(data);

              errorNames.forEach(value => {

                this.positionError[value].push(errors[value]);
                
              });
              
            }
            
          },
          (error) => {
            this.disabled = false;
            this.isUnauthorized(error);
          }
        );
      }
    },

    clear() {
      this.$v.$reset();
      this.editedItem.name = "";
      this.editedItem.department_id = "";
      this.positionError = {
        name: [],
        department_id: [],
      };
      this.search_position = "";
    },

    isUnauthorized(error) {
      // if unauthenticated (401)
      if (error.response.status == "401") {
        this.$router.push({ name: "unauthorize" });
      }
    },

    websocket() {
      // Socket.IO fetch data
      this.$options.sockets.sendData = (data) => {
        let action = data.action;
        if (
          action == "position-create" ||
          action == "position-edit" ||
          action == "position-delete"
        ) {
          this.getPosition();
        }
      };
    },
  },
  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "New Position" : "Edit Position";
    },
    positionErrors() {
      const errors = [];
      if (!this.$v.editedItem.name.$dirty) return errors;
      !this.$v.editedItem.name.required &&
        errors.push("Position is required.");
      return errors;
    },
    departmentErrors() {
      const errors = [];
      if (!this.$v.editedItem.department_id.$dirty) return errors;
      !this.$v.editedItem.department_id.required &&
        errors.push("Department is required.");
      return errors;
    },
    activeStatus() {
      if (this.switch1) {
        this.editedItem.active = "Y";
        return " Active";
      } else {
        this.editedItem.active = "N";
        return " Inactive";
      }
    },
    ...mapGetters("userRolesPermissions", ["hasRole", "hasPermission"]),
  },
  watch: {
  },
  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    this.getPosition();
    // this.websocket();
  },
};
</script>