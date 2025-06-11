<template>
  <div
    id="app"
    class="container">
    <modal
      ref="modal"
    />
    <h1 class="title mb-3">{{ subject !== null ? subject.first_name + " " + subject.last_name : 'Loading...' }}</h1>
    <v-card>
      <v-card-text>
        <v-layout
          v-if="subject !== null"
          row
          wrap>
          <v-flex
            md2
            style="max-width: inherit">
            <v-layout
              row
              wrap>
              <v-flex
                class="profile-img"
                md12>
                <img
                  :src="subject.identity_image_url ? subject.identity_image_url : '/profile.png'"
                  alt="">
                <div class="file">
                  <a @click="dialog = true">
                    Change Photo
                  </a>
                </div>
                <v-layout class="mt-3">
                  <v-flex md6>
                    <v-btn
                      v-if="!$auth.hasScope('ROLE_ANALYST')"
                      :to="{ name: $getRoute('SUBJECTS_EDIT'), params: { id: this.$route.params.id } }"
                      block
                      class="white--text"
                      color="teal darken-3"> Edit
                    </v-btn>
                  </v-flex>
                  <v-flex
                    v-if="subject.status !== 'completed'"
                    md6>
                    <v-btn
                      v-if="subject.status !== 'new_subject' && subject.status !== 'new_request' && subject.status !== 'search_started' && ($auth.hasScope('ROLE_SUPER_ADMIN') || $auth.hasScope('ROLE_TEAM_LEAD') || $auth.hasScope('ROLE_ANALYST'))"
                      :to="{ name: $getRoute('SUBJECTS_PROFILES'), params: { id: this.$route.params.id } }"
                      block
                      class="white--text"
                      color="teal darken-3"> Profiles
                    </v-btn>
                  </v-flex>
                </v-layout>
              </v-flex>
              <v-btn
                v-if="checkForCurrent()"
                class="buttonWidth"
                dark
                @click.prevent="currentInvestigation(subject.id)">Current Investigation
              </v-btn>
              <v-btn
                v-if="subject.status === 'validated' && ($auth.hasScope('ROLE_SUPER_ADMIN') || $auth.hasScope('ROLE_TEAM_LEAD') || $auth.hasScope('ROLE_ANALYST'))"
                class="buttonWidth"
                dark
                @click.prevent="newInvestigation(subject.id)">New Investigation
              </v-btn>
              <v-btn
                v-if="subject.reports && subject.reports.length > 0 || subject.reports && subject.status === 'completed'"
                class="buttonWidth"
                dark
                @click.prevent="previousInvestigations(subject.id)">Previous Investigations
              </v-btn>
              <v-btn
                v-if="subject.status === 'completed' && ($auth.hasScope('ROLE_SUPER_ADMIN') || $auth.hasScope('ROLE_TEAM_LEAD') || $auth.hasScope('ROLE_ANALYST'))"
                class="buttonWidth"
                dark
                @click.prevent="reportStatus()">Report
              </v-btn>
              <v-btn
                v-if="subject.status === 'completed' && subject.report_pdf.file_name !== null"
                :loading="loading"
                class="buttonWidth"
                color="teal darken-3 white--text"
                download="pdf"
                @click="createPdf(subject.id)"
              >
                Report Pdf
              </v-btn>
              <div
                v-if="subject.status == 'completed' && ($auth.hasScope('ROLE_SUPER_ADMIN') || $auth.hasScope('ROLE_TEAM_LEAD'))"
                style="width: 100% !important;">
                <v-btn
                  v-if="subject.status == 'completed'"
                  :loading="loading"
                  class="buttonWidth"
                  dark
                  @click.prevent="createPdfRebuild(subject.id)">Rebuild Report PDF
                </v-btn>
              </div>
            </v-layout>
          </v-flex>
          <v-flex
            class="subject-info"
            md9>
            <v-subheader class="justify-center">Personal Identity Information</v-subheader>
            <v-layout
              px-3
              row
              wrap>
              <v-flex
                md4
                py-2>
                <strong>First Name</strong>
              </v-flex>
              <v-flex
                md8
                py-2>
                <span>{{ subject.first_name || 'N/A' }}</span>
              </v-flex>
              <v-flex
                v-if="subject.middle_name"
                md4
                py-2>
                <strong>Middle Name</strong>
              </v-flex>
              <v-flex
                v-if="subject.middle_name"
                md8
                py-2>
                <span>{{ subject.middle_name }}</span>
              </v-flex>
              <v-flex
                md4
                py-2>
                <strong>Last Name</strong>
              </v-flex>
              <v-flex
                md8
                py-2>
                <span>{{ subject.last_name || 'N/A' }}</span>
              </v-flex>
              <v-flex
                v-if="subject.maiden_name"
                md4
                py-2>
                <strong>Maiden Name</strong>
              </v-flex>
              <v-flex
                v-if="subject.maiden_name"
                md8
                py-2>
                <span>{{ subject.maiden_name }}</span>
              </v-flex>
              <v-flex
                md4
                py-2>
                <strong>Handles</strong>
              </v-flex>
              <v-flex
                md8
                py-2>
                <span>{{ subject.handles ? subject.handles.join(', ') : 'N/A' }}</span>
              </v-flex>
              <v-flex
                md4
                py-2>
                <strong>Date of Birth</strong>
              </v-flex>
              <v-flex
                md8
                py-2>
                <span>{{ subject.date_of_birth || 'N/A' }}</span>
              </v-flex>
              <v-flex
                md4
                py-2>
                <strong>Gender</strong>
              </v-flex>
              <v-flex
                md8
                py-2>
                <span>{{ subject.gender || 'N/A' }}</span>
              </v-flex>
              <v-flex
                md4
                py-2>
                <strong>Report type</strong>
              </v-flex>
              <v-flex
                md8
                py-2>
                <span>{{ getFormattedText(subject.report_type) }}</span>
              </v-flex>
              <v-flex
                md4
                py-2>
                <strong>Company</strong>
              </v-flex>
              <v-flex
                md8
                py-2>
                <span>{{ subject.company.name || 'N/A' }}</span>
              </v-flex>
              <v-flex
                md4
                py-2>
                <strong>Status</strong>
              </v-flex>
              <v-flex
                md8
                py-2>
                <span
                  v-if="subject.status === 'abandoned'"
                  class="floatRight warningColor">{{ getFormattedText(subject.status) }}</span>
                <span
                  v-else
                  class="floatRight">{{ getFormattedText(subject.status) }}</span>
                <div v-if="subject.accounts.account_status == 'open'">
                  <v-menu
                    v-if="subject.status === 'new_subject' && (!$auth.hasScope('ROLE_ANALYST'))"
                    bottom
                    class="right"
                    offset-y>
                    <v-btn
                      slot="activator"
                      class="ma-0"
                      color="blue"
                      flat
                      small>Request Investigation
                    </v-btn>
                    <v-list>
                      <v-list-tile
                        v-if="subject.accounts.normal_report_allowed == true"
                        @click="requestInvestigation('normal')">
                        <v-list-tile-title>Normal Report</v-list-tile-title>
                      </v-list-tile>
                      <v-list-tile
                        v-else
                      >
                        <v-list-tile-title class="warningColor">Normal Report - Not enough bundle</v-list-tile-title>
                      </v-list-tile>
                      <v-list-tile
                        v-if="subject.accounts.rushed_report_allowed == true"
                        @click="requestInvestigation('rush')">
                        <v-list-tile-title>Rush Report</v-list-tile-title>
                      </v-list-tile>
                      <v-list-tile
                        v-else
                      >
                        <v-list-tile-title class="warningColor">Rush Report - Not enough bundle</v-list-tile-title>
                      </v-list-tile>
                      <v-list-tile
                        v-if="subject.accounts.test_report_allowed == true"
                        @click="requestInvestigation('test')">
                        <v-list-tile-title>Test Report</v-list-tile-title>
                      </v-list-tile>
                      <v-list-tile
                        v-else
                      >
                        <v-list-tile-title class="warningColor">Test Report - Not enough bundle</v-list-tile-title>
                      </v-list-tile>
                    </v-list>
                  </v-menu>
                </div>
                <div
                  v-else
                  class="warningColor right"><b>Account suspended</b>
                </div>
                <v-btn
                  v-if="((subject.status === 'investigation_completed' && subject.current_report.risk_comment !== 'none') && ($auth.hasScope('ROLE_SUPER_ADMIN') || $auth.hasScope('ROLE_TEAM_LEAD')))"
                  slot="activator"
                  class="ma-0 right"
                  color="blue"
                  flat
                  small
                  @click="approvalDialog = !approvalDialog">TeamLeader Approval
                </v-btn>
                <v-btn
                  v-if="((subject.status === 'team_lead_approved' && subject.current_report.risk_comment !== 'none') && $auth.hasScope('ROLE_SUPER_ADMIN'))"
                  slot="activator"
                  class="ma-0 right"
                  color="blue"
                  flat
                  small
                  @click="approvalDialog = !approvalDialog">Administrator Approval
                </v-btn>
              </v-flex>
              <v-flex
                md4
                py-2>
                <strong>Risk</strong>
              </v-flex>
              <v-flex
                md8
                py-2>
                <div
                  v-if="subject.current_process.risk_comment === true && ($auth.hasScope('ROLE_SUPER_ADMIN') || $auth.hasScope('ROLE_TEAM_LEAD') || $auth.hasScope('ROLE_ANALYST'))">
                  <v-btn
                    class="ma-2"
                    color="red lighten-3"
                    outlined
                    @click="risk()"
                  >
                    Edit Risk Comment
                  </v-btn>
                </div>
                <div v-else-if="subject.status === 'new_subject' ">
                  No Risk Comment
                </div>
                <div v-else>

                  {{ subject.current_report.risk_comment }}
                  <span v-if="subject.current_report.risk_comment !== 'none' || subject.status === 'investigation_completed'">
                    <v-btn
                      v-if="$auth.hasScope('ROLE_SUPER_ADMIN') || $auth.hasScope('ROLE_TEAM_LEAD')"
                      slot="
                    activator"
                      class="ma-0"
                      color="blue"
                      flat
                      small
                      @click="editRisk()"
                    >Edit
                    </v-btn>
                  </span>
                </div>
              </v-flex>
              <v-flex
                md4
                py-2>
                <strong>Warnings</strong>
              </v-flex>
              <v-flex
                md8
                py-2>
                <span
                  v-if="subject.request_check.warning == true"
                  class="requestWarning">
                  <b>Warning Please note not all requirement are met</b>
                  <br>
                  <ul
                    v-for=" check in subject.request_check.check "
                    :key="check.id">
                    <li
                      v-if="check.check == false"
                      class="nameWarnings">
                      <span class="material-icons">
                        edit
                      </span> &nbsp; {{ check.name }} -  <i>Not filled in</i>
                    </li>

                  </ul>
                </span>
              </v-flex>
              <v-flex
                v-if="($auth.hasScope('ROLE_SUPER_ADMIN')) || ($auth.hasScope('ROLE_TEAM_LEAD'))"
                md4
                py-2>
                <strong>Message Queues</strong>
              </v-flex>
              <v-flex
                v-if="($auth.hasScope('ROLE_SUPER_ADMIN')) || ($auth.hasScope('ROLE_TEAM_LEAD'))"
                md8
                py-2>
                <v-btn
                  slot="
                    activator"
                  class="ma-0"
                  color="blue"
                  flat
                  small
                  @click="messageQueue(subject.id)">Message Queues
                </v-btn>
              </v-flex>
              <!--              Daniel edit start-->
              <v-flex
                v-if="($auth.hasScope('ROLE_SUPER_ADMIN')) && subject.status === 'completed'"
                md4
                py-2>
                <strong>Calculations</strong>
              </v-flex>
              <v-flex
                v-if="($auth.hasScope('ROLE_SUPER_ADMIN')) && subject.status === 'completed'"
                md8
                py-2>
                <v-btn
                  slot="
                    activator"
                  class="ma-0"
                  color="blue"
                  flat
                  small
                  @click="calculationsView(subject.id)">View
                </v-btn>
              </v-flex>
              <!--              Daniel edit end-->
              <!--              Start-->
              <v-flex
                v-if="($auth.hasScope('ROLE_SUPER_ADMIN')) || ($auth.hasScope('ROLE_TEAM_LEAD'))"
                md4
                py-2
              >
                <strong>Edit Report</strong>
              </v-flex>
              <v-flex
                v-if="($auth.hasScope('ROLE_SUPER_ADMIN')) || ($auth.hasScope('ROLE_TEAM_LEAD'))"
                md8
                py-2
              >
                <v-btn
                  v-if="
                  subject.current_process.edit_report === true"
                  slot="activator"
                  class="ma-0"
                  color="blue"
                  flat
                  small
                  @click="editReport">Edit Report
                </v-btn>
              </v-flex>
              <!--              End-->
            </v-layout>
            <!--Start-->
            <v-layout
              v-if="($auth.hasScope('ROLE_SUPER_ADMIN') || $auth.hasScope('ROLE_TEAM_LEAD')) && (subject.status !== 'new_subject')"
              px-3
              row
              wrap>
              <v-flex
                md8
                py-2
              >
                <strong>Abort Report</strong>
              </v-flex>
              <v-flex>
                <v-btn
                  v-if="subject.current_report.status !== 'abandoned_request' && subject.current_report.status !== 'completed' && subject.current_report.status !== 'abandoned' "
                  slot="activator"
                  class="ma-0"
                  color="blue"
                  flat
                  small
                  @click="abortRequestDialog = true">Abort Report Request
                </v-btn>
                <v-btn
                  v-if="$auth.hasScope('ROLE_SUPER_ADMIN') && subject.current_report.status === 'abandoned_request' && subject.current_report.status !== 'completed' && subject.current_report.status !== 'abandoned'"
                  slot="activator"
                  class="ma-0"
                  color="red"
                  flat
                  small
                  @click="abortApproveDialog = true">Abort Report
                </v-btn>
              </v-flex>
              <!--              End-->
            </v-layout>
            <v-spacer class="mt-4"/>
            <v-subheader class="justify-center">Account Type</v-subheader>
            <v-layout
              px-3
              row
              wrap>
              <v-flex
                md4
                py-2
              >
                <strong>Account Type</strong>
              </v-flex>
              <v-flex
                md8
                py-2
              >
                {{ getFormattedText(subject.accounts.product_type) }}
              </v-flex>
              <v-flex
                md4
                py-2
              >
                <strong>Bundle remaining</strong>
              </v-flex>
              <v-flex
                md8
                py-2
              >
                <span v-if="subject.accounts.bundle_remaining >=10">

                  {{ subject.accounts.bundle_remaining }}
                </span>
                <span
                  v-else
                  class="warningColor">
                  <b> {{ subject.accounts.bundle_remaining }} </b>
                </span>
              </v-flex>
              <v-flex
                md4
                py-2
              >
                <strong>Amount Status</strong>
              </v-flex>
              <v-flex
                md8
                py-2
              >
                {{ getFormattedText(subject.accounts.account_status) }}
              </v-flex>
            </v-layout>
            <v-spacer class="mt-4"/>
            <v-subheader class="justify-center">Contact Information</v-subheader>
            <v-layout
              px-3
              row
              wrap>
              <v-flex
                md4
                py-2>
                <strong>Primary Email</strong>
              </v-flex>
              <v-flex
                md8
                py-2>
                <span>{{ subject.primary_email || 'N/A' }}</span>
              </v-flex>
              <v-flex
                md4
                py-2>
                <strong>Secondary Email</strong>
              </v-flex>
              <v-flex
                md8
                py-2>
                <span>{{ subject.secondary_email || 'N/A' }}</span>
              </v-flex>
              <v-flex
                md4
                py-2>
                <strong>Primary Mobile Number</strong>
              </v-flex>
              <v-flex
                md8
                py-2>
                <span>{{ subject.primary_mobile || 'N/A' }}</span>
              </v-flex>
              <v-flex
                md4
                py-2>
                <strong>Secondary Mobile Number</strong>
              </v-flex>
              <v-flex
                md8
                py-2>
                <span>{{ subject.secondary_mobile || 'N/A' }}</span>
              </v-flex>
              <v-flex
                md4
                py-2>
                <strong>Address</strong>
              </v-flex>
              <v-flex
                md8
                py-2>
                <span>{{ subject.address.street || 'N/A' }}</span>
              </v-flex>
              <v-flex
                md4
                py-2>
                <strong>Suburb</strong>
              </v-flex>
              <v-flex
                md8
                py-2>
                <span>{{ subject.address.suburb || 'N/A' }}</span>
              </v-flex>
              <v-flex
                md4
                py-2>
                <strong>City</strong>
              </v-flex>
              <v-flex
                md8
                py-2>
                <span>{{ subject.address.city || 'N/A' }}</span>
              </v-flex>
              <v-flex
                md4
                py-2>
                <strong>Country</strong>
              </v-flex>
              <v-flex
                md8
                py-2>
                <span>{{ subject.current_country || 'N/A' }}</span>
              </v-flex>
            </v-layout>
            <br>
            <v-subheader
              v-if="$auth.hasScope('ROLE_SUPER_ADMIN') || $auth.hasScope('ROLE_TEAM_LEAD') || $auth.hasScope('ROLE_ANALYST')"
              class="justify-center">Comments
            </v-subheader>
            <div
              v-if="$auth.hasScope('ROLE_SUPER_ADMIN') || $auth.hasScope('ROLE_TEAM_LEAD') || $auth.hasScope('ROLE_ANALYST')">
              <v-layout
                px-3
                row
                wrap>
                <v-flex
                  md1
                  py-3>
                  <strong class="capitalize"/>
                </v-flex>
                <v-flex
                  md6
                  py-2>
                  <strong class="capitalize"> Comment</strong>
                </v-flex>
                <v-flex
                  md2
                  py-2>
                  <strong class="capitalize"> Approved By</strong>
                </v-flex>
                <v-flex
                  md2
                  py-2>
                  <strong class="capitalize"> Date</strong>
                </v-flex>
              </v-layout>
              <v-layout
                v-for="comment in subject.approval_comments"
                :key="comment.id"
                px-3
                row
                wrap>
                <v-flex
                  md1
                  py-2>
                  <i
                    v-if="comment.approval == 'no'"
                    class="material-icons notApproved">
                    clear
                  </i>
                  <i
                    v-if="comment.approval == 'yes'"
                    class="material-icons approved">
                    done
                  </i>
                  <i
                    v-if="comment.comment_type == 'normal'"
                    class="material-icons comment">
                    comment
                  </i>
                </v-flex>
                <v-flex
                  md6
                  py-2>
                  {{ comment.comment }}
                </v-flex>
                <v-flex
                  md2
                  py-2>
                  <div
                    v-for="comment_by in comment"
                    :key="comment_by.id">
                    {{ comment_by.first_name }} {{ comment_by.last_name }}
                  </div>
                </v-flex>
                <v-flex
                  md2
                  py-2>
                  {{ comment.created_at }}
                </v-flex>
              </v-layout>
            </div>
          </v-flex>
        </v-layout>
      </v-card-text>
    </v-card>
    <profile-image :dialog.sync="dialog"/>
    <v-dialog
      v-model="passwordDialog"
      max-width="500px">
      <v-card>
        <v-card-title
          class="headline grey lighten-2"
          primary-title>
          PDF Password
        </v-card-title>
        <v-card-text>
          <v-text-field
            :disabled="true"
            :value="subject && subject.company ? subject.company.pdf_password: 'No company'"
            label="Company Current Password"/>
          <v-text-field
            v-model="pdfPassword"
            :placeholder="'press continue to use current password'"
            label="PDF Password"
            type="password"/>
        </v-card-text>
        <v-card-actions>
          <v-spacer/>
          <v-btn
            class="white--text"
            color="teal darken-3"
            flat
            @click="createPdf(subject.id)">Continue
          </v-btn>
          <v-btn
            color="primary"
            flat
            @click="passwordDialog = !passwordDialog">Cancel
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog
      v-model="approvalDialog"
      max-width="480"
      persistent>
      <v-card>
        <v-card-text>
          <v-flex>
            <v-text-field
              v-validate="'required'"
              v-model="comment"
              :error-messages="errors.collect('comment')"
              label="Comment"
              name="comment"/>
          </v-flex>
        </v-card-text>
        <v-card-actions class="justify-center">
          <v-btn
            v-if="approvalButton == false"
            color="error darken-1"
            flat
            @click="approveReport(false)">
            <v-icon dark>close</v-icon>
            Decline
          </v-btn>
          <v-btn
            v-if="approvalButton == false"
            color="success darken-1"
            flat
            @click="approveReport(true)">
            <v-icon dark>check</v-icon>
            Approve
          </v-btn>
          <v-progress-circular
            v-if="approvalButton == true"
            class="progressSize"
            color="success darken-1"
            indeterminate
          />
        </v-card-actions>
        <!--        <v-card-actions class="justify-center">-->
        <!--          <v-btn-->
        <!--            color="error darken-1"-->
        <!--            flat-->
        <!--            @click="approveReport(false)">-->
        <!--            <v-icon dark>close</v-icon>-->
        <!--            Decline-->
        <!--            <v-btn-->
        <!--              v-if="approvalButton == true"-->
        <!--              color="error darken-1"-->
        <!--              flat-->
        <!--            />-->
        <!--            <v-btn-->
        <!--              v-if="approvalButton == false"-->
        <!--              color="success darken-1"-->
        <!--              flat-->
        <!--              @click="approveReport(true)">-->
        <!--              <v-icon dark>check</v-icon>-->
        <!--              Approve-->
        <!--            </v-btn>-->
        <!--            <v-progress-circular-->
        <!--              v-if="approvalButton == true"-->
        <!--              :value="20" />-->
        <!--        </v-btn></v-card-actions>-->
      </v-card>
    </v-dialog>
    <!--start -->
    <v-dialog
      v-model="abortRequestDialog"
      max-width="480"
      persistent>
      <v-card>
        <v-card-text class="text-center">

          <span>
            A request is about to be made to Abort the investigation

          </span>
        </v-card-text>
        <v-card-actions class="justify-center">
          <v-btn
            v-if="approvalButton == false"
            color="error darken-1"
            flat
            @click="abortRequestDialog = false">
            <v-icon dark>close</v-icon>
            Decline
          </v-btn>
          <v-btn
            v-if="approvalButton == false"
            color="success darken-1"
            flat
            @click="abandonedRequestReport()">
            <v-icon dark>check</v-icon>
            Request
          </v-btn>
          <v-progress-circular
            v-if="approvalButton == true"
            class="progressSize"
            color="success darken-1"
            indeterminate
          />
        </v-card-actions>
      </v-card>
    </v-dialog>
    <!--End-->
    <!--start -->
    <v-dialog
      v-model="abortApproveDialog"
      max-width="480"
      persistent>
      <v-card>
        <v-card-text class="text-center">

          <span>
            You are about to Abort the investigation

          </span>
        </v-card-text>
        <v-card-actions class="justify-center">
          <v-btn
            v-if="approvalButton == false"
            color="error darken-1"
            flat
            @click="abandonedReportRejected()">
            <v-icon dark>close</v-icon>
            Decline
          </v-btn>
          <v-btn
            v-if="approvalButton == false"
            color="success darken-1"
            flat
            @click="abandonedReport()">
            <v-icon dark>check</v-icon>
            Approve
          </v-btn>
          <v-progress-circular
            v-if="approvalButton == true"
            class="progressSize"
            color="success darken-1"
            indeterminate
          />
        </v-card-actions>
      </v-card>
    </v-dialog>
    <!--End-->
    <template>
      <div class="text-center">
        <v-dialog
          v-model="dialogDownloadPdf"
          width="500"
        >
          <v-card>
            <v-card-text>
              <h2>Downloading Pdf</h2>
            </v-card-text>
            <v-divider/>
            <v-card-actions>
              <v-spacer/>
              <v-btn
                color="primary"
                text
                @click="dialogDownloadPdf = false"
              >
                Close
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </div>
    </template>
    <v-dialog
      v-model="dialogEditComment"
      max-width="700px"
    >
      <v-card>
        <v-card-title class="headline">Edit Risk Comment</v-card-title>
        <v-card-text>
          <v-textarea
            v-model="riskComment"
            label="Risk Comments"
            outline
            tabindex="11"
          />
          <!--{{ subject.current_report.risk_comment }}-->
        </v-card-text>
        <v-card-actions>
          <v-spacer/>
          <v-btn
            color="red lighten-3"
            text
            @click="dialogEditComment = false"
          >
            Close
          </v-btn>
          <v-btn
            color="green lighten-3"
            text
            @click="updateRiskComment()"
          >
            Update
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>
<script>
import { mapGetters } from 'vuex'
import _ from 'lodash'
import ProfileImage from '~/components/ProfileImage'
import modal from '~/components/risk/Risk'
import modal2 from '~/components/edit_report/Edit'

export default {
  inject: ['$validator'],
  components: { ProfileImage, modal, modal2 },
  head() {
    return {
      title: 'Profile :: Farosian'
    }
  },
  data() {
    return {
      abortApproveDialog: false,
      abortRequestDialog: false,
      dialogDownloadPdf: false,
      approvalDialog: false,
      dialog: false,
      dialogEditComment: false,
      passwordDialog: false,
      comment: '',
      pdfPassword: '',
      loading: false,
      investigationCompleteLoading: false,
      approvalButton: false,
      riskComment: ''
    }
  },
  asyncData({ app, params, store }) {
    store.dispatch('subject/getAll', params.id).catch(err => {
      app.$toast.error('error loading subject: ' + err)
    })
  },
  computed: {
    ...mapGetters({
      subject: 'subject/subject',
      comments: 'comments/subject'
    }),
    blobProfileUrl() {
      return 'https://stofarosiandev.blob.core.windows.net/profile-images/'
    }
  },

  mounted() {
    this.getData()

    // this.riskComment = this.subject.current_report.risk_comment
  },
  methods: {
    editRisk() {
      this.dialogEditComment = true
    },
    async updateRiskComment() {
      let data = {
        id: this.subject.current_report.id,
        risk_comment: this.riskComment
      }

      this.$store
        .dispatch('subject/addRiskComment', data)
        .then(response => {
          this.dialogEditComment = false
          this.$toast.success('Risk Comment saved successfully')
        })
        .catch(error => {
          this.$toast.error('Failed to update Risk Comment')
        })
    },
    downloadPdf() {
      this.dialogDownloadPdf = true
    },
    // editReport() {
    //   this.$store.dispatch('report/buildMaths', this.subject.id)//route.params.id)
    //     .then(response => {
    //       console.log(response);
    //     //this.dialogEditComment = false
    //     //this.$toast.success('Risk Comment saved successfully')
    //     this.$router.push(this.subject.id + '/scores')
    //     })
    //     .catch(error => {
    //        // Log the error for debugging purposes
    //     console.error('Error building report:', error);
    //      // Display a user-friendly error message
    //      const errorMessage = error.response && error.response.data && error.response.data.message
    //     ? error.response.data.message
    //     : 'An error occurred. Please try again later.';

    //   this.$toast.error(errorMessage);
    //     })
    // },
    editReport() {
    const maxRetries = 5;
    const retryDelay = 1000; // 1 second delay between retries
    let attempts = 0;

    const tryBuildMaths = () => {
        this.$store.dispatch('report/buildMaths', this.subject.id)
            .then(response => {
                console.log(response);
                this.$router.push(this.subject.id + '/scores');
            })
            .catch(error => {
                attempts++;
                if (attempts < maxRetries) {
                    console.warn(`Retrying buildMaths (${attempts}/${maxRetries})...`);
                    this.$toast.info(`Retrying... (${attempts}/${maxRetries})`, {
                        timeout: retryDelay
                    });
                    setTimeout(tryBuildMaths, retryDelay);
                } else {
                    console.error('Error building report after retries:', error);
                    const errorMessage = error.response && error.response.data && error.response.data.message
                        ? error.response.data.message
                        : 'An error occurred. Please try again later.';
                    this.$toast.error(errorMessage);
                }
            });
    };

      console.log("Try Build Maths");

    tryBuildMaths();
  },
    abandonedRequestReport() {
      this.approvalButton = true

      this.$store
        .dispatch(
          'report/abandonedRequestInvestigation',
          this.subject.current_report.id
        )
        .then(response => {
          if (response.error) {
            this.$toast.error(response.message)
          } else {
            this.$toast.success('Abandoned Requested')
            this.$router.go()
          }
        })
        .catch(() => {
          this.investigationCompleteLoading = false
          this.$toast.error('Could not complete Abandoned Requested.')
        })
    },
    abandonedReport() {
      this.approvalButton = true

      this.$store
        .dispatch(
          'report/abandonedInvestigation',
          this.subject.current_report.id
        )
        .then(response => {
          // this.investigationCompleteLoading = false

          if (response.error) {
            this.$toast.error(response.message)
          } else {
            this.$toast.success('Investigation Abandoned')
            this.$router.go()
          }
        })
        .catch(() => {
          this.investigationCompleteLoading = false
          this.$toast.error('Could not complete investigation.')
        })
    },
    abandonedReportRejected() {
      this.approvalButton = true

      this.$store
        .dispatch(
          'report/abandonedInvestigationRejected',
          this.subject.current_report.id
        )
        .then(response => {
          // this.investigationCompleteLoading = false

          if (response.error) {
            this.$toast.error(response.message)
          } else {
            this.$toast.success('Investigation Abandoned Rejected')
            this.$router.go()
          }
        })
        .catch(() => {
          this.investigationCompleteLoading = false
          this.$toast.error('Could not complete investigation.')
        })
    },
    async getData() {
      await this.$store
        .dispatch('subject/get', this.$route.params.id)
        .then(response => {
          this.riskComment = this.subject.current_report.risk_comment
        })
    },
    risk() {
      this.$refs.modal.show()
    },
    refreshProfile() {
      this.$store
        .dispatch('subject/refresh', this.$route.params.id)
        .then(() => {
          this.$toast.success('Search refreshing...')
        })
        .catch(() => {
          this.$toast.error('Could not refresh search. Please check error.')
        })
    },
    messageQueue(id) {
      this.$router.push('/subjects/' + this.subject.id + '/message-queue')
    },
    calculationsView(id) {
      this.$router.push('/subjects/' + this.subject.id + '/calculation-view')
    },
    newInvestigation(id) {
      if (this.subject.status === 'validated') {
        this.$store
          .dispatch('report/getStatus', this.subject.id)
          .then(() => {
            this.$router.push('/investigation/' + this.subject.id)
          })
          .catch(() => {
            this.$toast.error('Could not refresh search. Please check error.')
          })
      } else {
        this.$toast.error(
          'User investagation completed. Error: cannot investigate completed subject'
        )
      }
    },
    currentInvestigation(id) {
      if (
        this.subject.status === 'under_investigation' ||
        this.subject.status === 'team_lead_approved' ||
        this.subject.status === 'investigation_completed'
      ) {
        this.$router.push('/investigation/' + this.subject.id)
      } else {
        this.$toast.error(
          'User investagation completed. Error: cannot investigate completed subject'
        )
      }
    },
    previousInvestigations(id) {
      this.$router.push('/report/' + id + '/view')
    },
    reportStatus() {
      if (this.subject.status === 'completed') {
        const subjectId = this.subject.id
        this.$router.push({ path: `/report/${subjectId}` })
      } else {
        this.$toast.error(
          'Error: cannot create a report for subject, please make sure investigation is complete'
        )
      }
    },
    checkForCurrent() {
      let report = this.subject ? this.subject.current_report : {}
      let userAllowed =
        ((this.$auth.hasScope('ROLE_SUPER_ADMIN') ||
          this.$auth.hasScope('ROLE_TEAM_LEAD')) &&
          (this.subject.status === 'under_investigation' ||
            this.subject.status === 'team_lead_approved' ||
            this.subject.status === 'investigation_completed')) ||
        (this.$auth.hasScope('ROLE_ANALYST') &&
          this.subject.status === 'under_investigation')
      return report && userAllowed
    },
    checkForCurrentComplete() {
      let report = this.subject ? this.subject.current_report : {}
      let userAllowed =
        this.$auth.hasScope('ROLE_SUPER_ADMIN') ||
        this.$auth.hasScope('ROLE_TEAM_LEAD') ||
        this.$auth.hasScope('ROLE_ANALYST')
      return (
        this.subject.status === 'under_investigation' && report && userAllowed
      )
    },
    createPdfRebuild(id) {
      this.passwordDialog = false
      if (this.subject.status === 'completed') {
        this.loading = true
        let pass = ''
        if (this.subject.company && this.subject.company.password_set) {
          pass =
            this.pdfPassword !== ''
              ? this.pdfPassword
              : this.subject.company.pdf_password
        }
        this.$store
          .dispatch('report/pdfRebuild', { id, pass })
          .then(data => {
            this.downloadFile(id, data, '.pdf')
            this.loading = false
            this.$toast.success('Generated PDF successfully')
          })
          .catch(error => {
            this.$toast.error('Could not generate report. ' + error)
            this.loading = false
          })
      } else {
        this.$toast.error(
          'Error: cannot generate a pdf report for subject, please make sure investigation is complete'
        )
      }
    },
    createPdf(id) {
      this.passwordDialog = false
      if (this.subject.status === 'completed') {
        this.loading = true
        let pass = ''
        if (this.subject.company && this.subject.company.password_set) {
          pass =
            this.pdfPassword !== ''
              ? this.pdfPassword
              : this.subject.company.pdf_password
        }
        this.$store
          .dispatch('report/standardPdf', { id, pass })
          .then(data => {
            this.downloadFile(id, data, '.pdf')
            this.loading = false
            this.$toast.success('Generated PDF successfully')
          })
          .catch(error => {
            this.$toast.error('Could not generate report. ' + error)
            this.loading = false
          })
      } else {
        this.$toast.error(
          'Error: cannot generate a pdf report for subject, please make sure investigation is complete'
        )
      }
    },
    completeInvestigation() {
      this.investigationCompleteLoading = true
      this.$store
        .dispatch('report/completeInvestigation', this.$route.params.id)
        .then(response => {
          this.investigationCompleteLoading = false

          if (response.error) {
            this.$toast.error(response.message)
          } else {
            this.$toast.success('Investigation completed')
            this.$router.go()
          }
        })
        .catch(() => {
          this.investigationCompleteLoading = false
          this.$toast.error('Could not complete investigation.')
        })
    },
    downloadFile(id, fileData, extension) {
      const url = window.URL.createObjectURL(new Blob([fileData]))
      const link = document.createElement('a')
      link.href = url
      let fileName = id + '-' + this.getFormattedTime() + extension
      link.setAttribute('download', fileName) //or any other extension
      document.body.appendChild(link)
      link.click()
    },
    getFormattedTime() {
      var today = new Date()
      var y = today.getFullYear()
      // JavaScript months are 0-based.
      var m = today.getMonth() + 1
      var d = today.getDate()
      var h = today.getHours()
      var mi = today.getMinutes()
      var s = today.getSeconds()
      return y + '' + m + '' + d + '' + h + '' + mi + '' + s
    },
    getFormattedText(value) {
      return _.startCase(value)
    },
    requestInvestigation(type) {
      this.$store
        .dispatch('report/newRequest', {
          id: this.$route.params.id,
          requestType: type
        })
        .then(() => {
          this.$toast.success('Investigation requested successfully.')
          this.$router.go()
        })
        .catch(() => {
          this.$toast.error('Could not request investigation.')
        })
    },
    startNew(type) {
      this.$store
        .dispatch('report/newInvestigation', {
          id: this.$route.params.id,
          requestType: type
        })
        .then(() => {
          this.$toast.success('New Investigation requested successfully.')
          this.$router.go()
        })
        .catch(() => {
          this.$toast.error('Could not start a new investigation.')
        })
    },
    approveReport(approved) {
      this.$validator.errors.clear()
      if (!approved && this.comment === '') {
        return this.$validator.validate()
      } else {
        this.approvalButton = true
      }

      if (approved) {
        this.approvalButton = true
      }

      this.$store
        .dispatch('report/approveReport', {
          reportId: this.subject.current_report.id,
          approved: approved ? 'yes' : 'no',
          comment: this.comment
        })
        .then(() => {
          this.$toast.success('Report ' + (approved ? 'approved' : 'declined'))
          this.approvalDialog = false

          //Daniel disable when testing
          this.$router.go()
        })
        .catch(() => {
          this.$toast.error(
            'Could not ' + (approved ? 'approve' : 'decline') + ' report'
          )
        })
    }
  }
}
</script>
<style>
.capitalize {
  text-transform: capitalize;
}

.file a {
  color: white;
}

.profile-img {
  text-align: center;
  border: none !important;
}

.profile-img img {
  max-width: 100%;
  max-height: 100%;
}

.profile-img .file {
  position: relative;
  overflow: hidden;
  margin-top: -15%;
  width: 100%;
  border: none;
  border-radius: 0;
  font-size: 15px;
  background: #212529b8;
}

.profile-img .file input {
  position: absolute;
  opacity: 0;
  right: 0;
  top: 0;
}

.v-card .subject-info .flex {
  font-size: 15px;
  margin-bottom: -1px;
  border-bottom: 1px solid #ddd;
  border-top: 1px solid #ddd;
}

.v-subheader {
  font-size: 18px;
}

.btn-size {
  width: 14.5rem;
  background-color: rgba(0, 0, 0, 0.14);
}

.approved {
  color: darkgreen;
}

.notApproved {
  color: darkred;
}

.comment {
  color: lightblue;
}

.requestWarning {
  margin: 0px 0px 0px 20px !important;
  color: darkred;
}

ul {
  list-style-type: none;
}

.nameWarnings {
  color: black;
}

.warningColor {
  color: darkred;
}

.clearBoth {
  clear: both;
}

.buttonWidth {
  width: 97% !important;
  padding: 0px 0px 0px 0px !important;
  margin: 5px 10px 0px 5px !important;
  /*background-color: #17685b !important;*/
}

.progressSize {
  height: 20px !important;
  margin: 0px 0px 5px 0px !important;
}
</style>
