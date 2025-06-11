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

    const response = await this.$axios.$get('/team/paginated', {
      params: {
        limit: rowsPerPage,
        page,
        sort: sortBy,
        descending,
        search
      }
    })

    // Update State
    context.commit('SET_TEAMS', {
      teams: response._embedded.teams,
      total: response.total
    })
  },

  async queryCompanies(context, id) {
    const response = await this.$axios.$get('/team/' + id)

    return response.companies
  },

  async queryAnalysis(context, id) {
    const response = await this.$axios.$get('/team/' + id)

    return response.users
  },
  async queryTeamUser(context, id) {
    const response = await this.$axios.$get('/team/teamLead')

    return response.users
  },
  async deleteTeamUser(context, id) {
    const response = await this.$axios.$delete('/user/team-remove/' + id)

    return response.users
  },
  async deleteTeam(context, id) {
    const response = await this.$axios.$delete('/team/' + id)

    return response.users
  },
  async deleteTeamCompany(context, id) {
    const response = await this.$axios.$delete(
      '/companies/company-remove/' + id
    )

    return response.users
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
