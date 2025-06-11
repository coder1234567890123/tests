<template>
  <div
    id="app"
    class="container">
    <h1 class="title mb-3">Add Subject</h1>
    <v-stepper v-model="e1">
      <v-stepper-header>
        <v-stepper-step step="1">Personal Information</v-stepper-step>
        <v-divider/>
        <v-stepper-step step="2">Personal Information Attributes</v-stepper-step>
        <v-divider/>
        <v-stepper-step step="3">Academic</v-stepper-step>
        <v-divider/>
        <v-stepper-step step="4">Employment History</v-stepper-step>
        <v-divider/>
        <!-- <v-stepper-step step="5">Social Media</v-stepper-step> -->
      </v-stepper-header>
      <v-stepper-items>
        <v-stepper-content step="1">
          <v-card class="mb-2 pb-5">
            <v-form data-vv-scope="step1">
              <v-layout
                row
                wrap>
                <v-flex md6>
                  <v-text-field
                    v-validate="'required|max:36'"
                    v-model="subject.identification"
                    :error-messages="errors.collect('step1.identification_number')"
                    :counter="36"
                    :autofocus="true"
                    name="identification_number"
                    data-vv-name="identification_number"
                    label="ID/Passport/SSN"/>
                  <v-text-field
                    v-validate="'required|max:36'"
                    v-model="subject.first_name"
                    :error-messages="errors.collect('step1.first_name')"
                    name="first_name"
                    data-vv-name="first_name"
                    label="First Name"/>
                  <v-text-field
                    v-model="subject.middle_name"
                    label="Second Name"
                  />
                  <v-text-field
                    v-validate="'required|max:255'"
                    v-model="subject.last_name"
                    :error-messages="errors.collect('step1.last_name')"
                    name="last_name"
                    data-vv-name="last_name"
                    label="Last Name"/>
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    v-model="subject.maiden_name"
                    label="Maiden Name"/>
                  <v-text-field
                    v-validate="`${subject.nickname ? 'max:255' : ''}`"
                    :error-messages="errors.collect('step1.nickname')"
                    v-model="subject.nickname"
                    name="nickname"
                    data-vv-name="nickname"
                    label="Nickname"/>
                  <v-menu
                    :close-on-content-click="false"
                    v-model="dobMenu"
                    :nudge-right="40"
                    lazy
                    transition="scale-transition"
                    offset-y
                    full-width
                    max-width="290px"
                    min-width="290px"
                  >
                    <v-text-field
                      slot="activator"
                      v-model="subject.date_of_birth"
                      label="Date of Birth"
                      hint="YYYY-MM-DD format"
                      persistent-hint
                      typeable="true"
                      prepend-icon="event"
                    />
                    <v-date-picker
                      v-model="subject.date_of_birth"
                      no-title
                      typeable="true"
                      @input="dobMenu = false"/>
                  </v-menu>
                  <v-select
                    id="gender"
                    v-model="subject.gender"
                    :items="genders"
                    label="Gender"/>
                </v-flex>
                <v-flex md6>
                  <v-select
                    v-validate="'required'"
                    v-model="subject.report_type"
                    :items="report_types"
                    :error-messages="errors.collect('step1.report_types')"
                    item-text="label"
                    item-value="value"
                    name="report_types"
                    label="Report type"/>
                </v-flex>
                <v-flex
                  v-if="$auth.hasScope('ROLE_SUPER_ADMIN') || $auth.hasScope('ROLE_TEAM_LEAD')"
                  md6>
                  <v-select
                    v-validate="'required'"
                    v-model="subject.company.id"
                    :items="companies"
                    :error-messages="errors.collect('step1.company')"
                    item-text="name"
                    item-value="id"
                    autocomplete
                    name="company"
                    label="Company"/>
                </v-flex>
                <v-flex md12>
                  <v-layout row>
                    <v-flex md6>
                      <h1>Handles</h1>
                    </v-flex>
                    <v-flex class="text-md-right">
                      <v-btn
                        small
                        @click="addHandle()">
                        Add
                      </v-btn>
                    </v-flex>
                  </v-layout>
                  <hr>
                  <p
                    v-if="subject.handles.length === 0"
                    class="mt-1">No handles specified.</p>
                  <v-layout
                    v-if="subject.handles.length > 0"
                    row
                    wrap>
                    <v-flex
                      v-for="(handle, index) in subject.handles"
                      :key="index"
                      md4>
                      <v-text-field
                        v-model="subject.handles[index]"
                        append-icon="close"
                        label="Handle"
                        @click:append="removeHandle(index)"/>
                    </v-flex>
                  </v-layout>
                </v-flex>
              </v-layout>
            </v-form>
          </v-card>
          <v-layout row>
            <v-flex
              md12
              class="text-md-right">
              <v-btn
                :nuxt="true"
                to="/subjects"
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
                    v-validate="'min:5|max:30'"
                    :error-messages="errors.collect('step2.mobile_number')"
                    :autofocus="true"
                    v-model="subject.primary_mobile"
                    name="mobile_number"
                    label="Primary Mobile Number"/>
                  <v-text-field
                    v-validate="'email'"
                    :error-messages="errors.collect('step2.email')"
                    v-model="subject.primary_email"
                    name="email"
                    label="Primary Email"/>
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    v-validate="`${subject.secondary_mobile.length ? 'min:5|max:30' : ''}`"
                    :error-messages="errors.collect('step2.secondary_mobile')"
                    v-model="subject.secondary_mobile"
                    name="secondary_mobile"
                    label="Secondary Mobile Number"/>
                  <v-text-field
                    v-validate="`${subject.secondary_email.length ? 'email' : ''}`"
                    v-model="subject.secondary_email"
                    :error-messages="errors.collect('step2.secondary_email')"
                    name="secondary_email"
                    label="Secondary Email"/>
                </v-flex>
              </v-layout>
              <v-layout
                row
                wrap>
                <v-flex md6>
                  <v-text-field
                    v-model="subject.address.street"
                    label="Street / Full Address"/>
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    v-model="subject.address.suburb"
                    label="Suburb/County"/>
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    v-validate="`${subject.address.postal_code ? 'alpha_num|min:4|max:10' : ''}`"
                    :error-messages="errors.collect('step2.postal_code')"
                    v-model="subject.address.postal_code"
                    name="postal_code"
                    data-vv-name="postal_code"
                    label="Postal/Zip Code"/>
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    v-model="subject.address.city"
                    label="City"/>
                </v-flex>
                <v-flex md6>
                  <div class="floating-form">
                  <div class="floating-label">
                  <country-select
                    class="floating-select"
                    :error-messages="errors.collect('step2.country')"
                    v-model="subject.country"
                    :country="subject.country"
                    countryName="true"
                    placeholder=""
                    name="country"
                    label="Country"/>
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
                    v-model="subject.province"
                    :country="subject.country"
                    :region="subject.province"
                    countryName="true"
                    regionName="true"
                    placeholder=""
                    name="region"
                    label="Province/State"/>
                    <span class="highlight"></span>
                    <label for="region">Province/State</label>
                  </div>
                  </div>
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
                to="/subjects"
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
      </v-stepper-items>
    </v-stepper>
    <v-dialog
      v-model="duplicateDialog"
      max-width="800px">
      <v-card>
        <v-card-text>
          <v-layout
            row
            wrap>
            <v-flex
              md12
              class="text-xs-center">
              <v-card-title>
                <v-icon color="red darken-1">error</v-icon>
                <span class="headline">Duplicate Subject found</span>
              </v-card-title>
              <v-divider/>
            </v-flex>
          </v-layout>
          <v-layout
            row
            wrap>
            <v-flex md12>
              <v-card-title>
                <p class="text-xs-center">{{ response.message }}</p>
              </v-card-title>
            </v-flex>
          </v-layout>
        </v-card-text>
        <v-card-actions>
          <v-spacer/>
          <v-btn
            flat
            color="red"
            @click="duplicateDialog = !duplicateDialog">Close
          </v-btn>
          <v-btn
            flat
            color="green darken-3"
            @click="redirect(response.id)">Edit Subject
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>
<script>
import Datepicker from 'vuejs-datepicker'
import { mapGetters } from 'vuex'

export default {
  inject: ['$validator'],
  components: {
    Datepicker
  },
  head() {
    return {
      title: 'Add Subject :: Farosian'
    }
  },
  async fetch({ store }) {
    await store.dispatch('static/initCountries').catch(() => {
      console.log('Could not get countries')
    })
    await store.dispatch('company/queryAllCompanies').catch(() => {
      console.log('Could not get companies')
    })
  },
  data() {
    return {
      e1: 0,
      dobMenu: false,
      duplicateDialog: false,
      subject: {
        identification: '',
        first_name: '',
        second_name: '',
        last_name: '',
        middle_name: '',
        maiden_name: '',
        nickname: '',
        date_of_birth: null,
        handles: [],
        primary_mobile: '',
        secondary_mobile: '',
        primary_email: '',
        secondary_email: '',
        allow_trait: false,
        gender: '',
        province: '',
        country: '',
        address: {
          street: '',
          suburb: '',
          postal_code: '',
          city: ''
        },
        report_type: '',
        company: {
          id: ''
        }
      },
      date: null,
      handles: [],
      response: { message: '', id: '' },
      genders: ['Male', 'Female', 'Not Specified'],
      provinces: [
        'Eastern Cape',
        'Free State',
        'Gauteng',
        'KwaZulu-Natal',
        'Limpopo',
        'Mpumalanga',
        'North West',
        'Northern Cape',
        'Western Cape'
      ],
      valid: true,
      dictionary: {
        attributes: {
          // custom attributes
          identification_number: 'Identification Number',
          first_name: 'First Name',
          last_name: 'Last Name',
          secondary_email: 'Secondary Email',
          report_types: 'Report Type',
          company: 'Company',
          nickname: 'Nickname',
          secondary_mobile: 'Secondary mobile',
          mobile_number: 'Primary Mobile',
          email: 'Email Address',
          postal_code: 'Postal Code',
          allow_trait: 'Allow Trait',
          country: 'Country'
        },
        custom: {}
      }
    }
  },
  computed: {
    ...mapGetters({
      countries: 'static/countries',
      report_types: 'static/reportTypes',
      companies: 'company/companies'
    })
  },
  watch: {},
  mounted() {
    this.$validator.localize('en', this.dictionary)
  },
  methods: {
    removeHandle(index) {
      this.subject.handles.splice(index, 1)
    },
    redirect(id) {
      this.$router.push({
        name: this.$getRoute('SUBJECTS_EDIT'),
        params: { id: id },
        query: { continue: 1 }
      })
    },
    addHandle() {
      this.subject.handles.push('')
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
              this.step2Action()
              break
          }
        }
      })
    },
    step2Action() {
      if (
        !this.$auth.hasScope('ROLE_SUPER_ADMIN') &&
        this.$auth.$state.user.company !== undefined
      ) {
        //Checks to see if company is state
        if (this.$auth.$state.user.company.id) {
          this.subject.company = { id: this.$auth.$state.user.company.id }
        } else {
          this.subject.company = { id: this.subject.company.id }
        }
      }

      this.$store
        .dispatch('subject/create', this.subject)
        .then(data => {
          if (data.error) {
            this.response.message = data.message
            this.response.id = data.id
            this.duplicateDialog = true
            //this.$toast.error(data.message)
          } else {
            this.$toast.success('Subject successfully created!')
            this.$router.push({
              name: this.$getRoute('SUBJECTS_EDIT'),
              params: { id: data.id },
              query: { continue: 3 }
            })
          }
        })
        .catch(err => {
          this.$toast.error(
            'Could not create subject, please double check validation!'
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
