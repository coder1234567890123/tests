import Vue from 'vue'

export const state = () => ({
  questions: [],
  question: null,
  sections: [],
  getProof: null,
  proofs: [],
  search: '',
  platform: '',
  pagination: {
    descending: false,
    page: 1,
    rowsPerPage: 10,
    sortBy: 'order_number',
    totalItems: 0,
    rowsPerPageItems: [10, 25, 50, 100]
  }
})

export const mutations = {
  SET_PAGINATION(state, data) {
    state.pagination = data
  },
  SET_SEARCH(state, search) {
    state.search = search
  },
  SET_PLATFORM(state, platform) {
    state.platform = platform
  },
  UPDATE_ANSWER_OPTIONS(state, index) {
    if (index !== undefined) {
      state.question.answer_options.splice(index, 1)
      state.question.answer_score.splice(index, 1)
    } else {
      state.question.answer_options.push('')
      state.question.answer_score.push(0)
    }
  },
  UPDATE_STD_COMMENTS(state, index) {
    if (index !== undefined) {
      state.question.std_comments.splice(index, 1)
    } else {
      state.question.std_comments.push('')
    }
  },
  SET_ANSWER_OPTIONS(state, data) {
    if (undefined !== data.index) {
      state.question.answer_options[data.index] = data.value
    }
  },
  SET_STD_COMMENTS(state, data) {
    if (undefined !== data.index) {
      state.question.std_comments[data.index] = data.value
    }
  },
  SET_QUESTIONS(state, { questions, total }) {
    state.questions = questions
    Vue.set(state.pagination, 'totalItems', total)
  },
  SET_QUESTION(state, question) {
    state.question = question
  },
  GET_PROOF(state, data) {
    state.getProof = data
  },
  SET_PROOF(state, data) {
    state.proofs = data
  },
  UPDATE_PROOF_VALUES(state, data) {
    if (state.proofs.length > 0) {
      state.proofs.filter(function(proof) {
        if (proof.id === data.id) {
          proof[data.prop] = data.value
        }
        return proof
      })
    }
  },
  UPDATE_PROOF_BEHAVIOR(state, data) {
    if (state.proofs.length > 0) {
      state.proofs.filter(function(proof) {
        if (proof.id === data.id) {
          proof.behaviour_scores[data.prop] = data.value
        }
        return proof
      })
    }
  },
  SET_ANSWER_SCORE(state, data) {
    if (undefined !== data.index) {
      state.question.answer_score[data.index] = data.value
    }
  },
  SET_SLIDER_VALUE(state, data) {
    if (undefined !== data.index) {
      state.question.slider_values[data.index] = data.value
    }
  },
  UPDATE_QUESTION(state, data) {
    Vue.set(state.question, data.prop, data.value)
  },
  UPDATE_REPORT_TYPE(state, data) {
    state.question.report_types = data
  },
  RESET_ANSWER_OPTION_SCORE(state, value) {
    state.question.answer_options = []
    state.question.answer_score = []
    if (value === 'yes_no') {
      state.question.answer_options.push('YES')
      state.question.answer_score.push(0)
      state.question.answer_options.push('NO')
      state.question.answer_score.push(0)
    }
  },
  RESET_SLIDER_VALUES(state, value) {
    state.question.slider = value
    if (state.question.slider === true) {
      state.question.slider_values.push(0)
      state.question.slider_values.push(0)
    } else {
      state.question.slider_values = []
    }
  }
}

export const actions = {
  async queryQuestions(context) {
    const { sortBy, descending, page, rowsPerPage } = context.state.pagination
    const search = context.state.search
    const platform = context.state.platform

    const response = await this.$axios.$get('/question', {
      params: {
        limit: rowsPerPage,
        page,
        sort: sortBy,
        descending,
        search,
        platform: platform
      }
    })

    // Update State
    context.commit('SET_QUESTIONS', {
      questions: response._embedded.questions,
      total: response.total
    })
  },
  async getPlatformQuestions(context, platform) {
    const { sortBy, descending, page, rowsPerPage } = context.state.pagination
    // const platform = context.state.search

    const response = await this.$axios.$get('/question', {
      params: {
        limit: rowsPerPage,
        page,
        sort: sortBy,
        descending,
        platform: platform
      }
    })

    // Update State
    context.commit('SET_QUESTIONS', {
      questions: response._embedded.questions,
      total: response.total
    })

    // Update State
    context.commit('SET_PLATFORM', platform)
  },
  async get(context, id) {
    let response = await this.$axios.$get('/question/' + id)
    context.commit('SET_QUESTION', response)
  },
  create(context, data) {
    return this.$axios.$post('/question', data)
  },
  createProof(context, data) {
    return this.$axios.$post('/proof', data)
  },
  updateProof(context, id) {
    let proofs = context.state.proofs.filter(function(proof) {
      return proof.id === id
    })
    if (proofs.length > 0) {
      let data = {
        comment: proofs[0].comment,
        trait: proofs[0].trait,
        behaviour_scores: proofs[0].behaviour_scores
      }
      return this.$axios.$patch('/proof/' + id, data)
    } else {
      return {}
    }
  },
  async getProofByAnswer(context, id) {
    let response = await this.$axios.$get('/proof/answer/' + id)
    context.commit('SET_PROOF', response)
    return response
  },
  async deleteProof(context, id) {
    let response = await this.$axios.$delete('/proofstorage/' + id + '/image')

    return response
  },
  changeValues(context, data) {
    context.commit('UPDATE_PROOF_VALUES', data)
  },
  changeBehavior(context, data) {
    context.commit('UPDATE_PROOF_BEHAVIOR', data)
  },
  update(context, data) {
    return this.$axios.$patch('/question/' + context.state.question.id, data)
  },
  updateQuestion(context, data) {
    context.commit('UPDATE_QUESTION', data)
  },
  updateAnswerOptions(context, index) {
    context.commit('UPDATE_ANSWER_OPTIONS', index)
  },
  updateStdComments(context, index) {
    context.commit('UPDATE_STD_COMMENTS', index)
  },
  setAnswerOption(context, data) {
    context.commit('SET_ANSWER_OPTIONS', data)
  },
  setStdComment(context, data) {
    context.commit('SET_STD_COMMENTS', data)
  },
  setAnswerScore(context, data) {
    context.commit('SET_ANSWER_SCORE', data)
  },
  setSliderValues(context, data) {
    context.commit('SET_SLIDER_VALUE', data)
  },
  answerTypeChange(context, value) {
    context.commit('RESET_ANSWER_OPTION_SCORE', value)
  },
  updateSliderValues(context, value) {
    context.commit('RESET_SLIDER_VALUES', value)
  },
  enable(context, id) {
    return this.$axios.$put('/question/' + id + '/enable')
  },
  disable(context, id) {
    return this.$axios.$delete('/question/' + id)
  },
  async getProofById(context, id) {
    let response = await this.$axios.$get('/proof/' + id)
    console.log(response)
    context.commit('GET_PROOF', response)
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
  platform(state) {
    return state.platform
  },
  questions(state) {
    return state.questions
  },
  question(state) {
    return state.question
  },
  proofs(state) {
    return state.proofs
  },
  proofById(state) {
    return state.getProof
  }
}
