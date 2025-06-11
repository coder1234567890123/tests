import Vue from 'vue'
import _ from 'lodash'

export const state = () => ({
  subjectInfo: [],
  mediaValidated: [],
  riskComment: ''
})

export const mutations = {
  SET_SUBJECT_INFO(state, data) {
    state.subjectInfo = data
  },
  SET_MEDIA_VALIDATED(state, data) {
    state.mediaValidated = data
  },
  SET_RISK_COMMENT(state, data) {
    state.riskComment = data
  }
}

export const actions = {
  async getRiskComment(context, id) {
    let response = await this.$axios.$get('/report/' + id + '/risk-comments')
    context.commit('SET_SUBJECT_INFO', response.subject_info)
    context.commit('SET_MEDIA_VALIDATED', response.media_validated)
    context.commit('SET_RISK_COMMENT', response.risk_comment)
  },
  addRiskCommentUpdate(context, data) {
    return this.$axios.$patch('/report/' + data.id + '/risk-comments', data)
  }
}

export const getters = {
  subjectInfo(state) {
    return state.subjectInfo
  },
  mediaValidated(state) {
    return state.mediaValidated
  },
  riskComment(state) {
    return state.riskComment
  }
}
