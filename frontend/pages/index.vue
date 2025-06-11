<template>
  <v-layout
    wrap
    row>
    <v-flex
      xs12>
      <h1 class="title mb-3">Reports Overview Dashboard</h1>
      <!-- <h2>{{$config.blogStoragePath}}</h2> -->
    </v-flex>
    <v-flex>
      <v-card>
        <v-menu
          transition="slide-y-transition"
          bottom
          right
          class="messageClass"
        >
          <v-btn
            slot="activator"
            icon>
            <v-icon
              v-if="messages.count >= 1"
              color="red">notification_important
            </v-icon>
            Messages ( {{ messages.count }} )
          </v-btn>
          <div
            v-for="message in messages.messages"
            :key="message.type"
          >
            <v-list class="messageList">
              <v-list-tile-title
                class="clickMessage"
              >
                <div
                  v-if="message.message_type == 'rush'"
                  class="warningColor">
                  <span class="material-icons messageHeader">
                    mail_outline
                  </span>&nbsp;&nbsp; <span class="messageHeader">{{ message.header }}</span>
                </div>
                <div
                  v-else-if="message.message_type == 'test'"
                  class="testReportHeader">
                  <span class="material-icons messageHeader">
                    mail_outline
                  </span>&nbsp;&nbsp; <span class="messageHeader">{{ message.header }}</span>
                </div>
                <div
                  v-else
                  class="normalReportHeader"
                >
                  <span
                    class="material-icons messageHeader">
                    mail_outline
                  </span>&nbsp;&nbsp; <span class="messageHeader">{{ message.header }}</span>
                </div>
                <div class="clearBothMessage"/>
                <span
                  class="material-icons messageIconIn">
                  message
                </span>
                <span
                  class="messageInfo"
                  title="Click to view subject"
                  @click="viewedMessage(message.id, message.subject)"
                >{{ message.message }} </span>

                <span
                  title="Remove Message"
                  class="material-icons float-right"
                  @click="removeMessage(message.id, message.subject)"
                >
                  clear
                </span>
                <div class="clearBothMessage"/>
              </v-list-tile-title>
            </v-list>
          </div>
        </v-menu>
      </v-card>
    </v-flex>
    <v-flex
      v-if="$auth.hasScope('ROLE_USER_STANDARD') || $auth.hasScope('ROLE_USER_MANAGER') || $auth.hasScope('ROLE_ADMIN_USER')"
    >
      <v-card class="accountsBox">
        <span class="accountTitles">Account Type:</span>
        <span v-if="accounts.product_type !== 'suspended'">
          {{ getFormattedText(accounts.product_type) }}
        </span>
        <span
          v-else
          class="warningColor">
          <b> {{ getFormattedText(accounts.product_type) }} </b>
        </span>
        <span class="accountsSpacer"> | </span>
        <span class="accountTitles">Bundle remaining:</span>
        <span v-if="accounts.bundle_remaining >=10">
          {{ accounts.bundle_remaining }}
        </span>
        <span
          v-else
          class="warningColor">
          <b> {{ accounts.bundle_remaining }} </b>
        </span>
        <span class="accountsSpacer"> | </span>
        <span class="accountTitles">Amount Status: </span>
        <span v-if="accounts.account_status == 'open'">
          {{ getFormattedText(accounts.account_status) }}
        </span>
        <span
          v-else
          class="warningColor">
          <b> {{ getFormattedText(accounts.account_status) }} </b>
        </span>
      </v-card>
    </v-flex>
    <v-flex
      xs12>
      <div
        v-for="item in queues"
        :key="item.type"
      >
        <div
          v-if="item.status === 'teams'"
          class="col-12">
          <v-flex
            xs12>
            <v-card
              :style="'border-left: 6px solid ' + teams[0].color">
              <v-list class="pa-0">
                <v-list-tile
                  avatar
                  class="pa-4">
                  <v-list-tile-content>
                    <v-list-tile-sub-title class="pb-1">Teams</v-list-tile-sub-title>
                    <v-list-tile-title
                      class="display-1"
                      style="height: inherit">{{ item.count }}
                    </v-list-tile-title>
                  </v-list-tile-content>
                  <v-list-tile-avatar>
                    <router-link
                      :to="{ path: '/team' }"
                      style="text-decoration: none">
                      <v-icon
                        class="grey--text text--lighten-2"
                        style="font-size: 36px">{{ teams[0].icon }}
                      </v-icon>
                    </router-link>
                  </v-list-tile-avatar>
                </v-list-tile>
              </v-list>
            </v-card>
          </v-flex>
        </div>
        <div v-if="item.status === 'team_members'">
          <v-flex
            xs12>
            <v-card
              :style="'border-left: 6px solid ' + teams[0].color">
              <v-list class="pa-0">
                <v-list-tile
                  avatar
                  class="pa-4">
                  <v-list-tile-content>
                    <v-list-tile-sub-title class="pb-1"> Team members</v-list-tile-sub-title>
                    <v-list-tile-title
                      class="display-1"
                      style="height: inherit">{{ item.count }}
                    </v-list-tile-title>
                  </v-list-tile-content>
                  <v-list-tile-avatar>
                    <v-btn
                      icon
                      class="mx-0"
                      @click="openTeamDialog">
                      <v-icon color="info">{{ teams[0].icon }}</v-icon>
                    </v-btn>
                  </v-list-tile-avatar>
                </v-list-tile>
              </v-list>
            </v-card>
          </v-flex>
        </div>
      </div>
    </v-flex>
    <v-flex
      xs12>
      <v-subheader style="font-size: 18px">Reports Overview</v-subheader>
    </v-flex>
    <v-flex
      v-for="report in computedReports"
      :key="report.type"
      :md4="!firstPanelFocus"
      :md6="firstPanelFocus"
      xs12>
      <v-card :style="'border-left: 6px solid ' + report.color">
        <v-list class="pa-0">
          <v-list-tile
            avatar
            class="pa-4">
            <v-list-tile-content>
              <v-list-tile-sub-title class="pb-2">{{ report.label }}</v-list-tile-sub-title>
              <v-list-tile-title
                class="display-1"
                style="height: inherit">{{ report.count }}
              </v-list-tile-title>
            </v-list-tile-content>
            <v-list-tile-avatar>
              <router-link
                :to="{ path: '/report/queues', query: {report: report.type} }"
                style="text-decoration: none">
                <v-icon
                  class="grey--text text--lighten-2"
                  style="font-size: 36px">{{ report.icon }}
                </v-icon>
              </router-link>
            </v-list-tile-avatar>
          </v-list-tile>
        </v-list>
      </v-card>
    </v-flex>
    <v-flex
      xs12>
      <v-subheader
        class="mt-4"
        style="font-size: 18px">Subject Reports - Work Items
      </v-subheader>
    </v-flex>
    <v-flex
      v-for="queue in computedQueues"
      :key="queue.status"
      xs12
      md4>
      <v-card
        :style="'border-left: 6px solid ' + queue.color">
        <v-list class="pa-0">
          <v-list-tile
            avatar
            class="pa-4">
            <v-list-tile-content>
              <v-list-tile-sub-title class="pb-2">{{ queue.label }}</v-list-tile-sub-title>
              <v-list-tile-title
                class="display-1"
                style="height: inherit">{{ queue.count }}
              </v-list-tile-title>
            </v-list-tile-content>
            <v-list-tile-avatar>
              <router-link
                :to="{ path: 'report/queues', query: {status: queue.status} }"
                style="text-decoration: none">
                <v-icon
                  class="grey--text text--lighten-2"
                  style="font-size: 36px">{{ queue.icon }}
                </v-icon>
              </router-link>
            </v-list-tile-avatar>
          </v-list-tile>
        </v-list>
      </v-card>
    </v-flex>
    <v-flex
      xs12
      class="mt-4">
      <v-subheader style="font-size: 18px">Monthly Reports</v-subheader>
    </v-flex>
    <v-flex
      xs12>
      <v-card>
        <v-card-text>
          <my-line
            v-if="showChart"
            :height="150"
            :data="lineData"
            :options="options"/>
        </v-card-text>
      </v-card>
    </v-flex>
    <v-flex
      v-if="$auth.hasScope('ROLE_SUPER_ADMIN')"
      xs12
      class="mt-4">
      <v-subheader style="font-size: 18px">Audit Log</v-subheader>
    </v-flex>
    <v-flex
      v-if="$auth.hasScope('ROLE_SUPER_ADMIN')"
      xs12>
      <v-data-table
        :headers="headers"
        :items="computedAuditLog"
        hide-actions
        class="elevation-1"
      >
        <template
          slot="items"
          slot-scope="props">
          <td>{{ props.item.user }}</td>
          <td class="text-xs-left">{{ props.item.action_formatted }}</td>
          <td class="text-xs-left">{{ props.item.date }}</td>
          <td class="text-xs-left">{{ props.item.source }}</td>
        </template>
      </v-data-table>
    </v-flex>
    <v-dialog
      v-model="teamDialog"
      scrollable
      persistent
      max-width="320px">
      <v-card>
        <v-card-title>Team Analysis</v-card-title>
        <v-divider/>
        <v-card-text style="height: 300px;">
          <div
            v-for="item in teamsAnalysis"
            :key="item.id">
            {{ item.first_name }} {{ item.last_name }}
          </div>
        </v-card-text>
        <v-divider/>
        <v-card-actions>
          <v-btn
            color="grey darken-1"
            flat
            @click.native="teamDialog = !teamDialog">Close
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-layout>
</template>
<script>
import { mapGetters } from 'vuex'
import _ from 'lodash'

export default {
  middleware: 'auth',
  async fetch({ store }) {
    await store.dispatch('dashboard/queryDashboard')
  },
  head() {
    return {
      title: 'Dashboard :: Farosian'
    }
  },
  data() {
    return {
      teamDialog: false,
      firstPanelFocus: true,
      teams: [
        {
          icon: 'person',
          color: '',
          count: '0'
        }
      ],
      teamsAnalysis: [
        {
          icon: 'person',
          color: '',
          count: '0'
        }
      ],
      showChart: false,
      headers: [
        {
          text: 'User',
          align: 'left',
          sortable: false,
          value: 'user'
        },
        { text: 'Action', value: 'action' },
        { text: 'Date', value: 'date' },
        { text: 'Source', value: 'source' }
      ]
    }
  },
  computed: {
    ...mapGetters({
      queues: 'dashboard/queues',
      reports: 'dashboard/reports',
      auditLog: 'dashboard/auditLog',
      messages: 'dashboard/messages',
      accounts: 'dashboard/accounts',
      monthlyRequests: 'dashboard/monthlyRequests'
    }),

    computedQueues() {
      return this.queues
        ? this.queues.filter(function(item) {
            let statuses = {
              abandoned: { icon: 'clear', color: '#0B6623' },
              completed: { icon: 'done_all', color: '#00A86B' },
              team_lead_approved: { icon: 'done', color: '#00A86B' },
              new_subject: { icon: 'person', color: '#C7EA46' },
              new_request: { icon: 'open_in_new', color: '#98FB98' },
              unassigned: { icon: 'person_add_disabled', color: '#708238' },
              under_investigation: { icon: 'search', color: '#D0F0C0' },
              investigation_completed: {
                icon: 'spellcheck',
                color: '#9DC183'
              },
              validated: { icon: 'playlist_add_check', color: '#9DC183' },
              search_completed: {
                icon: 'playlist_add_check',
                color: '#9DC183'
              }
            }

            item['label'] = _.startCase(item.status)
            item['icon'] = statuses[item.status]
              ? statuses[item.status].icon
              : ''
            item['color'] = statuses[item.status]
              ? statuses[item.status].color
              : ''

            return Object.keys(statuses).includes(item.status)
          })
        : []
    },
    computedReports() {
      return this.reports
        ? this.reports.filter(function(item) {
            let types = {
              new_rush: { icon: 'assignment', color: '#98FB98' },
              rush_approved: { icon: 'assignment_turned_in', color: '#9DC183' },
              normal: { icon: 'description', color: '#00A86B' },
              new_test: { icon: 'description', color: '#00A86B' },
              test_approved: { icon: 'description', color: '#00A86B' }
            }

            item['label'] = _.startCase(item.type)
            item['icon'] = types[item.type] ? types[item.type].icon : ''
            item['color'] = types[item.type] ? types[item.type].color : ''
            item['color'] = types[item.type] ? types[item.type].color : ''

            return Object.keys(types).includes(item.type)
          })
        : []
    },
    computedAuditLog() {
      return this.auditLog
        ? this.auditLog.filter(function(item) {
            item['action_formatted'] = _.startCase(item['action'])

            return item
          })
        : []
    }
  },
  mounted() {
    this.showChart = true
    if (this.monthlyRequests) {
      this.lineData.labels = this.monthlyRequests['labels']
      this.lineData.datasets[0].data = this.monthlyRequests['datasets']
    }

    if (this.computedReports.length == 2) {
      this.firstPanelFocus = true
    } else {
      this.firstPanelFocus = false
    }
  },
  methods: {
    getFormattedText(value) {
      return _.startCase(value)
    },
    removeMessage(id) {
      this.$store
        .dispatch('dashboard/messageView', id)
        .then(() => {
          this.$toast.success('Message Viewed')

          this.$store.dispatch('dashboard/queryDashboard').then(response => {
            this.messages = response
          })
        })
        .catch(() => {
          this.$toast.error('Could not view message')
        })
    },
    viewedMessage(id, subjectId) {
      this.$store
        .dispatch('dashboard/messageView', id)
        .then(() => {
          this.$toast.success('Message Viewed')
          this.$router.push('/subjects/' + subjectId)
          // this.$store.dispatch('dashboard/queryDashboard').then(response => {
          //   //this.messages = response
          //
          // })
        })
        .catch(() => {
          this.$toast.error('Could not view message')
        })
    },
    openTeamDialog() {
      this.teamDialog = true

      this.$store.dispatch('teams/queryTeamUser').then(response => {
        this.teamsAnalysis = response
      })
    }
  },
  asyncData() {
    const lineData = {
      labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
      datasets: [
        {
          label: 'Completed Reports',
          backgroundColor: '#4F7942',
          borderColor: '#0B6623',
          data: [0, 0, 0, 0, 0, 0, 0],
          fill: false
        }
      ]
    }
    const options = {
      responsive: true,
      title: {
        display: false,
        text: 'Monthly Requests'
      },
      tooltips: {
        mode: 'index',
        intersect: false
      },
      hover: {
        mode: 'nearest',
        intersect: true
      },
      scales: {
        xAxes: [
          {
            display: true,
            scaleLabel: {
              display: true,
              labelString: 'Month'
            }
          }
        ],
        yAxes: [
          {
            display: true,
            scaleLabel: {
              display: true,
              labelString: 'Value'
            }
          }
        ]
      }
    }
    return { lineData, options }
  }
}
</script>
<style scoped>
.messageClass {
  margin: 0px 0px 0px 50px;
  width: 500px !important;
}

.clickMessage:hover {
  cursor: pointer;
  color: gray;
  padding: 10px 0px 10px 0px;
  height: 80px;
  overflow: hidden;
}

.messageIcon {
  color: darkred;
}

.warningColor {
  color: darkred;
}

.testReportHeader {
  color: dodgerblue;
}

.normalReportHeader {
  color: darkgreen;
}

.accountsBox {
  padding: 11px 0px 0px 20px;
  height: 48px;
  font-size: 16px;
}

.accountTitles {
  color: #727372;
  font-weight: 400;
  font-size: 18px;
}

.accountsSpacer {
  margin: 0px 5px 10px 5px;
}

.messageDropDownMenu {
  margin: 10px 10px 10px 10px !important;
}

.messageBox {
  height: 50px !important;
  /*margin: 10px 0px 10px 0px !important;*/
}

.messageList {
  padding: 5px 10px 5px 10px !important;
  overflow: hidden !important;
}

.messageHeader {
  float: left;
}

.messageInfo {
  float: left;
  margin-left: 2px;
}

.messageIconIn {
  float: left;
  margin-left: 10px;
}

.clearBothMessage {
  margin-bottom: 10px;
  clear: both;
}

.float-right {
  float: right;
}
</style>
