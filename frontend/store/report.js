import Vue from 'vue'
import _ from 'lodash'

export const state = () => ({
  questions: [],
  reports: [],
  duplicateDetails: [],
  reportInfo: [],
  report: null,
  updateScores: [],
  search: '',
  queues: [],
  reportScoreUpdated: [],
  reportScoreOverridden: [],
  generalComment: null,
  reportScore: [],
  filter_request: null,
  pagination: {
    descending: true,
    page: 1,
    rowsPerPage: 10,
    sortBy: 'created_at',
    id: '',
    totalItems: 0,
    rowsPerPageItems: [5, 10, 15, 20]
  },
  filters: {
    filter_request: null,
    dateFrom: null,
    dateTo: null,
    company: null,
    type: null,
    status: null
  }
})

export const mutations = {
  SET_QUESTIONS(state, data) {
    state.questions = data
  },
  SET_DUPLICATE_DETAILS(state, data) {
    state.duplicateDetails = data
  },
  SET_SEARCH(state, search) {
    state.search = search
  },
  SET_GENERAL_COMMENT(state, data) {
    state.generalComment = data
  },
  SET_REPORT_SCORE(state, data) {
    state.reportScore = data
  },
  SET_REPORT_SCORE_UPDATED(state, data) {
    state.reportScoreUpdated = data
  },
  SET_REPORT_SCORE_OVERRIDDEN(state, data) {
    state.reportScoreOverridden = data
  },
  SET_PAGINATION(state, data) {
    state.pagination = data
  },
  SET_FILTERS(state, data) {
    state.filters = data
  },
  SET_REPORT_DETAILS(state, data) {
    state.report = data
  },
  SET_REPORTS(state, { reports, total }) {
    state.reports = reports
    Vue.set(state.pagination, 'totalItems', total)
  },
  TOGGLE_GENERAL_COMMENTS(state, data) {
    Vue.set(state.report, 'hide_general_comments', data)
  },
  TOGGLE_REPORT_SCORE(state, data) {
    Vue.set(state.report, 'hide_report_score', data)
  },
  SET_QUEUES(state, { queues, total }) {
    state.queues = queues
    Vue.set(state.pagination, 'totalItems', total)
  },
  SET_REPORT_INFO(state, data) {
    state.reportInfo = data
  },
  UPDATE_OVER_WRITTEN_SCORES(state, data) {
    let updateReports = (state.reportScoreUpdated['platforms'][
      data.dataInfo.platform
    ][data.dataInfo.scoreType] = data.dataInfo.score)

    Vue.set(state.reportScoreUpdated, updateReports)
  },
  UPDATE_OVER_WRITTEN_SCORES_MAIN(state, data) {
    Vue.set(state.reportScoreUpdated, data.prop, data.value)
  },
  UPDATE_OVER_WRITTEN_BEHAVIOR_SCORES(state, data) {
    let updateReports = (state.reportScoreUpdated.overall_behavior_scores[
      data.dataInfo.scoreType
    ] = data.dataInfo.score)

    Vue.set(state.reportScoreUpdated, updateReports)
  }
}

export const actions = {
  async resetPageToOne(context) {
    let data = {
      descending: true,
      page: 1,
      rowsPerPage: 10,
      sortBy: 'created_at',
      id: '',
      totalItems: 0,
      rowsPerPageItems: [5, 10, 15, 20]
    }

    context.commit('SET_PAGINATION', data)
  },
  async queryReport(context, reportId) {
    let url = `/report/subject/${this.$router.currentRoute.params.id}${
      reportId !== null ? '?report=' + reportId : ''
    }`

    const response = await this.$axios.$get(url)
    context.commit('SET_QUESTIONS', {
      questions: response.questions
    })

    context.commit('SET_REPORT_DETAILS', response.details)
    context.commit('SET_GENERAL_COMMENT', response.generalComment)
    context.commit('SET_REPORT_SCORE', response.reportScore)
  },

  async getScore(context, reportId) {
    const response = await this.$axios.$get('/report/subject/' + reportId)
    context.commit('SET_REPORT_SCORE', response.reportScore)
  },

  async buildMaths(context, reportId) {
    // const status = true
    // const response = null
    // while (status) {
    //   response = await this.$axios.$get(
    //     '/report/subject/' + reportId + '/build-math'
    //   )
    //   console.log(response.status)
    //   if (response.status == 200) {
    //     status = false
    //   }
    // }
    const response = await this.$axios.$get(
      '/report/subject/' + reportId + '/build-math'
    )

    console.log(response)
    console.log(response.status)

    return response
    // context.commit('SET_REPORT_SCORE_OVERRIDDEN', response.reportScore)
  },

  async getScoreUpdated(context, subjectId) {
    const response = await this.$axios.$get(
      '/report/subject/' + subjectId + '/get-edit-report-scores'
    )

    context.commit('SET_REPORT_SCORE_UPDATED', response)
  },

  async queryQueues(context) {
    const { sortBy, descending, page, rowsPerPage } = context.state.pagination
    const {
      dateFrom,
      dateTo,
      company,
      type,
      status,
      filter_request
    } = context.state.filters

    const response = await this.$axios.$get('/report', {
      params: {
        filter_request: filter_request,
        limit: rowsPerPage,
        page,
        sort: sortBy,
        descending,
        date_from: dateFrom,
        date_to: dateTo,
        company,
        type,
        status
      }
    })

    context.commit('SET_QUEUES', {
      queues: response._embedded.report_queues,
      total: response.total
    })
  },
  async queryExportQueues(context) {
    const { sortBy, descending, page, rowsPerPage } = context.state.pagination
    const {
      dateFrom,
      dateTo,
      company,
      type,
      status,
      filter_request
    } = context.state.filters

    return this.$axios.get('/reporting/export', {
      params: {
        filter_request: filter_request,
        limit: rowsPerPage,
        page,
        sort: sortBy,
        descending,
        date_from: dateFrom,
        date_to: dateTo,
        company,
        type,
        status
      },
      responseType: 'arraybuffer'
    })
  },
  async queryReports(context, idx) {
    const { sortBy, descending, page, rowsPerPage } = context.state.pagination
    //const id = idx ? idx : context.state.search
    const search = context.state.search

    const response = await this.$axios.$get('/report/subject/reports', {
      params: {
        limit: rowsPerPage,
        page,
        sort: sortBy,
        descending,
        search,
        type: 'all'
      }
    })

    // Update State
    context.commit('SET_REPORTS', {
      reports: response._embedded.reports,
      total: response.total
    })
  },
  async queryUsers(context, subjectId) {
    const response = await this.$axios.$get('/team/subject/' + subjectId)

    return response.analysts
  },
  async getSubjectInfo(context, id) {
    let response = await this.$axios.$get('/report/' + id + '/information')
    context.commit('SET_DUPLICATE_DETAILS', response)
    return response
  },
  async duplicate(context, data) {
    let response = await this.$axios.$get(
      '/report/' + data.subject + '/duplicate/' + data.report
    )
    return response
  },
  async duplicateWithSearch(context, data) {
    let response = await this.$axios.$get(
      '/report/' + data.subject + '/duplicate_search/' + data.report
    )
    return response
  },
  async getReportInfo(context, subjectId) {
    const response = await this.$axios.$get(
      '/report/subject/' + subjectId + '/web'
    )
    context.commit('SET_REPORT_INFO', response)
  },
  open(context, data) {
    return this.$axios.$put('/report/open?id=' + data)
  },
  closed(context, data) {
    return this.$axios.$put('/report/' + data + '/close')
  },
  deleteReport(context, id) {
    return this.$axios.$delete('/report/' + id)
  },
  showComment(context, id) {
    return this.$axios.$patch('/comment/' + id + '/show')
  },
  hideComment(context, id) {
    return this.$axios.$patch('/comment/' + id + '/hide')
  },
  toggleGeneralComments(context, id) {
    return this.$axios.$patch('/report/' + id + '/toggleGeneralComments')
  },
  toggleReportScore(context, id) {
    return this.$axios.$patch('/report/' + id + '/toggleReportScore')
  },
  getStatus(context, id) {
    return this.$axios.$get(
      '/report/subject/' + id + '/status?status=under_investigation'
    )
  },
  setReportComplete(context, id) {
    return this.$axios.$get('/report/' + id + '/status?status=complete')
  },
  abandonedRequestInvestigation(context, id) {
    return this.$axios.$get(
      '/report/' + id + '/status?status=abandoned_request'
    )
  },
  abandonedInvestigation(context, id) {
    return this.$axios.$get('/report/' + id + '/abandoned')
  },
  abandonedInvestigationRejected(context, id) {
    return this.$axios.$get('/report/' + id + '/cancel-abandoned')
  },
  completeInvestigation(context, id) {
    return this.$axios.$get(
      '/report/subject/' + id + '/status?status=investigation_completed'
    )
  },
  pdf(context, { id, pass }) {
    let url = '/report/subject/' + id + '/pdf'
    if (pass !== '') {
      url += '?p=' + pass
    }
    return this.$axios.$get(url, {
      responseType: 'blob' // had to add this one here
    })
  },
  standardPdf(context, { id, pass }) {
    let url = '/report/subject/' + id + '/pdf-standard'
    if (pass !== '') {
      url += '?p=' + pass
    }
    return this.$axios.$get(url, {
      responseType: 'blob' // had to add this one here
    })
  },
  pdfRebuild(context, { id, pass }) {
    let url = '/report/subject/' + id + '/pdf-rebuild'

    return this.$axios.$get(url, {
      responseType: 'blob' // had to add this one here
    })
  },
  reportPdf(context, data) {
    let url = '/report/' + data.subjectId + '/pdf?report=' + data.id
    if (data.pass !== '') {
      url += '&p=' + data.pass
    }
    return this.$axios.$get(url, {
      responseType: 'blob' // had to add this one here
    })
  },
  createComment(context, payload) {
    return this.$axios.$post('/comment', payload)
  },
  editComment(context, payload) {
    return this.$axios.$patch('/comment/' + payload.id, {
      comment: payload.comment,
      private: payload.private || 0
    })
  },
  editCommentPrivate(context, payload) {
    return this.$axios.$patch('/comment/' + payload.id, {
      private: payload.private || 0
    })
  },
  deleteComment(context, id) {
    return this.$axios.$delete('/comment/' + id)
  },
  newRequest(context, { id, requestType }) {
    return this.$axios.$post('/report/subject/' + id + '/request', {
      request_type: requestType
    })
  },
  newInvestigation(context, { id, requestType }) {
    return this.$axios.$post('/report/subject/' + id + '/new_invest', {
      request_type: requestType
    })
  },
  startSearch(context, id) {
    return this.$axios.$get(
      '/report/subject/' + id + '/status?status=search_started'
    )
  },
  assignReport(context, { reportId, assignedTo }) {
    return this.$axios.$patch('/report/' + reportId, {
      assigned_to: { id: assignedTo }
    })
  },
  approveReport(context, { reportId, approved, comment }) {
    let payload = comment ? { comment, comment_type: 'approval' } : null

    return this.$axios.$post(
      '/report/queue/' + reportId + '/approve?approved=' + approved,
      payload
    )
  },
  approveFinalReport(context, { reportId, approved, comment }) {
    let payload = comment ? { comment, comment_type: 'approval' } : null

    return this.$axios.$post(
      '/report/queue/' + reportId + '/approve?approved=' + completed,
      payload
    )
  },
  updateOverWrittenScores(context, data) {
    context.commit('UPDATE_OVER_WRITTEN_SCORES', data)
  },
  updateOverWrittenScoresMain(context, data) {
    context.commit('UPDATE_OVER_WRITTEN_SCORES_MAIN', data)
  },
  updateOverWrittenBehaviorScores(context, data) {
    context.commit('UPDATE_OVER_WRITTEN_BEHAVIOR_SCORES', data)
  },
  updateWrittenScores(context, data) {
    let sendData = {
      over_write_report_scores:
        context.state.reportScoreUpdated.over_write_report_scores,
      pdf_filename: '',
      report_scores_updated: {
        platforms: context.state.reportScoreOverridden.platforms,
        overall_behavior_scores:
          context.state.reportScoreOverridden.overall_behavior_scores,
        risk_score: context.state.reportScoreOverridden.risk_score,
        weighted_social_media_score:
          context.state.reportScoreOverridden.weighted_social_media_score
      }
    }

    return this.$axios.$patch(
      '/report/subject/' + data + '/update-edit-report-scores',
      sendData
    )
  },
  async overRideScoresTest(context, data) {
    let sendData = {
      over_write_report_scores:
        context.state.reportScoreUpdated.over_write_report_scores,
      pdf_filename: '',
      report_scores_updated: {
        platforms: context.state.reportScoreUpdated.platforms,
        overall_behavior_scores:
          context.state.reportScoreUpdated.overall_behavior_scores,
        risk_score: context.state.reportScoreUpdated.risk_score,
        weighted_social_media_score:
          context.state.reportScoreUpdated.weighted_social_media_score
      }
    }

    const response = await this.$axios.$post(
      '/report/subject/' + data + '/change-math',
      sendData
    )

    context.commit('SET_REPORT_SCORE_OVERRIDDEN', response)
  }
}

export const getters = {
  questions(state) {
    return state.questions
  },
  report(state) {
    return state.report
  },
  reports(state) {
    return state.reports
  },
  generalComment(state) {
    return state.generalComment
  },
  reportScore(state) {
    return state.reportScore
  },
  reportScoreUpdated(state) {
    return state.reportScoreUpdated
  },
  reportScoreOverridden(state) {
    return state.reportScoreOverridden
  },
  pagination(state) {
    return state.pagination
  },
  filters(state) {
    return state.filters
  },
  queues(state) {
    return state.queues
  },
  duplicateDetails(state) {
    return state.duplicateDetails
  },
  reportInfo(state) {
    return state.reportInfo
  },
  search(state) {
    return state.search
  }
}
