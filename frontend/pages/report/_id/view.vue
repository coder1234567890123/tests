<template>
  <div
    id="app"
    class="container">
    <h1 class="title mb-3">Reports: {{ subject ? subject.first_name + ' ' + subject.last_name: 'loading...' }}</h1>
    <v-layout row>
      <v-flex
        md12>
        <v-card-actions
          class="pl-0">
          <v-btn
            :to="{ name: $getRoute('SUBJECTS_VIEW'), params : { id: this.$route.params.id } }"
            color="blue-grey"
            class="white--text mb-3">
            <v-icon>keyboard_arrow_left</v-icon>
            Back
          </v-btn>
        </v-card-actions>
      </v-flex>
    </v-layout>
    <v-card>
      <v-data-table
        :headers="headers"
        :loading="loading"
        :items="reports"
        :pagination.sync="pagination"
        :total-items="paginationState.totalItems"
        :rows-per-page-items="pagination.rowsPerPageItems"
        must-sort
        class="elevation-1">
        <template
          slot="items"
          slot-scope="props">
          <td class="text-xs-left">{{ props.item.sequence }}</td>
          <td class="text-xs-left">{{ props.item.user ? props.item.user : 'N/A' }}</td>
          <td class="text-xs-left">{{ props.item.status }}</td>
          <td class="text-xs-left">
            <v-btn
              v-if="props.item.status === 'completed'"
              :loading="loading"
              @click.prevent="checkDialog(props.item.id, props.item.status)">
              <v-icon
                :loading="loading"
                medium
                color="green">get_app</v-icon>
            </v-btn>
            <v-btn
              v-else>
              <v-icon
                medium
                color="red">error_outline</v-icon>
            </v-btn>  
          </td>
        </template>
        <v-alert
          slot="no-results"
          :value="true"
          color="error"
          icon="warning">
          Your search for "{{ subject ? subject.first_name + ' ' + subject.last_name: 'loading...' }}" found no results.
        </v-alert>
      </v-data-table>
    </v-card>

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
            :value="subject && subject.company ? subject.company.pdf_password: 'No company'"
            :disabled="true"
            label="Company Current Password"/>
          
          <v-text-field
            v-model="pdfPassword"
            :placeholder="'press continue to use current password'"
            type="password"
            label="PDF Password"/>
        </v-card-text>
        <v-card-actions>
          <v-spacer/>
          <v-btn
            flat
            class="white--text"
            color="teal darken-3"
            @click="createPdf(pdfPasswordId, pdfPasswordStatus)">Continue</v-btn>
          <v-btn
            flat
            color="primary"
            @click="passwordDialog = !passwordDialog">Cancel</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>


<script>
import { mapGetters } from 'vuex'

export default {
  head() {
    return {
      title: 'Subject - Reports :: Farosian'
    }
  },
  data() {
    return {
      loading: false,
      passwordDialog: false,
      pdfPassword: '',
      pdfPasswordId: '',
      pdfPasswordStatus: '',
      switch: false,
      headers: this.getHeaders()
    }
  },
  computed: {
    ...mapGetters({
      subject: 'subject/subject',
      paginationState: 'report/pagination',
      reports: 'report/reports'
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
        this.loading = true
        this.$store
          .dispatch('report/queryReports', this.$route.params.id)
          .then(() => {
            this.loading = false
          })
      },
      deep: true
    }
  },
  mounted() {
    this.$store.dispatch('subject/get', this.$route.params.id).catch(() => {
      console.log('Could not get the specified subject')
    })
  },
  methods: {
    getHeaders() {
      let headers = [
        {
          text: 'Sequence',
          align: 'left',
          value: 'sequence',
          sortable: false
        },
        {
          text: 'Created By',
          align: 'left',
          value: 'user',
          sortable: false
        },
        {
          text: 'Report Status',
          align: 'left',
          value: 'open',
          sortable: false
        },
        {
          text: 'PDF',
          align: 'left',
          value: 'open',
          sortable: false
        }
      ]

      return headers
    },
    checkDialog(id, reportStatus) {
      if (this.subject.company && this.subject.company.password_set) {
        this.pdfPasswordId = id
        this.pdfPasswordStatus = reportStatus
        this.passwordDialog = true
      } else {
        this.createPdf(id, reportStatus)
      }
    },
    createPdf(id, reportStatus) {
      this.passwordDialog = false
      if (
        reportStatus === 'investigation_completed' ||
        reportStatus === 'completed'
      ) {
        this.loading = true
        let pass = ''
        if (this.subject.company && this.subject.company.password_set) {
          pass =
            this.pdfPassword !== ''
              ? this.pdfPassword
              : this.subject.company.pdf_password
        }
        this.$store
          .dispatch('report/reportPdf', {
            subjectId: this.subject.id,
            id: id,
            pass: pass
          })
          .then(data => {
            this.downloadFile(id, data, '.pdf')
            this.loading = false
            this.pdfPassword = ''
            this.pdfPasswordId = ''
            this.pdfPasswordStatus = ''
            this.$toast.success('Generated PDF successfully')
          })
          .catch(error => {
            this.loading = false
            this.pdfPassword = ''
            this.pdfPasswordId = ''
            this.pdfPasswordStatus = ''
            this.$toast.error('Could not generate report. ' + error)
          })
      } else {
        this.$toast.error(
          'Error: cannot generate a pdf report for subject, please make sure investigation is complete'
        )
      }
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
    }
  }
}
</script>
<style>
.radioSelect {
  margin: 0 5px 0 20px;
}

.select {
  margin: 0 5px 0 20px;
}
</style>
