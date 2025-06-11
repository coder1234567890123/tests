<template>
  <div
    id="app"
    class="container">
    <h1 class="title mb-3">Add Question</h1>
    <v-card>
      <v-form
        ref="form"
        lazy-validation>
        <v-layout
          row
          wrap>
          <v-flex md6>
            <v-subheader>Which platform do you want to search from?</v-subheader>
            <v-select
              v-model="question.platform"
              :rules="platformRules"
              :items="platformList"
              item-text="label"
              item-value="value"
              label="Platform"
              class="mx-3"/>
          </v-flex>
          <v-flex md6>
            <v-subheader>Select an Answer Type suitable for this question:</v-subheader>
            <v-select
              v-model="question.answer_type"
              :rules="answerTypesRules"
              :items="answerTypes"
              item-text="label"
              item-value="value"
              label="Answer type"
              class="mx-3"
              @change="answerTypeChange($event)"
            />
          </v-flex>
          <v-flex 
            v-if="question.answer_type === 'yes_no'"
            md6>
            <v-subheader>Enter report label?</v-subheader>
            <v-text-field
              v-model="question.report_label"
              box
              hint="This adds the report section label"
              class="mx-3"
            />
          </v-flex>
          <v-flex
            md6>
            <v-subheader>Enter order number for the question</v-subheader>
            <v-text-field
              v-model="question.order_number"
              :rules="orderNumberRules"
              box
              hint="This adds the order precedence for the question"
              class="mx-3"
            />
          </v-flex>
          <v-flex md12>
            <v-list-tile>
              <v-checkbox
                v-model="question.slider"
                label="Include Slider"
                @change="sliderSelect"/>
            </v-list-tile>
          </v-flex>
          <v-flex
            v-if="question.slider"
            md6>
            <v-subheader>Enter slider average</v-subheader>
            <v-text-field
              v-model="question.slider_average"
              :rules="sliderAverageRules"
              type="number"
              box
              hint="This adds an average for a slider"
              class="mx-3"
            />
          </v-flex>

          <v-container
            class="pt-0"
          >
            <v-subheader>For which report type does this question apply?</v-subheader>
            <v-layout
              md12
              row
            >
              <v-list-tile
                v-for="(report_type, index) in reportTypeList"
                :key="index"
              >
                <v-checkbox
                  v-model="question.report_types"
                  :label="report_type.label" 
                  :value="report_type.value"
                  :disabled="report_type.disabled"/>
              </v-list-tile>
            </v-layout>
            <v-layout 
              v-if="question.answer_type === 'multiple_choice'"
              container>
              <v-flex md12>
                <v-layout row>
                  <v-flex md6>
                    <h1>Answer Options and Weights</h1>
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
                      col 
                      md6>
                      <v-text-field
                        v-model="question.answer_options[index]"
                        label="Answer Option"
                        @click:append="removeAnswerOption(index)"
                      />
                    </v-flex>
                    <v-flex
                      col 
                      md6>
                      <v-text-field
                        v-model="question.answer_score[index]"
                        :rules="answerScoreRules"
                        :min="minValue"
                        :max="maxValue"
                        :step="stepValue"
                        type="number"
                        append-icon="close"
                        label="Answer Weight"
                        @click:append="removeAnswerOption(index)"
                      />
                    </v-flex>
                 
                 
                  </v-layout>
                </v-container>
              </v-flex>
            </v-layout>
            <v-layout 
              v-if="question.answer_type === 'yes_no'"
              container>
              <v-flex md12>
                <v-layout row>
                  <v-flex md12>
                    <h1>Yes/No Answer Weights</h1>
                  </v-flex>
                </v-layout>
                <hr>

                <p
                  v-if="question.answer_score.length === 0"
                  class="mt-1"
                >No answer options specified.</p>
                <p>Enter the 'Yes' score on the left and the 'No' score on the right</p>
                <v-layout
                  v-if="question.answer_options.length > 1"
                  row
                  wrap>
                  <v-flex
                    v-for="(option, index) in question.answer_options"
                    :key="index"
                    col 
                    md6>
                    
                    <v-text-field
                      v-model="question.answer_score[index]"
                      :rules="answerScoreRules"
                      :label="index === 0 ? 'Yes Answer Weight' : 'No Answer Weight'"
                      :min="minValue"
                      :max="maxValue"
                      :step="stepValue"
                      type="number"/>
                  </v-flex>
                 
                </v-layout>
              </v-flex>
            </v-layout>
            <v-layout 
              v-if="question.slider === true"
              container>
              <v-flex md12>
                <v-layout row>
                  <v-flex md12>
                    <h1>Minmum and Maximum Weight for slider</h1>
                  </v-flex>
                </v-layout>
                <hr>

                <p
                  v-if="question.slider_values.length === 0"
                  class="mt-1"
                >No slider values specified.</p>
                <p>Enter the 'Minimum' number on the left and the 'Maximum' number on the right</p>
                <v-layout
                  v-if="question.slider_values.length > 1"
                  row
                  wrap>
                  <v-flex
                    v-for="(option, index) in question.slider_values"
                    :key="index"
                    col 
                    md6>
                    
                    <v-text-field
                      v-model="question.slider_values[index]"
                      :rules="sliderRules"
                      :label="index === 0 ? 'Minimum Value' : 'Maximum Value'"
                      type="number"
                      min="0"/>
                  </v-flex>
                 
                </v-layout>
              </v-flex>
            </v-layout>
            <v-flex 
              md12
            >
              <v-subheader>Enter Question</v-subheader>
              <v-textarea
                v-model="question.question"
                :rules="questionRules"
                box
                hint="This adds the questions that'll be used for the search"
                class="mx-3"
              />
            </v-flex>
        </v-container></v-layout>
        <v-layout row>
          <v-flex md12>
            <v-card-actions class="mx-2">
              <v-btn 
                color="grey darken-3"
                class="white--text"
                to="/question/view"
              >Back</v-btn>

              <v-btn
                color="teal darken-3"
                class="white--text"
                @click="add">Save</v-btn>
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
  head() {
    return {
      title: 'Add Question :: Farosian'
    }
  },
  data() {
    return {
      question: {
        question: '',
        answer_type: '',
        report_types: [],
        answer_options: [],
        std_comments: [],
        answer_score: [],
        slider: false,
        slider_average: 0,
        slider_values: [],
        report_section: {
          id: ''
        },
        report_label: '',
        order_number: ''
      },
      minValue: -5.0,
      maxValue: 5.0,
      stepValue: 0.01,
      reportsSelected: [],
      orderNumberRules: [v => !!v || 'Order number is required'],
      sliderAverageRules: [v => !!v || 'Slider Average is required'],
      questionRules: [v => !!v || 'A question is required'],
      platformRules: [v => !!v || 'Platform is required'],
      searchTermRules: [v => !!v || 'Question is required ']
    }
  },
  computed: {
    ...mapGetters({
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
            this.question.answer_options.filter(function(el) {
              return el
            }).length > 0) ||
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
        this.question.report_types = ['all']
    }
  },
  methods: {
    removeAnswerOption(index) {
      this.question.answer_options.splice(index, 1)
      this.question.answer_score.splice(index, 1)
    },
    sliderSelect() {
      if (this.question.slider === true) {
        this.question.slider_values.push(0)
        this.question.slider_values.push(0)
      } else {
        this.question.slider_values = []
      }
    },
    answerTypeChange(value) {
      this.question.answer_options = []
      this.question.answer_score = []
      if (value === 'yes_no') {
        this.question.answer_options.push('YES')
        this.question.answer_score.push(0)
        this.question.answer_options.push('NO')
        this.question.answer_score.push(0)
      }
    },
    addAnswerOption() {
      this.question.answer_options.push('')
      this.question.answer_score.push(0)
    },
    add() {
      if (this.$refs.form.validate()) {
        this.$store
          .dispatch('question/create', this.question)
          .then(() => {
            this.$toast.success('Question successfully created!')
            this.$router.push('/question/view')
          })
          .catch(e => {
            this.$toast.error(
              'Could not create question, please double check validation!' + e
            )
          })
      }
    }
  }
}
</script>
