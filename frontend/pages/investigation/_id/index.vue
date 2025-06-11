<template xmlns:v-slot="http://www.w3.org/1999/XSL/Transform">
  <v-layout
    v-if="subject !== null">
    <investigation-questions
      :commentsx="questionComments"
      :current-question="currentQuestion + 1"
      :general-comment="generalComments"
      :question="questions ? questions[currentQuestion] : {}"
      :questions-length="questions ? questions.length : 0"
      :report="subject.current_report ? subject.current_report:{}"
      @answer="handleAnswer"
      @general-comment-handler="handleGeneralComment"/>
    <div
      id="app"
      class="container pt-0 mt-5">
      <v-flex row>
        <v-breadcrumbs
          :items="breads"
          divider=">"/>
      </v-flex>
      <v-card
        class="mb-2 v-card-width"
        md6>
        <v-flex
          md12>
          <v-card-actions>
            <v-card-title primary-title>
              <div>
                <h3 class="headline mb-0">{{ subject.first_name }} {{ subject.last_name }}</h3>
              </div>
            </v-card-title>
          </v-card-actions>
        </v-flex>
      </v-card>
      <v-tabs
        slot="extension"
        v-model="tabVal"
        color="#ddd"
      >
        <v-tabs-slider
          color="#a3a3a3"/>
        <div
          v-for="(weight, index) in platformConfigs"
          :key="index">
          <v-tab
            v-if="checkNumQuestions(weight.social_platform) > 0"
            :key="weight.social_platform"
            :href="attrValue(weight.social_platform)"
            @click="changePlatform(weight.social_platform)">
            {{ weight.social_platform }} &nbsp;<v-chip color="light-blue"><small style="color: white;">{{
              checkAnsQuestions(weight.social_platform)
            }}/{{
              checkNumQuestions(weight.social_platform)
            }}</small>
            </v-chip>
          </v-tab>
        </div>
        <v-tabs-items :value="tabVal">
          <v-tab-item value="pinterest-tab">
            <pinterest-matches :investigation-mode="true"/>
          </v-tab-item>
          <v-tab-item value="facebook-tab">
            <facebook-matches :investigation-mode="true"/>
          </v-tab-item>
          <v-tab-item value="instagram-tab">
            <instagram-matches :investigation-mode="true"/>
          </v-tab-item>
          <v-tab-item value="twitter-tab">
            <twitter-matches :investigation-mode="true"/>
          </v-tab-item>
          <v-tab-item value="linkedin-tab">
            <linkedin-matches :investigation-mode="true"/>
          </v-tab-item>
          <v-tab-item value="flickr-tab">
            <flickr-matches :investigation-mode="true"/>
          </v-tab-item>
          <v-tab-item value="youtube-tab">
            <youtube-matches :investigation-mode="true"/>
          </v-tab-item>
          <v-tab-item value="web-tab">
            <WebSearchMatches :investigation-mode="true"/>
          </v-tab-item>
        </v-tabs-items>
      </v-tabs>
      <investigation-progress
        :allow-traits="subject ? subject.allow_trait: false"
        :comments="questionComments"
        :current-question="currentQuestion + 1"
        :questions="customQuestions"/>
      <add-comment
        :platform="getPlatform"
        :report="subject.current_report ? subject.current_report: {}"
        :report-id="subject ? subject.current_report.id: ''"/>
    </div>
  </v-layout>
</template>
<style>
.v-tabs__item:hover {
  text-decoration: none;
}
</style>
<script>
import { mapGetters } from 'vuex'
import ProfileImage from '~/components/ProfileImage'
import FacebookMatches from '~/components/FacebookMatches'
import InstagramMatches from '~/components/InstagramMatches'
import TwitterMatches from '~/components/TwitterMatches'
import LinkedinMatches from '~/components/LinkedinMatches'
import PinterestMatches from '~/components/PinterestMatches'
import FlickrMatches from '~/components/FlickrMatches'
import YoutubeMatches from '~/components/YoutubeMatches'
import WebSearchMatches from '~/components/WebSearchMatches'
import InvestigationQuestions from '~/components/InvestigationQuestions'
import InvestigationProgress from '~/components/InvestigationProgress'
import AddComment from '~/components/AddComment'

export default {
  layout: 'investigation',
  components: {
    FacebookMatches,
    InstagramMatches,
    TwitterMatches,
    LinkedinMatches,
    PinterestMatches,
    FlickrMatches,
    YoutubeMatches,
    WebSearchMatches,
    InvestigationQuestions,
    ProfileImage,
    InvestigationProgress,
    AddComment
  },
  head() {
    return {
      title: 'Investigation :: Farosian'
    }
  },
  data() {
    return {
      clipped: true,
      drawer: true,
      fixed: false,
      currentPlatform: null,
      custQstns: [],
      tabValue: null,
      swapPlatforms: false,
      getPlatform: null,
      swapPlatform: false
    }
  },
  computed: {
    ...mapGetters({
      subject: 'subject/subject',
      questions: 'investigation/questions',
      platformConfigs: 'global-weights/weights',
      investigateDialog: 'investigation/investigateDialog',
      currentQuestion: 'investigation/currentQuestion',
      generalComments: 'investigation/generalComment'
    }),
    customQuestions: {
      get: function() {
        let current = this.currentQuestion
        let platform = this.questions[current]
          ? this.questions[current].platform
          : ''

        let qs = this.questions.filter(function(sample) {
          return sample.platform === platform
        })
        return this.custQstns.length > 0 ? this.custQstns : qs
      },
      set: function(value) {
        let ref = this
        this.custQstns = this.questions.filter(function(sample) {
          ref.getPlatform = sample.platform
          return sample.platform === value
        })
      }
    },
    questionComments: {
      get: function() {
        let current = this.currentQuestion
        let platform = this.questions[current]
          ? this.questions[current].platform
          : ''
        let questions = this.platformConfigs.filter(function(obj) {
          return obj.social_platform === platform
        })
        return questions && questions.length > 0
          ? questions[0].std_comments
          : []
      }
    },
    tabVal: {
      get: function() {
        //Daniel

        //check which part must change the comment eg:button or change of question
        if (this.swapPlatform === true) {
          this.setPlatform()
          this.clearPlatformSwap()
        } else {
          this.setPlatformTab()
          this.clearPlatformSwap()
        }

        let value = this.tabValue
        let current = this.currentQuestion
        if (
          value === null &&
          this.platformConfigs &&
          this.platformConfigs[0] &&
          this.currentQuestion === 0
        ) {
          value = this.platformConfigs[0].social_platform + '-tab'
        } else if (this.currentQuestion > 0 && this.questions[current]) {
          value = this.questions[current].platform + '-tab'
        }

        return value
      },
      set: function(value) {
        if (this.currentQuestion + 1 !== this.questions.length) {
          this.tabValue = value
        } else {
          this.tabValue = this.questions[0] ? this.questions[0].platform : null
        }
      }
    },
    breads: {
      get: function() {
        let bread = [
          {
            text: 'Investigation',
            disabled: true,
            href: ''
          },
          {
            text: this.questions[this.currentQuestion]
              ? this.questions[this.currentQuestion].platform.toUpperCase()
              : '',
            disabled: true,
            href: ''
          }
        ]
        return bread
      }
    }
  },
  async fetch({ store, params }) {
    await store.dispatch('investigation/queryQuestions')
  },
  mounted() {
    this.$store.dispatch('subject/getAll', this.$route.params.id).catch(() => {
      this.$toast.error('Could not get the specified subject')
    })
    this.$store.dispatch('global-weights/queryConfig').catch(() => {
      this.$toast.error('Could not get the specified weights')
    })
    this.firstQuestion()
    this.setTab()

    let currentQuestion = this.currentQuestion
    this.setPlatform(this.questions[currentQuestion].platform)
  },
  methods: {
    setPlatform() {
      // Checks if Platform is set

      if (this.customQuestions[0] && this.customQuestions[0].platform) {
        this.getPlatform = this.customQuestions[0].platform
      } else {
        this.getPlatform = ''
      }
    },
    setPlatformTab() {
      let current = this.currentQuestion

      // Checks if Platform is set

      if (this.questions[current] && this.questions[current].platform) {
        this.getPlatform = this.questions[current].platform
        this.swapPlatform = false
      } else {
        this.getPlatform = ''
      }
    },
    clearPlatformSwap() {
      this.swapPlatform = false
    },
    changePlatform(platform) {
      this.swapPlatform = true
      this.customQuestions = platform
      this.setPlatform()
    },
    setTab() {
      this.tabValue = this.tabVal
    },
    attrValue(platform) {
      return '#' + platform + '-tab'
    },
    checkNumQuestions(platform) {
      let qs = this.questions.filter(function(sample) {
        return sample.platform === platform
      })

      return qs.length
    },
    checkAnsQuestions(platform) {
      let qs = this.questions.filter(function(sample) {
        return (
          sample.platform === platform &&
          sample.answers.length > 0 &&
          sample.answers[0].answer.trim().length > 0
        )
      })

      return qs.length
    },
    firstQuestion() {
      let first =
        this.currentQuestion === 0 &&
        ((this.questions[this.currentQuestion].answers &&
          this.questions[this.currentQuestion].answers.length === 0) ||
          this.questions[this.currentQuestion].answers === undefined)
      if (first) {
        this.answerFirst(this.currentQuestion)
      }
      return first
    },
    sendAnswer(answer, question) {
      this.$store
        .dispatch('investigation/updateAnswer', {
          activeQuestion: question,
          answer
        })
        .catch(e => {
          this.$toast.error('Could not update answer data. ' + e)
        })
    },
    answerFirst(current_question) {
      let answer = {
        answer: ' ',
        question: { id: this.questions[current_question].id },
        subject: { id: this.subject.id },
        report: { id: this.subject.current_report.id }
      }
      this.sendAnswer(answer, this.questions[current_question])
    },
    handleAnswer(answer) {
      if (answer.dirty !== true) {
        this.next()
        return
      }
      delete answer.dirty

      // Set Subject
      answer.subject = {
        id: this.$route.params.id
      }

      // Set Report
      answer.report = {
        id: this.subject.current_report.id
      }

      this.$store
        .dispatch('investigation/updateAnswer', {
          activeQuestion: this.questions.find(
            question => question.id === answer.question.id
          ),
          answer
        })
        .then(() => {
          this.next()
        })
        .catch(e => {
          this.$toast.error('Could not update answer data. ' + e)
        })
    },
    handleGeneralComment(generalComment) {
      if (generalComment.dirty !== true) {
        return
      }
      delete generalComment.dirty

      // Set Subject
      generalComment.subject = {
        id: this.$route.params.id
      }

      // Set Report
      generalComment.report = {
        id: this.subject.current_report.id
      }

      this.$store
        .dispatch('investigation/updateAnswer', {
          activeQuestion: null,
          answer: generalComment
        })
        .then(() => {
          this.$toast.success('General comment successfully saved.')
        })
        .catch(e => {
          this.$toast.error('Could not update answer data. ' + e)
        })
    },
    next() {
      let nextQuestion = this.currentQuestion + 1
      if (
        this.questions[nextQuestion] &&
        this.questions[nextQuestion].answers &&
        this.questions[nextQuestion].answers.length === 0
      ) {
        this.answerFirst(nextQuestion)
      }
      this.setTab()
      this.$store.commit('investigation/CHANGE_CURRENT_QUESTION', nextQuestion)
    },
    previous() {
      this.custQstns = []
      this.$store.commit(
        'investigation/CHANGE_CURRENT_QUESTION',
        this.currentQuestion - 1
      )
    },
    setCurrentQuetion(val) {
      let index = this.questions.findIndex(x => x.id === val)
      this.$store.commit('investigation/CHANGE_CURRENT_QUESTION', index)
      this.custQstns = []
      this.setTab()
    },
    reset() {
      this.custQstns = []
      this.$store.commit('investigation/CHANGE_CURRENT_QUESTION', 0)
    }
  }
}
</script>
