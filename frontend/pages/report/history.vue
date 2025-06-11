<template>
  <div
    id="app"
    class="container">
    <h1 class="title mb-3">Reports</h1>
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
        :items="reports"
        :pagination.sync="pagination"
        :total-items="paginationState.totalItems"
        :rows-per-page-items="pagination.rowsPerPageItems"
        must-sort
        class="elevation-1">
        <template
          v-if="props.item"
          slot="items"
          slot-scope="props">
          <tr>
            <td>{{ props.item.user }}</td>
            <td class="text-xs-left">{{ props.item.sequence }}</td>
            <td class="text-xs-left">{{ props.item.subject.first_name + ' ' + props.item.subject.last_name }}</td>
            <td class="text-xs-left">{{ props.item.company_name }}</td>
            <td class="text-xs-left">{{ props.item.created_at }}</td>
            <td class="text-xs-left">{{ getFormattedText(props.item.status) }}</td>
            <td class="text-xs-left">
              <v-menu
                transition="slide-y-transition"
                bottom>
                <v-btn
                  slot="activator"
                  icon>
                  <v-icon>more_vert</v-icon>
                </v-btn>
                <v-list>
                  <v-list-tile @click="openSubjectDialog(props.item.subject)">
                    <v-list-tile-title>View Subject</v-list-tile-title>
                  </v-list-tile>
                  <v-list-tile @click="goToSubject(props.item.subject)">
                    <v-list-tile-title>Go To Subject</v-list-tile-title>
                  </v-list-tile>
                </v-list>
              </v-menu>
            </td>
          </tr>
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
      title: 'Report History :: Farosian'
    }
  },
  data() {
    return {
      loading: false,
      show: false,
      headers: this.getHeaders()
    }
  },
  computed: {
    ...mapGetters({
      reports: 'report/reports',
      paginationState: 'report/pagination',
      searchState: 'report/search'
    }),
    pagination: {
      get: function() {
        return this.paginationState
      },
      set: function(value) {
        this.$store.commit('report/SET_PAGINATION', value)
      }
    },
    search: {
      get: function() {
        return this.searchState
      },
      set: _.debounce(function(value) {
        this.$store.commit('report/SET_SEARCH', value)
      }, 500)
    }
  },
  watch: {
    pagination: {
      handler() {
        this.getReports()
      },
      deep: true
    },
    search: {
      handler() {
        this.getReports()
      },
      deep: true
    }
  },
  methods: {
    goToSubject(subject) {
      this.$router.push('/subjects/' + subject.id)
    },
    getReports() {
      this.loading = true
      this.$store.dispatch('report/queryReports').then(() => {
        this.loading = false
      })
    },
    getHeaders() {
      return [
        { text: 'User', value: 'user' },
        { text: 'Sequence', value: 'sequence' },
        { text: 'Subject', value: 'subject' },
        { text: 'Company', value: 'company_name' },
        { text: 'Date Created', value: 'created_at' },
        { text: 'Status', value: 'status' },
        { text: 'Actions', value: '' }
      ]
    },
    getFormattedHeading() {
      return _.size(this.query)
        ? this.getFormattedText(Object.values(this.query)[0])
        : 'Custom'
    },
    getFormattedText(value) {
      return _.startCase(value)
    },
    openSubjectDialog(subject) {
      this.subjectDialog = true
      this.subject = subject
    }
  }
}
</script>
