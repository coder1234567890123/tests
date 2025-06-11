import Vue from 'vue'
import _ from 'lodash'

export const state = () => ({
  answers: [],
  answer: null,
  questions: [],
  question: null,
  comments: [],
  comment: null,
  search: '',
  pagination: {
    descending: false,
    page: 1,
    rowsPerPage: 10,
    sortBy: 'date_created',
    totalItems: 0,
    rowsPerPageItems: [10, 25, 50, 100]
  },
  currentQuestion: 0,
  generalComment: null,
  reportComment: null
})

export const mutations = {
  SET_REPORT_COMMENT(state, data) {
    state.reportComment = data
  },
  SET_QUESTIONS(state, { questions, total }) {
    state.questions = questions
    Vue.set(state.pagination, 'totalItems', total)
  },
  SET_QUESTION(state, data) {
    state.question = data
  },
  UPDATE_ANSWER(state, { activeQuestion, answer, response }) {
    activeQuestion.answers = [response]
    Vue.set(
      state.questions,
      state.questions.findIndex(question => question.id === answer.question.id),
      activeQuestion
    )
  },
  UPDATE_GENERAL_COMMENTS(state, { response }) {
    state.generalComment = response
  },
  CHANGE_CURRENT_QUESTION(state, data) {
    state.currentQuestion = data
  },
  SET_GENERAL_COMMENT(state, data) {
    state.generalComment = data
  }
}

export const actions = {
  async queryQuestions(context) {
    const response = await this.$axios.$get(
      '/question/investigate/' + this.$router.currentRoute.params.id
    )
    context.commit('SET_QUESTIONS', {
      questions: response.questions
    })

    if (response.generalComment) {
      context.commit('SET_GENERAL_COMMENT', response.generalComment[0])
    }

    // Sets next unanswered question as active
    let activeQuestion = 0
    _.each(response.questions, function(value, key) {
      if (
        value.answers.length === 0 ||
        (value.answers.length > 0 && value.answers[0].answer.trim().length == 0)
      ) {
        activeQuestion = key
        return false
      }
    })
    context.commit('CHANGE_CURRENT_QUESTION', activeQuestion)

    return response.questions
  },
  async updateAnswer(context, { activeQuestion, answer }) {
    let response = await this.$axios.$post('/answer', answer)

    if (activeQuestion) {
      context.commit('UPDATE_ANSWER', { activeQuestion, answer, response })
    } else {
      context.commit('UPDATE_GENERAL_COMMENTS', { answer, response })
    }

    return response
  },
  getStatus(context, id) {
    return this.$axios.$get(
      '/report/subject/' + id + '/status?status=completed'
    )
  },
  async reportComment(context, id) {
    let response = await this.$axios.$get('/question/investigate/' + id)
    context.commit('SET_REPORT_COMMENT', response.report_question)
  }
}

export const getters = {
  reportComment(state) {
    return state.reportComment
  },
  loading(state) {
    return state.loading
  },
  questions(state) {
    return state.questions
  },
  question(state) {
    return state.question
  },
  answers(state) {
    return state.answers
  },
  answer(state) {
    return state.answer
  },
  comment(state) {
    return state.comment
  },
  investigateDialog(state) {
    return state.investigateDialog
  },
  currentQuestion(state) {
    return state.currentQuestion
  },
  generalComment(state) {
    return state.generalComment
  }
}
