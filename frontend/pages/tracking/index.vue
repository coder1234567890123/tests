<template>
  <div
    id="app"
    class="container"
    style="list-style: none">
    <h1 class="title mb-3">Tracking</h1>
    <v-card>
      <v-card-title>
        <v-text-field
          v-model="search"
          append-icon="search"
          hide-details
          label="Search"
          single-line/>
      </v-card-title>
      <v-btn
        class="ma-4 right"
        color="info"
        small
        @click="exportQueues()">
        <v-icon dark>get_app</v-icon>
        Export
      </v-btn>
      <v-data-table
        :headers="headers"
        :items="tracking"
        :loading="loading"
        :pagination.sync="pagination"
        :rows-per-page-items="pagination.rowsPerPageItems"
        :total-items="paginationState.totalItems"
        class="elevation-1"
        must-sort>
        <template
          slot="items"
          slot-scope="props">
          <td>{{ props.item.created_at }}</td>
          <td>{{ props.item.time_created }}</td>
          <td>{{ props.item.user.first_name }} {{ props.item.user.last_name }}</td>
          <td>
            <div v-if="props.item.company">
              {{ props.item.company.name }}
            </div>
            <div v-else>
              No company
            </div>
          </td>
          <td>
            <span
              v-if="props.item.subject"
              class="mousePointer"
              @click="goToSubject(props.item.subject.id)">{{
                props.item.subject.first_name
              }} {{ props.item.subject.last_name }} </span></td>
          <td>{{ removeUnderScore(props.item.source) }}</td>
          <td>{{ removeUnderScore(props.item.action) }}</td>
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
      title: 'User Tracking :: Farosian'
    }
  },
  data() {
    return {
      teamDialog: false,
      loading: false,
      headers: this.getHeaders(),
      show: false,
      userDetails: [],
      teamsAnalysis: []
    }
  },
  computed: {
    ...mapGetters({
      tracking: 'tracking/tracking',
      paginationState: 'tracking/pagination',
      searchState: 'tracking/search'
    }),
    pagination: {
      get: function() {
        return this.paginationState
      },
      set: function(value) {
        this.$store.commit('tracking/SET_PAGINATION', value)
      }
    },
    search: {
      get: function() {
        return this.searchState
      },
      set: _.debounce(function(value) {
        this.$store.commit('tracking/SET_SEARCH', value)
      }, 500)
    }
  },
  watch: {
    pagination: {
      handler() {
        this.getTracking()
      },
      deep: true
    },
    search: {
      handler() {
        this.getTracking()
      },
      deep: true
    }
  },
  methods: {
    goToSubject(id) {
      this.$router.push('/subjects/' + id)
    },
    getTracking() {
      this.loading = true
      this.$store.dispatch('tracking/queryTracking').then(() => {
        this.loading = false
      })
    },
    getHeaders() {
      let headers = [
        { text: 'Date', value: 'created_at' },
        { text: 'Time', value: 'created_at' },
        { text: 'User', value: 'created_at' },
        { text: 'Company Name', value: 'created_at' },
        { text: 'Subject', value: 'created_at' },
        { text: 'Source', value: 'created_at' },
        { text: 'Action', value: 'created_at' }
      ]
      return headers
    },
    exportQueues() {
      this.loading = true
      this.$store.dispatch('tracking/queryExportQueues').then(response => {
        let filename = this.getName(response.headers['content-disposition'])
        this.loading = false
        this.downloadFile(response.data, filename)
      })
    },
    getName(disposition) {
      let filename = 'report.xlxs'
      if (disposition && disposition.indexOf('inline') !== -1) {
        var filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/
        var matches = filenameRegex.exec(disposition)
        if (matches != null && matches[1]) {
          filename = matches[1].replace(/['"]/g, '')
        }
      }
      return filename
    },
    downloadFile(fileData, fileName) {
      const url = window.URL.createObjectURL(new Blob([fileData]))
      const link = document.createElement('a')
      link.href = url
      link.setAttribute('download', fileName) //or any other extension
      document.body.appendChild(link)
      link.click()
    },
    removeUnderScore(action) {
      return _.startCase(action)
    }
  }
}
</script>
<style scoped>
.mousePointer {
  color: #111211;
  text-decoration: underline;
  cursor: pointer;
}

.mousePointer :hover {
  cursor: pointer;
}
</style>
