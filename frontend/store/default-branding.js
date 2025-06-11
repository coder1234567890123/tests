import Vue from 'vue'

export const state = () => ({
  getDefaultBranding: [],
  getDefaultBrandingState: ''
})

export const mutations = {
  SET_DEFAULT_BRANDING(state, data) {
    state.getDefaultBranding = data
  },
  UPDATE_DISCLAIMER(state, data) {
    state.getDefaultBrandingState = data.value
  }
}

export const actions = {
  updateDisclaimer(context, data) {
    context.commit('UPDATE_DISCLAIMER', data)
  },
  async getDefaultBranding(context, data) {
    let response = await this.$axios.$get('/default-branding')
    context.commit('SET_DEFAULT_BRANDING', response)
  },
  async updateDefaultBranding(context, data) {
    return await this.$axios.$patch('/default-branding/' + data.id, data)
  },
  async uploadFrontPage(context, data) {
    return this.$axios.$post('/default-branding/images', data)
  },
  async uploadCoFrontPage(context, data) {
    return this.$axios.$post('/default-branding/images', data)
  },
  async uploadFrontLogo(context, data) {
    return this.$axios.$post('/default-branding/images', data)
  },
  async update(context, data) {
    console.log(context.state.getDefaultBrandingState)

    let dataUpdate = {
      disclaimer: context.state.getDefaultBrandingState
    }

    return await this.$axios.$patch('/default-branding/' + data.id, dataUpdate)
  }
}

export const getters = {
  getBranding(state) {
    return state.getDefaultBranding
  }
}
