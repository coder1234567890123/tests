<template>
  <div
    id="app"
    class="container">
    <v-btn
      class="white--text"
      color="teal darken-3"
      style="float: right"
      @click="backButton()"
    >
      Back
    </v-btn>
    <h1 class="title mb-3">Calculation for : {{
      subjectFirstName !== null ? subjectFirstName + " " + subjectLastName :
      'Loading...'
    }}</h1>
    <br>
    <v-card class="padding40">
      <v-layout>
        <v-flex md6>
          <!-- Start-->
          <span class="mainHeaders">Calculation Breakdown</span>
          <br>
          <span class="mainCalFontSize">Weighted Risk score: <span class="mathShow">{{
            reportScores.risk_score
          }} </span></span><br>
          <span class="mainCalFontSize">Weighted Risk score Pre round: <span
            class="mathShow">{{ reportScores.risk_score_pre_round }} </span></span><br>
          <span class="mainCalFontSize">Weighted Social Media Score : <span class="mathShow">{{
            reportScores.weighted_social_media_score_round
          }} </span></span><br>
          <span class="mainCalFontSize">Weighted Social Media Score Pre round : <span class="mathShow">{{
            reportScores.weighted_social_media_score
          }} </span></span><br>
          <v-layout
            v-for="cal in calBreakDown"
            :key="cal">
            <v-flex
              class="subject-info"
              md11>
              <v-subheader class="justify-center"> {{ getFormattedText(cal.platform) }}</v-subheader>
              <v-layout
                row
                wrap
              >
                <v-flex
                  md4
                >
                  <span class="indexValues">Answer Total Score</span>
                </v-flex>
                <v-flex
                  md4
                  py-2>
                  {{ cal.answer_cal_all }}
                </v-flex>
                <v-flex
                  md4
                >
                  Total
                </v-flex>
                <v-flex
                  md4
                >
                  <span class="indexValues">Score</span>
                </v-flex>
                <v-flex
                  md4
                >
                  {{ cal.score }}
                </v-flex>
                <v-flex
                  md4
                >
                  Total * <span class="mathShow">{{ cal.post_platform_scoring_metric }}</span>
                </v-flex>
                <v-flex
                  md4
                >
                  <span class="indexValues">Final score - Unweighted</span>
                </v-flex>
                <v-flex
                  md4
                >
                  {{ cal.final_score_unweighted }}
                </v-flex>
                <v-flex
                  md4
                >
                  Score / <span class="mathShow">{{ cal.pre_platform_scoring_metric }}</span>
                </v-flex>
                <v-flex
                  md4
                >
                  <span class="indexValues">Weighted pre scoring</span>
                </v-flex>
                <v-flex
                  md4
                >
                  {{ cal.weighted_pre_scoring }}
                </v-flex>
                <v-flex
                  md4
                >
                  <span class="mathShow">{{ cal.global_weight }}</span> / (Other Weights) * Final score
                </v-flex>
              </v-layout>
            </v-flex>
          </v-layout>
          <!-- end-->
        </v-flex>
        <v-flex
          md6
        >
          <span class="mainHeaders">Behavior Scores</span>
          <div
            v-for="(overall_behavior_scores,index) in reportScores.overall_behavior_scores"
            :key="overall_behavior_scores">
            <v-layout>
              <v-flex
                class="break"
                md8
                py-2>
                <span class="indexValues">{{ getFormattedText(index) }}</span>
              </v-flex>
              <v-flex
                md4
              >
                {{ overall_behavior_scores }}
              </v-flex>
            </v-layout>
          </div>
          <br>
          <hr>
          <br>
          <span class="mainHeaders">
            <span class="material-icons">edit</span>
            Behavior Scores Override
          </span>
          <div
            v-for="(behavior_scores_override, index ) in reportScoresOverwrite.overall_behavior_scores "
            :key="behavior_scores_override">
            <v-layout>
              <v-flex
                class="break"
                md8
                py-2>
                <span class="indexValues">
                  {{ getFormattedText(index) }} </span>
              </v-flex>
              <v-flex
                md2
              >
                {{ behavior_scores_override }}
              </v-flex>
              <!--              </v-layout>-->
            </v-layout>
          </div>
          <br>
          <hr>
        </v-flex>
      </v-layout>
    </v-card>
    <div class="clearBoth subdivider"/>
    <v-card class="padding40">
      <v-layout>
        <v-flex md6>
          <v-card-title primary-title><h3>Platform Unweights</h3></v-card-title>
          <v-layout
            v-for="finalWeights in calculations.final_weights"
            :key="finalWeights">
            <v-layout>
              <v-flex md6>
                <span>{{ getFormattedText(finalWeights.social_platform) }}</span>
              </v-flex>
              <v-flex md2>
                <span>{{ finalWeights.global_usage_weighting }}</span>
              </v-flex>
            </v-layout>
          </v-layout>
        </v-flex>
        <v-flex md6>
          <v-card-title primary-title><h3>Global Score</h3></v-card-title>
          <v-layout
            v-for="scoringConfig in calculations.scoring_config"
            :key="scoringConfig">
            <v-layout>
              <v-flex md6>
                <span>{{ getFormattedText(scoringConfig.score_type) }}</span>
              </v-flex>
              <v-flex md2>
                <span>{{ scoringConfig.val }}</span>
              </v-flex>
            </v-layout>
          </v-layout>
        </v-flex>
      </v-layout>
    </v-card>
    <div class="clearBoth subdivider"/>
    <v-card class="padding40">
      <v-layout>
        <v-flex md6>
          <!-- Start-->
          <span class="mainHeaders">Questions / Answers Scores</span>
          <div
            v-for="(answers, index ) in answersGetAll"
            :key="answers">
            <v-subheader class="justify-center"> {{ getFormattedText(index) }}</v-subheader>
            <v-layout
              v-for="answersData in answers"
              :key="answersData">
              <v-flex
                class="break"
                md8
                py-2>
                <span
                  :title="answersData.question"
                  class="pointer"> {{ resizeString(answersData.question) }} </span>
              </v-flex>
              <v-flex
                md2
              >
                {{ answersData.answer_score }}
              </v-flex>
            </v-layout>
            <br>
            <hr>
          </div>
          <!-- end-->
        </v-flex>
        <v-flex
          class="marginLeft40"
          md6>
          <!-- Start-->
          <span class="mainHeaders">Original</span>
          <table width="100%">
            <tr>
              <td/>
              <td>
                <b>W Round</b>
              </td>
              <td>
                <b>Weighted</b>
              </td>
              <td>
                <b>UnW Round</b>
              </td>
              <td>
                <b>Unweighted</b>
              </td>
            </tr>
            <tr
              v-for="(platform, index) in reportScores.platforms"
              :key="platform">
              <td>
                {{ getFormattedText(index) }}
              </td>
              <td>
                <span class="align-center">
                  {{ platform.weighted_platform_score_rounded }}
                </span>
              </td>
              <td>
                <span class="align-center">
                  {{ platform.weighted_platform_score }}
                </span>
              </td>
              <td>
                <span class="align-center">
                  {{ platform.unweighted_platform_score_rounded }}
                </span>
              </td>
              <td>
                <span class="align-center">
                  {{ platform.unweighted_platform_score }}
                </span>
              </td>
            </tr>
          </table>
          <div class="clearBoth"/>
          <br>
          <hr>
          <br>
          <span class="mainHeaders">
            <span class="material-icons">edit</span>
            Overwritten
          </span>
          <table width="100%">
            <tr>
              <td/>
              <td>
                <b>UnWeighted</b>
              </td>
            </tr>
            <tr
              v-for="(platform, index) in reportScoresOverwrite.platforms"
              :key="platform">
              <td>
                {{ getFormattedText(index) }}
              </td>
              <td>
                {{ platform.weighted_platform_score_rounded }}
              </td>
            </tr>
          </table>
          <br>
          <span class="indexValues">Risk score: </span>
          <span> {{ reportScoresOverwrite.risk_score }}</span>
          <br>
          <span class="indexValues"> Weighted Social Media Score: </span>
          <span>
            {{ reportScoresOverwrite.weighted_social_media_score }}
          </span>
          <br>
          <br>
          <hr>
          <br>
          <span class="mainHeaders">Behavior Scores Overview</span>
          <div
            v-for="(getBehaviorScoresOverview, index ) in behaviorScoresOverview"
            :key="getBehaviorScoresOverview">
            <v-subheader class="justify-center"> {{ getFormattedText(index) }}</v-subheader>
            <span class="mainHeaders"> Behavior Scores </span>
            <br>
            <div
              v-for="(platforms, indexP) in reportScores.platforms "
              :key="platforms">
              <div v-if=" indexP === index">
                <v-layout
                  v-for="(platformsScores, indexScore) in platforms.behavior_scores"
                  :key="platformsScores">
                  <v-flex
                    class="break"
                    md6
                    py-2>
                    <span class="indexValues">{{ getFormattedText(indexScore) }} </span>
                  </v-flex>
                  <v-flex
                    md2
                  >
                    {{ platformsScores }}
                  </v-flex>
                </v-layout>
              </div>
            </div>
            <br>
            <span class="mainHeaders">
              <span class="material-icons">
                list
              </span>
              Breakdown
            </span>
            <br>
            <br>
            <div
              v-for="behaviorScoresOverview in getBehaviorScoresOverview"
              :key="behaviorScoresOverview">
              <span class="indexValues"> Question: </span>
              <span class="mathShow"> {{ behaviorScoresOverview.question.question }}</span>
              <span
                v-for="(question) in behaviorScoresOverview.question"
                :key="question">
                <span> {{ question.question }}</span>
              </span>
              <br>
              <span class="indexValues">Count: </span> <span class="mathShow">{{ behaviorScoresOverview.count }}</span>
              <div
                v-for="(getScores) in behaviorScoresOverview.behaviour_score"
                :key="getScores">

                <br>
                <span class="indexValues">{{ getScores.comment }}</span>
                <v-layout
                  v-for="(scores, index) in getScores.behaviour_scores"
                  :key="scores"
                  class="breakDownSpacing"
                >
                  <v-flex
                    class="break"
                    md6
                    py-2>
                    <span> {{ getFormattedText(index) }} </span>
                  </v-flex>
                  <v-flex
                    md2
                  >
                    {{ scores.score }}
                  </v-flex>
                  <v-flex
                    md2
                    class="mathShow"
                  >
                    {{ scores.cal }}
                  </v-flex>
                </v-layout>
                <br>
              </div>
            </div>
            <br>
            <hr>
          </div>
        </v-flex>
      </v-layout>
    </v-card>
    <div class="clearBoth subdivider"/>
  </div>
</template>
<script>
import _ from 'lodash'

export default {
  inject: ['$validator'],
  components: {},
  head() {
    return {
      title: 'Calculation :: Farosian'
    }
  },
  data() {
    return {
      subjectFirstName: '',
      subjectId: '',
      subjectLastName: '',
      subjectStatus: '',
      calculations: [],
      reportScores: [],
      reportScoresOverwrite: [],
      behaviorScoresOverview: [],
      calBreakDown: [],
      answersGetAll: []
    }
  },
  async mounted() {
    this.getData()
    this.getCalculations()
  },
  methods: {
    resizeString(str) {
      let length = 30
      let ending
      if (length == null) {
        length = 30
      }
      if (ending == null) {
        ending = ' ...'
      }
      if (str.length > length) {
        return str.substring(0, length - ending.length) + ending
      } else {
        return str
      }
    },
    getFormattedText(value) {
      return _.startCase(value)
    },
    backButton() {
      this.$router.push('/subjects/' + this.$route.params.id)
    },
    async getData() {
      await this.$store
        .dispatch('subject/messageQueueSubject', this.$route.params.id)
        .then(response => {
          this.subjectFirstName = response.first_name
          this.subjectLastName = response.last_name
          this.subjectStatus = response.status
        })
        .catch(() => {
          this.$toast.error('Could Not get subject.')
        })
    },
    async getCalculations() {
      await this.$store
        .dispatch('calculation/getCalculations', this.$route.params.id)
        .then(response => {
          this.calculations = response
          this.reportScores = response.report_scores
          this.reportScoresOverwrite = response.report_scores_overwrite
          this.behaviorScoresOverview = response.behavior_scores_overview
          this.calBreakDown = response.cal
          this.answersGetAll = response.answers
        })
        .catch(() => {
          this.$toast.error('Could Not get Calculation.')
        })
    }
  }
}
</script>
<style>
.capitalize {
  text-transform: capitalize;
}

.file a {
  color: white;
}

.profile-img {
  text-align: center;
  border: none !important;
}

.profile-img img {
  max-width: 100%;
  max-height: 100%;
}

.profile-img .file {
  position: relative;
  overflow: hidden;
  margin-top: -15%;
  width: 100%;
  border: none;
  border-radius: 0;
  font-size: 15px;
  background: #212529b8;
}

.profile-img .file input {
  position: absolute;
  opacity: 0;
  right: 0;
  top: 0;
}

.v-card .subject-info .flex {
  font-size: 15px;
  margin-bottom: -1px;
  border-bottom: 1px solid #ddd;
  border-top: 1px solid #ddd;
}

.v-subheader {
  font-size: 18px;
}

.btn-size {
  width: 14.5rem;
  background-color: rgba(0, 0, 0, 0.14);
}

.approved {
  color: darkgreen;
  font-weight: bold;
}

.notApproved {
  color: darkred;
}

.comment {
  color: lightblue;
}

.requestWarning {
  margin: 0px 0px 0px 20px !important;
  color: darkred;
}

ul {
  list-style-type: none;
}

.nameWarnings {
  color: black;
}

.warningColor {
  color: darkred;
}

.clearBoth {
  clear: both;
}

.buttonWidth {
  width: 97% !important;
  padding: 0px 0px 0px 0px !important;
  margin: 5px 10px 0px 5px !important;
  /*background-color: #17685b !important;*/
}

.marginLeft40 {
  margin-left: 40px !important;
}

.padding40 {
  padding: 40px;
}

.subdivider {
  padding: 10px 0px 10px 0px !important;
}

.pointer {
  cursor: pointer;
}

.tableNameWith {
  width: 200px;
}

.floatLeft {
  float: left !important;
}

.mathShow {
  color: dodgerblue;
  font-width: bold;
}

.break {
  flex-basis: 100%;
  height: 0;
}

.tablePlatform {
  width: 100px !important;
}

.mainCalFontSize {
  font-size: 18px;
}

.mainHeaders {
  font-size: 20px;
  font-weight: 450;
  margin: 0px 0px 15px 0px !important;
}

.indexValues {
  font-weight: 450;
}

.breakDownSpacing {
  margin: 0px 0px 0px 10px !important;
  padding: 0px 0px 0px 10px;
}
</style>
