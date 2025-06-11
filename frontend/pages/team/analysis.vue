<template>
  <div
    id="app"
    class="container">
    <h1 class="title mb-3">Analysis</h1>
    <v-card>
      <v-card-title>
        <v-text-field
          v-model="search"
          append-icon="search"
          label="Search"
          single-line
          hide-details />
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
          <td>
            <v-btn
              :to="'/user/'+props.item.id+'/profile'"
              icon
              class="mx-0">
              <v-icon color="info">visibility</v-icon>
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
  </div>
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
      loading: false,
      headers: this.getHeaders(),
      show: false
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
      return [
        { text: 'Teams', value: 'teamName' },
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
