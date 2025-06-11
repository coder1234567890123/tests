<template>
  <div
    id="app"
    class="container">
    <h1 class="title mb-3">Questions</h1>
    <v-card>
      <v-card-title>
        <v-text-field
          v-model="search"
          append-icon="search"
          label="Search"
          single-line
          hide-details/>
      </v-card-title>
      <v-card-title>
        <v-layout
          row
          wrap>
          <v-flex md2>
            <v-select
              id="platformGet"
              :items="platformGet"
              :value="platformGet"
              tabindex="5"
              label="Platform"
              @input="gatPlaform( $event)"/>
          </v-flex>
        </v-layout>
      </v-card-title>
      <v-data-table
        :headers="headers"
        :loading="loading"
        :items="questions"
        :pagination.sync="pagination"
        :total-items="paginationState.totalItems"
        :rows-per-page-items="pagination.rowsPerPageItems"
        must-sort
        class="elevation-1">
        <template
          v-if="props.item"
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
          <td class="text-xs-left">{{ props.item.question }}</td>
          <td class="text-xs-left">{{ getFormattedText(props.item.answer_type) }}</td>
          <td class="text-xs-left">{{ props.item.order_number }}</td>
          <td class="text-xs-left">{{ props.item.platform }}</td>
          <td>
            <a
              v-if="props.item.enabled === false"
              class="falseColor"
              @click.prevent="enableQuestion(props.item.id)">
              Enable
            </a>
            <a
              v-else
              class="trueColor"
              @click.prevent="disableQuestion(props.item.id)">
              Disable
            </a>
          </td>
          <td class="text-xs-right layout px-0">
            <v-btn
              :to="'/question/' + props.item.id + '/edit'"
              icon
              class="mx-0">
              <v-icon color="info">edit</v-icon>
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
      title: 'Questions :: Farosian'
    }
  },
  data() {
    return {
      loading: false,
      headers: this.getHeaders(),
      platformGet: [
        'Facebook',
        'Twitter',
        'Instagram',
        'Pinterest',
        'LinkedIn',
        'Youtube',
        'Flickr',
        'Web'
      ]
    }
  },
  computed: {
    ...mapGetters({
      questions: 'question/questions',
      paginationState: 'question/pagination',
      searchState: 'question/search',
      platformGet: 'question/platform'
    }),
    pagination: {
      get: function() {
        return this.paginationState
      },
      set: function(value) {
        this.$store.commit('question/SET_PAGINATION', value)
      }
    },
    search: {
      get: function() {
        return this.searchState
      },
      set: _.debounce(function(value) {
        this.$store.commit('question/SET_SEARCH', value)
      }, 500)
    }
  },
  watch: {
    pagination: {
      handler() {
        this.getQuestions()
      },
      deep: true
    },
    search: {
      handler() {
        this.$store.commit('question/SET_PLATFORM', '')
        this.getQuestions()
      },
      deep: true
    }
  },
  methods: {
    gatPlaform(value) {
      this.loading = true
      this.$store.dispatch('question/getPlatformQuestions', value).then(() => {
        this.loading = false
      })
    },
    getFormattedText(value) {
      return _.startCase(value)
    },
    inProgress: function() {},
    complete: function() {},
    matches: function() {},
    scored: function() {},
    all: function() {},

    getQuestions() {
      this.loading = true
      this.$store.dispatch('question/queryQuestions').then(() => {
        this.loading = false
      })
    },
    enableQuestion(id) {
      this.$store
        .dispatch('question/enable', id)
        .then(() => {
          this.$toast.success('Question successfully disabled!')
          this.loading = true
          this.$store.dispatch('question/queryQuestions').then(() => {
            this.loading = false
          })
        })
        .catch(e => {
          this.$toast.error(
            'could not disable phrase. please double check validation!' + e
          )
        })
    },
    disableQuestion(id) {
      this.$store
        .dispatch('question/disable', id)
        .then(() => {
          this.$toast.success('Question successfully enabled!')
          this.loading = true
          this.$store.dispatch('question/queryQuestions').then(() => {
            this.loading = false
          })

          // this.$router.go()
        })
        .catch(e => {
          this.$toast.error(
            'could not enable questions. pease double check validation' + e
          )
        })
    },
    getHeaders() {
      return [
        {},
        { text: 'Question', value: 'question' },
        { text: 'Answer Type', value: 'answer_type' },
        { text: 'Order Number', value: 'order_number' },
        { text: 'Platform', value: 'platform' },
        { text: 'Status', value: 'status' },
        { text: 'Actions', value: '', sortable: false }
      ]
    },
    deleteQuestion(id) {
      this.$store
        .dispatch('question/disable', id)
        .then(() => {
          this.$toast.success('Successfully deleted question')
          this.$store.dispatch('question/queryQuestions')
        })
        .catch(() => {
          this.$toast.error('Could not delete question')
        })
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

.warningColor {
  color: darkred;
}

.falseColor {
  color: darkred;
}

.trueColor {
  color: darkgreen;
}
</style>
