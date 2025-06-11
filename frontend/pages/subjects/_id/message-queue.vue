<template>

  <div
    id="app"
    class="container">
    <v-btn
      style="float: right"
      color="teal darken-3"
      class="white--text"
      @click="backButton()"
    >
      Back
    </v-btn>
    <h1 class="title mb-3">Message Queue : {{ subjectFirstName !== null ? subjectFirstName + " " + subjectLastName :
    'Loading...' }}</h1>
    <br>
    <v-card>
      <div v-if="subjectStatus == 'search_started'">
        <v-btn
          v-if="disableRefreshButton == true"
          color="success"
          class="left"
          @click="overwrite()">Overwrite
        </v-btn>
        <v-btn
          v-else
          color="success"
          class="left"
          @click="overwrite()">Overwrite
        </v-btn>
        <v-btn
          v-if="(disableRefreshButton == true)"
          color="error"
          class="right"
          disabled
        >Restart Search
        </v-btn>
        <v-btn
          v-else
          color="error"
          class="right"
          @click="refresh()">Restart Search
        </v-btn>
      </div>
      <div class="clearBoth"/>
      <v-card-text>
        <v-layout
          child-flex
        >
          <v-data-table
            :headers="headers"
            :items="messageQueue"
            hide-actions
            hide-footers
            class="elevation-1">
            <template
              slot="items"
              slot-scope="props">
              <td>{{ props.item.platform }}</td>
              <td>{{ props.item.token }}</td>
              <td>
                <span
                  v-if=" props.item.message_received == true"
                  class="material-icons approved">
                  done
                </span>
                <span
                  v-if=" props.item.message_received == false"
                  class="warningColor">
                  Waiting ...
                </span>
              </td>
              <td>
                <span
                  v-if=" props.item.over_written == true"
                  class="material-icons approved">
                  done
                </span>
                <span
                  v-if=" props.item.over_written == false"
                  class="">
                  N/A
                </span>
              </td>
              <td>
                <span
                  v-if=" props.item.system_over_write == true"
                  class="material-icons approved">
                  done
                </span>
                <span
                  v-else
                  class="">
                  N/A
                </span>
              </td>
              <td>{{ props.item.created_at }}</td>
            </template>
          </v-data-table>
        </v-layout>
      </v-card-text>
    </v-card>
  </div>
</template>
<script>
import { mapGetters } from 'vuex'
import _ from 'lodash'

export default {
  inject: ['$validator'],
  components: {},
  head() {
    return {
      title: 'Message Queue :: Farosian'
    }
  },
  data() {
    return {
      subjectFirstName: '',
      subjectId: '',
      subjectLastName: '',
      subjectStatus: '',
      messageQueue: [],
      disableRefreshButton: false,
      headers: [
        { text: 'Platform', sortable: false },
        { text: 'Token', sortable: false },
        { text: 'Message Received', sortable: false },
        { text: 'Manual Over Written', sortable: false },
        { text: 'System Over Written', sortable: false },
        { text: 'Created At', sortable: false }
      ]
    }
  },
  async mounted() {
    this.getData()
    this.getMessageQueue()
  },
  methods: {
    backButton() {
      this.$router.push('/subjects/' + this.$route.params.id)
    },
    async getData() {
      await this.$store
        .dispatch('subject/messageQueueSubject', this.$route.params.id)
        .then(response => {
          this.subjectFirstName = response.first_name
          this.subjectLastName = response.last_name
          this.subjectStatus = response.status
        })
        .catch(() => {
          this.$toast.error('Could Not get subject.')
        })
    },
    async getMessageQueue() {
      await this.$store
        .dispatch('subject/messageQueue', this.$route.params.id)
        .then(response => {
          this.messageQueue = response
        })
        .catch(() => {
          this.$toast.error('Could Not get Message Queue.')
        })
    },
    async overwrite() {
      await this.$store
        .dispatch('subject/overwriteMessage', this.$route.params.id)
        .then(() => {
          this.$toast.success('Message Queue overwritten ...')
          this.$router.go()
        })
        .catch(() => {
          this.$toast.error('Could Not overwritten Message Queue.')
        })
    },
    async refresh() {
      this.disableRefreshButton = true
      await this.$store
        .dispatch('subject/refresh', this.$route.params.id)
        .then(() => {
          this.$toast.success('Refreshing Search ...')
          this.$router.go()
        })
        .catch(() => {
          this.$toast.error('Could Not Refreshing Search.')
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
  font-weight: bold;
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
</style>
