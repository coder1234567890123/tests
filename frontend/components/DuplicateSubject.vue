<template>
  <div class="text-center">
    <v-dialog
      div
      class="text-center">
      <v-dialog
        v-model="dialog"
        fullscreen
        hide-overlay
        transition="dialog-bottom-transition">
        <v-card>
          <v-layout
            row
            wrap/>
          <div class="text-center">
            <v-dialog
              v-model="dialog"
              fullscreen
              hide-overlay
              transition="dialog-bottom-transition">
              <v-card>

                <v-toolbar
                  grey
                  color="grey lighten-5">
                  <v-list-tile-title>SUBJECT WITH SIMILAR IDENTIFICATION</v-list-tile-title>
                  <v-spacer/>
                  <v-toolbar-items>

                    <v-btn
                      flat
                      small
                      router
                      @click="dialog = false">
                      <v-icon class="mr-1">fa fa-sign-out</v-icon>
                      Exit
                    </v-btn>
                  </v-toolbar-items>
                </v-toolbar>
                <v-card-text>
                  <v-layout
                    row
                    wrap>
                    <v-flex md1/>
                    <v-flex md5>
                      <h2>Subject with similar identification</h2><br>
                      <v-divider/>
                      <v-list two-line>
                        <template v-for="(subject, index) in details.subjects">
                          <v-list-tile :key="index">
                            <v-list-tile-content>
                              <v-list-tile-title><b>{{ subject.name }}</b></v-list-tile-title>
                              <v-list-tile-sub-title>Company: {{ subject.company }}</v-list-tile-sub-title>
                              <v-list-tile-sub-title>Identification: {{ subject.identification }}</v-list-tile-sub-title>
                              <v-list-tile-sub-title>Status: {{ getFormattedText(subject.status) }}</v-list-tile-sub-title>
                              <v-list-tile-sub-title/>
                            </v-list-tile-content>
                          </v-list-tile>
                        </template>
                      </v-list>
                    </v-flex>
                    <v-flex md5>
                      <h2>Completed Reports with similar identification</h2><br>
                      <v-divider/>
                      <v-list two-line>
                        <v-radio-group
                          :mandatory="true"
                          v-model="selectedItem">
                          <v-list-tile>
                            <v-list-tile-action>
                              <v-radio
                                :value="''"
                                :key="'Empty'"/>
                            </v-list-tile-action>
                            <v-list-tile-content>
                              <v-list-tile-title><b>None</b></v-list-tile-title>
                              <v-list-tile-sub-title>Select none to allow for new report and new search </v-list-tile-sub-title>
                            </v-list-tile-content>
                          </v-list-tile>
                          <template v-for="(report, index) in details.reports">
                            <v-list-tile :key="index">
                              <v-list-tile-action>
                                <v-radio
                                  :value="report.id"
                                  :key="report.sequence"/>
                              </v-list-tile-action>
                              <v-list-tile-content class="infoBox">
                                <v-list-tile-title><b>{{ report.sequence }}</b></v-list-tile-title>
                                <v-list-tile-sub-title>{{ 'Report Status : ' + report.status.toUpperCase() }}</v-list-tile-sub-title>
                                <v-list-tile-sub-title>{{ report.completed_date ? 'Completed on: ' + report.completed_date: '' }} </v-list-tile-sub-title>
                                <v-list-tile-sub-title
                                  download="pdf"
                                  class="mouseOver"
                                  @click="createPdf(report.subject_id)" >View PDF </v-list-tile-sub-title>
                                <v-list-tile-sub-title
                                  class="mouseOver"
                                  @click="goToSubject(report.subject_id)">Subject </v-list-tile-sub-title>
                              </v-list-tile-content>
                            </v-list-tile>
                          </template>
                        </v-radio-group>
                      </v-list>
                    </v-flex>
                    <v-flex md1/>
                  </v-layout>
                  <v-divider/>
                  <v-layout
                    row
                    wrap>

                    <v-flex md3/>
                    <v-flex md2>
                      <v-btn
                        :disabled="selectedItem !== ''"
                        color="blue darken-1"
                        class="white--text"
                        @click="startSearch()">
                        New Report and Search
                      </v-btn>
                    </v-flex>
                    <v-flex md2>
                      <v-btn
                        :disabled="selectedItem === ''"
                        color="teal darken-2"
                        class="white--text"
                        @click="duplicate(selectedItem)">
                        Duplicate With Old Search
                      </v-btn>
                    </v-flex>
                    <v-flex md2>
                      <v-btn
                        :disabled="selectedItem === ''"
                        color="teal darken-3"
                        class="white--text"
                        @click="duplicateWithSearch(selectedItem)">
                        Duplicate With New Search
                      </v-btn>
                    </v-flex>
                    <v-flex md3/>
                  </v-layout>
                </v-card-text>

              </v-card>
            </v-dialog>
          </div>
        </v-card>
      </v-dialog>
    </v-dialog>
  </div>
</template>

<script>
import { mapGetters } from 'vuex'
import _ from 'lodash'

export default {
  props: {
    subjectId: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      dialog: false,
      loading: false,
      selectedItem: ''
    }
  },
  computed: {
    ...mapGetters({
      details: 'report/duplicateDetails'
    }),
    blogStoragePath() {
      return process.env.blogStoragePath
    }
  },
  watch: {
    subjectId: {
      handler: function() {
        this.$store.dispatch('report/getSubjectInfo', this.subjectId)
      },
      deep: true
    }
  },
  mounted() {
    process.env.blogStoragePath = this.$config.blogStoragePath
  },
  methods: {
    goToSubject(id) {
      this.$router.push('/subjects/' + id)
    },
    createPdf(id) {
      let pass = ''
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
    duplicate(reportId) {
      let sub = this.subjectId
      this.$store
        .dispatch('report/duplicate', { subject: sub, report: reportId })
        .then(response => {
          this.$toast.success(response.message)
          this.dialog = false
          this.$router.push('/subjects/' + sub)
        })
        .catch(error => {
          this.$toast.error('Could not duplicate record. \n ' + error)
        })
    },
    duplicateWithSearch(reportId) {
      let sub = this.subjectId
      this.$store
        .dispatch('report/duplicateWithSearch', {
          subject: sub,
          report: reportId
        })
        .then(response => {
          this.$toast.success(response.message)
          this.dialog = false
          this.$router.push('/subjects/' + sub)
        })
        .catch(error => {
          this.$toast.error('Could not duplicate record. \n ' + error)
        })
    },
    startSearch() {
      this.$store
        .dispatch('report/startSearch', this.subjectId)
        .then(() => {
          this.$toast.success('Search started...')
          this.dialog = false
          this.$router.push('/subjects/' + this.subjectId)
        })
        .catch(() => {
          this.$toast.error('Could not start search...')
        })
    },
    show() {
      this.dialog = true
    },
    hide() {
      this.dialog = false
    }
  }
}
</script>

<style scoped>
.mouseOver {
  cursor: pointer;
  color: green !important;
}
.infoBox {
  margin: 10px 0px 0px 0px;
  height: 250px !important;
}
</style>
