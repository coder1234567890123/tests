<template>
  <div
    id="app"
    class="container">
    <h1 class="title mb-3">Search Terms</h1>
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
        :items="phrases"
        :pagination.sync="pagination"
        :total-items="paginationState.totalItems"
        :rows-per-page-items="pagination.rowsPerPageItems"
        must-sort
        class="elevation-1">
        <template
          slot="items"
          slot-scope="props">
          <td>{{ props.item.phrase }}</td>
          <td class="text-xs-left">{{ props.item.priority || 0 }}</td>
          <td class="text-xs-left">{{ props.item.search_type }}</td>
          <td class="text-xs-left">{{ props.item.created_by.first_name }} {{ props.item.created_by.last_name }}</td>
          <td class="text-xs-left px-0">
            <v-btn
              :to="'/search/' + props.item.id + '/edit'"
              icon
              class="mx-0">
              <v-icon color="info">edit</v-icon>
            </v-btn>
            <v-btn
              icon
              class="mx-0"
              @click.prevent="deletePhrase(props.item.id)">
              <v-icon color="error">delete</v-icon>
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
      title: 'Search Terms :: Farosian'
    }
  },
  data() {
    return {
      loading: false,
      headers: this.getHeaders()
    }
  },
  computed: {
    ...mapGetters({
      phrases: 'phrase/phrases',
      paginationState: 'phrase/pagination',
      searchState: 'phrase/search'
    }),
    pagination: {
      get: function() {
        return this.paginationState
      },
      set: function(value) {
        this.$store.commit('phrase/SET_PAGINATION', value)
      }
    },
    search: {
      get: function() {
        return this.searchState
      },
      set: _.debounce(function(value) {
        this.$store.commit('phrase/SET_SEARCH', value)
      }, 500)
    }
  },
  watch: {
    pagination: {
      handler() {
        this.getSearchTerms()
      },
      deep: true
    },
    search: {
      handler() {
        this.getSearchTerms()
      },
      deep: true
    }
  },
  methods: {
    inProgress: function() {},
    complete: function() {},
    matches: function() {},
    scored: function() {},
    all: function() {},

    getSearchTerms() {
      this.loading = true
      this.$store.dispatch('phrase/queryPhrases').then(() => {
        this.loading = false
      })
    },
    deletePhrase(id) {
      this.$store
        .dispatch('phrase/delete', id)
        .then(() => {
          this.$toast.success('Phrase successfully deleted!')
          this.$router.go() //soft reload of current page
        })
        .catch(() => {
          this.$toast.error(
            'Could not delete phrase. please double check validation!'
          )
        })
    },
    disablePhrase(id) {
      this.$store
        .dispatch('phrase/disable', id)
        .then(() => {
          this.$toast.success('Phrase successfully disabled!')
          this.$router.go() //soft reload of current page
        })
        .catch(() => {
          this.$toast.error(
            'Could not disable phrase. please double check validation!'
          )
        })
    },
    enablePhrase(id) {
      this.$store
        .dispatch('phrase/enable', id)
        .then(() => {
          this.$toast.success('Phrase successfully enabled!')
          this.$router.go() //soft reload of current page
        })
        .catch(() => {
          this.$toast.error(
            'Could not enable phrase. please double check validation!'
          )
        })
    },
    getHeaders() {
      return [
        { text: 'Phrase', value: 'phrase' },
        { text: 'Priority', value: 'priority' },
        { text: 'Platform', value: 'search_type' },
        { text: 'Created By', value: 'created_by', sortable: false },
        { text: 'Actions', value: '', sortable: false }
      ]
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
</style>
