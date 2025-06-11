import Vue from 'vue'

export const state = () => ({
  productTypes: [],
  bundles_used: [],
  pagination: {
    descending: true,
    page: 1,
    rowsPerPage: 10,
    company: null,
    sortBy: 'created_at',
    totalItems: 0,
    rowsPerPageItems: [5, 10, 15, 20],
    dateFrom: null,
    dateTo: null
  },
  filters: {
    filter_request: null,
    dateFrom: null,
    dateTo: null
  }
})

export const mutations = {
  SET_PRODUCT_TYPES(state, data) {
    state.productTypes = data
  },
  SET_BUNDLES_USED(state, { bundles, total }) {
    state.bundles_used = bundles
  },
  SET_PAGINATION(state, data) {
    state.pagination = data
  },
  SET_FILTERS(state, data) {
    state.filters = data
  }
}
export const actions = {
  async getProductTypes(context, id) {
    let response = await this.$axios.$get('/companyproduct/' + id)
    context.commit('SET_PRODUCT_TYPES', response)
  },
  async getCompanyUsage(context, company) {
    const { sortBy, descending, page, rowsPerPage } = context.state.pagination
    const response = await this.$axios.$get('/accounts/usage', {
      params: {
        limit: rowsPerPage,
        page,
        company,
        sort: sortBy,
        descending
      }
    })

    context.commit('SET_BUNDLES_USED', {
      bundles: response._embedded.bundles,
      total: response.total
    })
  },
  async queryQueues(context) {
    const { sortBy, descending, page, rowsPerPage } = context.state.pagination
    const { dateFrom, dateTo, company } = context.state.filters

    const response = await this.$axios.$get('/accounts/usage', {
      params: {
        limit: rowsPerPage,
        page,
        company,
        sort: sortBy,
        descending,
        date_from: dateFrom,
        date_to: dateTo
      }
    })

    context.commit('SET_BUNDLES_USED', {
      bundles: response._embedded.bundles,
      total: response.total
    })

    // console.log('bundles')
    // console.log(response._embedded.bundles)
  },
  async queryExportUsage(context, company) {
    const { sortBy, descending, page, rowsPerPage } = context.state.pagination
    const { dateFrom, dateTo } = context.state.filters

    return this.$axios.get('/accounts/usage-export', {
      params: {
        limit: rowsPerPage,
        page,
        company,
        sort: sortBy,
        descending,
        date_from: dateFrom,
        date_to: dateTo
      },
      responseType: 'arraybuffer'
    })
  },
  async updateCompanyProduct(context, data) {
    return await this.$axios.$patch('/companyproduct/' + data.id, data)
  },
  async addCompanyBundle(context, data) {
    return await this.$axios.$post(
      '/companyproduct/add-to-bundle/' + data.id,
      data
    )
  },
  async monthlyBundleReset(context, data) {
    return await this.$axios.$get('/accounts/monthly-reset/' + data.id)
  }
}

export const getters = {
  productTypes(state) {
    return state.productTypes
  },
  pagination(state) {
    return state.pagination
  },
  bundlesUsed(state) {
    return state.bundles_used
  }
}
