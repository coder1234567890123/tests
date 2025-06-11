<template>
  <div
    id="app"
    class="container">
    <duplicate-subject
      ref="modal"
      :subject-id="currentSubject"/>
    <v-breadcrumbs
      :items="breadcrumbs"
      class="pt-0 px-0">
      <v-icon slot="divider">chevron_right</v-icon>
      <v-breadcrumbs-item
        slot="item"
        slot-scope="{ item }"
        :to="item.to"
        :disabled="item.disabled">
        {{ item.text }}
      </v-breadcrumbs-item>
    </v-breadcrumbs>
    <h1 class="title mb-3">Reports - {{ getFormattedHeading() }}</h1>
    <v-card class="mb-3">
      <v-container grid-list-md>
        <v-layout
          row
          wrap>
          <v-flex
            xs12
            sm6
            md4>
            <v-menu
              ref="menuFrom"
              :close-on-content-click="false"
              v-model="menuFrom"
              :nudge-right="40"
              lazy
              transition="scale-transition"
              offset-y
              full-width
              max-width="290px"
              min-width="290px">
              <v-text-field
                slot="activator"
                v-model="fromDateFormatted"
                label="From"
                persistent-hint
                prepend-icon="event"
                @blur="dateFrom = parseDate(fromDateFormatted)"/>
              <v-date-picker
                v-model="dateFrom"
                no-title
                @input="menuFrom = false"/>
            </v-menu>
          </v-flex>
          <v-flex
            xs12
            sm6
            md4>
            <v-menu
              :close-on-content-click="false"
              v-model="menuTo"
              :nudge-right="40"
              lazy
              transition="scale-transition"
              offset-y
              full-width
              max-width="290px"
              min-width="290px">
              <v-text-field
                slot="activator"
                v-model="toDateFormatted"
                label="To"
                persistent-hint
                prepend-icon="event"
                @blur="dateTo = parseDate(toDateFormatted)"/>
              <v-date-picker
                v-model="dateTo"
                no-title
                @input="menuTo = false"/>
            </v-menu>
          </v-flex>
          <v-flex
            xs12
            sm6
            md4>
            <v-select
              v-model="status"
              :items="statuses"
              label="Status"
              prepend-icon="equalizer"/>
          </v-flex>
          <v-flex
            v-if="$auth.hasScope('ROLE_SUPER_ADMIN') || $auth.hasScope('ROLE_TEAM_LEAD') || $auth.hasScope('ROLE_ANALYST')"
            xs12
            sm6
            md4>
            <v-select
              v-model="company"
              :items="companies"
              item-text="name"
              item-value="id"
              label="Company"
              prepend-icon="account_balance"/>
          </v-flex>
          <v-flex
            xs12
            sm6
            md4>
            <v-select
              v-model="reportType"
              :items="reportTypes"
              label="Report Type"
              prepend-icon="assignment"/>
          </v-flex>
          <v-flex
            xs12
            md4>
            <v-btn
              small
              top
              color="info"
              @click="filterQueues(true,'filter')">
              <v-icon dark>filter_list</v-icon>
              Filter
            </v-btn>
            <v-btn
              small
              flat
              top
              color="grey darken-1"
              @click="clearFilters">
              <v-icon dark>clear</v-icon>
              Clear All
            </v-btn>
          </v-flex>
        </v-layout>
      </v-container>
    </v-card>
    <v-card>
      <v-flex xs12>
        <v-btn
          small
          color="info"
          class="ma-4 right"
          @click="exportQueues()">
          <v-icon dark>get_app</v-icon>
          Export
        </v-btn>
      </v-flex>
      <v-data-table
        :headers="headers"
        :loading="loading"
        :items="queues"
        :pagination.sync="pagination"
        :total-items="paginationState.totalItems"
        :rows-per-page-items="pagination.rowsPerPageItems"
        must-sort
        class="elevation-1">
        <template
          v-if="props.item"
          slot="items"
          slot-scope="props">
          <tr
            :class="(props.item.option_value !== 0 && (props.item.status === 'new_request' || props.item.status === 'report_type_approved')) ? 'duplicateIdColor': ''">
            <td>{{ props.item.user }}</td>
            <td class="text-xs-left">{{ props.item.assigned_to }}</td>
            <td>
              <v-icon
                v-if="$auth.hasScope('ROLE_SUPER_ADMIN') || $auth.hasScope('ROLE_TEAM_LEAD')"
                color="blue"
                title="Assign Analyst"
                @click="openUserDialog(props.item.id, props.item.subject)">how_to_reg</v-icon>
            </td>
            <td class="text-xs-left">{{ props.item.subject.first_name + ' ' + props.item.subject.last_name }}</td>
            <td class="text-xs-left">{{ props.item.company_name }}</td>
            <td class="text-xs-left">{{ props.item.created_at }}</td>
            <td
              :style= "{ background : props.item.subject.background_color }"
              class="text-xs-left reportType">

              {{ props.item.subject.report_type }}

            </td>
            <td class="text-xs-left">
              <div
                v-if="props.item.request_type == 'rush'"
                class="rushReport"> Rush
              </div>
              <div
                v-else-if="props.item.request_type == 'test'"
                class="testReport"> Test
              </div>
              <div v-else> Normal
              </div>
            </td>
            <td class="text-xs-left">{{ getFormattedText(props.item.status) }}</td>
            <td class="text-xs-left">
              <v-menu
                transition="slide-y-transition"
                bottom>
                <v-btn
                  slot="activator"
                  icon>
                  <v-icon>more_vert</v-icon>
                </v-btn>
                <v-list>
                  <v-list-tile
                    v-if="props.item.option_value !== 0 && ((props.item.status === 'new_request' && props.item.request_type === 'normal') || (props.item.status === 'report_type_approved' && props.item.request_type === 'rush'))"
                    @click="duplicateDetail(props.item.subject.id)">
                    <v-list-tile-title>Duplicate Subject</v-list-tile-title>
                  </v-list-tile>
                  <!--                  check if need or replace-->
                  <!--                  <v-list-tile-->
                  <!--                    v-if="$auth.hasScope('ROLE_SUPER_ADMIN') || $auth.hasScope('ROLE_TEAM_LEAD')"-->
                  <!--                    @click="openUserDialog(props.item.id, props.item.subject)">-->
                  <!--                    <v-list-tile-title>-->
                  <!--                      {{ 'N/A' === props.item.assigned_to ? 'Assign' : 'Re-assign' }} to Analyst-->
                  <!--                    </v-list-tile-title>-->
                  <!--                  </v-list-tile>-->
                  <v-list-tile
                    v-if="checkNewRush(props.item.status, props.item.request_type) && props.item.option_value === 0"
                    @click="startSearch(props.item.subject.id)">
                    <v-list-tile-title>Start Search</v-list-tile-title>
                  </v-list-tile>
                  <v-list-tile @click="openSubjectDialog(props.item.subject)">
                    <v-list-tile-title>View Subject</v-list-tile-title>
                  </v-list-tile>
                  <v-list-tile @click="goToSubject(props.item.subject)">
                    <v-list-tile-title>Go To Subject</v-list-tile-title>
                  </v-list-tile>
                  <v-list-tile
                    v-if="reportType === 'new_rush' && $auth.hasScope('ROLE_SUPER_ADMIN')"
                    @click="openApprovalDialog(props.item.id)">
                    <v-list-tile-title>Approve Rush</v-list-tile-title>
                  </v-list-tile>
                  <v-list-tile
                    v-if="reportType === 'new_test' && $auth.hasScope('ROLE_SUPER_ADMIN')"
                    @click="openApprovalDialog(props.item.id)">
                    <v-list-tile-title>Approve Test</v-list-tile-title>
                  </v-list-tile>
                  <v-list-tile
                    v-if="props.item.status === 'team_lead_approved' && $auth.hasScope('ROLE_SUPER_ADMIN')"
                    @click="openApprovalDialog(props.item.id)">
                    <v-list-tile-title>Final Approval</v-list-tile-title>
                  </v-list-tile>
                </v-list>
              </v-menu>
            </td>
          </tr>
        </template>
      </v-data-table>
    </v-card>
    <v-dialog
      v-model="userDialog"
      scrollable
      persistent
      max-width="320px">
      <v-card>
        <v-card-title>Select User</v-card-title>
        <v-divider/>
        <v-card-text style="height: 300px;">
          <v-radio-group
            v-model="assignedTo"
            column>
            <v-radio
              v-for="item in users"
              :key="item.id"
              :label="item.name"
              :value="item.id"
            />
          </v-radio-group>
          <v-flex v-if="undefined === users || !users.length">
            <v-alert
              slot="no-results"
              :value="true"
              color="error"
              icon="warning">
              No analysts/users to assign.
            </v-alert>
          </v-flex>
        </v-card-text>
        <v-divider/>
        <v-card-actions>
          <v-btn
            color="grey darken-1"
            flat
            @click.native="userDialog = !userDialog">Close
          </v-btn>
          <v-btn
            color="blue darken-1"
            flat
            @click="assignReport">Assign
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <SubjectInfo ref="SubjectInfo" />
    <v-dialog
      v-model="approvalDialog"
      persistent
      max-width="480">
      <v-card>
        <v-card-text>
          <v-flex>
            <v-text-field
              v-validate="'required'"
              v-model="comment"
              :error-messages="errors.collect('comment')"
              name="comment"
              label="Comment"/>
          </v-flex>
        </v-card-text>
        <v-card-actions class="justify-center">
          <v-btn
            color="error darken-1"
            flat
            @click="approveReport(false)">
            <v-icon dark>close</v-icon>
            Decline
          </v-btn>
          <v-btn
            color="success darken-1"
            flat
            @click="approveReport(true)">
            <v-icon dark>check</v-icon>
            Approve
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog
      v-model="finalApprovalDialog"
      persistent
      max-width="480">
      <v-card>
        <v-card-text>
          <v-flex>
            <v-text-field
              v-validate="'required'"
              v-model="comment"
              :error-messages="errors.collect('comment')"
              name="comment"
              label="Comment"/>
          </v-flex>
        </v-card-text>
        <v-card-actions class="justify-center">
          <v-btn
            color="error darken-1"
            flat
            @click="approveReport(false)">
            <v-icon dark>close</v-icon>
            Decline
          </v-btn>
          <v-btn
            color="success darken-1"
            flat
            @click="approveReport(true)">
            <v-icon dark>check</v-icon>
            Approve
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>
<script>
import { mapGetters } from 'vuex'
import DuplicateSubject from '~/components/DuplicateSubject'
import _ from 'lodash'
import SubjectInfo from '~/components/SubjectInfo'

export default {
  components: { DuplicateSubject, SubjectInfo },
  inject: ['$validator'],
  async fetch({ store }) {
    await store.dispatch('company/queryCompanies')
  },
  head() {
    return {
      title: 'Queues :: Farosian'
    }
  },
  data() {
    return {
      finalApprovalDialog: false,
      loading: false,
      currentSubject: '',
      approvalDialog: false,
      subjectDialog: false,
      userDialog: false,
      assignedTo: null,
      report: null,
      comment: '',
      approved: false,
      subject: null,
      subjectView: null,
      users: [],
      headers: this.getHeaders(),
      filter_request: null,
      dateFrom: null,
      dateTo: null,
      company: null,
      reportType: null,
      status: null,
      fromDateFormatted: null,
      toDateFormatted: null,
      query: [],
      menuFrom: false,
      menuTo: false,
      breadcrumbs: [
        { text: 'Dashboard', disabled: false, to: '/' },
        { text: 'Reports', disabled: false, to: '/report/queues' }
      ],
      reportTypes: [
        { text: 'Normal', value: 'normal' },
        { text: 'Rush', value: 'rush' },
        { text: 'Test', value: 'test' },
        { text: 'New Rush', value: 'new_rush' },
        { text: 'New Test', value: 'new_test' },
        { text: 'Rush Approved', value: 'rush_approved' },
        { text: 'Test Approved', value: 'test_approved' },
        { text: 'Team Lead Approved', value: 'team_lead_approved' }
      ],
      statuses: [
        { text: 'New Request', value: 'new_request' },
        { text: 'Needs Approval', value: 'needs_approval' },
        { text: 'Report Approved', value: 'report_type_approved' },
        { text: 'Unassigned', value: 'unassigned' },
        { text: 'Validated', value: 'validated' },
        { text: 'Search Completed', value: 'search_completed' },
        { text: 'Under Investigation', value: 'under_investigation' },
        { text: 'Team Lead Approved', value: 'team_lead_approved' },
        { text: 'Completed', value: 'completed' },
        { text: 'Abandoned', value: 'abandoned' }
      ]
    }
  },
  computed: {
    ...mapGetters({
      queues: 'report/queues',
      companies: 'company/companies',
      paginationState: 'report/pagination',
      filtersState: 'report/filters'
    }),
    pagination: {
      get: function() {
        return this.paginationState
      },
      set: function(value) {
        this.$store.commit('report/SET_PAGINATION', value)
      }
    }
  },
  watch: {
    pagination: {
      handler() {
        this.refreshQueues()
      },
      deep: true
    },
    dateFrom(val) {
      this.fromDateFormatted = this.formatDate(this.dateFrom)
    },
    dateTo(val) {
      this.toDateFormatted = this.formatDate(this.dateTo)
    }
  },
  mounted() {
    this.query = this.$route.query
    this.reportType = this.$route.query.report
    this.status = this.query.status

    this.filterQueues(false)
    //Overwrites caching client wants queues to start fresh each time
    this.restPageToOne()
  },
  methods: {
    restPageToOne() {
      this.$store.dispatch('report/resetPageToOne')
    },
    duplicateDetail(subjectId) {
      this.currentSubject = subjectId
      this.$refs.modal.show()
    },
    checkNewRush(status, requestType) {
      if (
        this.$auth.hasScope('ROLE_ADMIN_USER') ||
        this.$auth.hasScope('ROLE_USER_MANAGER') ||
        this.$auth.hasScope('ROLE_USER_STANDARD')
      ) {
        return false
      }
      if (['new_request', 'report_type_approved'].includes(status)) {
        if (status === 'new_request' && requestType === 'normal') {
          return true
        } else if (
          status === 'report_type_approved' &&
          requestType === 'rush'
        ) {
          return true
        } else if (
          status === 'report_type_approved' &&
          requestType === 'test'
        ) {
          return true
        }
      }
      return false
    },
    checkNewTest(status, requestType) {
      if (
        this.$auth.hasScope('ROLE_ADMIN_USER') ||
        this.$auth.hasScope('ROLE_USER_MANAGER') ||
        this.$auth.hasScope('ROLE_USER_STANDARD')
      ) {
        return false
      }
      if (['new_request', 'report_type_approved'].includes(status)) {
        if (status === 'new_request' && requestType === 'normal') {
          return true
        } else if (
          status === 'report_type_approved' &&
          requestType === 'test'
        ) {
          return true
        }
      }
      return false
    },
    goToSubject(subject) {
      this.$router.push('/subjects/' + subject.id)
    },
    refreshQueues() {
      this.loading = true
      this.$store.dispatch('report/queryQueues').then(() => {
        this.loading = false
      })
    },
    exportQueues() {
      this.loading = true
      this.$store.dispatch('report/queryExportQueues').then(response => {
        let filename = this.getName(response.headers['content-disposition'])
        this.loading = false
        this.downloadFile(response.data, filename)
      })
    },

    getName(disposition) {
      let filename = 'report.xlsx'
      if (disposition && disposition.indexOf('inline') !== -1) {
        var filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/
        var matches = filenameRegex.exec(disposition)
        if (matches != null && matches[1]) {
          filename = matches[1].replace(/['"]/g, '')
        }
      }
      return filename
    },

    downloadFile(fileData, fileName) {
      const url = window.URL.createObjectURL(new Blob([fileData]))
      const link = document.createElement('a')
      link.href = url
      link.setAttribute('download', fileName) //or any other extension
      document.body.appendChild(link)
      link.click()
    },
    formatDate(date) {
      if (!date) return null

      const [year, month, day] = date.split('-')
      return `${day}/${month}/${year}`
    },
    parseDate(date) {
      if (!date) return null

      const [day, month, year] = date.split('/')
      return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`
    },
    getHeaders() {
      return [
        { text: 'User', value: 'user' },
        { text: 'Analyst', value: 'assigned_to' },
        { text: '' },
        { text: 'Subject', value: 'subject' },
        { text: 'Company', value: 'company_name' },
        { text: 'Date Created', value: 'created_at' },
        { text: 'R', value: 'subject.report_type' },
        { text: 'Type', value: 'report_type' },
        { text: 'Status', value: 'status' },
        { text: 'Actions', value: '' }
      ]
    },
    getFormattedHeading() {
      return _.size(this.query)
        ? this.getFormattedText(Object.values(this.query)[0])
        : 'Custom'
    },
    getFormattedText(value) {
      return _.startCase(value)
    },
    startSearch(id) {
      this.$store
        .dispatch('report/startSearch', id)
        .then(() => {
          this.$toast.success('Search started...')
          this.refreshQueues()
        })
        .catch(() => {
          this.$toast.error('Could not start search...')
        })
    },
    assignReport() {
      this.$store
        .dispatch('report/assignReport', {
          reportId: this.report,
          assignedTo: this.assignedTo
        })
        .then(() => {
          this.$toast.success('User assigned...')
          this.userDialog = false
          this.refreshQueues()
        })
        .catch(() => {
          this.$toast.error('Could not assign user...')
        })
    },
    approveReport(approved) {
      this.$validator.errors.clear()
      if (!approved && this.comment === '') {
        return this.$validator.validate()
      }
      this.$store
        .dispatch('report/approveReport', {
          reportId: this.report,
          approved: approved ? 'yes' : 'no',
          comment: this.comment
        })
        .then(() => {
          this.$toast.success('Report ' + (approved ? 'approved' : 'declined'))
          this.approvalDialog = false
          this.refreshQueues()
        })
        .catch(() => {
          this.$toast.error(
            'Could not ' + (approved ? 'approve' : 'decline') + ' report'
          )
        })
    },
    approveReportFinal(approved) {
      this.$validator.errors.clear()
      if (!approved && this.comment === '') {
        return this.$validator.validate()
      }

      this.$store
        .dispatch('report/approveFinalReport', {
          reportId: this.report,
          approved: approved ? 'yes' : 'no',
          comment: this.comment
        })
        .then(() => {
          this.$toast.success('Report ' + (approved ? 'approved' : 'declined'))
          this.approvalDialog = false
          this.refreshQueues()
        })
        .catch(() => {
          this.$toast.error(
            'Could not ' + (approved ? 'approve' : 'decline') + ' report'
          )
        })
    },
    openUserDialog(reportId, subject) {
      this.userDialog = true
      this.report = reportId
      this.$store.dispatch('report/queryUsers', subject.id).then(response => {
        this.users = response
      })
    },
    async openSubjectDialog(subject) {
      this.$refs.SubjectInfo.openSubjectDialog(subject.id)
    },
    openApprovalDialog(reportId) {
      this.approvalDialog = true
      this.report = reportId
    },
    openFinalApprovalDialog(reportId) {
      this.finalApprovalDialog = true
      this.report = reportId
    },
    filterQueues(reload) {
      if (reload) {
        this.filter_request = 'filter_request'
      }
      var updateStatus = ''
      if (this.reportType) {
        switch (this.reportType) {
          case 'new_rush':
            updateStatus = 'needs_approval'
            break
          case 'rush_approved':
            updateStatus = 'report_type_approved'
            break
          case 'new_test':
            updateStatus = 'needs_approval'
            break
          case 'test_approved':
            updateStatus = 'report_type_approved'
            break
          default:
            updateStatus = this.status
        }
      } else {
        updateStatus = this.status
      }

      this.$store.commit('report/SET_FILTERS', {
        filter_request: this.filter_request,
        dateFrom: this.dateFrom,
        dateTo: this.dateTo,
        company: this.company,
        status: updateStatus,
        type: this.typeCheck(this.reportType)
      })

      if (reload) {
        this.refreshQueues()
        this.query = {}
      }
    },
    typeCheck(type) {
      //Todo if better search is needed
      if (type === 'new_test' || type === 'test_approved') {
        return 'test'
      }

      if (type === 'new_rush' || type === 'rush_approved') {
        return 'rush'
      }

      if (type === 'normal') {
        return 'normal'
      }

      if (type === 'rush') {
        return 'rush'
      }

      if (type === 'test') {
        return 'test'
      }
    },
    clearFilters() {
      this.filter_request = null
      this.dateFrom = null
      this.dateTo = null
      this.company = null
      this.reportType = null
      this.status = null

      this.filterQueues(true)
    }
  }
}
</script>
<style>
.v-card .subject-info .flex {
  font-size: 15px;
  margin-bottom: -1px;
  padding-top: 8px !important;
  padding-bottom: 8px !important;
  border-bottom: 1px solid #ddd;
  border-top: 1px solid #ddd;
}

.rushReport {
  color: darkred;
  font-weight: bold;
}

.testReport {
  color: dodgerblue;
  font-weight: bold;
}

.reportType {
  font-weight: bold;
  /*font-weight: bolder;*/
}
.duplicateIdColor {
  background-color: #ffcccb;
}
</style>
