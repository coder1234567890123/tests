import Vue from 'vue'

export const state = () => ({
  strict: true,
  team: null,
  teams: [],
  loading: false,
  search: '',
  pagination: {
    descending: true,
    page: 1,
    rowsPerPage: 10,
    sortBy: 'name',
    totalItems: 0,
    rowsPerPageItems: [10, 25, 50, 100]
  }
})

export const mutations = {
  SET_PAGINATION(state, data) {
    state.pagination = data
  },
  SET_TEAMS(state, { teams, total }) {
    state.teams = teams
    Vue.set(state.pagination, 'totalItems', total)
  },
  SET_SEARCH(state, search) {
    state.search = search
  },
  SET_TEAM(state, team) {
    state.team = team
  },
  UPDATE_TEAMS(state, data) {
    Vue.set(state.teams, data.prop, data.value)
  }
}

export const actions = {
  async queryTeams(context) {
    const { sortBy, descending, page, rowsPerPage } = context.state.pagination
    const search = context.state.search

    const response = await this.$axios.$get(
      '/team/1a0d9f60-9111-4184-a78d-cc2ac6e66c02',
      {
        params: {
          limit: rowsPerPage,
          page,
          sort: sortBy,
          descending,
          search
        }
      }
    )

    // Update State
    context.commit('SET_TEAMS', {
      teams: response._embedded.users,
      total: response.total
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
  teams(state) {
    return state.teams
  }
}
