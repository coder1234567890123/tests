<template>
  <div
    id="app"
    class="container">
    <h1 class="title mb-3">Default PDF Branding</h1>
    <v-card>
      <v-form
        ref="form"
        lazy-validation
      />
      <div class="container">
        <v-subheader
          class="capitalize pl-0">
          Theme Color
        </v-subheader>
        <v-layout>
          <v-flex md4>
            <div
              :style="'background-color:' + showColor"
              class="colorBox"/>
          </v-flex>
        </v-layout>
        <v-layout>
          <v-flex md12>
            <v-text-field
              v-model="themeColor"
              label="Theme Color"
              tabindex="13"
            />
          </v-flex>
          <v-flex
            md2
          >
            <v-btn
              slot="activator"
              color="teal darken-3"
              class="ma-0  mt-2"
              @click="updateThemeColor()"
            >
              <v-icon
                color="white"
                medium>
                fa fa-edit
              </v-icon>
            </v-btn>
          </v-flex>
        </v-layout>

        <v-subheader
          class="capitalize pl-0">
          Theme Color 2
        </v-subheader>
        <v-layout>
          <v-flex md4>
            <div
              :style="'background-color:' + showColor2"
              class="colorBox"/>
          </v-flex>
        </v-layout>
        <v-layout>
          <v-flex md12>
            <v-text-field
              v-model="themeColor2"
              label="Theme Color 2"
              tabindex="13"
            />
          </v-flex>
          <v-flex
            md2
          >
            <v-btn
              slot="activator"
              color="teal darken-3"
              class="ma-0  mt-2"
              @click="updateThemeColor2()"
            >
              <v-icon
                color="white"
                medium>
                fa fa-edit
              </v-icon>
            </v-btn>
          </v-flex>
        </v-layout>

        <v-subheader
          class="capitalize pl-0">
          Footer Link
        </v-subheader>
        <v-layout>
          <v-flex
            md12
          >
            <v-text-field
              v-model="themeUrl"
              label="Select Footer Logo Image"
              prepend-icon="attach_file"
            />
            <input
              ref="footerLogo"
              type="file"
              style="display: none"
            >
          </v-flex>
          <v-flex
            md2
          >
            <v-btn
              slot="activator"
              color="teal darken-3"
              class="ma-0  mt-2"
              @click="updateUrl()"
            >
              <v-icon
                color="white"
                medium>
                fa fa-edit
              </v-icon>
            </v-btn>
          </v-flex>
        </v-layout>
        <!--            keep just incase start-->
        <!--        <v-subheader-->
        <!--          class="capitalize pl-0">-->
        <!--          Front Page Logo-->
        <!--        </v-subheader>-->
        <!--        <v-layout>-->
        <!--          <v-flex-->
        <!--            md2-->
        <!--          >-->
        <!--            <v-img-->
        <!--              :src="`${blogStoragePath}/system-assets/`+coverLogo"-->
        <!--              :lazy-src="`${blogStoragePath}/system-assets/`+coverLogo"-->
        <!--              class="logo"-->
        <!--              @click="pickFile('front_logo')"/>-->
        <!--          </v-flex>-->
        <!--        </v-layout>-->
        <!--            keep just incase end-->
        <v-layout>
          <v-flex
            md12
          >
            <!--            Daniel Edit-->
            <v-text-field
              v-model="coverLogo"
              label="Select front page Logo"
              prepend-icon="attach_file"
              @click="pickFile()"/>
            <input
              ref="frontLogoFile"
              type="file"
              style="display: none"
              accept="image/*"
              @change="frontLogoFileUpload()"
            >
          </v-flex>
          <v-flex
            md2
          >
            <v-btn
              slot="activator"
              :disabled="coverLogoButton === null"
              color="teal darken-3"
              class="ma-0  mt-2"
              @click="frontLogoFileUpload()">
              <v-icon
                color="white"
                medium>
                fa fa-upload
              </v-icon>
            </v-btn>
          </v-flex>
        </v-layout>
        <v-subheader
          class="capitalize pl-0">
          Background Cover
        </v-subheader>
        <v-layout>
          <v-flex
            md2
          >
            <v-img
              :src="`${blogStoragePath}/system-assets/`+frontImage"
              :lazy-src="`${blogStoragePath}/system-assets/`+frontImage"
              class="logo"
              @click="pickFile('front')"/>
          </v-flex>
        </v-layout>
        <v-layout>
          <v-flex
            md12
          >
            <!--            Daniel Edit-->
            <v-text-field
              v-model="frontImage"
              label="Select front page cover"
              prepend-icon="attach_file"
              @click="pickFile()"/>
            <input
              ref="frontImageFile"
              type="file"
              style="display: none"
              accept="image/*"
              @change="frontImageFileUpload()"
            >
          </v-flex>
          <v-flex
            md2
          >
            <v-btn
              slot="activator"
              :disabled="frontImageFileButton === null"
              color="teal darken-3"
              class="ma-0  mt-2"
              @click="submitFileFrontImage()">
              <v-icon
                color="white"
                medium>
                fa fa-upload
              </v-icon>
            </v-btn>
          </v-flex>
        </v-layout>

        <v-subheader
          class="capitalize pl-0">
          Footer Logo
        </v-subheader>
        <v-layout>
          <v-flex
            md2
          >
            <v-img
              :src="`${blogStoragePath}/system-assets/`+logo"
              :lazy-src="`${blogStoragePath}/system-assets/`+logo"
              class="logo"
              @click="pickFile('front')"/>
          </v-flex>
        </v-layout>
        <v-layout>
          <v-flex
            md12
          >
            <v-text-field
              v-model="logo"
              label="Select Logo Image"
              prepend-icon="attach_file"
              @click="pickFile('footer')"/>
            <input
              ref="logoFile"
              type="file"
              style="display: none"
              accept="image/*"
              @change="logoFileUpload()">
          </v-flex>
          <v-flex
            md2
          >
            <v-btn
              slot="activator"
              :disabled="logoFileButton === null"
              color="teal darken-3"
              class="ma-0  mt-2"
              @click="submitFileLogo()">
              <v-icon
                color="white"
                medium>
                fa fa-upload
              </v-icon>
            </v-btn>
          </v-flex>
        </v-layout>
        <v-layout>
          <v-flex
            md12
          >
            <v-subheader
              class="capitalize pl-0">
              Disclaimer
            </v-subheader>
            <client-only>
              <ckeditor
                :value="defaultBranding.disclaimer"
                :editor="editor"
                :config="editorConfig"
                label="Disclaimer"
                @input="updateState('disclaimer', $event)"/>
            </client-only>
          </v-flex>
          <v-flex
            md2
          >
            <v-btn
              slot="activator"
              color="teal darken-3"
              class="ma-0  mt-2 disclaimerBtn"
              @click="updateDisclaimer()"
            >
              <v-icon
                color="white"
                medium>
                fa fa-edit
              </v-icon>
            </v-btn>
          </v-flex>
        </v-layout>
      </div>
    </v-card>
  </div>
</template>
<script>
import { mapGetters } from 'vuex'
import _ from 'lodash'

let ClassicEditor
let CKEditor

if (process.client) {
  ClassicEditor = require('@ckeditor/ckeditor5-build-classic')
  CKEditor = require('@ckeditor/ckeditor5-vue')
} else {
  CKEditor = { component: { template: '<div></div>' } }
}

export default {
  async fetch({ store }) {
    await store.dispatch('default-branding/getDefaultBranding')
  },
  head() {
    return {
      title: 'Branding :: Farosian'
    }
  },
  data() {
    return {
      coverLogo: '',
      logoFileButton: '',
      coverLogoButton: '',
      frontImageFileButton: '',
      frontImageFile: '',
      coFrontImageFileButton: '',
      coFrontImageFile: '',
      logoFile: '',
      file: '',
      showColor: '',
      showColor2: '',
      editor: ClassicEditor,
      editorData: null,
      editorConfig: {
        // The configuration of the editor.
        toolbar: [
          'heading',
          '|',
          'bold',
          'italic',
          '|',
          'bulletedList',
          'numberedList'
        ]
      },
      frontImage: '',
      coFrontImage: '',
      frontLogo: '',
      themeColor: '',
      themeColor2: '',
      logo: '',
      themeUrl: '',
      themeDisclaimer: 'Disclaimer'
    }
  },
  computed: {
    ...mapGetters({
      defaultBranding: 'default-branding/getBranding'
    }),
    blogStoragePath() {
      return process.env.blogStoragePath
    }
  },
  mounted() {
    process.env.blogStoragePath = this.$config.blogStoragePath
    this.editor = ClassicEditor
    this.editorData = '<p>Content of the editor.</p>'

    this.editorConfig = {
      // The configuration of the editor.
      toolbar: [
        'heading',
        '|',
        'bold',
        'italic',
        '|',
        'bulletedList',
        'numberedList'
      ]
    }

    this.showColor = this.defaultBranding.theme_color
    this.showColor2 = this.defaultBranding.theme_color_second
    this.frontImage = this.defaultBranding.front_page
    this.coverLogo = this.defaultBranding.cover_logo
    this.logo = this.defaultBranding.logo
    this.themeColor = this.defaultBranding.theme_color
    this.themeColor2 = this.defaultBranding.theme_color_second
    this.themeColorOverlayRgb = this.defaultBranding.theme_color_overlay_rgb
    this.themeUrl = this.defaultBranding.footer_link
    this.themeDisclaimer = this.defaultBranding.disclaimer
  },
  methods: {
    updateState(prop, value) {
      this.$store
        .dispatch('default-branding/updateDisclaimer', { prop, value })
        .catch(() => {
          console.log('Could not update Disclaimer data')
        })
    },
    frontImageFileUpload() {
      this.frontImageFile = this.$refs.frontImageFile.files[0]
    },
    logoFileUpload() {
      this.logoFile = this.$refs.logoFile.files[0]
    },
    submitFileLogo() {
      this.logoFileButton = null
      let uploadFile = new FormData()

      uploadFile.append('file', this.logoFile)
      uploadFile.append('placement', 'logo')

      this.$store
        .dispatch('default-branding/uploadFrontPage', uploadFile)
        .then(response => {
          this.logo = response.logo
          this.$toast.success(' Successfully updated!')
          this.logoFileButton = true
        })
        .catch(error => {
          this.$toast.error('Error Default Branding Uploading')
        })
    },
    submitFileFrontImage() {
      this.frontImageFileButton = null
      let uploadFile = new FormData()

      uploadFile.append('file', this.frontImageFile)
      uploadFile.append('placement', 'front_page')

      this.$store
        .dispatch('default-branding/uploadFrontPage', uploadFile)
        .then(response => {
          this.frontImage = response.front_page
          this.$toast.success(' successfully updated!')
          this.frontImageFileButton = true
        })
        .catch(error => {
          this.$toast.error('Error Default Branding Uploading')
        })
    },
    frontLogoFileUpload() {
      this.coverLogoButton = null
      let uploadFile = new FormData()

      uploadFile.append('file', this.frontImageFile)
      uploadFile.append('placement', 'cover_logo')

      this.$store
        .dispatch('default-branding/uploadFrontLogo', uploadFile)
        .then(response => {
          this.coverLogo = response.cover_logo
          this.$toast.success(' successfully updated!')
          this.coverLogoButton = true
        })
        .catch(error => {
          this.$toast.error('Error Default Branding Uploading')
        })
    },
    updateDisclaimer() {
      let data = {
        id: this.defaultBranding.id
      }
      this.$store
        .dispatch('default-branding/update', data)
        .then(response => {
          this.$toast.success('Default Branding Updated')
        })
        .catch(error => {
          this.$toast.error('Error Default Branding Updated')
        })
    },
    updateThemeColor() {
      let data = {
        id: this.defaultBranding.id,
        theme_color: this.themeColor
      }
      this.$store
        .dispatch('default-branding/updateDefaultBranding', data)
        .then(response => {
          this.showColor = this.themeColor
          this.$toast.success('Default Branding Updated')
        })
        .catch(error => {
          this.$toast.error('Error Default Branding Updated')
        })
    },
    updateThemeColor2() {
      let data = {
        id: this.defaultBranding.id,
        theme_color_second: this.themeColor2
      }
      this.$store
        .dispatch('default-branding/updateDefaultBranding', data)
        .then(response => {
          this.showColor2 = this.themeColor2
          this.$toast.success('Default Branding Updated')
        })
        .catch(error => {
          this.$toast.error('Error Default Branding Updated')
        })
    },
    updateUrl() {
      let data = {
        id: this.defaultBranding.id,
        footer_link: this.themeUrl
      }
      this.$store
        .dispatch('default-branding/updateDefaultBranding', data)
        .then(response => {
          this.$toast.success('Default Branding Updated')
        })
        .catch(error => {
          this.$toast.error('Error Default Branding Updated')
        })
    },
    pickFile(name) {
      if (name === 'footer') {
        this.$refs.logoFile.click()
      } else if (name === 'front_logo') {
        this.$refs.frontLogoFile.click()
      } else if (name === 'co_front') {
        this.$refs.coFrontImageFile.click()
      } else {
        this.$refs.frontImageFile.click()
      }
    },
    uploadLogo() {
      let data = {
        id: this.defaultBranding.id,
        logo: this.logo
      }
      this.$store
        .dispatch('default-branding/updateDefaultBranding', data)
        .then(response => {
          this.$toast.success('Default Branding Updated')
        })
        .catch(error => {
          this.$toast.error('Error Default Branding Updated')
        })
    }
  }
}
</script>
<style scoped>
.capitalize {
  text-transform: uppercase;
}

.clearBoth {
  clear: both;
}

.logo {
  height: 150px !important;
  width: auto;
  margin: 10px 10px 10px 15px;
}

.disclaimerBtn {
  margin: 95px 0 0 0 !important;
}

.colorBox {
  height: 50px;
  width: 120px;
}
</style>
