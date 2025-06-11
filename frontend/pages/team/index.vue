<template>
  <li
    id="app"
    style="list-style: none"
    class="container">
    <h1 class="title mb-3">Teams</h1>
    <v-card>
      <v-card-title>
        <v-text-field
          v-model="search"
          append-icon="search"
          label="Search"
          single-line
          hide-details/>
      </v-card-title>
      <v-data-table
        :headers="headers"
        :loading="loading"
        :items="teams"
        :pagination.sync="pagination"
        :total-items="paginationState.totalItems"
        :rows-per-page-items="pagination.rowsPerPageItems"
        must-sort
        class="elevation-1">
        <template
          slot="items"
          slot-scope="props">
          <td>{{ props.item.team_name }}</td>
          <td>{{ props.item.team_lead_email }}</td>
          <td v-if="$auth.hasScope('ROLE_SUPER_ADMIN')">
            <v-btn
              icon
              class="mx-0"
              @click="openCompanyDialog(props.item.id)">
              <v-icon color="brown">business</v-icon>
            </v-btn>
            <v-btn
              icon
              class="mx-0"
              @click="openTeamDialog(props.item.id)">
              <v-icon color="info">supervised_user_circle</v-icon>
            </v-btn>
          </td>
        </template>
        <v-alert
          slot="no-results"
          :value="true"
          color="error"
          icon="warning">
          Your search for "{{ search }}" found no results.
        </v-alert>
      </v-data-table>
    </v-card>
    <v-dialog
      v-model="companyDialog"
      scrollable
      persistent
      max-width="320px">
      <v-card>
        <v-card-title>Companies</v-card-title>
        <v-divider/>
        <v-card-text style="height: 300px;">
          <div
            v-for="item in teamsCompanies"
            :key="item.id">
            <v-layout
              row
              wrap
              px-100>
              <v-flex
                md8
                py-2
                class="listPadding"
              >
                <div class="listNames">
                  {{ item.name }}
                </div>
              </v-flex>
              <v-flex
                md2
                py-2
                class="listPadding"
              >
                <v-btn
                  :to="'/company/'+item.id+'/view'"
                  icon
                  class="mx-0">
                  <v-icon color="info">visibility</v-icon>
                </v-btn>
              </v-flex>
              <v-flex
                md2
                py-2
                class="listPadding"
              >
                <v-btn
                  icon
                  class="mx-0"
                  @click="deleteCompany(item.id)">
                  <v-icon color="red">delete</v-icon>
                </v-btn>
              </v-flex>
            </v-layout>
          </div>
        </v-card-text>
        <v-divider/>
        <v-card-actions>
          <v-btn
            color="grey darken-1"
            flat
            @click.native="companyDialog = !companyDialog">Close
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog
      v-model="teamDialog"
      scrollable
      persistent
      max-width="320px">
      <v-card>
        <v-card-title>Analysis</v-card-title>
        <v-divider/>
        <v-card-text style="height: 300px;">
          <div
            v-for="item in teamsAnalysis"
            :key="item.id">
            <v-layout
              row
              wrap
              px-100>
              <v-flex
                md8
                py-2
                class="listPadding"
              >
                <div class="listNames">
                  {{ item.first_name }} {{ item.last_name }}
                </div>
              </v-flex>
              <v-flex
                md2
                py-2
                class="listPadding"
              >
                <v-btn
                  :to="'/user/'+item.id+'/profile'"
                  icon
                  class="mx-0">
                  <v-icon color="info">visibility</v-icon>
                </v-btn>
              </v-flex>
              <v-flex
                md2
                py-2
                class="listPadding"
              >
                <v-btn
                  icon
                  class="mx-0"
                  @click="deleteUser(item.id)">
                  <v-icon color="red">delete</v-icon>
                </v-btn>
              </v-flex>
            </v-layout>
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
  </li>
</template>
<script>
import { mapGetters } from 'vuex'
import _ from 'lodash'

export default {
  head() {
    return {
      title: 'Teams :: Farosian'
    }
  },
  data() {
    return {
      companyDialog: false,
      teamDialog: false,
      loading: false,
      headers: this.getHeaders(),
      show: false,
      userDetails: [],
      teamsAnalysis: [],
      teamsCompanies: [],
      currentTeam: ''
    }
  },
  computed: {
    ...mapGetters({
      teams: 'teams/teams',
      paginationState: 'teams/pagination',
      searchState: 'teams/search'
    }),
    pagination: {
      get: function() {
        return this.paginationState
      },
      set: function(value) {
        this.$store.commit('teams/SET_PAGINATION', value)
      }
    },
    search: {
      get: function() {
        return this.searchState
      },
      set: _.debounce(function(value) {
        this.$store.commit('teams/SET_SEARCH', value)
      }, 500)
    }
  },
  watch: {
    pagination: {
      handler() {
        this.getTeams()
      },
      deep: true
    },
    search: {
      handler() {
        this.getTeams()
      },
      deep: true
    }
  },
  methods: {
    getTeams() {
      this.loading = true
      this.$store.dispatch('teams/queryTeams').then(() => {
        this.loading = false
      })
    },
    getHeaders() {
      let headers = [
        { text: 'Team Name', value: 'team_name' },
        { text: 'Team Leader Email', value: 'team_lead_email' }
      ]
      if (this.$auth.hasScope('ROLE_SUPER_ADMIN')) {
        headers.push({ text: 'Actions', value: '', sortable: false })
      }
      return headers
    },
    assignTeam() {
      this.$store
        .dispatch('company/assignTeam', {
          company: this.company,
          team: this.assignedTeam
        })
        .then(() => {
          this.$toast.success('Company assigned...')
          this.teamDialog = false
        })
        .catch(() => {
          this.$toast.error('Could not assign company...')
        })
    },
    deleteTeam(id) {
      this.$store.dispatch('teams/deleteTeam', id).then(response => {
        this.getTeams()
      })
    },
    deleteUser(id) {
      this.$store
        .dispatch('teams/deleteTeamUser', id)
        .then(response => {
          this.$toast.success('User Removed')
          this.getAnalysis()
        })
        .catch(() => {
          this.$toast.error('Error Removing User')
        })
    },
    deleteCompany(id) {
      this.$store
        .dispatch('teams/deleteTeamCompany', id)
        .then(response => {
          this.$toast.success('Company Removed')
          this.getCompanies()
        })
        .catch(() => {
          this.$toast.error('Error Removing Company')
        })
    },
    openCompanyDialog(id) {
      this.teamsCompanies = []
      this.companyDialog = true
      this.currentTeam = id
      this.getCompanies()
    },
    openTeamDialog(id) {
      this.teamsAnalysis = []
      this.teamDialog = true
      this.currentTeam = id
      this.getAnalysis()
    },
    getAnalysis() {
      let id = this.currentTeam
      this.$store.dispatch('teams/queryAnalysis', id).then(response => {
        this.teamsAnalysis = response
      })
    },
    getCompanies() {
      let id = this.currentTeam
      this.$store.dispatch('teams/queryCompanies', id).then(response => {
        this.teamsCompanies = response
      })
    }
  }
}
</script>
<style scoped>
.listPadding {
  margin: 0px 0px 0px 0px !important;
  padding: 0px 0px 0px 0px !important;
}

.listNames {
  padding: 12px 0px 0px 0px !important;
}
</style>
