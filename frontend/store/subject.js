import Vue from 'vue'

export const state = () => ({
  strict: true,
  subject: null,
  subjects: [],
  messageQueue: [],
  loading: false,
  search: '',
  pagination: {
    descending: true,
    page: 1,
    rowsPerPage: 10,
    sortBy: 'created_at',
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
  SET_SUBJECTS(state, { subjects, total }) {
    state.subjects = subjects
    Vue.set(state.pagination, 'totalItems', total)
  },
  SET_SUBJECT(state, subject) {
    state.subject = subject
  },
  SET_MESSAGE_QUEUE(state, subject) {
    state.messageQueue = subject
  },
  UPDATE_SUBJECT_REPORT_COMMENT_PRIVATE(state, data) {
    if (state.subject.reports) {
      state.subject.current_report.comments.filter(function(comment) {
        if (comment.id === data.id) {
          comment.comment = data.value
        }
        return comment
      })
    }
    // Vue.set(state.subject, data.prop, data.value)
  },
  UPDATE_SUBJECT_REPORT_COMMENT(state, data) {
    if (state.subject.reports) {
      state.subject.current_report.comments.filter(function(comment) {
        if (comment.id === data.id) {
          comment.comment = data.value
          comment.private = data.prvt
        }
        return comment
      })
    }
  },
  UPDATE_REPORT_COMMENTS(state, comment) {
    if (state.subject.current_report && state.subject.current_report.comments) {
      state.subject.current_report.comments.push(comment)
    } else {
      state.subject.current_report.comments = []
      state.subject.current_report.comments.push(comment)
    }
  },
  UPDATE_REPORT_RISK_COMMENT(state, comment) {
    state.subject.current_report.risk_comment = comment
  },
  REMOVE_REPORT_COMMENT(state, id) {
    if (state.subject.current_report && state.subject.current_report.comments) {
      state.subject.current_report.comments = state.subject.current_report.comments.filter(
        function(comment) {
          return comment.id !== id
        }
      )
    }
  },
  UPDATE_SUBJECT(state, data) {
    Vue.set(state.subject, data.prop, data.value)
  },
  UPDATE_HANDLES(state, index) {
    if (undefined !== index) {
      state.subject.handles.splice(index, 1)
    } else {
      state.subject.handles.push('')
    }
  },
  SET_HANDLES(state, data) {
    if (undefined !== data.index) {
      state.subject.handles[data.index] = data.value
    }
  },
  UPDATE_ADDRESS(state, data) {
    Vue.set(state.subject.address, data.prop, data.value)
  },
  UPDATE_COMPANY(state, data) {
    if (undefined !== state.subject.company) {
      Vue.set(state.subject.company, data.prop, data.value)
    } else {
      state.subject = { ...state.subject, company: { id: data.value } }
    }
  }
}

export const actions = {
  async querySubjects(context) {
    const { sortBy, descending, page, rowsPerPage } = context.state.pagination
    const search = context.state.search

    const response = await this.$axios.$get('/subject', {
      params: {
        limit: rowsPerPage,
        page,
        sort: sortBy,
        descending,
        search
      }
    })

    // Update State
    context.commit('SET_SUBJECTS', {
      subjects: response._embedded.subjects,
      total: response.total
    })
  },
  create(context, data) {
    return this.$axios.$post('/subject', data)
  },
  update(context, data) {
    let subjectData = JSON.parse(JSON.stringify(data))
    subjectData.company = { id: subjectData.company.id }
    return this.$axios.$patch(
      '/subject/' + context.state.subject.id,
      subjectData
    )
  },
  updateSubject(context, data) {
    context.commit('UPDATE_SUBJECT', data)
  },
  updateSubjectReportComment(context, data) {
    context.commit('UPDATE_SUBJECT_REPORT_COMMENT', data)
  },
  updateSubjectReportCommentPrivate(context, data) {
    context.commit('UPDATE_SUBJECT_REPORT_COMMENT_PRIVATE', data)
  },
  updateHandles(context, index) {
    context.commit('UPDATE_HANDLES', index)
  },
  setHandles(context, data) {
    context.commit('SET_HANDLES', data)
  },
  updateAddress(context, data) {
    context.commit('UPDATE_ADDRESS', data)
  },
  updateCompany(context, data) {
    context.commit('UPDATE_COMPANY', data)
  },
  async get(context, id) {
    if (context.state.subject === null || context.state.subject.id !== id) {
      let response = await this.$axios.$get('/subject/' + id)
      context.commit('SET_SUBJECT', response)
    }
  },
  async messageQueue(context, id) {
    let response = await this.$axios.$get('/subject/message_bus/' + id)
    return response
  },
  async messageQueueSubject(context, id) {
    let response = await this.$axios.$get('/subject/' + id)
    return response
  },
  async getAll(context, id) {
    let response = await this.$axios.$get('/subject/' + id)
    context.commit('SET_SUBJECT', response)
  },
  async getById(context, id) {
    let response = await this.$axios.$get('/subject/' + id)

    return response
    //context.commit('SET_SUBJECT', response)
  },
  async refresh(context, id) {
    await this.$axios.$get('/subject/' + id + '/refresh')
  },
  async overwriteMessage(context, id) {
    let response = await this.$axios.$get(
      '/subject/message_bus/overwrite/' + id
    )
  },
  uploadImage(context, data) {
    return this.$axios.$post(
      '/subject/' + context.state.subject.id + '/image',
      data
    )
  },
  async addRiskComment(context, data) {
    let resp = await this.$axios.$patch('/report/' + data.id, data)
    context.commit('UPDATE_REPORT_RISK_COMMENT', resp.risk_comment)
    return resp
  },
  saveQualification(context, data) {
    return this.$axios.$post(
      '/subject/' + data.subject + '/qualification',
      data
    )
  },
  updateQualification(context, data) {
    return this.$axios.$patch(
      '/subject/' + data.subject + '/qualification/' + data.qualification.id,
      data
    )
  },
  deleteQualification(context, data) {
    return this.$axios.$delete(
      '/subject/' + data.subject.id + '/qualification/' + data.qualification.id
    )
  },
  saveEmployment(context, data) {
    if (data.employment.id == '') {
      return this.$axios.$post(
        '/subject/' + data.subject + '/employment',
        data.employment
      )
    } else {
      let employment = data.employment
      employment.country = employment.country.id
      return this.$axios.$patch(
        '/subject/' + data.subject + '/employment/' + data.employment.id,
        data.employment
      )
    }
  },
  deleteEmployment(context, data) {
    return this.$axios.$delete(
      '/subject/' + data.subject.id + '/employment/' + data.employment.id
    )
  },
  async createPlatformComment(context, data) {
    let response = await this.$axios.$post('/comment', data)
    context.commit('UPDATE_REPORT_COMMENTS', response)
    return response
  },
  deletePlatformComment(context, id) {
    let response = this.$axios.$delete('/comment/' + id)
    context.commit('REMOVE_REPORT_COMMENT', id)
    return response
  },
  async searchFilter(context, data) {
    const { sortBy, descending, page, rowsPerPage } = context.state.pagination
    const search = context.state.search

    const response = await this.$axios.$get('/subject', {
      params: {
        limit: rowsPerPage,
        page,
        sort: sortBy,
        descending,
        search_first_name: data.search_first_name,
        search_last_name: data.search_last_name,
        search_email: data.search_email
      }
    })

    // Update State
    context.commit('SET_SUBJECTS', {
      subjects: response._embedded.subjects,
      total: response.total
    })
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
  subjects(state) {
    return state.subjects
  },
  subject(state) {
    return state.subject
  },
  messageQueue(state) {
    return state.messageQueue
  },
  academicHistory(state) {
    if (state.subject !== null) {
      return state.subject.qualifications
    }

    return []
  },
  employmentHistory(state) {
    if (state.subject !== null) {
      return state.subject.employments
    }

    return []
  },
  facebookProfiles(state) {
    return validated => {
      return state.subject && state.subject.facebook_profiles
        ? getPlatformProfiles(state.subject.facebook_profiles, validated)
        : []
    }
  },
  instagramProfiles(state) {
    return validated => {
      return state.subject && state.subject.instagram_profiles
        ? getPlatformProfiles(state.subject.instagram_profiles, validated)
        : []
    }
  },
  twitterProfiles(state) {
    return validated => {
      return state.subject && state.subject.twitter_profiles
        ? getPlatformProfiles(state.subject.twitter_profiles, validated)
        : []
    }
  },
  linkedinProfiles(state) {
    return validated => {
      return state.subject && state.subject.linkedin_profiles
        ? getPlatformProfiles(state.subject.linkedin_profiles, validated)
        : []
    }
  },
  pinterestProfiles(state) {
    return validated => {
      return state.subject && state.subject.pinterest_profiles
        ? getPlatformProfiles(state.subject.pinterest_profiles, validated)
        : []
    }
  },
  flickrProfiles(state) {
    return validated => {
      return state.subject && state.subject.flickr_profiles
        ? getPlatformProfiles(state.subject.flickr_profiles, validated)
        : []
    }
  },
  youtubeProfiles(state) {
    return validated => {
      return state.subject && state.subject.youtube_profiles
        ? getPlatformProfiles(state.subject.youtube_profiles, validated)
        : []
    }
  },
  webSearchProfiles(state) {
    return validated => {
      return state.subject && state.subject.web_search_profiles
        ? getPlatformProfiles(state.subject.web_search_profiles, validated)
        : []
    }
  }
}

export function getPlatformProfiles(platform, validated = false) {
  let profiles = Object.keys(platform).map(key => {
    return platform[key]
  })

  if (validated === true) {
    return profiles.filter(profile => {
      return profile.valid
    })
  }

  return profiles
}
