import Vue from 'vue'

export const state = () => ({
  strict: true,
  team: null,
  tracking: [],
  loading: false,
  search: '',
  pagination: {
    descending: true,
    page: 1,
    rowsPerPage: 10,
    sortBy: 'name',
    totalItems: 0,
    rowsPerPageItems: [10, 25, 50, 100]
  },
  filters: {
    dateFrom: null,
    dateTo: null,
    company: null,
    type: null,
    status: null
  }
})

export const mutations = {
  SET_PAGINATION(state, data) {
    state.pagination = data
  },
  SET_TRACKING(state, { tracking, total }) {
    state.tracking = tracking
    Vue.set(state.pagination, 'totalItems', total)
  },
  SET_SEARCH(state, search) {
    state.search = search
  },
  SET_TEAM(state, team) {
    state.team = team
  },
  UPDATE_TEAMS(state, data) {
    Vue.set(state.tracking, data.prop, data.value)
  }
}

export const actions = {
  async queryTracking(context) {
    const { sortBy, descending, page, rowsPerPage } = context.state.pagination
    const search = context.state.search

    const response = await this.$axios.$get('/usertracking', {
      params: {
        limit: rowsPerPage,
        page,
        sort: sortBy,
        descending,
        search
      }
    })

    // Update State
    context.commit('SET_TRACKING', {
      tracking: response._embedded.usertracking,
      total: response.total
    })
  },
  async queryExportQueues(context) {
    const { sortBy, descending, page, rowsPerPage } = context.state.pagination
    const search = context.state.search

    return await this.$axios.get('/usertracking/export', {
      params: {
        limit: rowsPerPage,
        page,
        sort: sortBy,
        descending,
        search
      },
      responseType: 'arraybuffer'
    })
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
  tracking(state) {
    return state.tracking
  }
}
