<template>
  <div class="qr-upload-page">
    <div v-if="loading">Validating token...</div>

    <div v-else-if="!valid">
      <h2>Invalid or expired QR</h2>
    </div>

    <div v-else class="upload-box"
         @dragover.prevent
         @drop.prevent="onDrop">

      <input
        ref="fileInput"
        type="file"
        hidden
        @change="onFileChange"
      />

      <div class="upload-ui" @click="browse">
        <i class="icon-cloud"></i>
        <h3>Select your file or drag and drop</h3>
        <p>png, pdf, jpg, docx accepted</p>
        <button>Browse</button>
      </div>

    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      loading: true,
      valid: false,
      token: null,
      user_id: null,
      branch_id: null,
      file: null
    };
  },

  mounted() {
    this.token = this.$route.params.token;
    this.validateToken();
  },

  methods: {
    validateToken() {
      axios.get(this.$apiBaseUrl + `/api/validate-token/${this.token}`)
        .then(res => {
          this.valid = true;
          this.user_id = res.data.user_id;
          this.branch_id = res.data.branch_id;
        })
        .catch(() => {
          this.valid = false;
        })
        .finally(() => {
          this.loading = false;
        });
    },

    browse() {
      this.$refs.fileInput.click();
    },

    onDrop(e) {
      this.file = e.dataTransfer.files[0];
      this.upload();
    },

    onFileChange(e) {
      this.file = e.target.files[0];
      this.upload();
    },

    upload() {
      let form = new FormData();
      form.append("file", this.file);
      form.append("token", this.token);

      axios.post(this.$apiBaseUrl + "/api/file-upload", form).then(
        (response) => {
          console.log(response);
          if(response.data.success)
          {
            this.showAlert("File has been uploaded", "success");
          }
          else
          {
            this.showAlert("Error uploading file", "error");
          }
          
        },
        (error) => {
          console.log(error);
          
          this.showAlert("Error uploading file", "error");
        })
    // ).catch(() => {
    //     this.showAlert("Upload failed!", "error");
    //   });
    },
    showAlert(msg, icon) {
      this.$swal({
        position: "center",
        icon: icon,
        title: msg,
        showConfirmButton: false,
        timer: 2500,
      });
    },
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
button {
  background: #7c83ff;
  color: white;
  padding: 10px 20px;
  border-radius: 6px;
}
</style>
