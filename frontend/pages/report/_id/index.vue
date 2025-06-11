<template>
  <v-app
    id="report">
    <div class="container">
      <v-btn
        color="teal darken-3"
        class="white--text backButton "
        @click="backButton()"
      >
        Back
      </v-btn>
    </div>

    <!-- Subject -->
    <v-card>
      <v-card-title
        class="headline lighten-2"
        primary-title
      >
        PLATFORM SUMMARY
      </v-card-title>
      <v-card-title
        class="headline lighten-2"
      >
        Subject: {{ subject.first_name + ' ' + subject.last_name }}
      </v-card-title>
    </v-card>
    <div class="findingsDivider"/>
    <v-card>
      <v-card-text>
        {{ subject.current_report.risk_comment }}

      </v-card-text>
    </v-card>
    <div class="findingsDivider"/>
    <v-card>
      <v-layout
        v-if="showReportScore"
        row
        wrap>
        <v-flex
          v-if="reportScore"
          md6>
          <v-card>
            <v-card-title primary-title><h1>Report Score (Platform Unweighted)</h1></v-card-title>
            <v-card-text>
              <v-list>
                <v-list-tile
                  v-for="(platform, key1) in reportScore.platforms"
                  :key="'10' + key1"
                >
                  <v-list-tile-avatar>
                    <v-icon
                      :color="(platform.unweighted_platform_score > 0.5 ) ? 'red' : 'green'"
                      large>{{ platform.unweighted_platform_score > 0.5 ? 'clear' : 'done_all' }}
                    </v-icon>
                  </v-list-tile-avatar>
                  <v-list-tile-content>
                    <v-list-tile-title><span class="grey--text">{{ key1 }}</span></v-list-tile-title>
                  </v-list-tile-content>
                  <v-list-tile-action>
                    <v-list-tile-title>
                      <span :class="(platform.unweighted_platform_score > 0.5 ) ? 'red--text' : 'green--text'">
                        {{ platform.unweighted_platform_score }}
                      </span>
                    </v-list-tile-title>
                  </v-list-tile-action>
                </v-list-tile>
                <hr>
                <hr>
                <v-list-tile>
                  <v-list-tile-avatar>
                    <v-icon
                      :color="(reportScore.risk_score >= 35 ) ? 'red' : 'green'"
                      large>{{ reportScore.risk_score >= 35 ? 'clear': 'done_all' }}
                    </v-icon>
                  </v-list-tile-avatar>
                  <v-list-tile-content>
                    <v-list-tile-title><span class="grey--text">Total Risk Score</span></v-list-tile-title>
                  </v-list-tile-content>
                  <v-list-tile-action>
                    <v-list-tile-title>
                      <span :class="(reportScore.risk_score >= 35) ? 'red--text' : 'green--text'">
                        {{ reportScore.risk_score }}%
                      </span>
                    </v-list-tile-title>
                  </v-list-tile-action>
                </v-list-tile>
              </v-list>
            </v-card-text>
          </v-card>
        </v-flex>
        <v-flex
          v-else
          md6>
          <h1>Report Score</h1>
          <h3 class="font-weight-light red--text">No score, please make sure all questions have scores and are
          answered</h3>
        </v-flex>
        <v-flex
          v-if="reportScore"
          md6>
          <v-card>
            <v-card-title primary-title><h1>Report Score (Platform Weighted)</h1></v-card-title>
            <v-card-text>
              <v-list>
                <v-list-tile
                  v-for="(platform, key2) in reportScore.platforms"
                  :key="'20' + key2"
                >
                  <v-list-tile-avatar>
                    <v-icon
                      :color="(platform.weighted_platform_score > 0.5 ) ? 'red' : 'green'"
                      large>{{ platform.weighted_platform_score > 0.5 ? 'clear' : 'done_all' }}
                    </v-icon>
                  </v-list-tile-avatar>
                  <v-list-tile-content>
                    <v-list-tile-title><span class="grey--text">{{ key2 }}</span></v-list-tile-title>
                  </v-list-tile-content>
                  <v-list-tile-action>
                    <v-list-tile-title>
                      <span :class="(platform.weighted_platform_score > 0.5 ) ? 'red--text' : 'green--text'">
                        {{ platform.weighted_platform_score }}
                      </span>
                    </v-list-tile-title>
                  </v-list-tile-action>
                </v-list-tile>
                <hr>
                <hr>
                <v-list-tile>
                  <v-list-tile-avatar>
                    <v-icon
                      :color="(reportScore.weighted_social_media_score <= 450) ? 'red' : 'green'"
                      large>{{ (450 >= reportScore.weighted_social_media_score) ? 'clear': 'done_all' }}
                    </v-icon>
                  </v-list-tile-avatar>
                  <v-list-tile-content>
                    <v-list-tile-title><span class="grey--text">Weighted Social Media Score</span></v-list-tile-title>
                  </v-list-tile-content>
                  <v-list-tile-action>
                    <v-list-tile-title>
                      <span :class="(reportScore.weighted_social_media_score <= 450) ? 'red--text' : 'green--text'">
                        {{ reportScore.weighted_social_media_score }}
                      </span>
                    </v-list-tile-title>
                  </v-list-tile-action>
                </v-list-tile>
              </v-list>
            </v-card-text>
          </v-card>
        </v-flex>
        <v-flex
          v-else
          md6>
          <h1>Report Score</h1>
          <h3 class="font-weight-light red--text">No score, please make sure all questions have scores and are
          answered</h3>
        </v-flex>
      </v-layout>
    </v-card>
    <v-card>
      <div
        v-for="getReport in reportInfo"
        :key="'1A' + getReport">
        <div
          v-for="(getComment, getCommentIndex3) in getReport.comment"
          :key="'1A' + getCommentIndex3">
          <div class="findingsDivider"/>
          <v-container
            grid-list-md
          >
            <v-layout
              row
              wrap
              xs12
            >
              <v-flex xs12>
                <span class=" text-md-center platformHeader"> {{ getComment.name }} </span>
              </v-flex>
              <v-flex
                xs2>
                <div
                  v-for="(getIcon, getIconIndex) in getReport.socialMediaIcon"
                  :key="'2B' + getIconIndex">
                  <div v-if="getIconIndex == getComment.name">
                    <img
                      :src="getIcon"
                      class="socialMediaIcon">
                  </div>
                </div>
              </v-flex>
              <v-flex
                xs5>
                <p class="lightGrey">FINDINGS</p>
                <ul class="nobull">
                  <div
                    v-for="(getComments, getCommentIndex ) in getComment.comments"
                    :key="'3C' + getCommentIndex">
                    <li>
                      {{ getComments.comment }}
                    </li>
                  </div>
                </ul>
              </v-flex>
              <v-flex
                xs2>
                <p class="lightGrey">SCORE</p>
                <div
                  v-for="(getScore, social) in getReport.platform_scores"
                  :key="'4D' + social">
                  <div v-if="social == getComment.name">
                    <p class="font-size18"> {{ getScore.weighted_platform_score }} </p>
                  </div>
                </div>
              </v-flex>
            </v-layout>
            <br>
            <br>
            <v-layout
              row
              wrap
              md12
            >
              <v-flex
                md2/>
              <v-flex>
                <p class="lightGrey font-size18">QUESTION AND ANSWERS</p>
                <div
                  v-for="(getQuestionsGet, socialIndex) in getReport.report.questions.platforms"
                  :key="'2A' + socialIndex">
                  <div v-if="socialIndex == getComment.name">
                    <div
                      v-for="(questions, questionsIndex ) in getQuestionsGet"
                      :key="questionsIndex">
                      <div class="clearBoth"/>
                      <ul class="nobull">
                        <li>
                          <v-layout>
                            <v-flex class="question"><b>Question:</b></v-flex>
                            <v-flex
                              wrap
                              class="questionWidth"
                            > <p class="">{{ questions.question }}</p>
                            </v-flex>
                          </v-layout>
                        </li>
                      </ul>
                      <div class="clearBoth"/>
                      <ul class="nobull">
                        <li>
                          <v-layout>
                            <v-flex>
                              <b>Answer:</b>
                            </v-flex>
                            <v-flex class="text-left">
                              <span
                                v-for="(answers, answers2 ) in questions"
                                :key="'2B' + answers2">

                                <span
                                  v-for="(answer, answer1 ) in answers"
                                  :key="'2C' + answer1">
                                  {{ getFormattedText(answer.answer) }}
                                </span>

                              </span>
                            </v-flex>
                          </v-layout>
                        </li>
                      </ul>
                      <br>
                      <br>
                    </div>
                  </div>
                </div>
              </v-flex>
              <v-flex
                md2/>
            </v-layout>
            <br>
            <br>
            <v-layout
              row
              wrap
              md12
            >
              <v-flex
                md2/>
              <v-flex
                md5>
                <p class="lightGrey font-size18">NOTEWORTHY FINDINGS</p>
                <div
                  v-for="(getQuestions1, socialIndex1) in getReport.report.questions.platforms"
                  :key="'A1'+ socialIndex1">
                  <div v-if="socialIndex1 == getComment.name">
                    <div
                      v-for="(questions1, questionsIndex1) in getQuestions1"
                      :key="'A10' + questionsIndex1">
                      <div class="clearBoth"/>
                      <ul class="nobull">
                        <li>
                          <div
                            v-for="(answers1, answersIndex1) in questions1"
                            :key="'B2' + answersIndex1">
                            <div
                              v-for="(answer1, answerIndex1) in answers1"
                              :key="'C3'+ answerIndex1">
                              <div
                                v-for="proof1 in answer1.proofs"
                                :key="'C4'+ proof1">
                                <b>Comment:</b> {{ proof1.comment }}
                              </div>
                            </div>
                          </div>
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
              </v-flex>
              <v-flex
                md2>
                <p class="lightGrey font-size18">Proof</p>
                <div
                  v-for="(getQuestions2, socialIndex2) in getReport.report.questions.platforms"
                  :key="'A'+ socialIndex2">
                  <div v-if="socialIndex2 == getComment.name">
                    <div
                      v-for="(questions2, questions2Index) in getQuestions2"
                      :key="'B'+ questions2Index">
                      <div class="clearBoth"/>
                      <div
                        v-for="(answers2, answersIndex2) in questions2"
                        :key="answersIndex2">
                        <div
                          v-for="(answer2, index2) in answers2"
                          :key="'D'+ index2">
                          <div
                            v-for="proof2 in answer2.proofs"
                            :key="'E'+ proof2">
                            <v-img
                              :src="`${blogStoragePath}/profile-images/${proof2.proof_storage.subject.blob_folder}/${proof2.proof_storage.image_file}`"
                              :lazy-src="`${blogStoragePath}/profile-images/${proof2.proof_storage.subject.blob_folder}/${proof2.proof_storage.image_file}`"
                              class="noteworthyFindings noteworthyFindingsImage proofImage"/>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </v-flex>
            </v-layout>
          </v-container>
        </div>
      </div>
    </v-card>
    <v-dialog
      v-model="dialog"
      max-width="700px"
    >
      <v-card>
        <v-card-title class="headline">Edit Risk Comment</v-card-title>
        <v-card-text>
          <v-textarea
            v-model="riskComment"
            :value="subject.current_report.risk_comment"
            label="Risk Comments"
            outline
            tabindex="11"
          />

          <!--{{ subject.current_report.risk_comment }}-->
        </v-card-text>
        <v-card-actions>
          <v-spacer/>
          <v-btn
            color="red lighten-3"
            text
            @click="dialog = false"
          >
            Close
          </v-btn>
          <v-btn
            color="green lighten-3"
            text
            @click="updateRiskComment()"
          >
            Update
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-app>
</template>
<script>
import { mapGetters } from 'vuex'
import { error } from 'util'
import _ from 'lodash'

export default {
  head() {
    return {
      title: 'Report :: Farosian'
    }
  },
  data() {
    return {
      platform: '',
      dialog: false,
      riskComment: '',
      getQuestions: ''
    }
  },
  computed: {
    ...mapGetters({
      reportInfo: 'report/reportInfo',
      subject: 'subject/subject',
      questions: 'report/questions',
      report: 'report/report',
      generalComment: 'report/generalComment',
      reportScore: 'report/reportScore'
    }),
    showGeneralComments: {
      get: function() {
        return this.report ? !this.report.hide_general_comments : true
      },
      set: function(val) {
        this.$store.commit('report/TOGGLE_GENERAL_COMMENTS', !val)
      }
    },
    showReportScore: {
      get: function() {
        return this.report ? !this.report.hide_report_score : true
      },
      set: function(val) {
        this.$store.commit('report/TOGGLE_REPORT_SCORE', !val)
      }
    },
    blogStoragePath() {
      return this.$config.blogStoragePath
    }
  },
  mounted() {
    process.env.blogStoragePath = this.$config.blogStoragePath
    this.getData()

    this.riskComment = this.subject.current_report.risk_comment
  },
  methods: {
    backButton() {
      this.$router.push('/subjects/' + this.$route.params.id)
    },
    getFormattedText(value) {
      return _.startCase(value)
    },
    async updateRiskComment() {
      let data = {
        id: this.report.id,
        risk_comment: this.riskComment
      }

      this.$store
        .dispatch('subject/addRiskComment', data)
        .then(response => {
          this.dialog = false
          this.$toast.success('Risk Comment saved successfully')
        })
        .catch(error => {
          this.$toast.error('Failed to update Risk Comment')
        })
    },
    editRisk() {
      this.dialog = true
    },
    async getData() {
      await this.$store
        .dispatch('report/getReportInfo', this.$route.params.id)
        .then(response => {})
    },
    round(value) {
      return parseFloat(value).toFixed(2)
    },
    updateComment(id, value) {
      if (!value) {
        this.$store.dispatch('report/showComment', id).then(() => {
          this.getReport()
        })
      } else {
        this.$store.dispatch('report/hideComment', id).then(() => {
          this.getReport()
        })
      }
    },
    getReport() {
      this.$store.dispatch('report/queryReport')
    },
    toggleGeneralComments() {
      this.$store.dispatch('report/toggleGeneralComments', this.report.id)
    },
    toggleReportScore() {
      this.$store.dispatch('report/toggleReportScore', this.report.id)
    }
  },
  fetch({ store, params, query }) {
    const reportId = query.r ? query.r : null
    if (store.state.subject.subject === null) {
      return store
        .dispatch('subject/get', params.id)
        .then(() => {
          return store.dispatch('report/queryReport', reportId)
        })
        .catch(() => {})
    } else {
      return store.dispatch('report/queryReport', reportId).catch(() => {})
    }
  }
}
</script>
<style scoped>
.capitalize {
  text-transform: uppercase;
}

.platformHeader {
  font-size: 25px;
  text-decoration: underline;
}

.normal-border {
  border: 1px solid lightgrey;
}

.pad-top {
  style: 'padding-top: 20px !important;';
}

/*new sytles*/

.formHeaders {
  font-size: 25px;
  /*text-decoration: underline;*/
  font-weight: 500;
  font-family: Arial, Helvetica, sans-serif;
}

.lightGrey {
  color: #bfbfbf;
  font-size: 18px;
}

.lightGreyHr {
  border-color: #bfbfbf;
}

.tdPadding {
  padding: 5px 15px 0 0;
  font-size: 18px;
}

.tratesName {
  padding: 5px 15px 0 10px;
  font-size: 18px;
}

.tratesScores {
  border-right: 2px solid black;
  border-left: 2px solid black;
  padding: 5px 15px 0 15px;
}

.clearBoth {
  clear: both;
}

.findingHeader {
  text-decoration: underline;
}

.nobull {
  list-style-type: none;
  padding: 0 0 0 0;
  margin: 0 0 0 0;
  font-size: 18px;
  float: left !important;
}

.findingsDivider {
  width: 100%;
  border-bottom: 1px solid #aaaaaa;
  border-top: 1px solid #aaaaaa;
  margin: 0 0 10px 0;
  padding: 0 0 0 0;
  height: 5px;
  background-color: #bfbfbf;
  clear: both;
}

.noteworthyFindings {
  margin: 10px 0 0 0;
  font-size: 18px;
}

.noteworthyFindingsHr {
  padding: 0 0 0 0 !important;
  margin: 5px 0 5px 0 !important;
  border: 1px solid #bfbfbf;
}

.noteworthyFindingsImage {
  margin-left: 55px !important;
}

.proofImage {
  height: 190px;
  width: 320px;
  border: 1px solid lightgrey;
}

.socialMediaIcon {
  height: 80px;
}

.th {
  font-size: 18px;
}

.searchIcon {
  color: grey;
}

.riskComment {
  font-size: 18px;
}

.font-size18 {
  font-size: 18px;
}
.answerQuestionSpacing {
  margin: 0px 10px 0px 0px !important;
}

.question {
  color: steelblue;
}

.questionWidth {
  width: 650px !important;
}

.backButton {
  float: right !important;
  width: 150px !important;
  margin: 0px 0px 10px 0px;
}
</style>
