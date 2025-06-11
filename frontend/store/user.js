import Vue from 'vue'

export const state = () => ({
  strict: true,
  user: null,
  users: [],
  loading: false,
  search: '',
  pagination: {
    descending: false,
    page: 1,
    rowsPerPage: 10,
    sortBy: 'first_name',
    totalItems: 0,
    rowsPerPageItems: [10, 25, 50, 100]
  }
})

export const mutations = {
  SET_PAGINATION(state, data) {
    state.pagination = data
  },
  SET_USERS(state, { users, total }) {
    state.users = users
    Vue.set(state.pagination, 'totalItems', total)
  },
  SET_SEARCH(state, search) {
    state.search = search
  },
  SET_USER(state, subject) {
    state.user = subject
  },
  UPDATE_USER(state, data) {
    Vue.set(state.user, data.prop, data.value)
  },
  UPDATE_USER_PROFILE(state, data) {
    Vue.set(state.user, data.prop, data.value)
  }
}

export const actions = {
  async queryUsers(context) {
    const { sortBy, descending, page, rowsPerPage } = context.state.pagination
    const search = context.state.search

    const response = await this.$axios.$get('/users', {
      params: {
        limit: rowsPerPage,
        page,
        sort: sortBy,
        descending,
        search
      }
    })

    // Update State
    context.commit('SET_USERS', {
      users: response._embedded.users,
      total: response.total
    })
  },
  async queryTeams(context) {
    const response = await this.$axios.$get('/team')

    return response
  },
  assignTeam(context, teamId) {
    return this.$axios.$patch('/users/' + context.state.user.id, {
      team: { id: teamId }
    })
  },
  assignTeamIndex(context, sendData) {
    console.log('sendData - Index')
    console.log(sendData)
    return this.$axios.$patch('/users/' + sendData.user_id, {
      team: { id: sendData.team_id }
    })
  },
  async queryCompanies(context) {
    const response = await this.$axios.$get('/companies')

    return response._embedded.companies
  },
  create(context, data) {
    return this.$axios.$post('/users', data)
  },
  createCompanyUser(context, data) {
    return this.$axios.$post('/users/company', data)
  },
  update(context, data) {
    return this.$axios.$patch('/users/' + context.state.user.id, data)
  },
  updateCompanyUser(context, data) {
    return this.$axios.$patch('/users/company' + context.state.user.id, data)
  },
  updateProfile(context, data) {
    return this.$axios.$patch('/users/update', data)
  },
  updateUser(context, data) {
    context.commit('UPDATE_USER', data)
  },
  async get(context, id) {
    if (context.state.user === null || context.state.user.id !== id) {
      let response = await this.$axios.$get('/users/' + id)
      context.commit('SET_USER', response)
    }
  },
  async getMe(context, id) {
    let response = await this.$axios.$get('/users/me')
    context.commit('SET_USER', response)
  },
  forgot(context, data) {
    return this.$axios.$post('/reset-password', data)
  },
  async resetCompany(context, id) {
    return this.$axios.$patch('/users/reset-company/' + id)
  },
  reset(context, data) {
    return this.$axios.$post(
      '/reset-password/' + this.$router.currentRoute.params.token,
      data
    )
  },
  changeStatus(context, data) {
    if (data.status) {
      return this.$axios.$put('/user/' + data.id + '/enable')
    }

    return this.$axios.$delete('/user/' + data.id)
  },
  archiveUser(context, id) {
    return this.$axios.$put('/user/' + id + '/archive')
  },
  async searchFilter(context, data) {
    const { sortBy, descending, page, rowsPerPage } = context.state.pagination
    const search = context.state.search

    const response = await this.$axios.$get('/users', {
      params: {
        limit: rowsPerPage,
        page,
        sort: sortBy,
        descending,
        search_first_name: data.search_first_name,
        search_last_name: data.search_last_name,
        search_email: data.search_email
      }
    })

    // Update State
    context.commit('SET_USERS', {
      users: response._embedded.users,
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
  users(state) {
    return state.users
  },
  user(state) {
    return state.user
  }
}
