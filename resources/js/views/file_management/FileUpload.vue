<template>
  <v-container fluid class="qr-upload-page pa-0 px-2">
    <v-row justify="center">
      <v-col cols="12" sm="10" md="8" lg="6">
        <!-- Title Input -->
        <v-text-field
          v-model="file_title"
          label="File Title"
          placeholder="Enter a title for this file"
          outlined
          dense
          class="mt-3"
          :disabled="!files.length"
        />

        <!-- Upload Box -->
        <div class="upload-box text-center" @dragover.prevent @drop.prevent="onDrop">
          <input
            ref="fileInput"
            type="file"
            hidden
            @change="onFileChange"
            :accept="acceptedExtensions"
          />

          <div class="upload-ui" @click="browse">
            <v-icon large class="mb-2">mdi-cloud-upload-outline</v-icon>
            <h3>Select your files</h3>
            <p>Accepted file types: {{ acceptedExtensions.replace(/,/g, ', ') }}</p>
            <v-btn color="primary" class="mt-2">Browse</v-btn>
          </div>
        </div>

        <!-- Selected Files Preview -->
        <v-card outlined class="mt-4" v-if="files.length">
          <v-list dense>
            <v-list-item>
              <v-list-item-icon class="mr-2 pr-0">
                <v-icon>{{ fileIcon(files[0]) }}</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title class="file-name pb-2">
                  {{ files[0].name }}
                </v-list-item-title>
              </v-list-item-content>
              <v-list-item-action>
                <v-btn icon color="red" dark small @click="removeFile(0)" title="Remove">
                  <v-icon>mdi-close</v-icon>
                </v-btn>
              </v-list-item-action>
            </v-list-item>
          </v-list>
        </v-card>

        <!-- Upload Button -->
        <v-btn
          class="mt-3"
          color="primary"
          :disabled="!files.length"
          :loading="loading"
          @click="upload"
          block
        >
          Upload
        </v-btn>
      </v-col>
    </v-row>
    <v-dialog
      v-model="loading"
      hide-overlay
      persistent
      width="350"
    >
      <v-card
        color="primary"
        dark
      >
        <v-card-text>
          <p class="text-center pt-4">
            Uploading Files. Please stand by...
          </p>
          <v-progress-linear
            indeterminate
            color="white"
            class="mb-0"
          ></v-progress-linear>
        </v-card-text>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      loading: true,
      valid: false,
      token: null,
      user_id: null,
      branch_id: null,
      files: [],
      file_title: '', // new title input   
      acceptedFiles: {
        pdf: { icon: 'mdi-file-pdf' },
        ods: { icon: 'mdi-file-excel' },
        xls: { icon: 'mdi-file-excel' },
        xlsx: { icon: 'mdi-file-excel' },
        doc: { icon: 'mdi-file-word' },
        docx: { icon: 'mdi-file-word' },
        jpg: { icon: 'mdi-file-image' },
        png: { icon: 'mdi-file-image' }
      },
      loading: false,
    };
  },
  computed: {
    acceptedExtensions() {
      return Object.keys(this.acceptedFiles).map(e => '.' + e).join(',');
    }
  },
  mounted() {
    this.token = this.$route.params.token;
    this.validateToken();
  },
  methods: {
    validateToken() {
      axios.get(`${this.$apiBaseUrl}/api/validate-token/${this.token}`)
        .then(res => {
          this.valid = true;
          this.user_id = res.data.user_id;
          this.branch_id = res.data.branch_id;
        })
        .catch(() => { this.valid = false; })
        .finally(() => { this.loading = false; });
    },
    browse() {
      this.$refs.fileInput.click();
    },
    onDrop(e) {
      this.addFiles(e.dataTransfer.files);
    },
    onFileChange(e) {
      // this.addFiles(e.target.files);
      this.files = e.target.files;
    },
    // addFiles(fileList) {
    //   const newFiles = Array.from(fileList).filter(file =>
    //     Object.keys(this.acceptedFiles).includes(file.name.split('.').pop().toLowerCase())
    //   );

    //   this.files.push(...newFiles);
    // },
    removeFile(index) {
      // this.files.splice(index, 1);
      this.files = [];
    },
    fileIcon(file) {
      const ext = file.name.split('.').pop().toLowerCase();
      return this.acceptedFiles[ext]?.icon || 'mdi-file';
    },
    async upload() {
      if (!this.files.length) return;
      if (!this.file_title.trim()) {
        return this.showAlert('Please enter a file title', 'warning');
      }

      const form = new FormData();
      form.append('file', this.files[0]); // single file
      form.append('file_title', this.file_title); // append the title
      form.append('token', this.token);
      
      this.loading = true;  

      try {
        const res = await axios.post(`${this.$apiBaseUrl}/api/file-upload`, form, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
        console.log(res);
        
        if (res.data.success) {
          this.showAlert(`File uploaded successfully!`, 'success');
          this.files = [];
          this.file_title = ''; // reset title
        } else {
          this.showAlert('Error uploading file', 'error');
        }

        this.loading = false;
      } catch (err) {
        console.error(err);
        this.showAlert('Error uploading file', 'error');
      }
    },
    showAlert(msg, icon) {
      this.$swal({
        position: 'center',
        icon,
        title: msg,
        showConfirmButton: false,
        timer: 2500
      });
    }
  }
};
</script>

<style scoped>
.qr-upload-page {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
}
.upload-box {
  border: 2px dashed #ccc;
  padding: 60px;
  text-align: center;
  cursor: pointer;
}
.file-name {
  max-width: 230px; /* fix width */
  white-space: nowrap; /* prevent wrapping */
  overflow: hidden; /* hide overflow */
  text-overflow: ellipsis; /* add ... for long names */
}
</style>
