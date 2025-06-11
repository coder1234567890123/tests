<template>
  <div
    id="app"
    class="container">
    <product
      ref="modal"
      :company-id="currentCompany"/>
    <h1 class="title mb-3">Edit Company</h1>
    <v-stepper v-model="e1">
      <v-stepper-header>
        <v-stepper-step step="1">Company Information</v-stepper-step>
        <v-divider/>
        <v-stepper-step step="2">Contact Information</v-stepper-step>
        <v-divider/>
        <v-stepper-step step="3">Contact Person Information</v-stepper-step>
        <v-divider/>
        <v-stepper-step step="4">Account Holder Information</v-stepper-step>
        <v-divider/>
        <v-stepper-step step="5">Company CI and Branding</v-stepper-step>
      </v-stepper-header>
      <v-stepper-items>
        <v-stepper-content step="1">
          <v-card class="mb-2 pb-5">
            <v-form data-vv-scope="step1">
              <v-layout
                row
                wrap>
                <v-flex md12>
                  <v-text-field
                    v-validate="'required|max:255'"
                    :value="company.name"
                    :error-messages="errors.collect('step1.name')"
                    name="name"
                    label="Name"
                    @input="updateState('name', $event)"/>
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    v-validate="'required'"
                    :value="company.street1"
                    :error-messages="errors.collect('step1.street1')"
                    name="street1"
                    label="Street 1"
                    @input="updateState('street1', $event)"/>
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    v-validate="'required'"
                    :value="company.street2"
                    :error-messages="errors.collect('step1.street2')"
                    name="street2"
                    label="Street 2"
                    @input="updateState('street2', $event)"/>
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    v-validate="'required'"
                    :value="company.suburb"
                    :error-messages="errors.collect('step1.suburb')"
                    name="suburb"
                    label="Suburb"
                    @input="updateState('suburb', $event)"/>
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    v-validate="'required'"
                    :value="company.postal_code"
                    :error-messages="errors.collect('step1.postal_code')"
                    name="postal_code"
                    label="Postal Code"
                    @input="updateState('postal_code', $event)"/>
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    v-validate="'required'"
                    :value="company.city"
                    :error-messages="errors.collect('step1.city')"
                    name="city"
                    label="City"
                    @input="updateState('city', $event)"/>
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    v-validate="'required'"
                    :value="company.province"
                    :error-messages="errors.collect('step1.province')"
                    name="province"
                    label="Province"
                    @input="updateState('province', $event)"/>
                </v-flex>
                <v-flex md6>
                  <v-select
                    v-validate="'required'"
                    :value="company.country"
                    :error-messages="errors.collect('step1.country')"
                    :items="countries"
                    item-text="name"
                    item-value="id"
                    name="country"
                    label="Country"
                    @input="updateState('country', $event)"/>
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    :value="company.vat_number"
                    label="Vat Number"
                    @input="updateState('vat_number', $event)"/>
                </v-flex>
                <v-flex md6>
                  <v-select
                    v-validate="'required'"
                    :error-messages="errors.collect('step1.Company Types')"
                    :value="company.company_types"
                    :items="company_types"
                    label="Company Type"
                    name="Company Types"
                    @input="updateState('company_types', $event)"
                  />
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    :value="company.registration_number"
                    label="Registration Number"
                    @input="updateState('registration_number', $event)"/>
                </v-flex>
              </v-layout>
              <br>
              <hr class="gray">
              <br>
              <v-flex md12>
                <v-textarea
                  :value=" company.note"
                  label="Notes"
                  outline
                  tabindex="11"
                  @input="updateState('note', $event)"
                />
              </v-flex>
              <v-flex md12>
                <v-checkbox
                  :input-value="company.allow_trait === null ? false: company.allow_trait"
                  label="Use Behavior Trait"
                  @change="updateState('allow_trait', $event)"/>
              </v-flex>
            </v-form>
          </v-card>
          <v-btn
            :to="'/accounts/' + company.id +'/edit'"
            color="teal darken-3"
            class="white--text">
            Accounts
          </v-btn>
          <v-layout row>
            <v-flex
              md12
              class="text-md-right">
              <v-btn
                :nuxt="true"
                to="/company"
                flat>Cancel
              </v-btn>
              <v-btn
                color="teal darken-3"
                class="white--text"
                @click="validateForm('step1')">Continue
              </v-btn>
            </v-flex>
          </v-layout>
        </v-stepper-content>
        <v-stepper-content step="2">
          <v-card class="mb-2 pb-5">
            <v-form data-vv-scope="step2">
              <v-layout
                row
                wrap>
                <v-flex md6>
                  <v-text-field
                    v-validate="'required|email'"
                    :value="company.email"
                    :error-messages="errors.collect('step2.email')"
                    name="email"
                    label="Email"
                    @input="updateState('email', $event)"/>
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    :value="company.website"
                    label="Website"
                    @input="updateState('website', $event)"/>
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    v-validate="'required|numeric|min:10'"
                    :error-messages="errors.collect('step2.tel_number')"
                    :value="company.tel_number"
                    name="tel_number"
                    label="Telephone Number"
                    @input="updateState('tel_number', $event)"/>
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    :value="company.mobile_number"
                    label="Mobile Number"
                    @input="updateState('mobile_number', $event)"/>
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    :value="company.fax_number"
                    label="Fax Number"
                    @input="updateState('fax_number', $event)"/>
                </v-flex>
              </v-layout>
            </v-form>
          </v-card>
          <v-layout row>
            <v-flex md6>
              <v-btn
                color="primary"
                @click="e1 = 1">
                Previous
              </v-btn>
            </v-flex>
            <v-flex
              md6
              class="text-md-right">
              <v-btn
                :nuxt="true"
                to="/company"
                flat>Cancel
              </v-btn>
              <v-btn
                color="teal darken-3"
                class="white--text"
                @click="validateForm('step2')">
                Continue
              </v-btn>
            </v-flex>
          </v-layout>
        </v-stepper-content>
        <v-stepper-content step="3">
          <v-card class="mb-2 pb-5">
            <v-form data-vv-scope="step3">
              <v-layout
                row
                wrap>
                <v-flex md6>
                  <v-text-field
                    v-validate="'required|max:36'"
                    :value="company.contact_firstname"
                    :error-messages="errors.collect('step3.contact_firstname')"
                    name="contact_firstname"
                    label="First Name"
                    @input="updateState('contact_firstname', $event)"/>
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    v-validate="'required|max:36'"
                    :value="company.contact_lastname"
                    :error-messages="errors.collect('step3.contact_lastname')"
                    name="contact_lastname"
                    label="Last Name"
                    @input="updateState('contact_lastname', $event)"/>
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    v-validate="'required|email'"
                    :value="company.contact_email"
                    :error-messages="errors.collect('step3.contact_email')"
                    name="contact_email"
                    label="Email"
                    @input="updateState('contact_email', $event)"/>
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    v-validate="'required|numeric|min:10'"
                    :value="company.contact_telephone"
                    :error-messages="errors.collect('step3.contact_telephone')"
                    name="contact_telephone"
                    label="Mobile Number"
                    @input="updateState('contact_telephone', $event)"/>
                </v-flex>
              </v-layout>
            </v-form>
          </v-card>
          <v-layout row>
            <v-flex md6>
              <v-btn
                color="primary"
                @click="e1 = 2">
                Previous
              </v-btn>
            </v-flex>
            <v-flex
              md6
              class="text-md-right">
              <v-btn
                :nuxt="true"
                to="/company"
                flat>Cancel
              </v-btn>
              <v-btn
                color="teal darken-3"
                class="white--text"
                @click="validateForm('step3')">
                Continue
              </v-btn>
            </v-flex>
          </v-layout>
        </v-stepper-content>
        <v-stepper-content step="4">
          <v-card class="mb-2 pb-5">
            <v-form data-vv-scope="step4">
              <v-layout
                row
                wrap>
                <v-flex md6>
                  <v-text-field
                    v-validate="'required|max:36'"
                    :value="company.account_holder_first_name"
                    :error-messages="errors.collect('step4.account_holder_first_name')"
                    name="account_holder_first_name"
                    label="First Name"
                    @input="updateState('account_holder_first_name', $event)"/>
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    v-validate="'required|max:36'"
                    :value="company.account_holder_last_name"
                    :error-messages="errors.collect('step4.account_holder_last_name')"
                    name="account_holder_last_name"
                    label="Last Name"
                    @input="updateState('account_holder_last_name', $event)"/>
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    v-validate="'required|email'"
                    :value="company.account_holder_email"
                    :error-messages="errors.collect('step4.account_holder_email')"
                    name="account_holder_email"
                    label="Email"
                    @input="updateState('account_holder_email', $event)"/>
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    v-validate="'required|numeric|min:10'"
                    :value="company.account_holder_phone"
                    :error-messages="errors.collect('step4.account_holder_phone')"
                    name="account_holder_phone"
                    label="Mobile Number"
                    @input="updateState('account_holder_phone', $event)"/>
                </v-flex>
              </v-layout>
            </v-form>
          </v-card>
          <v-layout row>
            <v-flex md6>
              <v-btn
                color="primary"
                @click="e1 = 3">
                Previous
              </v-btn>
            </v-flex>
            <v-flex
              md6
              class="text-md-right">
              <v-btn
                :nuxt="true"
                to="/company"
                flat>Cancel
              </v-btn>
              <v-btn
                color="teal darken-3"
                class="white--text"
                @click="validateForm('step4')">
                Continue
              </v-btn>
            </v-flex>
          </v-layout>
        </v-stepper-content>
        <v-stepper-content step="5">
          <v-card class="mb-2 pb-5">
            <v-form data-vv-scope="step5">
              <v-layout
                row
                wrap>
                <v-flex md6>
                  <v-select
                    :value="company.branding_type"
                    :items="brandingTypes"
                    :disabled="!($auth && ($auth.hasScope('ROLE_SUPER_ADMIN') || $auth.hasScope('ROLE_COMPANY_EDIT')))"
                    item-text="label"
                    item-value="value"
                    label="Branding Type"
                    class="mx-3"
                    @input="updateState('branding_type', $event)"/>
                </v-flex>
                <v-flex md6>
                  <v-flex md4>
                    <div
                      :style="'background-color:' + company.theme_color"
                      class="colorBox"/>
                  </v-flex>
                  <v-text-field
                    :value="company.theme_color"
                    label="Theme Color"
                    tabindex="13"
                    @input="updateState('theme_color', $event)"/>
                  <v-flex md4>
                    <div
                      :style="'background-color:' + company.theme_color_second"
                      class="colorBox"/>
                  </v-flex>
                  <v-text-field
                    :value="company.theme_color_second"
                    label="Theme Color background"
                    tabindex="13"
                    @input="updateState('theme_color_second', $event)"/>
                  <v-text-field
                    :value="company.footer_link"
                    tabindex="13"
                    label="Footer Link"
                    @input="updateState('footer_link', $event)"/>
                </v-flex>
                <!--                toDo remove this-->
                <!--                <v-flex md6>-->
                <!--                  <v-checkbox-->
                <!--                    :input-value="company.password_set === null ? false: company.password_set"-->
                <!--                    label="Use Password on PDF"-->
                <!--                    @change="updateState('password_set', $event)"/>-->
                <!--                </v-flex>-->
                <!--                <v-flex md6>-->
                <!--                  <v-text-field-->
                <!--                    v-if="company.password_set"-->
                <!--                    :value="company.pdf_password"-->
                <!--                    label="PDF Password"-->
                <!--                    tabindex="13"-->
                <!--                    @input="updateState('pdf_password', $event)"/>-->
                <!--                </v-flex>-->
                <!--                toDo remove this-->
              </v-layout>
            </v-form>
          </v-card>
          <!--start of cover-->
          <!--          <v-layout-->
          <!--            row-->
          <!--            wrap>-->
          <!--            <v-flex md12>-->
          <!--              &lt;!&ndash;              <v-subheader&ndash;&gt;-->
          <!--              &lt;!&ndash;                class="capitalize pl-0">&ndash;&gt;-->
          <!--              &lt;!&ndash;                Cover Logo&ndash;&gt;-->
          <!--              &lt;!&ndash;              </v-subheader>&ndash;&gt;-->
          <!--            </v-flex>-->
          <!--          </v-layout>-->
          <!--          <v-layout-->
          <!--            v-if="company.image_cover_logo"-->
          <!--            row-->
          <!--            wrap>-->
          <!--            <v-flex md2>-->
          <!--              &lt;!&ndash;              <v-img&ndash;&gt;-->
          <!--              &lt;!&ndash;                :src="`${blogStoragePath}/company-images/${company.id}/${company.image_cover_logo}`"&ndash;&gt;-->
          <!--              &lt;!&ndash;                :lazy-src="`${blogStoragePath}/company-images/${company.id}/${company.image_cover_logo}`"&ndash;&gt;-->
          <!--              &lt;!&ndash;                class="logo"&ndash;&gt;-->
          <!--              &lt;!&ndash;                @click="pickFile('cover_logo')"/>&ndash;&gt;-->
          <!--            </v-flex>-->
          <!--          </v-layout>-->
          <v-layout
            row
            wrap>

            <!--            keep just incase start-->
            <!--            <v-flex-->
            <!--              md10-->
            <!--              py-2>-->
            <!--              <v-text-field-->
            <!--                :value="coverLogoImageName"-->
            <!--                label="Select Cover Logo Image"-->
            <!--                prepend-icon="attach_file"-->
            <!--                @click="pickFile('cover_logo')"/>-->
            <!--              <input-->
            <!--                ref="coverLogo"-->
            <!--                type="file"-->
            <!--                style="display: none"-->
            <!--                accept="image/*"-->
            <!--                @change="onFilePicked($event, 'cover_logo')">-->
            <!--            </v-flex>-->

            <!--             keep just incase end-->

            <!--            <v-flex-->
            <!--              md2-->
            <!--              py-2>-->
            <!--              <v-tooltip-->
            <!--                slot="append"-->
            <!--                right>-->
            <!--                <v-btn-->
            <!--                  slot="activator"-->
            <!--                  :disabled="coverLogoImage === null"-->
            <!--                  :loading="uploadAction.footer"-->
            <!--                  color="teal"-->
            <!--                  class="ma-0 mt-2"-->
            <!--                  @click="upload('cover_logo')">-->
            <!--                  <v-icon-->
            <!--                    color="white"-->
            <!--                    medium>-->
            <!--                    fa fa-upload-->
            <!--                  </v-icon>-->
            <!--                </v-btn>-->
            <!--                <span>Click to upload image </span>-->
            <!--              </v-tooltip>-->
            <!--            </v-flex>-->
            <!--             keep just incase end-->
          </v-layout>
          <!--end of cover-->
          <!--start of Footer Logo-->
          <v-layout
            row
            wrap>
            <v-flex md12>
              <v-subheader
                class="capitalize pl-0">
                Footer Logo
              </v-subheader>
            </v-flex>
          </v-layout>
          <v-layout
            v-if="company.image_footer_logo"
            row
            wrap>
            <v-flex md2>
              <v-img
                :src="`${blogStoragePath}/company-images/${company.id}/${company.image_footer_logo}`"
                :lazy-src="`${blogStoragePath}/company-images/${company.id}/${company.image_footer_logo}`"
                class="logo"
                @click="pickFile('footer')"/>
            </v-flex>
          </v-layout>
          <v-layout
            row
            wrap>
            <v-flex
              md10
              py-2>
              <v-text-field
                :value="footerImageName"
                label="Select Footer Logo Image"
                prepend-icon="attach_file"
                @click="pickFile('footer')"/>
              <input
                ref="footerLogo"
                type="file"
                style="display: none"
                accept="image/*"
                @change="onFilePicked($event, 'footer')">
            </v-flex>
            <v-flex
              md2
              py-2>
              <v-tooltip
                slot="append"
                right>
                <v-btn
                  slot="activator"
                  :disabled="footerImage === null"
                  :loading="uploadAction.footer"
                  color="teal"
                  class="ma-0 mt-2"
                  @click="upload('footer')">
                  <v-icon
                    color="white"
                    medium>
                    fa fa-upload
                  </v-icon>
                </v-btn>
                <span>Click to upload image </span>
              </v-tooltip>
            </v-flex>
          </v-layout>
          <!--end of Footer Logo-->
          <!--start of Footer Logo-->
          <v-layout
            row
            wrap>
            <v-flex md12>
              <v-subheader
                class="capitalize pl-0">
                Front Page Image
              </v-subheader>
            </v-flex>
          </v-layout>
          <v-layout
            v-if="company.image_front_page"
            row
            wrap>
            <v-flex md2>
              <v-img
                :src="`${blogStoragePath}/company-images/${company.id}/${company.image_front_page}`"
                :lazy-src="`${blogStoragePath}/company-images/${company.id}/${company.image_front_page}`"
                class="logo"
                @click="pickFile('front')"/>
            </v-flex>
          </v-layout>
          <v-layout
            row
            wrap>
            <v-flex
              md10
              py-2>
              <v-text-field
                :value="frontImageName"
                label="Select Front Page Image"
                prepend-icon="attach_file"
                @click="pickFile('front')"/>
              <input
                ref="frontPage"
                type="file"
                style="display: none"
                accept="image/*"
                @change="onFilePicked($event, 'front')">
            </v-flex>
            <v-flex
              md2
              py-2>
              <v-tooltip
                slot="append"
                right>
                <v-btn
                  slot="activator"
                  :disabled="frontImage === null"
                  :loading="uploadAction.front"
                  color="teal"
                  class="ma-0 mt-2"
                  @click="upload('front')">
                  <v-icon
                    color="white"
                    medium>
                    fa fa-upload
                  </v-icon>
                </v-btn>
                <span>Click to upload image </span>
              </v-tooltip>
            </v-flex>
            <v-flex md12>
              <v-subheader
                class="capitalize pl-0">
                Disclaimer
              </v-subheader>
              <client-only>
                <ckeditor
                  :value=" company.disclaimer"
                  :editor="editor"
                  :config="editorConfig"
                  label="Disclaimer"
                  @input="updateState('disclaimer', $event)"/>
              </client-only>
            </v-flex>
            <v-flex md6>
              <v-checkbox
                :input-value="company.use_disclaimer === null ? false: company.use_disclaimer"
                label="Use Disclaimer"
                @change="updateState('use_disclaimer', $event)"/>
            </v-flex>
          </v-layout>
          <!--end of Footer Logo-->
          <v-layout row>
            <v-flex md6>
              <v-btn
                color="primary"
                @click="e1 = 4">
                Previous
              </v-btn>
            </v-flex>
            <v-flex
              md6
              class="text-md-right">
              <v-btn
                :nuxt="true"
                to="/company"
                flat>Cancel
              </v-btn>
              <v-btn
                color="teal darken-3"
                class="white--text"
                @click="validateForm('step5')">
                Save and Continue
              </v-btn>
            </v-flex>
          </v-layout>
        </v-stepper-content>
      </v-stepper-items>
    </v-stepper>
  </div>
</template>
<script>
import Vue from 'vue'
import { mapGetters } from 'vuex'
import dictionary from '~/locales/en'

import product from '~/components/product_type/Product'

let ClassicEditor
let CKEditor

if (process.client) {
  ClassicEditor = require('@ckeditor/ckeditor5-build-classic')
  CKEditor = require('@ckeditor/ckeditor5-vue')
} else {
  CKEditor = { component: { template: '<div></div>' } }
}

export default {
  inject: ['$validator'],
  components: {
    ckeditor: CKEditor.component
  },
  components: {
    ckeditor: CKEditor.component,
    product
  },
  async fetch({ store, route }) {
    await store.dispatch('company/get', route.params.id)
    await store.dispatch('static/initCountries')
  },
  head() {
    return {
      title: 'Edit Company :: Farosian'
    }
  },
  data() {
    return {
      editor: ClassicEditor,
      editorData: null,
      currentCompany: '',
      company_types: ['Internal', 'External'],
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
      e1: 1,
      selectedFile: null,
      fooImageName: '',
      froImageName: '',
      logoFrtName: '',
      footerImage: null,
      frontImage: null,
      coverLogoImage: null,
      uploadAction: {
        footer: false,
        front: false
      }
    }
  },
  computed: {
    ...mapGetters({
      company: 'company/company',
      countries: 'static/countries',
      brandingTypes: 'static/brandingTypes'
    }),
    frontImageName: {
      get: function() {
        return this.froImageName !== ''
          ? this.froImageName
          : this.company.image_front_page
      },
      set: function(value) {
        this.froImageName = value
      }
    },
    coverLogoImageName: {
      get: function() {
        return this.logoFrtName !== ''
          ? this.logoFrtName
          : this.company.image_cover_logo
      },
      set: function(value) {
        this.logoFrtName = value
      }
    },
    footerImageName: {
      get: function() {
        return this.fooImageName !== ''
          ? this.fooImageName
          : this.company.image_footer_logo
      },
      set: function(value) {
        this.fooImageName = value
      }
    },
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

    Vue.nextTick(
      function() {
        if (this.$route.query.continue !== undefined) {
          this.e1 = this.$route.query.continue
        }
      }.bind(this)
    )
    this.$validator.localize('en', dictionary)
  },
  methods: {
    productType(company) {
      this.currentCompany = company
      this.$refs.modal.show()
    },
    updateState(prop, value) {
      this.$store
        .dispatch('company/updateCompany', { prop, value })
        .catch(() => {
          console.log('Could not update company data')
        })
    },
    validateForm(scope) {
      const step = scope.replace('step', '')
      this.$validator.validateAll(scope).then(result => {
        if (result) {
          switch (step) {
            case '1':
              this.e1 = 2
              break
            case '2':
              this.e1 = 3
              break
            case '3':
              this.e1 = 4
              break
            case '4':
              this.e1 = 5
              break
            case '5':
              this.submitForm()
              break
          }
        }
      })
    },
    submitForm() {
      if (this.company.country instanceof Object) {
        this.updateState('country', this.company.country.id)
      }
      this.$store
        .dispatch('company/update', this.company)
        .then(() => {
          this.$toast.success('Company successfully updated!')
          this.$router.push('/company')
        })
        .catch(() => {
          this.$toast.error(
            'Could not update company, please double check validation!'
          )
        })
    },
    pickFile(name) {
      if (name === 'footer') {
        this.$refs.footerLogo.click()
      } else if (name === 'cover_logo') {
        this.$refs.coverLogo.click()
      } else {
        this.$refs.frontPage.click()
      }
    },
    onFilePicked(e, name) {
      const files = e.target.files

      if (files[0].size >= 75000) {
        alert('File size less than 750k')
      } else {
        this.frontImage = null
        this.coverLogoImage = null
        this.footerImage = null
      }

      let imageName = ''
      if (files[0] !== undefined) {
        imageName = files[0].name
        if (imageName.lastIndexOf('.') <= 0) {
          return
        }
        const fr = new FileReader()
        fr.readAsDataURL(files[0])
        let imageUrl = ''
        fr.addEventListener('load', () => {
          imageUrl = fr.result
          if (name === 'footer') {
            this.footerImageName = imageName
            this.footerImage = files[0] // this is an image file that can be sent to server...
          } else if (name === 'cover_logo') {
            this.coverLogoImageName = imageName
            this.coverLogoImage = files[0] // this is an image file that can be sent to server...
          } else {
            this.frontImageName = imageName
            this.frontImage = files[0] // this is an image file that can be sent to server...
          }
        })
      }
    },
    upload(prop) {
      let action = ''
      let uploadFile = new FormData()
      this.uploadAction[prop] = true
      if (prop === 'front') {
        uploadFile.append('file', this.frontImage, this.frontImage.name)
        action = 'company/uploadFrontPage'
      } else if (prop === 'cover_logo') {
        uploadFile.append('file', this.coverLogoImage, this.coverLogoImage.name)
        action = 'company/uploadFrontLogoPage'
      } else {
        uploadFile.append('file', this.footerImage, this.footerImage.name)
        action = 'company/uploadFooterLogo'
      }
      this.$store
        .dispatch(action, uploadFile)
        .then(() => {
          if (prop === 'front') {
            this.frontImage = null
          } else if (prop === 'cover_logo') {
            this.coverLogoImage = null
          } else {
            this.footerImage = null
          }
          this.uploadAction[prop] = false
          this.$store
            .dispatch('company/getCompany', this.$route.params.id)
            .catch(err => {
              this.$toast.success('Unable to reload company info: ' + err)
            })
          this.$toast.success(prop + ' successfully updated!')
        })
        .catch(err => {
          this.uploadAction[prop] = false
          let errorMessage = err
          if (err.response && err.response.data && err.response.data.message)
            errorMessage = err.response.data.message
          this.$toast.error(
            prop.toUpperCase() + ' upload error: ' + errorMessage
          )
        })
    }
  }
}
</script>
<style>
.capitalize {
  text-transform: uppercase;
}

.uploader {
  padding: 6px 21px;
  border: solid 1px #ddd;
  width: 195px;
}

.grey.lighten-2 {
  cursor: pointer !important;
}

span.v-btn__loading {
  color: #fff !important;
}

.logo {
  height: 150px !important;
  margin: 10px 10px 10px 15px;
}

.colorBox {
  height: 50px;
  width: 120px;
}
</style>
