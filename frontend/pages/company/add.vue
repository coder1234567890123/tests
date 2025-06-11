<template>
  <div
    id="app"
    class="container">
    <h1 class="title mb-3">Add Company</h1>

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
          <v-card class="mb-2 pb-2">
            <v-form data-vv-scope="step1">
              <v-layout
                row
                wrap>
                <v-flex md12>
                  <v-text-field
                    v-validate="'required|max:255'"
                    v-model="company.name"
                    :error-messages="errors.collect('step1.name')"
                    name="name"
                    label="Name" />
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    v-validate="'required'"
                    v-model="company.street1"
                    :error-messages="errors.collect('step1.street1')"
                    name="street1"
                    label="Street 1" />
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    v-validate="'required'"
                    v-model="company.street2"
                    :error-messages="errors.collect('step1.street2')"
                    name="street2"
                    label="Street 2" />
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    v-validate="'required'"
                    v-model="company.suburb"
                    :error-messages="errors.collect('step1.suburb')"
                    name="suburb"
                    label="Suburb/County" />
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    v-validate="'required'"
                    v-model="company.postal_code"
                    :error-messages="errors.collect('step1.postal_code')"
                    name="postal_code"
                    label="Postal/Zip Code" />
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    v-validate="'required'"
                    v-model="company.city"
                    :error-messages="errors.collect('step1.city')"
                    name="city"
                    label="City" />
                </v-flex>
                <v-flex md6>
                  <div class="floating-form">
                  <div class="floating-label">
                  <country-select
                    class="floating-select"
                    v-validate="'required'"
                    v-model="company.country"
                    :error-messages="errors.collect('step1.country')"
                    :country="company.country"
                    countryName="true"
                    placeholder=""
                    name="country"
                    label="Country" />
                    <span class="highlight"></span>
                    <label for="country">Country</label>
                  </div>
                  </div>
                </v-flex>
                <v-flex md6>
                  <div class="floating-form">
                  <div class="floating-label">
                  <region-select
                    class="floating-select"
                    v-validate="'required'"
                    :error-messages="errors.collect('step1.province')"
                    v-model="company.province"
                    :country="company.country"
                    :region="company.province"
                    countryName="true"
                    regionName="true"
                    placeholder=""
                    name="region"
                    label="Province/State" />
                    <span class="highlight"></span>
                    <label for="region">Province/State</label>
                  </div>
                  </div>
                </v-flex>

                <v-flex md6>
                  <v-text-field
                    v-model="company.vat_number"
                    label="Vat Number" />
                </v-flex>
                <v-flex md6>
                  <v-select
                    v-validate="'required'"
                    v-model="company.company_types"
                    :error-messages="errors.collect('step1.Company Types')"
                    :items="company_types"
                    name="Company Types"
                    label="Company Types"/>
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    v-model="company.registration_number"
                    label="Registration Number" />
                </v-flex>
              </v-layout>
              <br>
              <hr class="gray">
              <br>
              <v-flex md12>
                <v-checkbox
                  v-model="company.allow_trait"
                  label="Use Behavior Trait"/>
              </v-flex>
              <v-flex md12>
                <v-textarea
                  v-model="company.note"
                  label="Notes"
                  outline />
              </v-flex>
            </v-form>
          </v-card>

          <v-layout row>
            <v-flex
              md12
              class="text-md-right">
              <v-btn
                :nuxt="true"
                to="/user"
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
                    v-model="company.email"
                    :error-messages="errors.collect('step2.email')"
                    name="email"
                    label="Email" />
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    v-model="company.website"
                    label="Website" />
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    v-validate="'min:10|max:30'"
                    v-model="company.tel_number"
                    :error-messages="errors.collect('step2.tel_number')"
                    name="tel_number"
                    label="Telephone Number" />
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    v-validate="'min:10|max:30'"
                    v-model="company.mobile_number"
                    :error-messages="errors.collect('step2.mobile_number')"
                    name="mobile_number"
                    label="Mobile Number" />
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    v-model="company.fax_number"
                    label="Fax Number" />
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
                @click="validateForm('step2')">Continue
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
                    v-model="company.contact_firstname"
                    :error-messages="errors.collect('step3.contact_firstname')"
                    name="contact_firstname"
                    label="First Name" />
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    v-validate="'required|max:36'"
                    v-model="company.contact_lastname"
                    :error-messages="errors.collect('step3.contact_lastname')"
                    name="contact_lastname"
                    label="Last Name" />
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    v-validate="'required|email'"
                    v-model="company.contact_email"
                    :error-messages="errors.collect('step3.contact_email')"
                    name="contact_email"
                    label="Email" />
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    v-validate="'required|numeric|min:10'"
                    v-model="company.contact_telephone"
                    :error-messages="errors.collect('step3.contact_telephone')"
                    name="contact_telephone"
                    label="Contact Phone Number" />
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
                @click="validateForm('step3')">Continue
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
                    v-model="company.account_holder_first_name"
                    :error-messages="errors.collect('step4.account_holder_first_name')"
                    name="account_holder_first_name"
                    label="First Name" />
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    v-validate="'required|max:36'"
                    v-model="company.account_holder_last_name"
                    :error-messages="errors.collect('step4.account_holder_last_name')"
                    name="account_holder_last_name"
                    label="Last Name" />
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    v-validate="'required|email'"
                    v-model="company.account_holder_email"
                    :error-messages="errors.collect('step4.account_holder_email')"
                    name="account_holder_email"
                    label="Email" />
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    v-validate="'required|numeric|min:10'"
                    v-model="company.account_holder_phone"
                    :error-messages="errors.collect('step4.account_holder_phone')"
                    name="account_holder_phone"
                    label="Mobile Number" />
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
                Save and Continue to CI
              </v-btn>
            </v-flex>
          </v-layout>
        </v-stepper-content>

        <v-stepper-content step="5">
          <v-card class="mb-2 pb-5">
            <v-form data-vv-scope="step5">
              <v-layout
                row
                wrap />
              <v-flex md6>
                <input
                  ref="fileInputCoverPage"
                  style="display: none"
                  type="file"
                  @change="onFileSelected">
                <button
                  class="uploader"
                  @click.stop.prevent="$refs.fileInputCoverPage.click()">Add a Cover Page</button>
                <button
                  class="white--text v-btn teal darken-3"
                  @click.stop.prevent="onFrontPageUpload">Upload</button>
              </v-flex>

              <v-flex md6>
                <input
                  ref="fileInputFooterLogo"
                  style="display: none"
                  type="file"
                  @change="onFileSelected">
                <button
                  class="uploader"
                  @click.stop.prevent="$refs.fileInputFooterLogo.click()">Add a Footer Logo</button>
                <button
                  class="white--text v-btn teal darken-3"
                  @click.stop.prevent="onFooterUpload">Upload</button>
              </v-flex>

              <v-flex md6>
                <input
                  ref="fileInputCoFooterLogo"
                  style="display: none"
                  type="file"
                  @change="onFileSelected">
                <button
                  class="uploader"
                  @click.stop.prevent="$refs.fileInputCoFooterLogo.click()">Add a Co-footer Logo</button>
                <button
                  class="white--text v-btn teal darken-3"
                  @click.stop.prevent="onCoFooterUpload">Upload</button>
              </v-flex>

              <v-flex md6>
                <v-text-field
                  v-model="company.footer_link"
                  label="Footer Link" />
              </v-flex>

              <v-flex md6>
                <v-text-field
                  v-model="company.theme_color"
                  label="Theme Color" />
              </v-flex>

              <v-flex md6>
                <!-- Add toggle-init class -->
                Enable Password?:
                <label class="toggle toggle-init">
                  <input type="checkbox">
                  <span class="toggle-icon"/>
                </label>
              </v-flex>

              <v-flex md6>
                <v-text-field
                  v-model="company.pdf_password"
                  label="PDF Password" />
              </v-flex>
            </v-form>
          </v-card>

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
                Save and Exit
              </v-btn>
            </v-flex>
          </v-layout>
        </v-stepper-content>
      </v-stepper-items>
    </v-stepper>
  </div>
</template>

<script>
import { mapGetters } from 'vuex'
import dictionary from '~/locales/en'

export default {
  inject: ['$validator'],
  async fetch({ store }) {
    await store.dispatch('static/initCountries')
  },
  head() {
    return {
      title: 'Add Company :: Farosian'
    }
  },
  data() {
    return {
      e1: 1,
      company_types: ['Internal', 'External'],
      company: {
        name: '',
        email: '',
        street1: '',
        street2: '',
        suburb: '',
        city: '',
        province: '',
        country: 200,
        postal_code: '',
        registration_number: '',
        vat_number: '',
        allow_trait: true,
        note: '',
        tel_number: '',
        fax_number: '',
        mobile_number: '',
        website: '',
        contact_firstname: '',
        contact_lastname: '',
        contact_email: '',
        contact_phone: '',
        account_holder_first_name: '',
        account_holder_last_name: '',
        account_holder_email: '',
        account_holder_phone: '',
        image_front_page: '',
        footer_link: 'http://www.farosian.co.za/',
        image_footer_logo: '',
        image_co_footer_logo: '',
        theme_color: '#166c36',
        pdf_password: '123456',
        branding_type: 'default'
      },
      passwordStatus: 'Yes',
      selectedFile: null
    }
  },
  computed: {
    ...mapGetters({
      countries: 'static/countries'
    })
  },
  mounted() {
    this.$validator.localize('en', dictionary)
  },
  methods: {
    onFileSelected(event) {
      this.selectedFile = event.target.files[0]
    },
    onFrontPageUpload() {
      const fd = new FormData()
      fd.append('file', this.selectedFile, this.selectedFile.name),
        this.$axios
          .$post('/companies/' + this.$route.params.id + '/imagefrontpage', fd)
          .then(res => {
            console.log(res)
          })
    },
    onFooterUpload() {
      const fd = new FormData()
      fd.append('file', this.selectedFile, this.selectedFile.name),
        this.$axios
          .$post('/companies/' + this.$route.params.id + '/imagefooterlogo', fd)
          .then(res => {
            console.log(res)
          })
    },
    onCoFooterUpload() {
      const fd = new FormData()
      fd.append('file', this.selectedFile, this.selectedFile.name),
        this.$axios
          .$post(
            '/companies/' + this.$route.params.id + '/imagecofrontpage',
            fd
          )
          .then(res => {
            console.log(res)
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
              this.submitForm()
              break
          }
        }
      })
    },
    submitForm() {
      this.$store
        .dispatch('company/create', this.company)
        .then(data => {
          this.$toast.success('Company successfully created!')
          this.$router.push({
            name: this.$getRoute('COMPANY_EDIT'),
            params: { id: data.id },
            query: { continue: 5 }
          })
        })
        .catch(() => {
          this.$toast.error(
            'Could not create company, please double check validation!'
          )
        })
    }
  }
}
</script>
<!-- Changing Styling of vuetify-country-region-input to match Vuetify correctly
TODO - Override vuetify-country-region-input and incorporate vue-country-region-input -->
<style>
.floating-label {
  position: relative;
  margin-bottom: 20px;
  height: 20px;
  line-height: 20px;
}

.floating-input,
.floating-select {
  font-size: 16px;
  padding: 8px 0 4px;
  display: block;
  width: 100%;
  line-height: 30px;
  height: 42px;
  /* background-color: transparent; */
  background-color: #fff;
  border-color: #fff;
  color: rgba(0,0,0,0.87);
  border: none;
  border-bottom: 1px solid rgba(0,0,0,0.5);
  overflow: hidden;
  margin: 0;
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  background: url('https://www.charbase.com/images/glyph/9662') no-repeat right;
  background-size: 18px;
  background-position: top 15px right 10px;
  -webkit-user-select: none;
  -moz-user-select: none;
  -webkit-padding-end: 20px;
  -moz-padding-end: 20px;
  -webkit-padding-start: 2px;
  -moz-padding-start: 2px;

}

.floating-input:focus,
.floating-select:focus {
  outline: none;
  border-bottom: 2px solid #000000;
}

.floating-input:hover,
.floating-select:hover {
  outline: none;
  border-bottom: 1px solid #000000;
}

label {
  color: rgba(0,0,0,0.6);
  font-size: 16px;
  font-weight: 400;
  min-height: 8px;
  position: absolute;
  pointer-events: none;
  left: 1px;
  top: 15px;
  transition: 0.2s ease all;
  -moz-transition: 0.2s ease all;
  -webkit-transition: 0.2s ease all;
}

.floating-input:focus~label,
.floating-input:not(:placeholder-shown)~label {
  top: -7px;
  font-size: 13px;
  color: rgba(0,0,0,0.6);
}

.floating-select:focus~label,
.floating-select:not([value=""]):valid~label {
  top: -7px;
  font-size: 13px;
  color: rgba(0,0,0,0.6);
}


/* active state */

.floating-input:focus~.bar:before,
.floating-input:focus~.bar:after,
.floating-select:focus~.bar:before,
.floating-select:focus~.bar:after {
  width: 50%;
}

*,
*:before,
*:after {
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
}

.floating-textarea {
  min-height: 30px;
  max-height: 260px;
  overflow: hidden;
  overflow-x: hidden;
}


/* highlighter */

.highlight {
  position: absolute;
  height: 50%;
  width: 100%;
  top: 15%;
  left: 0;
  pointer-events: none;
  opacity: 0.5;
}


/* active state */

.floating-input:focus~.highlight,
.floating-select:focus~.highlight {
  -webkit-animation: inputHighlighter 0.3s ease;
  -moz-animation: inputHighlighter 0.3s ease;
  animation: inputHighlighter 0.3s ease;
}


/* animation */

@-webkit-keyframes inputHighlighter {
  from {
    background: rgba(0,0,0,0.87);
  }
  to {
    width: 0;
    background: transparent;
  }
}

@-moz-keyframes inputHighlighter {
  from {
    background: rgba(0,0,0,0.87);
  }
  to {
    width: 0;
    background: transparent;
  }
}

</style>
