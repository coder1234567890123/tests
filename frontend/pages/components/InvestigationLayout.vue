<template>
  <v-app>
    <v-navigation-drawer
      v-for="(question, index) in questions"
      :key="index + count"
      :clipped="clipped"
      v-model="drawer"
      fixed
      app>
      <v-list
        v-if="count < questions.length"
        class="pt-0">
        <v-list-tile class="tile">
          <v-list-tile-action>
            <v-icon>search</v-icon>
          </v-list-tile-action>
          <v-list-tile-content>
            <v-list-tile-title>Investigation mode</v-list-tile-title>
          </v-list-tile-content>
        </v-list-tile>

        <v-divider />

        <v-list
          subheader
          three-line>
          <v-subheader>Question {{ count + 1 }}</v-subheader>

          <v-divider />

          <div>
            <v-list-tile>
              <v-card flat>
                <v-card-text>{{ questions[count].question }}</v-card-text>
              </v-card>
            </v-list-tile>
          </div>
        </v-list>

        <v-divider />

        <v-list
          subheader
          three-line>
          <v-subheader>Answers</v-subheader>
          <v-divider />
          <v-list-tile class="mx-3">
            <v-list
              v-if="questions[count].answer_type === 'multiple_choice'"
              class="mt-7"
              row>
              <v-list-tile
                v-for="(index,options) in questions[count].answer_options.length "
                :key="options"
                row>
                <v-checkbox 
                  :label="`${questions[count].answer_options[index - 1]} `"
                  :value="questions[count].answer_options[index - 1]"
                  hide-details
                  @change="toggle($event, index)"/>
              </v-list-tile>
            </v-list>
            <v-list 
              v-else-if="questions[count].answer_type === 'text'" 
              class="container py-0 mt-6 ">
              <v-text-field 
                v-model="answer.answer"
                label=" answer" />
            </v-list>
            <v-list
              v-else-if="questions[count].answer_type === 'yes_no'"
              row
              class="text-xs-center mt-7 ml-4">
              <v-container
                fluid>
                <v-radio-group
                  v-model="answer.answer">
                  <v-radio
                    label="Yes"
                    value="yes"
                    class="mt-3"/>
                  <v-radio
                    label="No"
                    value="no"
                    class="mt-5"/>
                </v-radio-group>
              </v-container>
            </v-list>
          </v-list-tile>
        </v-list>
        <div class="container py-0 mt-7">
          <v-list
            row
            class="text-xs-center">
            <v-btn
              block
              color="teal darken-3"
              dark
              @click="dialog = !dialog">Add Comment</v-btn>
          </v-list>
        </div>

        <v-divider />

        <v-list 
          row 
          class="text-xs-center"> 
          <v-btn 
            color="teal darken-3" 
            class="white--text" 
            @click.prevent="skipQuestions(question.id)">Skip</v-btn>
          <v-btn @click.prevent="questNotAvailable(question.id)">N/A</v-btn>
        </v-list>
        <div class="container py-0">
          <v-list
            row
            class="text-xs-center">
            <v-btn
              block
              color="teal darken-3 btn-size mx-auto"
              dark 
              @click="AnswerQuestion(question.id, answer.answer)">Submit</v-btn>
          </v-list>
        </div>
        <v-list 
          row 
          class="text-xs-center">
          <v-btn
            v-show="count!==0"
            @click="previous">prev</v-btn>
          <v-btn 
            color="teal darken-3" 
            class="white--text" 
            @click="next">next</v-btn>
        </v-list>
      </v-list>
      <v-list
        v-else
        class="pt-0">
        <v-list-tile class="tile">
          <v-list-tile-action>
            <v-icon>search</v-icon>
          </v-list-tile-action>
          <v-list-tile-content>
            <v-list-tile-title>Investigation mode</v-list-tile-title>
          </v-list-tile-content>
        </v-list-tile>

        <v-divider />

        <v-list
          row
          class="text-xs-center mt-4">
          <p>You have completed the investigation mode.</p>
          <p>What would you like to do next?</p>
          <v-btn
            block
            color="teal darken-3 btn-size mx-auto"
            dark
            @click="generalComment = !generalComment">Add General Comment</v-btn>
          <v-btn
            block
            color="darken-3 btn-size mx-auto"
            dark
            @click="reset">Start over</v-btn>
          <v-btn
            :to="{ name: $getRoute('SUBJECTS_VIEW'), params: { id: $route.params.id } }"
            block
            class="btn-size mx-auto">Exit Investigation</v-btn>
        </v-list>

        <v-divider />

      </v-list>
    </v-navigation-drawer>
    <v-dialog
      v-model="dialog"
      max-width="500px">
      <v-card>
        <v-card-text>
          <v-text-field 
            v-model="comment.comment"
            label="Comment"/>
          <small class="grey--text">*Add a comment</small>
        </v-card-text>
        <v-card-actions>
          <v-spacer/>
          <v-btn 
            flat 
            color="primary" 
            @click= "addComment()">Submit</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-dialog
      v-model="generalComment"
      max-width="500px">
      <v-card>
        <v-card-text>
          <v-text-field
            v-model="answer.answer"
            label="General Comment"/>
        </v-card-text>
        <v-card-actions>
          <v-spacer/>
          <v-btn
            flat
            color="primary"
            @click="AnswerQuestion('', answer.answer)">Submit General</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-app>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  data() {
    return {
      generalComment: false,
      dialog: false,
      answer: {
        answer: '',
        question: {
          id: ''
        },
        answered_by: {
          id: ''
        },
        answered_for: {
          id: ''
        }
      },
      answerRespId: '',
      answerSelected: [],
      question: {
        question: '',
        report_section: {
          id: ''
        },
        report_label: '',
        platforms: ''
      },
      comment: {
        comment: '',
        answer: {
          id: ''
        }
      },
      count: 0,
      widgets: '',
      clipped: true,
      drawer: true,
      fixed: false,
      items: [
        {
          icon: 'apps',
          title: 'Dashboard',
          to: '/'
        },
        {
          icon: 'call_split',
          title: 'Licenses',
          to: '/subscriptions'
        },
        {
          icon: 'present_to_all',
          title: 'Azure',
          to: '/azure'
        }
      ],
      active: null,
      right: true,
      rightDrawer: false,
      title: 'Adcorp Social Profiling'
    }
  },
  computed: {
    ...mapGetters({
      questions: 'investigation/questions',
      answers: 'investigation/answers'
    })
  },
  methods: {
    next() {
      this.count++
    },
    previous() {
      this.count--
    },
    reset() {
      this.count = 0
    },
    skipQuestions(id) {
      this.$store
        .dispatch('investigation/skipQuestion', id)
        .then(() => {})
        .catch(() => {
          this.$toast.error(
            'could not skip question. please double check validation'
          )
        })
    },
    questNotAvailable(id) {
      this.$store
        .dispatch('investigation/questionNotAvailable', id)
        .then(() => {
          this.next()
        })
        .catch(() => {
          this.$toast.error(
            'question not available error. please double check validation'
          )
        })
    },
    AnswerQuestion(id, response) {
      if (id !== '') {
        this.answer.question.id = id
      } else {
        delete this.answer.question
      }
      this.answer.answer = response
      this.answer.answered_for.id = id
      this.$store
        .dispatch('investigation/answerQuestions', this.answer)
        .then(response => {
          this.answerRespId = response.id
          this.$toast.success('successfully submitted answer')
        })
        .catch(() => {
          this.$toast.error(
            'question not available error. please double check validation'
          )
        })
      this.generalComment = false
    },
    toggle(val, id) {
      this.answerSelected['X' + id] = val
      let filteredValues = Object.values(this.answerSelected).filter(function(
        item
      ) {
        return item
      })

      this.answer.answer = filteredValues.join()
    },
    addComment() {
      if (this.answerRespId !== '') {
        this.comment.answer.id = this.answerRespId
        this.$store
          .dispatch('investigation/createComment', this.comment)
          .then(
            this.$toast.success(
              'successfully submitted comment',
              (this.dialog = false)
            )
          )
          .catch(() => {
            this.$toast.error(
              'question not available error. please double check validation'
            )
          })
      } else {
        this.$toast.error('Comment did not send, answer cannot be empty')
      }
    }
  }
}
</script>

<style scoped>
ul {
  list-style-type: none;
}

.logo-text {
  width: 160px;
  vertical-align: middle;
}

.tile {
  background-color: rgba(0, 0, 0, 0.14);
}

.btn-size {
  width: 14.5rem;
}

.mt-6 {
  margin-top: 6rem;
}

.mt-7 {
  margin-top: 10rem;
}
</style>
