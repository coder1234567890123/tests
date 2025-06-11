import Vue from 'vue'
import { Line } from 'vue-chartjs'

Vue.component('my-line', {
  extends: Line,
  props: {
    data: {
      type: Object,
      default: function() {
        return {}
      }
    },
    options: {
      type: Object,
      default: function() {
        return {}
      }
    },
    height: {
      type: Number,
      default: function() {
        return {}
      }
    }
  },
  mounted() {
    this.renderChart(this.data, this.options)
  }
})
