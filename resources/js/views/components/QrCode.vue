<template>
  
  <!-- QR Code Dialog -->
  <v-dialog v-model="dialog_qr_code" max-width="420">
    <v-card>
      <v-card-title class="pa-4 text-center">
        <span class="headline">
          QR Code
        </span>
        <v-spacer></v-spacer>
        <v-icon @click="dialog_qr_code = false">mdi-close-circle</v-icon>
      </v-card-title>
      <v-divider class="mt-0 pt-0"></v-divider>
      <v-card-text class="text-center">
        <!-- QR code SVG -->
        <template v-if="qr_code">
          <span :class="(isQrExpired ? 'red--text text--darken-2' : '') + 'subtitle-1'">
            (Expiration: <strong>{{ expires_at }}</strong>)
          </span>
          <div ref="qrContainer" v-html="qr_code" :key="qrComponentKey"></div>
        </template>
        <div v-if="!qr_code" style="display: inline-block; width: 350px; height: 300px; border: 1px solid #ccc; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
          <span style="color: #999;" class="font-weight-bold subtitle-1">Please generate QR Code</span>
        </div>
      </v-card-text>

      <v-card-actions class="justify-center">
        <v-btn class="mb-2" outlined color="primary" @click="generateQrCode()">Generate</v-btn>
        <v-btn class="mb-2" color="primary" @click="downloadQrCode()">Download</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>

</template>

<script>
import axios from "axios";
import QRCode from 'qrcode';

export default {
  props: ['user'],
  data() {
    return {
      dialog_qr_code: false,
      qr_code: "",
      qr_url: "",
      qr_expiration: "",
      qrComponentKey: 1,
      expires_at: "",
    };
  },

  methods: {
    async viewQrCode() {
      this.dialog_qr_code = true; 
      this.qr_code = ''; 
      
      try {
        const response = await axios.post(this.$apiBaseUrl + '/api/user/view-qr-token', this.user);
        this.qr_url = response.data.qr_url;
        this.expires_at = response.data.expires_at;
        console.log(this.qr_url);
        
        if(this.qr_url)
        {
          // Generate SVG QR code
          this.qr_code = await QRCode.toString(this.qr_url, {
            type: 'svg',
            errorCorrectionLevel: 'H',
          });
        } 

      } catch (error) {
        console.error('Error generating QR code:', error);
      }
    },
    async generateQrCode() {
      const generateQr = async () => {
        try {
          const response = await axios.post(this.$apiBaseUrl + '/api/user/generate-qr-token', this.user);
          console.log(response.data);
          
          this.qr_url = await response.data.qr_url;
          this.expires_at = response.data.expires_at;
  
          // Generate SVG QR code
          this.qr_code = await QRCode.toString(this.qr_url, {
            type: 'svg',
            errorCorrectionLevel: 'H',
          });

        } catch (error) {
          console.error('Error generating QR code:', error);  
        }
      };

      if (this.qr_code) { 
        // Ask for confirmation
        const result = await this.$swal({
          title: "Are you sure?",
          text: "Generating a new QR code will invalidate the old one.",
          icon: "warning",
          showCancelButton: true,
          cancelButtonColor: "#6c757d",
          confirmButtonColor: "#1976d2", 
          confirmButtonText: "Yes, generate",
        });

        if (result.isConfirmed) {
          await generateQr();
        }
      } else {
        // No existing QR code, just generate
        await generateQr();
      }
    },
    // Download the QR code as an SVG file
    downloadQrCode() {
      if (!this.qr_code) return;

      const blob = new Blob([this.qr_code], { type: 'image/svg+xml' });
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = 'qr-code.svg';
      link.click();
      URL.revokeObjectURL(url);
    },
    isUnauthorized(error) {
      // if unauthenticated (401)
      if (error.response.status == "401") {
        this.$router.push({ name: "unauthorize" });
      }
    },
  },
  computed: {
    isQrExpired() {
      if (!this.expires_at) return false

      // Convert "m/d/Y g:iA" to Date
      return new Date(this.expires_at) < new Date()
    }
  },
  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
  },
};
</script>