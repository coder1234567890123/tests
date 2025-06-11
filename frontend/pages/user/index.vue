<template>
  <div
    id="app"
    class="container">
    <h1 class="title mb-3">Users</h1>
    <v-card class="mb-3">
      <v-container grid-list-md>
        <v-layout
          row
          wrap>
          <v-flex
            md4
            sm6
            xs12>
            <v-text-field
              v-model="searchFirstName"
              append-icon="search"
              hide-details
              label="First Name"
              single-line/>
          </v-flex>
          <v-flex
            md4
            sm6
            xs12>
            <v-text-field
              v-model="searchLastName"
              append-icon="search"
              hide-details
              label="Last Name"
              single-line/>
          </v-flex>
          <v-flex
            md4
            sm6
            xs12>
            <v-text-field
              v-model="searchEmail"
              append-icon="search"
              hide-details
              label="Email"
              single-line/>
          </v-flex>
          <v-flex
            md4
            xs12>
            <v-btn
              color="info"
              small
              top
              @click="filterQueues(true,'filter')">
              <v-icon dark>filter_list</v-icon>
              Filter
            </v-btn>
            <v-btn
              color="grey darken-1"
              flat
              small
              top
              @click="clearFilters">
              <v-icon dark>clear</v-icon>
              Clear All
            </v-btn>
          </v-flex>
        </v-layout>
      </v-container>
    </v-card>
    <v-card>
      <v-card-title/>
      <v-data-table
        :headers="headers"
        :items="users"
        :loading="loading"
        :pagination.sync="pagination"
        :rows-per-page-items="pagination.rowsPerPageItems"
        :total-items="paginationState.totalItems"
        class="elevation-1"
        must-sort>
        <template
          slot="items"
          slot-scope="props">
          <td>

            <span
              v-if="props.item.enabled == true"
              class="material-icons trueColor">
              done
            </span>
            <span
              v-if="props.item.enabled == false"
              class="material-icons falseColor">
              clear
            </span>
          </td>
          <td>{{ props.item.first_name }}</td>
          <td class="text-xs-left">{{ props.item.last_name }}</td>
          <td class="text-xs-left">{{ props.item.company }}</td>
          <td class="text-xs-left">{{ props.item.email }}</td>
          <td class="text-xs-left">
            {{ props.item.roles.length ? getFormattedText(props.item.roles.join(', '), 'ROLE') : 'N/A' }} &nbsp;
            <v-btn
              v-if="getFormattedText(props.item.roles) =='ROLE ANALYST' "
              slot="activator"
              class="mx-0"
              icon
              @click="openTeamDialog(props.item.id)">
              <v-icon color="blue">how_to_reg</v-icon>
            </v-btn>
          </td>
          <td class="text-xs-left">
            <a
              v-if="props.item.enabled && ($auth.hasScope('ROLE_SUPER_ADMIN') || $auth.hasScope('ROLE_TEAM_LEAD'))"
              class="trueColor"
              href=""
              @click.prevent="changeStatus(props.item.id, false)">
              Disable
            </a>
            <a
              v-if="!props.item.enabled && ($auth.hasScope('ROLE_SUPER_ADMIN') || $auth.hasScope('ROLE_TEAM_LEAD'))"
              class=" falseColor"
              href=""
              @click.prevent="changeStatus(props.item.id, true)">
              Enable
            </a>
          </td>
          <td class="text-xs-left px-0">
            <v-btn
              :to="'/user/' + props.item.id + '/profile'"
              class="mx-0"
              icon>
              <v-icon color="grey">visibility</v-icon>
            </v-btn>
            <v-btn
              v-if="($auth.hasScope('ROLE_SUPER_ADMIN') || $auth.hasScope('ROLE_TEAM_LEAD'))"
              :to="'/user/' + props.item.id + '/edit'"
              class="mx-0"
              icon>
              <v-icon color="info">edit</v-icon>
            </v-btn>
            <!--            <v-btn-->
            <!--              v-if="($auth.hasScope('ROLE_SUPER_ADMIN') || $auth.hasScope('ROLE_TEAM_LEAD')) "-->
            <!--              icon-->
            <!--              class="mx-0"-->
            <!--              @click.prevent="archiveUser(props.item.id)">-->
            <!--              <v-icon color="error">delete</v-icon>-->
            <!--            </v-btn>-->
          </td>
        </template>
        <v-alert
          slot="no-results"
          :value="true"
          icon="warning">
          Your search for "{{ search }}" found no results.
        </v-alert>
      </v-data-table>
    </v-card>
    <v-dialog
      v-model="teamDialog"
      max-width="320px"
      persistent
      scrollable>
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
      title: 'Users :: Farosian'
    }
  },
  data() {
    return {
      assignedTeam: null,
      teams: [],
      userId: '',
      teamDialog: false,
      loading: false,
      headers: this.getHeaders(),
      searchFirstName: '',
      searchLastName: '',
      searchEmail: ''
    }
  },
  computed: {
    ...mapGetters({
      users: 'user/users',
      paginationState: 'user/pagination',
      searchState: 'user/search'
    }),
    pagination: {
      get: function() {
        return this.paginationState
      },
      set: function(value) {
        this.$store.commit('user/SET_PAGINATION', value)
      }
    },
    search: {
      get: function() {
        return this.searchState
      },
      set: _.debounce(function(value) {
        this.$store.commit('user/SET_SEARCH', value)
      }, 500)
    }
  },
  watch: {
    pagination: {
      handler() {
        this.getUsers()
      },
      deep: true
    },
    search: {
      handler() {
        this.getUsers()
      },
      deep: true
    }
  },
  methods: {
    filterQueues() {
      let data = {
        search_first_name: this.searchFirstName,
        search_last_name: this.searchLastName,
        search_email: this.searchEmail
      }

      this.$store.dispatch('user/searchFilter', data).then(() => {
        this.loading = false
      })
    },
    clearFilters() {
      this.searchFirstName = ''
      this.searchLastName = ''
      this.searchEmail = ''
      this.filterQueues()
    },
    assignTeam() {
      let sendData = {
        user_id: this.userId,
        team_id: this.assignedTeam
      }

      this.$store
        .dispatch('user/assignTeamIndex', sendData)
        .then(() => {
          this.$toast.success('User assigned...')
          this.teamDialog = false
          this.teams = []
          this.userId = ''
          this.assignedTeam = ''
        })
        .catch(() => {
          this.$toast.error('Could not assign user...')
        })
    },
    openTeamDialog(id) {
      this.userId = id

      this.teams = []
      this.teamDialog = true
      this.$store.dispatch('user/queryTeams').then(response => {
        this.teams = response
      })
    },
    getUsers() {
      this.loading = true
      this.$store.dispatch('user/queryUsers').then(() => {
        this.loading = false
      })
    },
    changeStatus(id, status) {
      this.$store
        .dispatch('user/changeStatus', { id, status })
        .then(() => {
          this.$toast.success(
            'User successfully ' + (status ? 'enabled!' : 'disabled!')
          )
          this.$store.dispatch('user/queryUsers')
        })
        .catch(() => {
          console.log('Could not update user status. Please try again.')
        })
    },

    archiveUser(id) {
      this.$store
        .dispatch('user/archiveUser', id)
        .then(() => {
          this.$toast.success('User successfully archived!')
          this.$store.dispatch('user/queryUsers')
        })
        .catch(() => {
          console.log('Could not archive user status. Please try again.')
        })
    },
    getHeaders() {
      return [
        {},
        { text: 'First Name', value: 'first_name' },
        { text: 'Last Name', value: 'last_name' },
        { text: 'Company', value: 'company' },
        { text: 'Email', value: 'email' },
        { text: 'Role', value: 'roles' },
        { text: 'Enabled', value: 'status', sortable: false },
        { text: 'Actions', value: '', sortable: false }
      ]
    },
    getFormattedText(value, trim = '') {
      return _.startCase(_.replace(value, new RegExp(trim, 'gi'), ''))
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

.falseColor {
  color: darkred;
}

.trueColor {
  color: darkgreen;
}
</style>
