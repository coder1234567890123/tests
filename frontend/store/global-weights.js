import Vue from 'vue'

export const state = () => ({
  weights: [],
  uuyu: 100
})

export const mutations = {
  SET_WEIGHTS(state, data) {
    state.weights = data
  },
  GLOBAL_WEIGHT_ADD_NEW_COMMENT(state, id) {
    state.weights.filter(function(elem) {
      if (elem.id == id) {
        elem.std_comments.push('')
      }
    })
  },
  GLOBAL_WEIGHT_REMOVE_COMMENT(state, data) {
    state.weights.filter(function(elem) {
      if (elem.id == data.id) {
        elem.std_comments.splice(data.index, 1)
      }
    })
  },
  GLOBAL_WEIGHT_CHANGE_COMMENT(state, data) {
    state.weights.filter(function(elem) {
      if (elem.id == data.id) {
        elem.std_comments[data.index] = data.value
      }
    })
  },
  UPDATE_GLOBAL_USAGE(state, data) {
    state.weights.filter(function(elem) {
      if (elem.id == data.id) {
        elem[data.prop] = data.value
      }
    })
  }
}

export const actions = {
  async queryConfig(context) {
    const response = await this.$axios.$get('/global-weights')
    context.commit('SET_WEIGHTS', response)
  },
  updateSettings(context, data) {
    context.commit('UPDATE_SETTINGS', data)
  },
  update(context, data) {
    let obj = context.state.weights.filter(function(elem) {
      if (elem.id == data) return elem
    })
    return this.$axios.$patch('/global-weights/' + data, obj[0])
  },
  updateGlobalUsage(context, data) {
    context.commit('UPDATE_GLOBAL_USAGE', data)
  },
  addNewComment(context, id) {
    context.commit('GLOBAL_WEIGHT_ADD_NEW_COMMENT', id)
  },
  removeComment(context, data) {
    context.commit('GLOBAL_WEIGHT_REMOVE_COMMENT', data)
  },
  changeComment(context, data) {
    context.commit('GLOBAL_WEIGHT_CHANGE_COMMENT', data)
  }
}

export const getters = {
  weights(state) {
    return state.weights
  }
}
