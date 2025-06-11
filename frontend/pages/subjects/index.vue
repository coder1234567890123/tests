<template>
  <div
    id="app"
    class="container">
    <h1 class="title mb-3">Subjects</h1>
    <v-card class="mb-3">
      <v-container grid-list-md>
        <v-layout
          row
          wrap>
          <v-flex
            xs12
            sm6
            md4>
            <v-text-field
              v-model="searchFirstName"
              append-icon="search"
              label="First Name"
              single-line
              hide-details/>
          </v-flex>
          <v-flex
            xs12
            sm6
            md4>
            <v-text-field
              v-model="searchLastName"
              append-icon="search"
              label="Last Name"
              single-line
              hide-details/>
          </v-flex>
          <v-flex
            xs12
            sm6
            md4>
            <v-text-field
              v-model="identificationNo"
              append-icon="search"
              label="Identification No"
              single-line
              hide-details/>
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
      <!--      <v-card-title>-->
      <!--        <v-text-field-->
      <!--          v-model="search"-->
      <!--          append-icon="search"-->
      <!--          label="Search"-->
      <!--          single-line-->
      <!--          hide-details />-->
      <!--      </v-card-title>-->
      <!--      <hr>-->
      <v-data-table
        :headers="headers"
        :loading="loading"
        :items="subjects"
        :pagination.sync="pagination"
        :total-items="paginationState.totalItems"
        :rows-per-page-items="pagination.rowsPerPageItems"
        must-sort
        class="elevation-1">
        <template
          slot="items"
          slot-scope="props">
          <td>{{ `${props.item.first_name} ${props.item.last_name}` }}</td>
          <td>{{ props.item.company }}</td>
          <td class="text-xs-left">{{ props.item.created_by ? props.item.created_by : 'N/A' }}</td>
          <td class="text-xs-left">{{ props.item.created_at }}</td>
          <td
            class="text-xs-left">
            <nuxt-link :to="{ name: $getRoute('SUBJECTS_PROFILES'), params: { id: props.item.id } }">Inspect</nuxt-link>
          </td>
          <td class="text-xs-left text-no-wrap">
            {{ getFormattedText(props.item.status) }}
            <v-tooltip
              v-if="props.item.status === 'new_subject'"
              top>
              <v-btn
                slot="activator"
                icon>
                <v-icon
                  color="error lighten-1"
                  small>warning
                </v-icon>
              </v-btn>
              <span>An investigation request needs to be sent for this subject</span>
            </v-tooltip>
          </td>
          <td class="text-xs-left">{{ getFormattedText(props.item.report_type) }}</td>
          <td class="justify-center layout px-0">
            <v-btn
              icon
              class="mx-0"
              @click="$router.push({ name: $getRoute('SUBJECTS_VIEW'), params: { id: props.item.id } })">
              <v-icon color="grey">visibility</v-icon>
            </v-btn>
            <v-btn
              v-if="$auth.hasScope('ROLE_SUPER_ADMIN') || $auth.hasScope('ROLE_ADMIN_USER') || $auth.hasScope('ROLE_USER_STANDARD')
              || $auth.hasScope('ROLE_USER_MANAGER') || ($auth.hasScope('ROLE_TEAM_LEAD') && search.length === 0)"
              icon
              class="mx-0"
              @click="$router.push({ name: $getRoute('SUBJECTS_EDIT'), params: { id: props.item.id } })">
              <v-icon color="info">edit</v-icon>
            </v-btn>
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
  </div>
</template>
<script>
import { mapGetters } from 'vuex'
import _ from 'lodash'

export default {
  head() {
    return {
      title: 'Subjects :: Farosian'
    }
  },
  data() {
    return {
      loading: false,
      headers: this.getHeaders(),
      searchFirstName: '',
      searchLastName: '',
      identificationNo: ''
    }
  },
  computed: {
    ...mapGetters({
      subjects: 'subject/subjects',
      paginationState: 'subject/pagination',
      searchState: 'subject/search'
    }),
    pagination: {
      get: function() {
        return this.paginationState
      },
      set: function(value) {
        this.$store.commit('subject/SET_PAGINATION', value)
      }
    },
    search: {
      get: function() {
        return this.searchState
      },
      set: _.debounce(function(value) {
        this.$store.commit('subject/SET_SEARCH', value)
      }, 500)
    }
  },
  watch: {
    pagination: {
      handler() {
        this.getSubjects()
      },
      deep: true
    },
    search: {
      handler() {
        this.getSubjects()
      },
      deep: true
    }
  },
  methods: {
    filterQueues() {
      let data = {
        search_first_name: this.searchFirstName,
        search_last_name: this.searchLastName,
        search_id_no: this.identificationNo
      }

      this.$store.dispatch('subject/searchFilter', data).then(() => {
        this.loading = false
      })
    },
    clearFilters() {
      this.searchFirstName = ''
      this.searchLastName = ''
      this.identificationNo = ''
      this.filterQueues()
    },
    getSubjects() {
      this.loading = true
      this.$store.dispatch('subject/querySubjects').then(() => {
        this.loading = false
      })
    },
    getHeaders() {
      return [
        { text: 'Full Name', value: 'first_name' },
        { text: 'Company', value: 'company' },
        { text: 'Created By', value: 'created_by' },
        { text: 'Date Created', value: 'created_at' },
        { text: 'Profiles', value: '', sortable: false },
        { text: 'Status', value: 'status' },
        { text: 'Report Type', value: 'report_type' },
        { text: 'Actions', value: '', sortable: false }
      ]
    },
    getFormattedText(value) {
      return _.startCase(value)
    }
  }
}
</script>
