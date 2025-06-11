import Vue from 'vue'

export const state = () => ({
  configurations: [],
  footer_logo: null,
  front_page: null,
  co_front_page: null
})

export const mutations = {
  SET_CONFIGURATION(state, data) {
    state.configurations = data
  },
  SET_FOOTER_LOGO(state, data) {
    let config = data.filter(function(sample) {
      return sample.opt === 'image_footer_logo'
    })
    state.footer_logo = config[0]
  },
  CO_SET_FRONT_PAGE(state, data) {
    let config = data.filter(function(sample) {
      return sample.opt === 'image_co_front_page'
    })
    state.co_front_page = config[0]
  },
  UPDATE_SETTINGS(state, data) {
    let configs = state.configurations.filter(function(sample) {
      return sample.id === data.id
    })
    if (configs.length > 0) {
      let config = configs[0]
      config.val = data.val
    }
  }
}

export const actions = {
  async queryConfig(context) {
    const response = await this.$axios.$get('/systemconfig')
    context.commit('SET_CONFIGURATION', response)
    context.commit('SET_FOOTER_LOGO', response)
    context.commit('SET_FRONT_PAGE', response)
    context.commit('CO_SET_FRONT_PAGE', response)
  },
  updateSettings(context, data) {
    context.commit('UPDATE_SETTINGS', data)
  },
  update(context, data) {
    return this.$axios.$patch('/systemconfig/' + data.id, data)
  },
  upload(context, data) {
    return this.$axios.$post(
      '/systemconfig/systemassets/' + data.id,
      data.uploadFile
    )
  }
}

export const getters = {
  configurations(state) {
    return state.configurations
  },
  footerLogo(state) {
    return state.footer_logo
  },
  frontPage(state) {
    return state.front_page
  },
  coFrontPage(state) {
    return state.co_front_page
  }
}
