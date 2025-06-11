<template xmlns:v-slot="http://www.w3.org/1999/XSL/Transform">
  <div
    id="app"
    class="container">
    <h1 class="title mb-3">Edit Subject</h1>

    <v-stepper v-model="e1">
      <v-stepper-header>
        <v-stepper-step step="1">Personal Information</v-stepper-step>
        <v-divider/>
        <v-stepper-step step="2">Personal Information Attributes</v-stepper-step>
        <v-divider/>
        <v-stepper-step step="3">Academic</v-stepper-step>
        <v-divider/>
        <v-stepper-step step="4">Employment History</v-stepper-step>
      </v-stepper-header>

      <v-stepper-items
        v-if="subject !== null"
        key="subjectStep"
      >
        <v-stepper-content step="1">
          <v-card class="mb-2 pb-5">
            <v-form
              v-model="valid"
              data-vv-scope="step1">
              <v-layout
                row
                wrap>
                <v-flex md6>
                  <v-text-field
                    v-validate="'required|max:36'"
                    :value="subject.identification"
                    :error-messages="errors.collect('step1.identification_number')"
                    :counter="36"
                    :autofocus="true"
                    data-vv-name="identification_number"
                    label="ID/Passport/SSN"
                    @input="updateState('identification', $event)"/>
                  <v-text-field
                    v-validate="'required|max:36'"
                    :value="subject.first_name"
                    :error-messages="errors.collect('step1.first_name')"
                    label="First Name"
                    data-vv-name="first_name"
                    @input="updateState('first_name', $event)"/>
                  <v-text-field
                    :value="subject.middle_name"
                    label="Second Name"
                    @input="updateState('middle_name', $event)"/>
                  <v-text-field
                    v-validate="'required|max:255'"
                    :value="subject.last_name"
                    :error-messages="errors.collect('step1.last_name')"
                    label="Last Name"
                    data-vv-name="last_name"
                    @input="updateState('last_name', $event)"/>
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    :value="subject.maiden_name"
                    label="Maiden Name"
                    @input="updateState('maiden_name', $event)"/>
                  <v-text-field
                    v-validate="`${subject.nickname ? 'max:255' : ''}`"
                    :error-messages="errors.collect('step1.nickname')"
                    :value="subject.nickname"
                    label="Nickname"
                    data-vv-name="nickname"
                    @input="updateState('nickname', $event)"/>
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
                      :value="subject.date_of_birth"
                      label="Date of Birth"
                      hint="YYYY-MM-DD format"
                      persistent-hint
                      typeable="true"
                      prepend-icon="event"
                    />
                    <v-date-picker
                      :value="subject.date_of_birth"
                      no-title
                      typeable="true"
                      @input="dobMenu = false; updateState('date_of_birth', $event)"/>
                  </v-menu>
                  <v-select
                    id="gender"
                    :value="subject.gender"
                    :items="genders"
                    tabindex="5"
                    label="Gender"
                    @input="updateState('gender', $event)"/>
                </v-flex>
                <v-flex md6>
                  <v-select
                    v-validate="'required'"
                    :disabled="!$auth.hasScope('ROLE_SUPER_ADMIN') && (!$auth.hasScope('ROLE_SUPER_ADMIN') && subject.status !== 'new_subject')"
                    :value="subject.report_type"
                    :items="report_types"
                    :error-messages="errors.collect('step1.report_types')"
                    item-text="label"
                    item-value="value"
                    label="Report type"
                    data-vv-name="report_type"
                    @input="updateState('report_type', $event)"/>
                </v-flex>
                <v-flex
                  v-if="$auth.hasScope('ROLE_SUPER_ADMIN') || $auth.hasScope('ROLE_TEAM_LEAD')"
                  key="ROLE_SUPER_ADMIN"
                  md6>
                  <v-select
                    v-validate="'required'"
                    :value="subject.company ? subject.company.id : ''"
                    :items="companies"
                    :error-messages="errors.collect('step1.company')"
                    item-text="name"
                    item-value="id"
                    label="Company"
                    data-vv-name="company"
                    @input="updateCompany('id', $event)"/>
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
                        :value="subject.handles[index]"
                        append-icon="close"
                        label="Handle"
                        @input="updateHandle(index, $event)"
                        @click:append="removeHandle(index)" />
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
                :to="{ name: $getRoute('SUBJECTS_VIEW'), params: { id: this.$route.params.id } }"
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
                    :autofocus="true"
                    :value="subject.primary_mobile"
                    :error-messages="errors.collect('step2.primary_mobile')"
                    tabindex="1"
                    label="Primary Mobile Number"
                    data-vv-name="primary_mobile"
                    @input="updateState('primary_mobile', $event)"/>
                  <v-text-field
                    v-validate="'email'"
                    :value="subject.primary_email"
                    :error-messages="errors.collect('step2.primary_email')"
                    tabindex="3"
                    label="Email"
                    data-vv-name="primary_email"
                    @input="updateState('primary_email', $event)"/>
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    v-validate="`${subject.secondary_mobile.length ? 'min:5|max:30' : ''}`"
                    :error-messages="errors.collect('step2.secondary_mobile')"
                    :value="subject.secondary_mobile"
                    tabindex="2"
                    label="Secondary Mobile Number"
                    data-vv-name="secondary_mobile"
                    @input="updateState('secondary_mobile', $event)"/>
                  <v-text-field
                    v-validate="`${subject.secondary_email.length ? 'email' : ''}`"
                    :error-messages="errors.collect('step2.secondary_email')"
                    :value="subject.secondary_email"
                    tabindex="4"
                    label="Secondary Email"
                    data-vv-name="secondary_email"
                    @input="updateState('secondary_email', $event)"/>
                </v-flex>
              </v-layout>

              <v-layout
                row
                wrap
                class="mt-4">
                <v-flex md6>
                  <v-text-field
                    :value="subject.address.street"
                    label="Street / Full Address"
                    @input="updateAddress('street', $event)"/>
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    :value="subject.address.suburb"
                    label="Suburb/County"
                    @input="updateAddress('suburb', $event)"/>
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    v-validate="`${subject.address.postal_code ? 'alpha_num|min:4|max:10' : ''}`"
                    :error-messages="errors.collect('step2.postal_code')"
                    :value="subject.address.postal_code"
                    data-vv-name="postal_code"
                    label="Postal/Zip Code"
                    @input="updateAddress('postal_code', $event)"/>
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    :value="subject.address.city"
                    label="City"
                    @input="updateAddress('city', $event)"/>
                </v-flex>
                <v-flex md6>
                  <v-autocomplete
                    :error-messages="errors.collect('step2.country')"
                    :value="subject.country"
                    :items="countries"
                    item-text="name"
                    item-value="id"
                    label="Country"
                    data-vv-name="country"
                    @input="updateState('country', $event)"/>
                </v-flex>
                <v-flex md6>
                  <v-text-field
                    :value="subject.province"
                    label="Province/State"
                    @input="updateState('province', $event)"/>
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
                :to="{ name: $getRoute('SUBJECTS_VIEW'), params: { id: this.$route.params.id } }"
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
            <v-layout
              row
              wrap>
              <v-flex
                md12
                class="text-xs-right">
                <v-btn
                  dark
                  @click="openAcademic">
                  Add
                </v-btn>
              </v-flex>
              <v-flex md12>
                <v-data-table
                  :headers="academicHeaders"
                  :items="academicHistory"
                  class="elevation-1">
                  <template
                    slot="items"
                    slot-scope="props">
                    <td>{{ props.item.name }}</td>
                    <td>{{ props.item.start_date }}</td>
                    <td>{{ props.item.end_date }}</td>
                    <td>{{ props.item.institute }}</td>
                    <td class="left">
                      <v-btn
                        small
                        @click="editAcademic(props.item)">
                        Edit
                      </v-btn>
                      <v-btn
                        small
                        @click="deleteAcademic(props.item)">
                        Delete
                      </v-btn>
                    </td>
                  </template>
                </v-data-table>

                <v-dialog
                  v-model="academicDialog"
                  persistent
                  max-width="600px">
                  <v-card>
                    <v-card-title>
                      <span class="headline">Academic History</span>
                    </v-card-title>
                    <v-card-text>
                      <v-container grid-list-md>
                        <v-form data-vv-scope="academic">
                          <v-layout wrap>
                            <v-flex md12>
                              <v-text-field
                                v-validate="'required'"
                                key="academicDialog"
                                :error-messages="errors.collect('academic.name')"
                                v-model="activeAcademic.name"
                                label="Qualification Name"
                                data-vv-name="name"
                                autofocus
                                class="mb-2"
                                required/>
                            </v-flex>
                            <v-flex
                              md6
                            >
                              <p
                              >Start Date</p>
                              <v-overflow-btn
                                v-validate="'required'"
                                :items="dates"
                                :error-messages="errors.collect('academic.start_date')"
                                v-model="activeAcademic.start_date"
                                data-vv-name="start_date"
                                class="mt-0 pt-0"
                                label="Start Date"
                              />
                            </v-flex>
                            <v-flex md6>
                              <p
                              >End Date</p>
                              <v-overflow-btn
                                v-validate="'required'"
                                :items="dates"
                                :error-messages="errors.collect('academic.end_date')"
                                v-model="activeAcademic.end_date"
                                data-vv-name="end_date"
                                class="mt-0 pt-0"
                                label="End Date"
                              />
                            </v-flex>
                            <v-flex md12>
                              <v-text-field
                                v-validate="'required'"
                                :error-messages="errors.collect('academic.institute')"
                                v-model="activeAcademic.institute"
                                data-vv-name="institute"
                                label="Institute Name"
                                required
                              />
                            </v-flex>
                          </v-layout>
                        </v-form>
                      </v-container>
                    </v-card-text>
                    <v-card-actions>
                      <v-spacer/>
                      <v-btn
                        color="blue darken-1"
                        flat
                        @click="closeDialog()">Close</v-btn>
                      <div v-if="activeAcademic.id == undefined">
                        <v-btn
                          :loading="academicLoading"
                          color="blue darken-1"
                          flat
                          @click="validateForm('academic')">Save</v-btn>
                      </div>
                      <div v-else>
                        <v-btn
                          :loading="academicLoading"
                          color="blue darken-1"
                          flat
                          @click="validateForm('academic')">Update</v-btn>
                      </div>
                    </v-card-actions>
                  </v-card>
                </v-dialog>
              </v-flex>
            </v-layout>
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
                :to="{ name: $getRoute('SUBJECTS_VIEW'), params: { id: this.$route.params.id } }"
                flat>Cancel
              </v-btn>
              <v-btn
                color="teal darken-3"
                class="white--text"
                @click="e1 = 4">
                Continue
              </v-btn>
            </v-flex>
          </v-layout>
        </v-stepper-content>

        <v-stepper-content step="4">
          <v-card class="mb-2 pb-5">
            <v-layout
              row
              wrap>
              <v-flex
                md12
                class="text-xs-right">
                <v-btn
                  dark
                  @click="openEmploymentDialog">
                  Add
                </v-btn>
              </v-flex>
              <v-flex md12>
                <v-data-table
                  :headers="employmentHeaders"
                  :items="employmentHistory"
                  class="elevation-1">
                  <template
                    slot="items"
                    slot-scope="props">
                    <td>{{ props.item.job_title }}</td>
                    <td>{{ props.item.employer }}</td>
                    <td>{{ props.item.start_date }}</td>
                    <td>{{ props.item.end_date }}</td>
                    <td>
                      <i
                        v-if="props.item.currently_employed == true"
                        class="material-icons">
                        business_center
                      </i>
                    </td>
                    <td class="text-sm-left">
                      <v-btn
                        small
                        @click="editEmployment(props.item)">
                        Edit
                      </v-btn>
                      <v-btn
                        small
                        @click="deleteEmployment(props.item)">
                        Delete
                      </v-btn>
                    </td>
                  </template>
                </v-data-table>

                <v-dialog
                  v-model="employmentDialog"
                  persistent
                  max-width="600px">
                  <v-card>
                    <v-card-title>
                      <span class="headline">Employment History</span>
                    </v-card-title>
                    <v-card-text>
                      <v-container grid-list-md>
                        <v-form data-vv-scope="employment">
                          <v-layout wrap>
                            <v-flex
                              md12>
                              <v-text-field
                                v-validate="'required'"
                                :error-messages="errors.collect('employment.job_title')"
                                v-model="activeEmployment.job_title"
                                label="Job Title"
                                data-vv-name="job_title"
                                autofocus
                                required/>
                            </v-flex>
                            <v-flex md12>
                              <v-text-field
                                v-validate="'required'"
                                :error-messages="errors.collect('employment.employer')"
                                v-model="activeEmployment.employer"
                                data-vv-name="employer"
                                label="Employer"
                                required/>
                            </v-flex>
                            <v-flex md6>
                              <v-menu
                                :close-on-content-click="false"
                                v-model="employmentStartDateMenu"
                                :nudge-right="40"
                                lazy
                                transition="scale-transition"
                                offset-y
                                full-width
                                max-width="290px"
                                min-width="290px"
                              >
                                <v-text-field
                                  v-validate="'required'"
                                  slot="activator"
                                  :error-messages="errors.collect('employment.start_date')"
                                  v-model="activeEmployment.start_date"
                                  label="Start Date"
                                  hint="YYYY-MM-DD format"
                                  persistent-hint
                                  prepend-icon="event"
                                  data-vv-name="start_date"
                                  typeable="true"
                                  required
                                />
                                <v-date-picker
                                  v-model="activeEmployment.start_date"
                                  no-title
                                  typeable="true"
                                  actions/></v-menu></v-flex>
                            <v-flex md6>
                              <v-menu
                                :close-on-content-click="false"
                                v-model="employmentEndDateMenu"
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
                                  :error-messages="errors.collect('employment.end_date')"
                                  v-model="activeEmployment.end_date"
                                  label="End Date"
                                  hint="YYYY-MM-DD format"
                                  persistent-hint
                                  prepend-icon="event"
                                  data-vv-name="end_date"
                                  typeable="true"
                                  required
                                />
                                <v-date-picker
                                  v-model="activeEmployment.end_date"
                                  no-title
                                  typeable="true"
                                  actions/></v-menu>
                            </v-flex>
                            <v-flex md6/>
                            <v-flex md6>
                              <v-checkbox
                                v-model="activeEmployment.currently_employed"
                                :input-value="activeEmployment.currently_employed === null ? false: activeEmployment.currently_employed"
                                label="Currently Employed"/>
                            </v-flex>
                            <v-flex md6>
                              <div class="floating-form">
                              <div class="floating-label">
                              <country-select
                                class="floating-select"
                                v-validate="'required'"
                                :error-messages="errors.collect('employment.country')"
                                v-model="activeEmployment.country"
                                :country="activeEmployment.country"
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
                                v-model="activeEmployment.province"
                                :country="activeEmployment.country"
                                :region="activeEmployment.province"
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
                            <v-flex md12>
                              <v-text-field
                                v-model="activeEmployment.address.street"
                                label="Street / Full Address"/>
                            </v-flex>
                            <v-flex md6>
                              <v-text-field
                                v-model="activeEmployment.address.suburb"
                                label="Suburb"/>
                            </v-flex>
                            <v-flex md6>
                              <v-text-field
                                v-model="activeEmployment.address.city"
                                label="City"/>
                            </v-flex>
                            <v-flex md6>
                              <v-text-field
                                v-model="activeEmployment.address.postal_code"
                                label="Postal Code"/>
                            </v-flex>
                          </v-layout>
                        </v-form>
                      </v-container>
                    </v-card-text>
                    <v-card-actions>
                      <v-spacer/>
                      <v-btn
                        color="blue darken-1"
                        flat
                        @click="closeEmploymentDialog()">Close</v-btn>
                      <div v-if="activeEmployment.id.length == 0">
                        <v-btn
                          :loading="employmentLoading"
                          color="blue darken-1"
                          flat
                          @click="validateForm('employment')">Save</v-btn>
                      </div>
                      <div v-else>
                        <v-btn
                          :loading="employmentLoading"
                          color="blue darken-1"
                          flat
                          @click="validateForm('employment')">Update</v-btn>
                      </div>
                    </v-card-actions>
                  </v-card>
                </v-dialog>
              </v-flex>
            </v-layout>
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
                :to="{ name: $getRoute('SUBJECTS_VIEW'), params: { id: this.$route.params.id } }"
                flat>Complete
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
import Datepicker from 'vuejs-datepicker'
import { mapGetters } from 'vuex'

export default {
  inject: ['$validator'],
  components: {
    Datepicker
  },
  head() {
    return {
      title: 'Edit Subject :: Farosian'
    }
  },
  async fetch({ store, route }) {
    await store.dispatch('static/initCountries').catch(() => {
      console.log('Could not get countries')
    })
    await store.dispatch('subject/get', route.params.id).catch(() => {
      console.log('Could not get the specified subject')
    })
    await store.dispatch('company/queryAllCompanies').catch(() => {
      console.log('Could not get companies')
    })
  },
  data() {
    return {
      employmentStartDateMenu: false,
      employmentEndDateMenu: false,
      e1: 0,
      startDate: '',
      endDate: '',
      dobMenu: false,
      genders: ['Male', 'Female', 'Not Specified'],
      valid: true,
      academicHeaders: [
        { text: 'Name', value: 'name' },
        { text: 'Start Year', value: 'start_date' },
        { text: 'End Year', value: 'end_date' },
        { text: 'Institute', value: 'institute' },
        { text: 'Actions', value: '' }
      ],
      academicDialog: false,
      academicLoading: false,
      activeAcademic: {
        name: '',
        start_date: '',
        end_date: '',
        institute: '',
        actions: ''
      },
      employmentDialog: false,
      employmentLoading: false,
      employmentHeaders: [
        { text: 'Job Title', value: 'job_title' },
        { text: 'Employer', value: 'employer' },
        { text: 'Start Date', value: 'start_date' },
        { text: 'End Date', value: 'end_date' },
        { text: 'Current', value: '' },
        { text: 'Actions', value: '' }
      ],
      activeEmployment: {
        id: '',
        job_title: '',
        employer: '',
        start_date: '',
        end_date: '',
        currently_employed: '',
        country: '',
        province: '',
        address: {
          city: '',
          postal_code: '',
          street: '',
          suburb: ''
        }
      },
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
          country: 'Country',
          primary_mobile: 'Primary Mobile Number',
          job_title: 'Job Title',
          start_date: 'Start Date',
          end_date: 'End Date',
          institute: 'Institute Name'
        },
        custom: {}
      }
    }
  },
  computed: {
    ...mapGetters({
      countries: 'static/countries',
      subject: 'subject/subject',
      academicHistory: 'subject/academicHistory',
      employmentHistory: 'subject/employmentHistory',
      report_types: 'static/reportTypes',
      companies: 'company/companies'
    }),
    dates() {
      var ans = []
      for (let i = this.startDate; i <= this.endDate; i++) {
        ans.push(i)
      }
      return ans
    },
    data: () => ({
      date: new Date().toISOString().substr(0, 7),
      menu: false,
      modal: false
    })
  },
  mounted() {
    // this.$store.dispatch('product-types/getProductTypes', this.subject.company.id)
    //   .catch(() => {
    //     console.log('Could not get ProductTypes')
    //   })

    Vue.nextTick(
      function() {
        if (this.$route.query.continue !== undefined) {
          this.e1 = this.$route.query.continue
        }
      }.bind(this)
    )
    this.$validator.localize('en', this.dictionary)
  },
  methods: {
    removeHandle(index) {
      this.$store.dispatch('subject/updateHandles', index).catch(() => {
        console.log('Could not update handles data')
      })
    },
    updateHandle(index, value) {
      this.$store
        .dispatch('subject/setHandles', { index: index, value: value })
        .catch(() => {
          console.log('Could not update handles data')
        })
    },
    addHandle() {
      this.$store.dispatch('subject/updateHandles').catch(() => {
        console.log('Could not update handles data')
      })
    },
    updateState(prop, value) {
      this.$store
        .dispatch('subject/updateSubject', { prop, value })
        .catch(() => {
          console.log('Could not update subject data')
        })
    },
    updateAddress(prop, value) {
      this.$store
        .dispatch('subject/updateAddress', { prop, value })
        .catch(() => {
          console.log('Could not update address data')
        })
    },
    updateCompany(prop, value) {
      this.$store
        .dispatch('subject/updateCompany', { prop, value })
        .catch(() => {
          console.log('Could not update company data')
        })
    },
    validateForm(scope) {
      const step = scope.replace('step', '')
      this.$validator.validateAll(scope).then(result => {
        if (result) {
          // this.$validator.reset()
          switch (step) {
            case '1':
              this.e1 = 2
              break
            case '2':
              this.step2Action()
              break
            case 'employment':
              this.saveEmployment()
              break
            case 'academic':
              if (this.activeAcademic.id == undefined) {
                this.saveAcademic()
              } else {
                this.updateAcademic()
              }
              break
          }
        }
      })
    },
    step2Action() {
      if (this.subject.country instanceof Object) {
        this.updateState('country', this.subject.country.id)
      }

      this.$store
        .dispatch('subject/update', this.subject, this.$route.params.id)
        .then(() => {
          this.e1 = 3
        })
        .catch(() => {
          this.$toast.error(
            'Could not update subject, please double check validation!'
          )
        })
    },
    saveAcademic() {
      this.academicLoading = true
      this.$store
        .dispatch('subject/saveQualification', {
          qualification: this.activeAcademic,
          subject: this.subject.id
        })
        .then(data => {
          this.$toast.success('Qualification saved!')
          this.academicLoading = false
          this.closeDialog()
          this.$store.commit('subject/SET_SUBJECT', data)
        })
        .catch(() => {
          this.$toast.error(
            'Could not save qualification, please double check validation!'
          )
        })
      this.academicLoading = false
    },
    closeDialog() {
      this.academicDialog = false
      this.activeAcademic = {
        name: '',
        start_date: '',
        end_date: '',
        institute: ''
      }
    },
    openAcademic() {
      this.academicDialog = true

      let year = new Date().getFullYear()

      this.activeAcademic.start_date = 1970
      this.activeAcademic.end_date = year

      this.startDate = 1970
      this.endDate = year
    },
    updateAcademic() {
      this.academicLoading = true
      this.$store
        .dispatch('subject/updateQualification', {
          qualification: this.activeAcademic,
          subject: this.subject.id
        })
        .then(data => {
          this.$toast.success('Qualification saved!')
          this.academicLoading = false
          this.closeDialog()
          this.$store.commit('subject/SET_SUBJECT', data)
        })
        .catch(() => {
          this.$toast.error(
            'Could not save qualification, please double check validation!'
          )
        })
      this.academicLoading = false
    },
    editAcademic(qualification) {
      let year = new Date().getFullYear()

      this.activeAcademic.start_date = 1970
      this.activeAcademic.end_date = year

      this.startDate = 1970
      this.endDate = year

      this.activeAcademic = {
        id: qualification.id,
        name: qualification.name,
        start_date: qualification.start_date,
        end_date: qualification.end_date,
        institute: qualification.institute
      }
      this.academicDialog = true
    },
    deleteAcademic(qualification) {
      this.$store
        .dispatch('subject/deleteQualification', {
          qualification,
          subject: this.subject
        })
        .then(data => {
          this.$toast.success('Qualification deleted!')
          this.$store.commit('subject/SET_SUBJECT', data)
        })
    },
    openEmploymentDialog() {
      this.employmentDialog = true
      let day = new Date().getDate()
      let month = new Date().getMonth() + 1
      let year = new Date().getFullYear()

      if (
        this.activeEmployment.start_date == '' ||
        this.activeEmployment.start_date == null
      ) {
        this.activeEmployment.start_date = year + '-' + month + '-' + day
      }
      if (
        this.activeEmployment.end_date == '' ||
        this.activeEmployment.end_date == null
      ) {
        this.activeEmployment.end_date = year + '-' + month + '-' + day
      }
    },
    closeEmploymentDialog() {
      this.employmentDialog = false
      this.activeEmployment = {
        id: '',
        job_title: '',
        employer: '',
        start_date: '',
        end_date: '',
        currently_employed: '',
        country: '',
        province: '',
        address: {
          city: '',
          postal_code: '',
          street: '',
          suburb: ''
        }
      }
    },
    saveEmployment() {
      this.employmentLoading = true
      this.$store
        .dispatch('subject/saveEmployment', {
          employment: this.activeEmployment,
          subject: this.subject.id
        })
        .then(data => {
          this.employmentLoading = false
          this.$toast.success('Employment saved!')
          this.closeEmploymentDialog()
          this.$store.commit('subject/SET_SUBJECT', data)
        })
        .catch(() => {
          this.employmentLoading = false
          this.$toast.error(
            'Could not save employment, please double check validation!'
          )
        })
      this.employmentLoading = false
    },
    editEmployment(employment) {
      this.activeEmployment = {
        id: employment.id,
        job_title: employment.job_title,
        employer: employment.employer,
        start_date: employment.start_date,
        end_date: employment.end_date,
        currently_employed: employment.currently_employed,
        country: employment.country,
        province: employment.province,
        address: {
          city: employment.address.city,
          postal_code: employment.address.postal_code,
          street: employment.address.street,
          suburb: employment.address.suburb
        }
      }
      this.employmentDialog = true
    },
    deleteEmployment(employment) {
      this.$store
        .dispatch('subject/deleteEmployment', {
          employment,
          subject: this.subject
        })
        .then(data => {
          this.$toast.success('Employment deleted!')
          this.$store.commit('subject/SET_SUBJECT', data)
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
