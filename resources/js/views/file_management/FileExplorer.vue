<template>
  <div class="flex column">
    <div id="_wrapper" class="pa-5">
      <v-main>
        <v-container fluid>
          <v-row>
            <!-- LEFT: Folder Tree -->
            <v-col cols="3">
              <v-card outlined>
                <v-toolbar dense flat color="primary" dark>
                  <v-icon left>mdi-folder</v-icon>
                  <span class="font-weight-bold">Folders</span>
                </v-toolbar>

                <v-divider class="my-0 py-0"/>

                <v-list dense nav>
                  <v-list-item
                    v-for="folder in folders"
                    :key="folder"
                    @click="selectFolder(folder)"
                    :class="{'blue lighten-4': currentFolder === folder, 'hoverable-folder': true}"
                  >
                    <v-list-item-icon>
                      <v-icon color="amber">mdi-folder</v-icon>
                    </v-list-item-icon>
                    <v-list-item-title class="font-weight-bold">
                      {{ folderName(folder) }}
                    </v-list-item-title>
                  </v-list-item>
                </v-list>
              </v-card>
            </v-col>

            <!-- RIGHT: Files -->
            <v-col cols="9">
              <v-card outlined class="pa-0">
                <v-data-table
                  :headers="headers"
                  :items="filteredFiles"
                  :search="search"
                  :loading="loading"
                  disable-pagination
                  v-model="selectedItems"
                  item-key="id"
                  show-select
                  hide-default-footer
                  dense
                  class="elevation-2 file-table"
                >
                  <!-- Toolbar -->
                  <template v-slot:top>
                    <v-toolbar flat dense class="file-toolbar">
                      <v-tooltip top>
                        <template v-slot:activator="{ on, attrs }">
                          <v-btn 
                            small 
                            color="#AB47BC" 
                            fab rounded 
                            class="mr-2 white--text"
                            @click="getFiles()"
                            :loading="loading"
                            v-bind="attrs"
                            v-on="on"
                          >
                            <v-icon>mdi-refresh</v-icon>
                          </v-btn>
                        </template>
                        <span>Refresh Data</span>
                      </v-tooltip>

                      <v-tooltip top v-if="hasPermission('file-delete')">
                        <template v-slot:activator="{ on, attrs }">
                          <v-btn 
                            small 
                            color="error" 
                            fab rounded 
                            :disabled="!selectedItems.length"
                            @click="confirmDelete('multiple-delete', selectedItems)"
                            class="mr-2"
                            v-bind="attrs"
                            v-on="on"
                          >
                            <v-icon>mdi-delete</v-icon>
                          </v-btn>
                        </template>
                        <span>Delete Selected Row(s)</span>
                      </v-tooltip>

                      <v-spacer></v-spacer>

                      <v-autocomplete
                        v-model="search_branch"
                        :items="branchItems"
                        item-text="name"
                        item-value="id"
                        label="Filter Branch"
                        outlined
                        dense
                        hide-details
                        class="mx-2"
                        color="primary"
                      />

                      <v-text-field
                        v-model="search"
                        append-icon="mdi-magnify"
                        label="Search"
                        outlined
                        dense
                        hide-details
                        class="mx-2"
                        color="primary"
                      />
                    </v-toolbar>
                    <v-divider class="mt-2"></v-divider>
                  </template>

                  <!-- Row Selection Checkbox -->
                  <template v-slot:header.data-table-select="{ props, on }">
                    <v-simple-checkbox
                      v-model="selectAll"
                      v-on="on"
                      :indeterminate="indeterminate"
                      color="primary"
                    />
                  </template>

                  <template v-slot:item.data-table-select="{ isSelected, select }">
                    <v-simple-checkbox 
                      v-model="isSelected" 
                      color="primary" 
                      v-ripple 
                      @input="select($event)"
                    />
                  </template>

                  <!-- File Name -->
                  <template v-slot:item.title="{ item }">
                    <v-icon small class="mr-2" :color="fileIcon(item.file_type).color">
                      {{ fileIcon(item.file_type).icon }}
                    </v-icon>
                    <span class="file-name">{{ item.title || item.file_name }}</span>
                  </template>

                  <!-- Actions -->
                  <template v-slot:item.actions="{ item }">
                    <v-btn icon @click="download(item)" title="Download">
                      <v-icon color="primary">mdi-download</v-icon>
                    </v-btn>
                    <v-btn
                      icon
                      @click="confirmDelete('single-delete', item)"
                      title="Delete"
                      v-if="hasPermission('file-delete')"
                    >
                      <v-icon color="red">mdi-delete</v-icon>
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

<style scoped>
.hoverable-folder:hover {
  background-color: #f3e5f5; /* light purple hover */
  cursor: pointer;
}

.file-name {
  max-width: 300px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.file-table tbody tr:hover {
  background-color: #f5f5f5;
}

.file-toolbar {
  padding: 4px 8px;
}

.v-data-table .v-data-table__checkbox {
  color: #AB47BC !important; /* primary purple checkboxes */
}
</style>


<script>

import axios from "axios";
import { watch } from "vue";
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
        { text: 'Name', value: 'title' },
        { text: 'Type', value: 'file_type', width: 120 },
        { text: 'Date Modified', value: 'date_modified', width: 180 },
        { text: 'Branch', value: 'branch.name', width: 300 },
        { text: '', value: 'actions', width: 120 }
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
      search: "",
      search_branch: "",
      branches: [],
      selectedItems: [],
      selectAll: false,
      indeterminate: false,
    };
  },

  methods: {
    getFiles() {
      this.loading = true;
      axios.get(this.$apiBaseUrl + "/api/file-manager/index").then(
        (response) => {
            console.log(response.data);
          let data = response.data;
          this.files = data.user_files;
          this.branches = data.branches;
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
        pdf: { icon: 'mdi-file-pdf', color: 'red' },
        ods: { icon: 'mdi-file-excel', color: 'green' },
        xls: { icon: 'mdi-file-excel', color: 'green' },
        xlsx: { icon: 'mdi-file-excel', color: 'green' },
        doc: { icon: 'mdi-file-word', color: 'blue' },
        docx: { icon: 'mdi-file-word', color: 'blue' },
        jpg: { icon: 'mdi-file-image', color: 'secondary' },
        png: { icon: 'mdi-file-image', color: 'secondary' },
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

    confirmDelete(file, type){
      this.$swal({
        title: "Delete File",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Delete",
      }).then((result) => {
        // <--

        if (result.value) {
  
          this.deleteFile(file);
          
        }
      });
    },

    deleteFile(file, type) {
      const items = type === 'single-delete' ? [file] : this.selectedItems.map(value => value.id);
      const data = { deleted_items: items };
      
      axios.post(this.$apiBaseUrl + "/api/file-manager/file-delete", data).then(
        (response) => {
          console.log(response);
          if (response.data.success) {

            if(type == 'single-delete')
            {
              let index = this.files.indexOf(file);
              this.files.splice(index, 1);
            }
            else
            {
              this.getFiles();
              this.indeterminate = false;
            }

            this.$swal({
              position: "center",
              icon: "success",
              title: response.data.success,
              showConfirmButton: false,
              timer: 2500,
            });
            

          }
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

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

    branchItems() {
      return [{ name: "ALL BRANCHES", id: 0 }, ...this.branches];
    },

    filteredFiles() {
      return this.files.filter(
        f => f.file_path === this.currentFolder && (this.search_branch == 0 || f.branch_id === this.search_branch)
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
  watch: {
    selectAll() {
      if(this.selectAll)
      {
        this.selectedItems = this.files;
      }
      else if(!this.indeterminate && !this.selectAll)
      {
        this.selectedItems = [];
      }
    },  
    selectedItems() {
      console.log('asad');
      
      let selectedItemsID = this.selectedItems.map(value => value.id);
      let filesID = this.files.map(value => value.id);
      this.selectAll = false;
      this.indeterminate = false;
      if(selectedItemsID.length == filesID.length && filesID.length != 0)
      {
        this.selectAll = true;
      }
      else if(selectedItemsID.length > 0 && filesID.length != selectedItemsID.length)
      {
        this.indeterminate = true;
      }
      console.log(selectedItemsID);
      console.log(filesID.length);
      
      

    },
    files() {
      if(this.files.length == 0)
      {
        this.selectAll = false;
        this.indeterminate = false;
      }
    },
  },
  async mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    await this.getFiles();
    this.currentFolder = this.folders[0] || null;
  },
};
</script>