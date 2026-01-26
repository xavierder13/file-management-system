<template>
  <div class="flex column">
    <div id="_wrapper" class="pa-5">
      <v-main>
        <v-container fluid>
        <!-- Breadcrumb -->
        <v-breadcrumbs :items="breadcrumbs" class="mb-2" />
        <v-row>
          <!-- LEFT: Folder Tree -->
          <v-col cols="3">
            <v-card outlined>
              <v-toolbar dense flat>
                <v-icon left>mdi-folder</v-icon>
                <span>Folders</span>
              </v-toolbar>

              <v-divider class="my-0 py-0"/>

              <v-list dense nav>
                <v-list-item
                  v-for="folder in folders"
                  :key="folder"
                  @click="selectFolder(folder)"
                  :class="{ 'grey lighten-3': currentFolder === folder }"
                >
                  <v-list-item-icon>
                    <v-icon color="amber">mdi-folder</v-icon>
                  </v-list-item-icon>
                  <v-list-item-title>
                    {{ folderName(folder) }}
                  </v-list-item-title>
                </v-list-item>
              </v-list>
            </v-card>
          </v-col>

          <!-- RIGHT: Files -->
          <v-col cols="9">
            <v-card outlined>
              <v-data-table
                :headers="headers"
                :items="filteredFiles"
                dense
                class="elevation-0"
                disable-pagination
                hide-default-footer
              >
                <!-- File Name -->
                <template v-slot:item.name="{ item }">
                  <v-icon small class="mr-2">
                    {{ fileIcon(item.file_type) }}
                  </v-icon>
                  {{ item.title || item.file_name }}
                </template>

                <!-- Download -->
                <template v-slot:item.actions="{ item }">
                  <v-btn
                    icon
                    @click="download(item)"
                    title="Download"
                  >
                    <v-icon color="primary">mdi-download</v-icon>
                  </v-btn>
                </template>
              </v-data-table>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
      </v-main>
    </div>
  </div>
</template>

<script>

import axios from "axios";
import { validationMixin } from "vuelidate";
import { mapState, mapGetters } from 'vuex';

export default {

  mixins: [validationMixin],

  validations: {
  },
  data() {
    return {
      search: "",
      currentFolder: null,

      headers: [
        { text: 'Name', value: 'name' },
        { text: 'Type', value: 'file_type', width: 120 },
        { text: 'Date Modified', value: 'date_modified', width: 180 },
        { text: '', value: 'actions', width: 60 }
      ],
      disabled: false,
      dialog: false,
      files: [],
      editedIndex: -1,
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/",
        },
        {
          text: "File Explorer",
          disabled: true,
        },
      ],
      loading: true,
    };
  },

  methods: {
    getFiles() {
      this.loading = true;
      axios.get(this.$apiBaseUrl + "/api/file-manager/index").then(
        (response) => {
            console.log(response.data);
            
          this.files = response.data.user_files;
          this.loading = false;
        },
        (error) => {
          // this.isUnauthorized(error);
        }
      );
    },
    
    folderName(path) {
      return path.split('/').pop()
    },

    fileIcon(type) {
      const map = {
        pdf: 'mdi-file-pdf',
        ods: 'mdi-file-excel',
        xls: 'mdi-file-excel',
        xlsx: 'mdi-file-excel',
        doc: 'mdi-file-word',
        docx: 'mdi-file-word',
        jpg: 'mdi-file-image',
        png: 'mdi-file-image'
      }
      return map[type] || 'mdi-file'
    },
    download(file){
      // const file = applicant_file;
      
      const data = { file_id: file.id };

      axios.post(this.$apiBaseUrl + `/api/file-manager/file-download/${file.id}`, data, { responseType: 'arraybuffer'})
        .then((response) => {
            var fileURL = window.URL.createObjectURL(new Blob([response.data]));
            var fileLink = document.createElement('a');
            fileLink.href = fileURL;
            fileLink.setAttribute('download', file.title + '.' + file.file_type);
            document.body.appendChild(fileLink);
            fileLink.click();
        }, (error) => {
          console.log(error);
        }
      );
    },
    // download(file) {
    //   window.open(this.$apiBaseUrl + `/api/file-manager/file-download/${file.id}`, '_blank')
    // },

    selectFolder(folder) {
      this.currentFolder = folder
    },

    isUnauthorized(error) {
      // if unauthenticated (401)
      if (error.response.status == "401") {
        this.$router.push({ name: "unauthorize" });
      }
    },
  },
  computed: {
    folders() {
      const paths = this.files.map(file => file.file_path)
      const uniquePaths = paths.filter(
        (path, index) => paths.indexOf(path) === index
      )

      return uniquePaths
    },

    filteredFiles() {
      return this.files.filter(
        f => f.file_path === this.currentFolder
      )
    },

    breadcrumbs() {
      if (!this.currentFolder) return []

      const parts = this.currentFolder.split('/').filter(Boolean)
      let path = ''

      return parts.map(p => {
        path += '/' + p
        return {
          text: p,
          disabled: false
        }
      })
    },
    ...mapGetters("userRolesPermissions", ["hasRole", "hasPermission"]),
  },
  async mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    await this.getFiles();
    this.currentFolder = this.folders[0] || null;
  },
};
</script>