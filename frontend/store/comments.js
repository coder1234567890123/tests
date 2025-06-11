import Vue from 'vue'

export const state = () => ({
  comments: []
})

export const mutations = {
  GET_COMMENTS(state, data) {
    state.subjectComments = data
  }
}

export const actions = {
  async getSubjectComments(context, reportId) {
    //return this.$axios.$get('/comment/subject', data)

    const response = await this.$axios.$get('/comment/subject/' + reportId)
    context.commit('GET_COMMENTS', response)
  }
}

export const getters = {
  subjectComment(state) {
    return state.subjectComments
  }
}
