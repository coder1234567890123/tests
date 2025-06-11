<template>
  <div class="text-center">
    <v-toolbar
      grey
      color="grey lighten-5">
      <v-btn
        v-if="saveButton == false"
        class="float-right"
        disabled
        @click="update()">
        <v-progress-circular
          indeterminate
          color="primary"
        />
        Saving
      </v-btn>
      <v-btn
        v-if="saveButton == true"
        @click="update">Save
      </v-btn>
      <span
        class="putInline"
        style="float:right !important;"/>
    </v-toolbar>
    <v-card>
      <v-layout>
        <v-flex m4>
          <v-card>
            <div class="container">
              <h1 class="title mb-3"> {{ productTypes.company.name }}</h1>
              <h2 class="title mb-3"> Current Package: {{ getFormattedText(productTypes.product_type) }}</h2>
              <v-layout>
                <v-flex m2>
                  <v-select
                    v-validate="'required'"
                    :items="productTypeDropdown"
                    class="selectProductType"
                    item-text="name"
                    item-value="productType"
                    label="Product Type"
                    @change="changeProduct($event)"
                  />
                </v-flex>
                <v-flex m10/>
              </v-layout>
              <div v-if="productTypes.product_type == 'pre_paid'">
                <v-layout>
                  <v-flex
                    m2
                    class="padding5">
                    <v-card
                      :style="'border-left: 6px solid '">
                      <v-list class="pa-0">
                        <v-list-tile
                          avatar
                          class="pa-4">
                          <v-list-tile-content>
                            <v-list-tile-sub-title class="pb-2"/>
                            Bundle Total Used
                            <v-list-tile-title
                              class="display-1"
                              style="height: inherit"/>
                            <h2>{{ productTypes.bundle_total_used }}</h2>
                          </v-list-tile-content>
                          <v-list-tile-avatar>
                            <v-icon
                              class="grey--text text--lighten-2"
                              style="font-size: 36px"/>
                          </v-list-tile-avatar>
                        </v-list-tile>
                      </v-list>
                    </v-card>
                  </v-flex>
                  <v-flex
                    m2
                    class="padding5">
                    <v-card
                      :style="'border-left: 6px solid '">
                      <v-list class="pa-0">
                        <v-list-tile
                          avatar
                          class="pa-4">
                          <v-list-tile-content>
                            <v-list-tile-sub-title class="pb-2"/>
                            Bundle remaining
                            <v-list-tile-title
                              class="display-1"
                              style="height: inherit"/>
                            <h2>{{ productTypes.bundle_remaining }}</h2>
                          </v-list-tile-content>
                          <v-list-tile-avatar>
                            <v-icon
                              class="grey--text text--lighten-2"
                              style="font-size: 36px"/>
                          </v-list-tile-avatar>
                        </v-list-tile>
                      </v-list>
                    </v-card>
                  </v-flex>
                  <v-flex
                    m2
                    class="padding5">
                    <v-card
                      :style="'border-left: 6px solid '">
                      <v-list class="pa-0">
                        <v-list-tile
                          avatar
                          class="pa-4">
                          <v-list-tile-content>
                            <v-list-tile-sub-title class="pb-2"/>
                            Bundle Added
                            <v-list-tile-title
                              class="display-1"
                              style="height: inherit"/>
                            <h2>{{ productTypes.bundle_add }}</h2>
                          </v-list-tile-content>
                          <v-list-tile-avatar>
                            <v-icon
                              class="grey--text text--lighten-2"
                              style="font-size: 36px"/>
                          </v-list-tile-avatar>
                        </v-list-tile>
                      </v-list>
                    </v-card>
                  </v-flex>
                </v-layout>
                <v-layout
                  row
                  wrap>
                  <div class="container">
                    <v-layout
                      row
                      wrap>
                      <v-flex md12>
                        <v-card>
                          <v-layout class="padding5">
                            <v-flex
                              m4
                              class="padding5">
                              <h2>Monthly Setup</h2>
                              <hr>
                              <br>
                              <v-text-field
                                v-model="bundleAmount"
                                label="Bundle Amount"
                              />
                              <v-layout>
                                <v-flex
                                  m1
                                  class="padding5">
                                  <v-checkbox
                                    v-model="bundleCarriedOver"
                                    label="Bundle Carries over"
                                  />
                                </v-flex>
                              </v-layout>
                              <v-text-field
                                v-model="normalUnitPrice"
                                label="Normal Unit Price"
                              />
                              <v-text-field
                                v-model="rushedUnitPrice"
                                :value="productTypes.rushed_unit_price "
                                label="Rushed Unit Price"
                              />
                              <v-text-field
                                v-model="testUnitPrice"
                                :value="productTypes.test_unit_price "
                                label="Test Unit Price"
                              />
                              <i>Please make sure all <b>Monthly Setup</b> options are saved
                              before resetting monthly bundles.</i>
                              <v-btn
                                v-if="resetbutton == true"
                                @click="resetBundleMonthly">Reset Monthly
                              </v-btn>
                              <v-btn
                                v-if="resetbutton == false"
                                class="float-right"
                                disabled
                              >
                                <v-progress-circular
                                  indeterminate
                                  color="primary"
                                />
                                Resitting
                              </v-btn>
                            </v-flex>
                            <v-flex
                              m6
                              class="padding5">
                              <h2>Extra Setup</h2>
                              <hr>
                              <br>
                              <v-text-field
                                v-model="additionalBundle"
                                :value="productTypes.bundle_amount "
                                label="Additional requested"
                              />
                              <v-btn
                                v-if="addbutton == true"
                                @click="addBundle">Add Bundle
                              </v-btn>
                              <v-btn
                                v-if="addbutton == false"
                                class="float-right"
                                disabled
                              >
                                <v-progress-circular
                                  indeterminate
                                  color="primary"
                                />
                                Adding ...
                              </v-btn>
                            </v-flex>
                          </v-layout>
                        </v-card>
                      </v-flex>
                    </v-layout>
                  </div>
                </v-layout>
              </div>
              <div v-if="currentPackage == 'retainer'">
                <v-card class="padding5">
                  <h1>Please pull report from Dashboard</h1>
                </v-card>
              </div>
              <div v-if="currentPackage == 'suspended'">
                <v-card class="padding5">
                  <h1 class="warningColor">Account Has been Suspended</h1>
                </v-card>
              </div>
            </div>
          </v-card>
          <v-container>
            <h2 class="title mb-3"> Bundle List used</h2>
            <v-card>
              <v-layout
                row
                wrap>
                <v-flex
                  xs12
                  sm6
                  md4>
                  <v-menu
                    ref="menuFrom"
                    :close-on-content-click="false"
                    v-model="menuFrom"
                    :nudge-right="40"
                    lazy
                    transition="scale-transition"
                    offset-y
                    full-width
                    max-width="290px"
                    min-width="290px">
                    <v-text-field
                      slot="activator"
                      v-model="fromDateFormatted"
                      label="From"
                      persistent-hint
                      prepend-icon="event"
                      @blur="dateFrom = parseDate(fromDateFormatted)"/>
                    <v-date-picker
                      v-model="dateFrom"
                      no-title
                      @input="menuFrom = false"/>
                  </v-menu>
                </v-flex>
                <v-flex
                  xs12
                  sm6
                  md4>
                  <v-menu
                    :close-on-content-click="false"
                    v-model="menuTo"
                    :nudge-right="40"
                    lazy
                    transition="scale-transition"
                    offset-y
                    full-width
                    max-width="290px"
                    min-width="290px">
                    <v-text-field
                      slot="activator"
                      v-model="toDateFormatted"
                      label="To"
                      persistent-hint
                      prepend-icon="event"
                      @blur="dateTo = parseDate(toDateFormatted)"/>
                    <v-date-picker
                      v-model="dateTo"
                      no-title
                      @input="menuTo = false"/>
                  </v-menu>
                </v-flex>
                <v-flex
                  xs12
                  md4>
                  <v-btn
                    small
                    top
                    color="info"
                    @click="filterQueues(true,'filter')">
                    <v-icon dark>filter_list</v-icon>
                    Filter
                  </v-btn>
                  <v-btn
                    small
                    flat
                    top
                    color="grey darken-1"
                    @click="clearFilters">
                    <v-icon dark>clear</v-icon>
                    Clear All
                  </v-btn>
                </v-flex>
              </v-layout>
            </v-card>
          </v-container>
          <div class="container">
            <v-flex xs12>
              <v-btn
                small
                color="info"
                class="ma-4 right"
                @click="exportQueues()">
                <v-icon dark>get_app</v-icon>
                Export
              </v-btn>
            </v-flex>
            <v-data-table
              :headers="headers"
              :loading="loading"
              :items="bundles"
              must-sort
              class="elevation-1">
              <template
                v-if="props.item"
                slot="items"
                slot-scope="props">
                <tr>
                  <td class="text-xs-left">{{ props.item.company.name }}</td>
                  <td>
                    <div v-if="props.item.subject">
                      <div
                        v-for="(subjectItem, index) in props.item"
                        :key="index"
                      >
                        <div v-if="subjectItem.first_name">
                          {{ subjectItem.first_name }} {{ subjectItem.last_name }}
                        </div>
                      </div>
                    </div>
                    <div v-else>
                      <i class="material-icons">
                        remove
                      </i>
                    </div>
                  </td>
                  <td class="text-xs-left">
                    <div v-if="props.item.request_type">
                      {{ props.item.request_type }}
                    </div>
                    <div v-else>
                      <i class="material-icons">
                        remove
                      </i>
                    </div>
                  </td>
                  <td class="text-xs-left">{{ props.item.unit_used }}</td>
                  <td class="text-xs-left">{{ props.item.add_unit }}
                  </td>
                  <td class="text-xs-left">
                    <div v-if="props.item.reset_monthly_amounts == true">
                      <i class="material-icons">
                        autorenew
                      </i>
                    </div>
                    <div v-else>
                      <i class="material-icons">
                        remove
                      </i>
                    </div>
                  </td>
                  <td class="text-xs-left">{{ props.item.created_at }}</td>
                </tr>
              </template>
            </v-data-table>
          </div>
        </v-flex>
      </v-layout>
    </v-card>
  </div>
</template>
<script>
import { mapGetters } from 'vuex'
import _ from 'lodash'

export default {
  props: {
    companyId: {
      type: String,
      default: ''
    }
  },
  head() {
    return {
      title: 'Edit Subject :: Farosian'
    }
  },
  async fetch({ store, route }) {
    await store
      .dispatch('product-types/getProductTypes', route.params.id)
      .catch(() => {
        console.log('Could not get the getProductTypes')
      })
    await store
      .dispatch('product-types/getCompanyUsage', route.params.id)
      .catch(() => {
        console.log('Could not get companies')
      })
  },
  data() {
    return {
      menuFrom: false,
      menuTo: false,
      filter_request: null,
      dateFrom: null,
      dateTo: null,
      fromDateFormatted: null,
      toDateFormatted: null,
      saveButton: true,
      resetbutton: true,
      addbutton: true,
      headers: this.getHeaders(),
      loading: false,
      bundleAmount: '',
      normalUnitPrice: '',
      rushedUnitPrice: '',
      testUnitPrice: '',
      additionalBundle: '',
      resetsMonthly: false,
      bundleCarriedOver: false,
      dialog: true,
      currentPackage: '',
      bundle_total_used: '',
      productTypeDropdown: [
        {
          name: 'Pre Paid',
          productType: 'pre_paid'
        },
        {
          name: 'Retainer',
          productType: 'retainer'
        },
        {
          name: 'Suspend Account',
          productType: 'suspended'
        }
      ]
    }
  },
  computed: {
    ...mapGetters({
      productTypes: 'product-types/productTypes',
      bundles: 'product-types/bundlesUsed',
      paginationState: 'product-types/pagination'
    }),
    pagination: {
      get: function() {
        return this.paginationState
      },
      set: function(value) {
        this.$store.commit('product-types/SET_PAGINATION', value)
      }
    }
  },
  watch: {
    companyId: {
      handler: function() {
        this.$store.dispatch(
          'product-types/getCompanyUsage',
          this.$route.params.id
        )
      },
      deep: true
    },
    dateFrom(val) {
      this.fromDateFormatted = this.formatDate(this.dateFrom)
    },
    dateTo(val) {
      this.toDateFormatted = this.formatDate(this.dateTo)
    },
    pagination: {
      handler() {
        this.refreshBundles()
      },
      deep: true
    }
  },
  mounted() {
    this.additionalBundle = this.productTypes.additional_requested
    this.show()
  },
  methods: {
    save() {},
    getFormattedText(value) {
      return _.startCase(value)
    },
    refreshQueues() {
      this.$store.dispatch('product-types/queryQueues').then(() => {})
    },
    clearFilters() {
      this.filter_request = null
      this.dateFrom = null
      this.dateTo = null

      this.$store.commit('product-types/SET_FILTERS', {
        filter_request: this.filter_request,
        dateFrom: null,
        dateTo: null,
        company: this.$route.params.id
      })

      this.$store
        .dispatch('product-types/getCompanyUsage', this.$route.params.id)
        .catch(() => {
          console.log('Could not get companies')
        })
    },
    filterQueues(reload) {
      this.$store.commit('product-types/SET_FILTERS', {
        filter_request: this.filter_request,
        dateFrom: this.dateFrom,
        dateTo: this.dateTo,
        company: this.$route.params.id
      })

      if (reload) {
        this.refreshQueues()
      }
    },
    formatDate(date) {
      if (!date) return null

      const [year, month, day] = date.split('-')
      return `${day}/${month}/${year}`
    },
    parseDate(date) {
      if (!date) return null

      const [day, month, year] = date.split('/')
      return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`
    },
    resetBundleMonthly() {
      var r = confirm('Warning you about to reset monthly accounts !')
      if (r == true) {
        this.addbutton = false
        let data = {
          id: this.productTypes.id
        }
        this.$store
          .dispatch('product-types/monthlyBundleReset', data)
          .then(response => {
            this.additionalBundle = ''
            this.$toast.success('Monthly Bundle Reset')
            this.addbutton = true
            this.show()
            this.refreshBundles()
          })
          .catch(error => {
            this.$toast.error('Failed to Reset Monthly Bundle')
          })
      }
    },
    addBundle() {
      this.addbutton = false
      let data = {
        id: this.productTypes.id,
        add_unit: this.additionalBundle
      }
      this.$store
        .dispatch('product-types/addCompanyBundle', data)
        .then(response => {
          this.additionalBundle = ''
          this.$toast.success('Add to Bundle')
          this.addbutton = true
          this.show()
          this.refreshBundles()
        })
        .catch(error => {
          this.$toast.error('Failed to update Company Product')
        })
    },
    getHeaders() {
      return [
        { text: 'Company', value: 'company.name' },
        { text: 'Subject', value: 'subject' },
        { text: 'Request Type', value: 'request_type' },
        { text: 'Unit Used', value: 'unit_used' },
        { text: 'Unit add', value: 'add_unit' },
        { text: 'Reset Monthly', value: 'reset_monthly_amounts' },
        { text: 'Date Created', value: 'created_at' }
      ]
    },
    refreshBundles() {
      this.loading = true
      this.$store
        .dispatch('product-types/getCompanyUsage', this.$route.params.id)
        .then(() => {
          this.loading = false
        })
    },
    resetBundle() {
      let data = {
        id: this.productTypes.id,
        additional_requested: 0,
        bundle_amount: 0
      }
      this.$store
        .dispatch('product-types/updateCompanyProduct', data)
        .then(response => {
          this.show()

          this.$toast.success('Company Product Updated')
        })
        .catch(error => {
          this.$toast.error('Failed to update Company Product')
        })
    },
    update() {
      this.resetbutton = true
      this.saveButton = true
      let data = {
        id: this.productTypes.id,
        product_type: this.currentPackage,
        additional_requested: this.additionalBundle,
        bundle_amount: this.bundleAmount,
        normal_unit_price: this.normalUnitPrice,
        rushed_unit_price: this.rushedUnitPrice,
        test_unit_price: this.testUnitPrice,
        monthly_recurring: this.resetsMonthly,
        units_carry_over: this.bundleCarriedOver
      }
      this.$store
        .dispatch('product-types/updateCompanyProduct', data)
        .then(response => {
          this.$toast.success('Company Product Updated')
          this.show()
        })
        .catch(error => {
          this.$toast.error('Failed to update Company Product')
        })
    },
    changeProduct(value) {
      this.currentPackage = value
    },
    exportQueues() {
      this.loading = true
      this.$store
        .dispatch('product-types/queryExportUsage', this.$route.params.id)
        .then(response => {
          let filename = this.getName(response.headers['content-disposition'])
          this.loading = false
          this.downloadFile(response.data, filename)
        })
    },
    getName(disposition) {
      let filename = 'bundlesused.xlsx'
      if (disposition && disposition.indexOf('inline') !== -1) {
        var filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/
        var matches = filenameRegex.exec(disposition)
        if (matches != null && matches[1]) {
          filename = matches[1].replace(/['"]/g, '')
        }
      }
      return filename
    },
    downloadFile(fileData, fileName) {
      const url = window.URL.createObjectURL(new Blob([fileData]))
      const link = document.createElement('a')
      link.href = url
      link.setAttribute('download', fileName) //or any other extension
      document.body.appendChild(link)
      link.click()
    },
    async show() {
      await this.$store
        .dispatch('product-types/getProductTypes', this.$route.params.id)
        .then(response => {
          this.currentPackage = this.productTypes.product_type
          this.bundleAmount = this.productTypes.bundle_amount
          this.additionalBundle = this.productTypes.additional_requested
          this.normalUnitPrice = this.productTypes.normal_unit_price
          this.rushedUnitPrice = this.productTypes.rushed_unit_price
          this.testUnitPrice = this.productTypes.test_unit_price
          this.resetsMonthly = this.productTypes.monthly_recurring
          this.bundleCarriedOver = this.productTypes.units_carry_over
          this.bundle_total_used = this.productTypes.bundle_total_used
        })
    },
    hide() {
      this.dialog = false
    }
  }
}
</script>
<style scoped>
.padding5 {
  padding: 5px !important;
}

.selectProductType {
  width: 250px !important;
}

.margin5 {
  margin: 5px;
}

.warningColor {
  color: darkred;
}
</style>
