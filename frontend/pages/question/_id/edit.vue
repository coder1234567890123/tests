<template>
  <div
    id="app"
    class="container">
    <h1 class="title mb-3">Edit Question</h1>
    <v-card>
      <v-form
        ref="form"
        v-model="valid"
        lazy-validation
      >
        <v-layout
          row
          wrap>
          <v-flex md6>
            <v-subheader>Which platform do you want to search from?</v-subheader>
            <v-select
              :value="question.platform"
              :rules="platformRules"
              :items="platformList"
              item-text="label"
              item-value="value"       
              label="Platform"
              class="mx-3"
              @change="updateState('platform', $event)"/>
          </v-flex>
          <v-flex 
            md6
            class="mt-2">
            <v-subheader class="pb-2">Select an Answer Type suitable for this question:</v-subheader>
            <v-select
              :value="question.answer_type"
              :rules="answerTypesRules"
              :items="answerTypes"
              item-text="label"
              item-value="value"
              label="Answer type"
              class="mx-3"
              @input="updateState('answer_type', $event)"
              @change="answerTypeChange($event)"
            />
          </v-flex>
          <v-flex 
            v-if="question.answer_type === 'yes_no'"
            md6>
            <v-subheader>Enter report label?</v-subheader>
            <v-text-field
              :value="question.report_label"
              box
              hint="This adds the report section label"
              class="mx-3"
              @input="updateState('report_label', $event)"
            />
          </v-flex>
          <v-flex 
            md6>
            <v-subheader>Enter order number for the question</v-subheader>
            <v-text-field
              :value="question.order_number"
              :rules="orderNumberRules"
              box
              hint="This adds the order precedence for the question"
              class="mx-3"
              @input="updateState('order_number', $event)"
            />
          </v-flex>
          <v-flex md12>
            <v-list-tile>
              <v-checkbox
                :value="question.slider"
                label="Include Slider"
                @change="sliderSelect('slider', $event === null ? false: true)"/>
            </v-list-tile>
          </v-flex>
          <v-flex
            v-if="question.slider"
            md6>
            <v-subheader>Enter slider average</v-subheader>
            <v-text-field
              :value="question.slider_average"
              :rules="sliderAverageRules"
              type="number"
              box
              hint="This adds an average for a slider"
              class="mx-3"
              @input="updateState('slider_average', $event)"
            />
          </v-flex>
        </v-layout>
        <v-layout 
          v-if="question.answer_type === 'multiple_choice'"
          container>
          <v-flex md12>
            <v-layout row>
              <v-flex md6>
                <h1>Answer Options</h1>
              </v-flex>
              <v-flex class="text-md-right">
                <v-btn 
                  small 
                  @click="addAnswerOption()">Add</v-btn>
              </v-flex>
            </v-layout>
            <hr>

            <p
              v-if="question.answer_options.length === 0"
              class="mt-1"
            >No answer options specified.</p>
            <v-container
              v-if="question.answer_options.length > 0">
              <v-layout 
                v-for="(option, index) in question.answer_options" 
                :key="index" 
                row 
                wrap>
                <v-flex 
                  md6>
                  <v-text-field
                    :value="question.answer_options[index]"
                    label="Answer Option"
                    @input="updateAnswerOption(index, $event)"
                  />
                </v-flex>
                <v-flex 
                  md6>
                  <v-text-field
                    :value="question.answer_score[index]"
                    :rules="answerScoreRules"
                    :min="minValue"
                    :max="maxValue"
                    :step="stepValue"
                    append-icon="close"
                    type="number"
                    label="Answer Score"
                    @input="updateAnswerScore(index, $event)"
                    @click:append="removeAnswerOption(index)"
                  />
                </v-flex>
              </v-layout>
            </v-container>
          </v-flex>
        </v-layout>

        <v-layout>
          <v-container
            class="pt-0"
          >
            <v-subheader>What type of report type do you expect?</v-subheader>
            <v-layout
              md12
              row
            >
              <v-list-tile
                v-for="(report_type, index) in reportTypeList"
                :key="index"
              >
                <v-checkbox
                  :label="`${report_type.label}`"
                  :value="report_type.value"
                  :input-value="question.report_types"
                  :disabled="report_type.disabled"
                  @change="updateState('report_types', $event)"/>
              </v-list-tile>
            </v-layout>
          </v-container>
        </v-layout>

        <v-layout
          v-if="question.answer_type === 'yes_no'"
          container>
          <v-flex md12>
            <v-layout row>
              <v-flex md6>
                <h1>Yes/No Answer Weights</h1>
              </v-flex>
            </v-layout>
            <hr>

            <p
              v-if="question.answer_score.length < 0"
              class="mt-1">
              No answer options specified.
            </p>
            <p>Enter the 'Yes' score on the left and the 'No' score on the right</p>

            <v-layout
              v-if="question.answer_score.length > 1"
              row
              wrap>
              <v-flex
                v-for="(option, index) in question.answer_score"
                :key="index"
                md6>
            
                
                <v-text-field
                  :value="question.answer_score[index]"
                  :rules="answerScoreRules"
                  :label="index === 0 ? 'Yes Answer Weight' : 'No Answer Weight'"
                  :min="minValue"
                  :max="maxValue"
                  :step="stepValue"
                  type="number"
                  @input="updateAnswerScore(index, $event)"
                />
              </v-flex>
            </v-layout>
          </v-flex>
        </v-layout>
        
        <v-layout
          v-if="question.slider === true"
          container>
          <v-flex md12>
            <v-layout row>
              <v-flex md6>
                <h1>Minmum and Maximum Weight for slider</h1>
              </v-flex>
            </v-layout>
            <hr>

            <p
              v-if="question.slider_values.length < 0"
              class="mt-1">
              No slider values specified.
            </p>
            <p>Enter the 'Minimum' number on the left and the 'Maximum' number on the right</p>

            <v-layout
              v-if="question.slider_values.length > 1"
              row
              wrap>
              <v-flex
                v-for="(option, index) in question.slider_values"
                :key="index"
                md6>
            
                
                <v-text-field
                  :value="question.slider_values[index]"
                  :rules="sliderRules"
                  :label="index === 0 ? 'Minimum Value' : 'Maximum Value'"
                  type="number"
                  min="0"
                  @input="updateSliderValues(index, $event)"
                />
              </v-flex>
            </v-layout>
          </v-flex>
        </v-layout>

        <v-layout row>
          <v-flex>
            <v-subheader>Enter Question</v-subheader>
            <v-textarea
              :value="question.question"
              :rules="questionRules"
              box
              hint="This adds the questions that'll be used for the search"
              class="mx-3"
              @change="updateState('question', $event)"/>
          </v-flex>
        </v-layout>
        <v-layout row>
          <v-flex
            md12>
            <v-card-actions class="mx-2">
              <v-btn
                :nuxt="true"
                color="grey darken-3"
                class="white--text"
                to="/question/view">Back
              </v-btn>
              <v-btn
                color="teal darken-3"
                class="white--text"
                @click="validateForm">
                Save
              </v-btn>
            </v-card-actions>
          </v-flex>
        </v-layout>
      </v-form>
    </v-card>
  </div>
</template>
<script>
import { mapGetters } from 'vuex'
import _ from 'lodash'

export default {
  async fetch({ store, route }) {
    await store.dispatch('question/get', route.params.id)
  },
  head() {
    return {
      title: 'Edit Question :: Farosian'
    }
  },
  data() {
    return {
      valid: true,
      minValue: -5.0,
      maxValue: 5.0,
      stepValue: 0.01,
      reportsSelected: [],
      orderNumberRules: [v => !!v || 'Order number is required'],
      sliderAverageRules: [v => !!v || 'Slider Average is required'],
      questionRules: [v => !!v || 'A question is required'],
      platformRules: [v => !!v || 'Platform is required']
    }
  },
  computed: {
    ...mapGetters({
      question: 'question/question',
      sections: 'question/sections',
      platforms: 'static/platforms',
      reportTypes: 'static/reportTypes',
      answerTypes: 'static/answerTypes'
    }),
    answerTypesRules() {
      return [
        v => !!v || 'Answer type is required',
        this.question.answer_type !== 'multiple_choice' ||
          (this.question.answer_type === 'multiple_choice' &&
            this.question.answer_options.length > 0) ||
          'Please enter at least one answer option'
      ]
    },
    answerScoreRules() {
      return [
        v => !!v || 'Score is required and should be a number',
        v =>
          v >= this.minValue || 'Score should be greater than ' + this.minValue,
        v => v <= this.maxValue || 'Score should be less than ' + this.maxValue
      ]
    },
    sliderRules() {
      return [
        v => !!v || 'Value is required and should be a number',
        v => v >= 0 || 'Score should be greater than 0'
      ]
    },
    platformList() {
      return this.question.platform
        ? this.platforms.map(item => ({
            label: item.label,
            value: item.value
          }))
        : this.platforms
    },
    reportTypeList() {
      return this.question.report_types &&
        this.question.report_types.includes('all')
        ? this.reportTypes.map(item => ({
            label: item.label,
            value: item.value,
            disabled: item.value !== 'all'
          }))
        : this.reportTypes
    }
  },
  watch: {
    reportTypeList: function() {
      if (
        this.question.report_types &&
        this.question.report_types.length > 1 &&
        this.question.report_types.includes('all')
      )
        this.updateState('report_types', ['all'])
    }
  },
  methods: {
    removeAnswerOption(index) {
      this.$store.dispatch('question/updateAnswerOptions', index).catch(() => {
        this.$toast.error('Could not update answer option data')
      })
    },
    sliderSelect(prop, value) {
      this.$store.dispatch('question/updateSliderValues', value).catch(() => {
        this.$toast.error('Could not update slider data')
      })
    },
    answerTypeChange(value) {
      this.$store.dispatch('question/answerTypeChange', value).catch(() => {
        this.$toast.error('Could not reset answer option and score')
      })
    },
    updateAnswerOption(index, value) {
      this.$store
        .dispatch('question/setAnswerOption', { index: index, value: value })
        .catch(() => {
          this.$toast.error('Could not update answer option data')
        })
    },
    addAnswerOption() {
      this.$store.dispatch('question/updateAnswerOptions').catch(() => {
        this.$toast.error('Could not update answer option data')
      })
    },
    updateAnswerScore(index, value) {
      this.$store
        .dispatch('question/setAnswerScore', { index: index, value: value })
        .catch(() => {
          this.$toast.error('Could not update answer score data')
        })
    },
    updateSliderValues(index, value) {
      this.$store
        .dispatch('question/setSliderValues', { index: index, value: value })
        .catch(() => {
          this.$toast.error('Could not update answer score data')
        })
    },
    updateState(prop, value) {
      this.$store
        .dispatch('question/updateQuestion', { prop, value })
        .catch(() => {
          this.$toast.error('Could not update question data')
        })
    },
    validateForm() {
      if (this.$refs.form.validate()) {
        this.$store
          .dispatch('question/update', this.question)
          .then(() => {
            this.$toast.success('Question successfully updated!')
            this.$router.push('/question/view')
          })
          .catch(() => {
            this.$toast.error(
              'Could not update question, please double check validation!'
            )
          })
      }
    }
  }
}
</script>
