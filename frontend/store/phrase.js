import Vue from 'vue'

export const state = () => ({
  strict: true,
  phrase: null,
  phrases: [],
  loading: false,
  search: '',
  pagination: {
    descending: false,
    page: 1,
    rowsPerPage: 10,
    sortBy: 'priority',
    totalItems: 0,
    rowsPerPageItems: [10, 25, 50, 100]
  }
})

export const mutations = {
  SET_PAGINATION(state, data) {
    state.pagination = data
  },
  SET_SEARCH(state, search) {
    state.search = search
  },
  SET_PHRASES(state, { phrases, total }) {
    state.phrases = phrases
    Vue.set(state.pagination, 'totalItems', total)
  },
  SET_PHRASE(state, phrase) {
    state.phrase = phrase
  },
  UPDATE_PHRASE(state, phrase) {
    state.phrase.phrase = phrase
  },
  UPDATE_PLATFORM(state, platform) {
    state.phrase.search_type = platform
  }
}

export const actions = {
  async queryPhrases(context) {
    const { sortBy, descending, page, rowsPerPage } = context.state.pagination
    const search = context.state.search

    const response = await this.$axios.$get('/phrase', {
      params: {
        limit: rowsPerPage,
        page,
        sort: sortBy,
        descending,
        search
      }
    })

    // Update State
    context.commit('SET_PHRASES', {
      phrases: response._embedded.phrase,
      total: response.total
    })
  },
  validate(context, data) {
    return this.$axios.$post('/phrase/test', data)
  },
  create(context, data) {
    return this.$axios.$post('/phrase', data)
  },
  update(context, data) {
    return this.$axios.$patch('/phrase/' + context.state.phrase.id, data)
  },
  disable(context, id) {
    return this.$axios.$delete('/phrase/' + id + '/disable')
  },
  enable(context, id) {
    return this.$axios.$put('/phrase/' + id + '/enable')
  },
  delete(context, id) {
    return this.$axios.$delete('/phrase/' + id)
  },
  async get(context, id) {
    if (context.state.phrase === null || context.state.phrase.id !== id) {
      let response = await this.$axios.$get('/phrase/' + id)
      context.commit('SET_PHRASE', response)
    }
  },
  async updateTerm(context, value) {
    let phrase = !context.state.phrase
      ? context.state.phrase.phrase + value
      : context.state.phrase.phrase + ' ' + value
    context.commit('UPDATE_PHRASE', phrase)
  },
  updatePhrase(context, value) {
    context.commit('UPDATE_PHRASE', value)
  },
  updatePlatform(context, value) {
    context.commit('UPDATE_PLATFORM', value)
  }
}

export const getters = {
  loading(state) {
    return state.loading
  },
  pagination(state) {
    return state.pagination
  },
  search(state) {
    return state.search
  },
  phrases(state) {
    return state.phrases
  },
  phrase(state) {
    return state.phrase
  }
}
