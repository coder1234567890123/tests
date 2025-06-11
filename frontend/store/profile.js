import Vue from 'vue'

export const state = () => ({
  strict: true,
  profile: null,
  profiles: [],
  loading: false,
  search: '',
  pagination: {
    descending: false,
    page: 1,
    rowsPerPage: 10,
    sortBy: 'platform',
    totalItems: 0,
    rowsPerPageItems: [10, 25, 50, 100]
  }
})

export const mutations = {
  SET_PAGINATION(state, data) {
    state.pagination = data
  },
  SET_PROFILES(state, { profiles, total }) {
    state.profiles = profiles
    Vue.set(state.pagination, 'totalItems', total)
  },
  SET_SEARCH(state, search) {
    state.search = search
  },
  SET_PROFILE(state, profile) {
    state.profile = profile
  },
  UPDATE_PROFILE(state, data) {
    Vue.set(state.profile, data.prop, data.value)
  }
}

export const actions = {
  async queryProfiles(context) {
    const { sortBy, descending, page, rowsPerPage } = context.state.pagination
    const search = context.state.search

    const response = await this.$axios.$get('/profile', {
      params: {
        limit: rowsPerPage,
        page,
        sort: sortBy,
        descending,
        search
      }
    })

    // Update State
    context.commit('SET_PROFILES', {
      profiles: response,
      total: response.total
    })
  },
  create(context, data) {
    let id = this.$router.currentRoute.params.id
    return this.$axios.$post('/subject/' + id + '/profile', data)
  },
  update(context, data) {
    return this.$axios.$patch('/profile/' + context.state.profile.id, data)
  },
  validate(context, data) {
    return this.$axios.$put('/profile/' + data + '/validate')
  },
  invalidate(context, data) {
    return this.$axios.$put('/profile/' + data + '/invalidate')
  },
  confirmationOfIdentity(context, data) {
    return this.$axios.$post('/identity-confirmation/' + data['id'], data)
  },
  async getConfirmationOfIdentity(context, data) {
    let response = await this.$axios.$post(
      '/identity-confirmation/platform/' + data['id'],
      data
    )
    return response
  },
  async get(context, id) {
    if (context.state.profile === null || context.state.profile.id !== id) {
      let response = await this.$axios.$get('/profile/' + id)
      context.commit('SET_PROFILE', response)
    }
  },
  updateProfile(context, data) {
    context.commit('UPDATE_PROFILE', data)
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
  profiles(state) {
    return state.profiles
  },
  profile(state) {
    return state.profile
  }
}
