export const state = () => ({
  calculations: []
})

export const mutations = {
  SET_CALCULATIONS(state, data) {
    state.queues = data.queues
  }
}

export const actions = {
  async getCalculations(context, id) {
    const response = await this.$axios.$get('/calculation/' + id)

    context.commit('SET_CALCULATIONS', response)

    return response
  }
}

export const getters = {
  calculations(state) {
    return state.calculations
  }
}
