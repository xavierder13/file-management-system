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
            File Lists
            <v-spacer></v-spacer>
            <v-text-field
              v-model="search"
              append-icon="mdi-magnify"
              label="Search"
              single-line
            ></v-text-field>
          </v-card-title>
          <v-data-table
            :headers="headers"
            :items="files"
            :search="search"
            :loading="loading"
            loading-text="Loading... Please wait"
          >
          </v-data-table>
        </v-card>
      </v-main>
    </div>
  </div>
</template>
<script>

import axios from "axios";
import { validationMixin } from "vuelidate";
import { required, maxLength, email } from "vuelidate/lib/validators";
import { mapState, mapGetters } from 'vuex';

export default {

  mixins: [validationMixin],

  validations: {
  },
  data() {
    return {
      search: "",
      headers: [
        { text: "Name", value: "name" },
        { text: "Type", value: "type" },
        { text: "Size", value: "size" },
        { text: "Last Modified", value: "modified" },
        { text: "Actions", value: "actions", sortable: false }
      ],
      disabled: false,
      dialog: false,
      files: [],
      editedIndex: -1,
      editedPermission: {
        name: "",
      },
      defaultItem: {
        name: "",
      },
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/",
        },
        {
          text: "File Lists",
          disabled: true,
        },
      ],
      loading: true,
    };
  },

  methods: {
    getFiles() {
      this.loading = true;
      axios.get("/api/file-explorer").then(
        (response) => {
            console.log(response.data);
            
          this.files = response.data.files;
          this.loading = false;
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },
  },
  computed: {
    ...mapGetters("userRolesPermissions", ["hasRole", "hasPermission"]),
  },
  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    this.getFiles();
  },
};
</script>