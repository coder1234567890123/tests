export const state = () => ({
  strict: true,
  loading: false,
  queues: null,
  reports: null,
  auditLog: null,
  messages: null,
  accounts: null,
  monthlyRequests: null
})

export const mutations = {
  SET_QUEUES(state, data) {
    state.queues = data.queues
  },
  SET_REPORTS(state, data) {
    state.reports = data.reports
  },
  SET_AUDIT_LOG(state, data) {
    state.auditLog = data.audit_log
  },
  SET_MESSAGES(state, data) {
    state.messages = data.messages
  },
  SET_ACCOUNTS(state, data) {
    state.accounts = data.accounts
  },
  SET_MONTHLY_REQUESTS(state, data) {
    state.monthlyRequests = data.monthly_request
  }
}

export const actions = {
  async queryDashboard(context) {
    const response = await this.$axios.$get('/reporting/dashboard')

    context.commit('SET_QUEUES', response)
    context.commit('SET_REPORTS', response)
    context.commit('SET_AUDIT_LOG', response)
    context.commit('SET_MONTHLY_REQUESTS', response)
    context.commit('SET_MESSAGES', response)
    context.commit('SET_ACCOUNTS', response)
  },
  async messageView(context, id) {
    return this.$axios.$post('/message_system/viewed/' + id)
  }
}

export const getters = {
  queues(state) {
    return state.queues
  },
  reports(state) {
    return state.reports
  },
  auditLog(state) {
    return state.auditLog
  },
  messages(state) {
    return state.messages
  },
  accounts(state) {
    return state.accounts
  },
  monthlyRequests(state) {
    return state.monthlyRequests
  }
}
