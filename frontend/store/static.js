export const state = () => ({
  countries: [],
  provinces: [],
  roles: [],
  platforms: [
    { label: 'Facebook', value: 'facebook', disabled: false },
    { label: 'Twitter', value: 'twitter', disabled: false },
    { label: 'Instagram', value: 'instagram', disabled: false },
    { label: 'Pinterest', value: 'pinterest', disabled: false },
    { label: 'LinkedIn', value: 'linkedin', disabled: false },
    { label: 'Youtube', value: 'youtube', disabled: false },
    { label: 'Flickr', value: 'flickr', disabled: false },
    { label: 'Web', value: 'web', disabled: false }
  ],
  answerTypes: [
    { label: 'Yes/No', value: 'yes_no' },
    { label: 'Multiple Choice', value: 'multiple_choice' }
  ],
  reportTypes: [
    { label: 'All Reports', value: 'all', disabled: false },
    { label: 'Basic Report', value: 'basic', disabled: false },
    { label: 'Full Report', value: 'full', disabled: false },
    { label: 'Standard Report', value: 'standard', disabled: false },
    { label: 'High Profile Report', value: 'high_profile', disabled: false }
  ],
  brandingTypes: [
    { label: 'Default', value: 'default' },
    { label: 'White Label', value: 'white_label' },
    { label: 'Co Branded', value: 'co_branded' }
  ]
})

export const mutations = {
  SET_COUNTRIES(state, data) {
    state.countries = data
  },
  SET_ROLES(state, data) {
    state.roles = data
  },
  SET_PROVINCES(state, data) {
    state.provinces = data
  },
  UPDATE_PLATFORM(state, data) {
    state.platforms.slice(1, state.platforms.length).forEach(function(item) {
      item.disabled = data
    })
  },
  UPDATE_REPORT_TYPE(state, data) {
    state.report_types
      .slice(0, state.report_types.length - 1)
      .forEach(function(item) {
        item.disabled = data
      })
  }
}

export const actions = {
  async initCountries({ commit, state }) {
    if (state.countries.length === 0) {
      const response = await this.$axios.$get('/country')

      commit('SET_COUNTRIES', response)
    }
  },
  async initRoles({ commit, state }) {
    if (state.roles.length === 0) {
      const response = await this.$axios.$get('/roles')

      commit('SET_ROLES', response)
    }
  },
  async initProvinces({ commit, state }) {
    if (state.provinces.length === 0) {
      const response = await this.$axios.$get('/provinces')

      commit('SET_PROVINCES', response)
    }
  }
}

export const getters = {
  countries(state) {
    return state.countries
  },

  roles(state) {
    return state.roles
      .map(group => {
        return group.roles
      })
      .reduce((roles, currValue) => {
        return roles.concat(currValue)
      }, [])
  },

  provinces(state) {
    return state.provinces
  },

  platforms(state) {
    return state.platforms
  },

  answerTypes(state) {
    return state.answerTypes
  },

  reportTypes(state) {
    return state.reportTypes
  },

  brandingTypes(state) {
    return state.brandingTypes
  }
}
