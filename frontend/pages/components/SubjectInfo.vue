<template>
  <div class="app">
    <v-dialog
      v-model="subjectDialog"
      scrollable
      persistent
      max-width="600px">
      <v-card>
        <v-card-title>
          <span class="headline">Subject Overview</span>
        </v-card-title>
        <v-card-text>
          <v-layout
            v-if="subjectView !== null"
            row
            wrap>
            <v-flex
              xs12
              class="subject-info">
              <v-subheader class="justify-center">Personal Identity Information</v-subheader>
              <v-layout
                row
                wrap
                px-3>
                <v-flex md5><strong>First Name</strong></v-flex>
                <v-flex md7><span>{{ subjectView.first_name || 'N/A' }}</span></v-flex>
                <v-flex
                  v-if="subjectView.middle_name"
                  md5><strong>Middle Name</strong></v-flex>
                <v-flex
                  v-if="subjectView.middle_name"
                  md7><span>{{ subjectView.middle_name }}</span></v-flex>
                <v-flex md5><strong>Last Name</strong></v-flex>
                <v-flex md7><span>{{ subjectView.last_name || 'N/A' }}</span></v-flex>
                <v-flex
                  v-if="subjectView.maiden_name"
                  md5><strong>Maiden Name</strong></v-flex>
                <v-flex
                  v-if="subjectView.maiden_name"
                  md7><span>{{ subjectView.maiden_name }}</span></v-flex>
                <v-flex md5><strong>Handles</strong></v-flex>
                <v-flex md7><span>{{ subjectView.handles ? subjectView.handles.join(', ') : 'N/A' }}</span></v-flex>
                <v-flex md5><strong>Date of Birth</strong></v-flex>
                <v-flex md7><span>{{ subjectView.date_of_birth || 'N/A' }}</span></v-flex>
                <v-flex md5><strong>Gender</strong></v-flex>
                <v-flex md7><span>{{ subjectView.gender || 'N/A' }}</span></v-flex>
                <v-flex md5><strong>Status</strong></v-flex>
                <v-flex md7><span>{{ getFormattedText(subjectView.status) }}</span></v-flex>
                <v-flex md5><strong>Report Type</strong></v-flex>
                <v-flex md7><span>{{ getFormattedText(subjectView.report_type) }}</span></v-flex>
              </v-layout>
              <v-spacer class="mt-4"/>
              <v-subheader class="justify-center">Contact Information</v-subheader>
              <v-layout
                row
                wrap
                px-3>
                <v-flex md5><strong>Primary Email</strong></v-flex>
                <v-flex md7><span>{{ subjectView.primary_email || 'N/A' }}</span></v-flex>
                <v-flex md5><strong>Secondary Email</strong></v-flex>
                <v-flex md7><span>{{ subjectView.secondary_email || 'N/A' }}</span></v-flex>
                <v-flex md5><strong>Primary Mobile Number</strong></v-flex>
                <v-flex md7><span>{{ subjectView.primary_mobile || 'N/A' }}</span></v-flex>
                <v-flex md5><strong>Secondary Mobile Number</strong></v-flex>
                <v-flex md7><span>{{ subjectView.secondary_mobile || 'N/A' }}</span></v-flex>
                <v-flex md5><strong>Address</strong></v-flex>
                <v-flex md7><span>{{ subjectView.address.street || 'N/A' }}</span></v-flex>
                <v-flex md5><strong>Suburb</strong></v-flex>
                <v-flex md7><span>{{ subjectView.address.suburb || 'N/A' }}</span></v-flex>
                <v-flex md5><strong>City</strong></v-flex>
                <v-flex md7><span>{{ subjectView.address.city || 'N/A' }}</span></v-flex>
                <v-flex md5><strong>Country </strong></v-flex>
                <v-flex md7><span>{{ subjectView.current_country || 'N/A' }}</span></v-flex>
              </v-layout>

              <v-spacer class="mt-4"/>
              <v-subheader class="justify-center">Education</v-subheader>

              <v-layout
                v-for="qualifications in subjectView.qualifications"
                :key="qualifications.id"
                row
                wrap
                px-3>

                <v-flex md5><strong>Qualifications</strong></v-flex>
                <v-flex md7><span>{{ qualifications.name || 'N/A' }}</span></v-flex>
                <v-flex md5><strong>Institute</strong></v-flex>
                <v-flex md7><span>{{ qualifications.institute || 'N/A' }}</span></v-flex>
                <v-flex md5><strong>Start date</strong></v-flex>
                <v-flex md7><span>{{ qualifications.start_date || 'N/A' }}</span></v-flex>
                <v-flex md5><strong>End Date</strong></v-flex>
                <v-flex md7><span>{{ qualifications.end_date || 'N/A' }}</span></v-flex>
                <v-flex md5><strong/></v-flex>
                <v-flex md7><span/></v-flex>

              </v-layout>

              <v-spacer class="mt-4"/>
              <v-subheader class="justify-center">Employments</v-subheader>

              <v-layout
                v-for="employments in subjectView.employments"
                :key="employments.id"
                row
                wrap
                px-3>

                <v-flex md5><strong>Employer</strong></v-flex>
                <v-flex md7><span>{{ employments.employer || 'N/A' }}</span></v-flex>
                <v-flex md5><strong>Job Title</strong></v-flex>
                <v-flex md7><span>{{ employments.job_title || 'N/A' }}</span></v-flex>
                <v-flex md5><strong>Country</strong></v-flex>
                <v-flex md7><span>{{ employments.country.name || 'N/A' }}</span></v-flex>
                <v-flex md5><strong>Currently</strong></v-flex>
                <v-flex md7>
                  <span
                    v-if="employments.currently_employed ===
                    true"><span class="material-icons">
                      business_center
                  </span></span>
                  <span v-else>N/A</span>
                </v-flex>
                <v-flex md5><strong>Start date</strong></v-flex>
                <v-flex md7><span>{{ employments.start_date || 'N/A' }}</span></v-flex>
                <v-flex md5><strong>End Date</strong></v-flex>
                <v-flex md7><span>{{ employments.end_date || 'N/A' }}</span></v-flex>
                <v-flex md5><strong/></v-flex>
                <v-flex md7><span/></v-flex>

              </v-layout>

            </v-flex>
          </v-layout>
          <v-divider/>
        </v-card-text>
        <v-card-actions>
          <v-spacer/>
          <v-btn
            color="grey darken-1"
            flat
            @click.native="subjectDialog = !subjectDialog">Close
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import _ from 'lodash'

export default {
  name: 'SubjectOverview',
  data() {
    return {
      subjectView: null,
      subjectDialog: false
    }
  },
  methods: {
    getFormattedText(value) {
      return _.startCase(value)
    },
    async openSubjectDialog(id) {
      await this.$store
        .dispatch('subject/getById', id)
        .then(response => {
          this.subjectView = response
          this.subjectDialog = true
        })

        .catch(() => {
          console.log('Could not get Subject Details')
        })
    }
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="scss">
</style>
