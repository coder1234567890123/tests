<template>
  <div
    id="app"
    class="container">
    <h1 class="title mb-3">Team Analysts</h1>
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
          <td>{{ props.item.first_name }}</td>
          <td :class="[$auth.hasScope('ROLE_SUPER_ADMIN') ? 'text-xs-center px-0' : 'text-xs-left']">
            <v-btn
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
            <v-tooltip
              v-if="$auth.hasScope('ROLE_SUPER_ADMIN')"
              v-model="show"
              top>
              <v-btn
                slot="activator"
                icon
                class="mx-0"
                @click="openTeamDialog(props.item.id)">
                <v-icon color="blue">how_to_reg</v-icon>
              </v-btn>
              <v-btn
                slot="activator"
                icon
                class="mx-0"
                @click="openTeamDialog(props.item.id)">
                <v-icon color="blue">how_to_reg</v-icon>
              </v-btn>
              <span>{{ props.item.team_id ? 'Re-assign' : 'Assign' }} company to a team</span>
            </v-tooltip>
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
      teams: 'analysts/teams',
      paginationState: 'analysts/pagination',
      searchState: 'analysts/search'
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
        { text: 'Teams', value: 'team_name' },
        { text: 'Actions', value: '', sortable: false }
      ]
    }
  }
}
</script>
