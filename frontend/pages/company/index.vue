<template>
  <div
    id="app"
    class="container">
    <h1 class="title mb-3">Companies</h1>
    <v-card>
      <v-card-title>
        <v-text-field
          v-model="search"
          append-icon="search"
          label="Search"
          single-line
          hide-details
        />
      </v-card-title>
      <v-data-table
        :headers="headers"
        :loading="loading"
        :items="companies"
        :pagination.sync="pagination"
        :total-items="paginationState.totalItems"
        :rows-per-page-items="pagination.rowsPerPageItems"
        must-sort
        class="elevation-1">
        <template
          slot="items"
          slot-scope="props">
          <td>{{ props.item.name }}</td>
          <td class="text-xs-left">{{ props.item.city }}</td>
          <td class="text-xs-left">{{ props.item.province }}</td>
          <td class="text-xs-left">{{ props.item.created_by ? props.item.created_by : 'N/A' }}</td>
          <td class="text-xs-left">{{ props.item.created_at }}</td>
          <td :class="[$auth.hasScope('ROLE_SUPER_ADMIN') ? 'text-xs-center px-0' : 'text-xs-left']">
            <v-btn
              v-if="$auth.hasScope('ROLE_SUPER_ADMIN')"
              :to="'/accounts/' + props.item.id +'/edit'"
              icon
              class="mx-0">
              <icon class="material-icons">
                account_balance_wallet
              </icon>
            </v-btn>
            <v-btn
              v-if="$auth.hasScope('ROLE_SUPER_ADMIN') || $auth.hasScope('ROLE_TEAM_LEAD')"
              :to="'/company/' + props.item.id +'/view'"
              icon
              class="mx-0">
              <v-icon color="grey">visibility</v-icon>
            </v-btn>
            <v-btn
              v-if="$auth.hasScope('ROLE_SUPER_ADMIN')"
              :to="'/company/' + props.item.id +'/edit'"
              icon
              class="mx-0">
              <v-icon color="info">edit</v-icon>
            </v-btn>
            <span
              v-if="$auth.hasScope('ROLE_SUPER_ADMIN')"
              onclick="show"
            >
              <v-btn
                slot="activator"
                :title="props.item.team_id ? 'Re-assign' : 'Assign company to a team'"
                icon
                class="mx-0"
                @click="openTeamDialog(props.item.id)">
                <v-icon color="blue">how_to_reg</v-icon>
              </v-btn>
            </span>
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
      v-model="teamDialog"
      scrollable
      persistent
      max-width="320px">
      <v-card>
        <v-card-title>Select a Team Lead</v-card-title>
        <v-divider/>
        <v-card-text style="height: 300px;">
          <v-radio-group
            v-model="assignedTeam"
            column>
            <v-radio
              v-for="item in teams"
              :key="item.id"
              :label="item.team_name"
              :value="item.id"/>
          </v-radio-group>
          <v-flex v-if="undefined === teams">
            <v-alert
              slot="no-results"
              :value="true"
              color="error"
              icon="warning">
              No Team Lead to assign.
            </v-alert>
          </v-flex>
        </v-card-text>
        <v-divider/>
        <v-card-actions>
          <v-btn
            color="grey darken-1"
            flat
            @click.native="teamDialog = !teamDialog">Close
          </v-btn>
          <v-btn
            color="blue darken-1"
            flat
            @click="assignTeam">Assign
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>
<script>
import { mapGetters } from 'vuex'
import _ from 'lodash'

export default {
  head() {
    return {
      title: 'Companies :: Farosian'
    }
  },
  async fetch({ store }) {
    await store.dispatch('company/queryCompanies').catch(() => {
      console.log('Could not get companies')
    })
  },
  data() {
    return {
      loading: false,
      headers: this.getHeaders(),
      company: null,
      teamDialog: false,
      teams: [],
      show: false,
      assignedTeam: null
    }
  },
  computed: {
    ...mapGetters({
      companies: 'company/companies',
      paginationState: 'company/pagination',
      searchState: 'company/search'
    }),
    pagination: {
      get: function() {
        return this.paginationState
      },
      set: function(value) {
        this.$store.commit('company/SET_PAGINATION', value)
      }
    },
    search: {
      get: function() {
        return this.searchState
      },
      set: _.debounce(function(value) {
        this.$store.commit('company/SET_SEARCH', value)
      }, 500)
    }
  },
  watch: {
    pagination: {
      handler() {
        this.getCompanies()
      },
      deep: true
    },
    search: {
      handler() {
        this.getCompanies()
      },
      deep: true
    }
  },
  methods: {
    getCompanies() {
      this.loading = true
      this.$store.dispatch('company/queryCompanies').then(() => {
        this.loading = false
      })
    },
    getHeaders() {
      return [
        { text: 'Name', value: 'name' },
        { text: 'City', value: 'city', sortable: false },
        { text: 'Province', value: 'province', sortable: false },
        { text: 'Created By', value: 'created_by_id', sortable: false },
        { text: 'Date Created', value: 'created_at', sortable: false },
        { text: 'Actions', value: '', sortable: false }
      ]
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
    openTeamDialog(company) {
      this.teamDialog = true
      this.company = company
      this.$store.dispatch('company/queryTeams').then(response => {
        this.teams = response
      })
    }
  }
}
</script>
