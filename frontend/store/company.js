import Vue from 'vue'

export const state = () => ({
  strict: true,
  company: null,
  companies: [],
  loading: false,
  search: '',
  pagination: {
    descending: true,
    page: 1,
    rowsPerPage: 10,
    sortBy: 'name',
    totalItems: 0,
    rowsPerPageItems: [10, 25, 50, 100]
  }
})

export const mutations = {
  SET_PAGINATION(state, data) {
    state.pagination = data
  },
  SET_ALL_COMPANIES(state, { companies, total }) {
    state.companies = companies
    Vue.set(state.pagination, 'totalItems', total)
  },
  SET_COMPANIES(state, { companies, total }) {
    state.companies = companies
    Vue.set(state.pagination, 'totalItems', total)
  },
  SET_SEARCH(state, search) {
    state.search = search
  },
  SET_COMPANY(state, company) {
    state.company = company
  },
  UPDATE_COMPANY(state, data) {
    Vue.set(state.company, data.prop, data.value)
  }
}

export const actions = {
  async queryAllCompanies(context) {
    let allCompanies = []
    let total = 0
    if (
      this.$auth.hasScope('ROLE_SUPER_ADMIN') ||
      this.$auth.hasScope('ROLE_TEAM_LEAD') ||
      this.$auth.hasScope('ROLE_ANALYST')
    ) {
      const { sortBy, descending, page, rowsPerPage } = context.state.pagination
      const search = context.state.search

      const response = await this.$axios.$get('/companies', {
        params: {
          limit: 100,
          page,
          sort: sortBy,
          descending: false,
          search
        }
      })

      allCompanies = response._embedded.companies
      total = response.total
    }

    // Update State
    context.commit('SET_ALL_COMPANIES', {
      companies: allCompanies,
      total: total
    })
  },
  async queryCompanies(context) {
    let companies = []
    let total = 0
    if (
      this.$auth.hasScope('ROLE_SUPER_ADMIN') ||
      this.$auth.hasScope('ROLE_TEAM_LEAD') ||
      this.$auth.hasScope('ROLE_ANALYST')
    ) {
      const { sortBy, descending, page, rowsPerPage } = context.state.pagination
      const search = context.state.search

      const response = await this.$axios.$get('/companies', {
        params: {
          limit: rowsPerPage,
          page,
          sort: sortBy,
          descending,
          search
        }
      })

      companies = response._embedded.companies
      total = response.total
    }

    // Update State
    context.commit('SET_COMPANIES', {
      companies: companies,
      total: total
    })
  },
  async queryTeams(context) {
    return await this.$axios.$get('/team')
  },
  assignTeam(context, { company, team }) {
    return this.$axios.$patch('/companies/' + company, {
      team: { id: team }
    })
  },
  create(context, data) {
    return this.$axios.$post('/companies', data)
  },
  update(context, data) {
    return this.$axios.$patch(
      '/companies/' + this.state.company.company.id,
      data
    )
  },
  updateCompany(context, data) {
    context.commit('UPDATE_COMPANY', data)
  },
  async get(context, id) {
    if (context.state.company === null || context.state.company.id !== id) {
      let response = await this.$axios.$get('/companies/' + id)
      context.commit('SET_COMPANY', response)
    }
  },
  async getCurrentCompany(context) {
    let response = await this.$axios.$get('/companies/current-company')
    context.commit('SET_COMPANY', response)
  },
  async getCompany(context, id) {
    let response = await this.$axios.$get('/companies/' + id)
    context.commit('SET_COMPANY', response)
  },
  uploadFooterLogo(context, data) {
    return this.$axios.$post(
      '/companies/' + this.state.company.company.id + '/imagefooterlogo',
      data
    )
  },
  uploadFrontPage(context, data) {
    return this.$axios.$post(
      '/companies/' + this.state.company.company.id + '/imagefrontpage',
      data
    )
  },
  uploadFrontLogoPage(context, data) {
    return this.$axios.$post(
      '/companies/' + this.state.company.company.id + '/imagefrontlogo',
      data
    )
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
  companies(state) {
    return state.companies
  },
  company(state) {
    return state.company
  }
}
