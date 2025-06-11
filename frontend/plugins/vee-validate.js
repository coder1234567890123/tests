import Vue from 'vue'
import VeeValidate from 'vee-validate'

Vue.directive('VeeValidate', VeeValidate)
Vue.use(VeeValidate, {
  inject: false
})
