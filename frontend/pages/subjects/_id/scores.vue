<template>
  <div class="text-center">
    <v-dialog
      v-model="dialog"
      fullscreen
      hide-overlay
      transition="dialog-bottom-transition"
    >
      <v-toolbar
        grey
        color="grey lighten-5">
        <v-btn
          :disabled="!isFormValid"
          @click="updateScores">
          Update Scores
        </v-btn>
        <span
          class="putInline"
          style="float:right !important;"/>
        <v-list-tile-title>Edit Report</v-list-tile-title>
        <v-spacer/>
        <v-toolbar-items>
          <v-btn
            flat
            small
            router
            @click="exitPage">
            <v-icon class="mr-1">fa fa-sign-out</v-icon>
            Exit
          </v-btn>
        </v-toolbar-items>
      </v-toolbar>
      <v-card>
        <div class="container">
          <v-card class="padding5">
            <h2>Current Scores</h2>
            <v-layout>
              <v-flex
                md4
                class="padding5">
                <v-card-title primary-title><h2>Platform Unweighted</h2></v-card-title>
                <v-layout
                  v-for="(platform, key1) in reportScore.platforms"
                  :key="'10' + key1"
                  row
                  wrap
                  class="padding5"
                >
                  <v-flex
                    md4
                    class="padding5">
                    {{ key1 }}
                  </v-flex>
                  <v-flex
                    md4
                    class="padding5">
                    {{ platform.unweighted_platform_score_rounded }}
                  </v-flex>
                </v-layout>
                <hr>
                <v-layout class="padding5">
                  <v-flex
                    md4
                    class="padding5">
                    Total Risk
                  </v-flex>
                  <v-flex
                    md4
                    class="padding5">
                    {{ reportScore.risk_score }}%
                  </v-flex>
                </v-layout>
                <hr>
              </v-flex>
              <v-flex
                md4
                class="padding5"
              >

                <v-card-title primary-title><h2>Platform Weighted</h2></v-card-title>
                <v-layout
                  v-for="(platform2, key2) in reportScore.platforms"
                  :key="'10' + key2"
                  row
                  wrap
                  class="padding5"
                >
                  <v-flex
                    md5
                    class="padding5">
                    {{ key2 }}
                  </v-flex>
                  <v-flex
                    md5
                    class="padding5">
                    {{ platform2.weighted_platform_score_rounded }}
                  </v-flex>
                </v-layout>
                <hr>
                <v-layout class="padding5">
                  <v-flex
                    md5
                    class="padding5">
                    Weighted Social Media
                  </v-flex>
                  <v-flex
                    md4
                    class="padding5">
                    {{ reportScore.weighted_social_media_score_round }}%
                  </v-flex>
                </v-layout>
                <hr>
              </v-flex>
              <v-flex
                md4
                class="padding5">

                <v-card-title primary-title><h2>Overall Behavior</h2></v-card-title>
                <v-layout
                  v-for="(scores, key3) in reportScore.overall_behavior_scores"
                  :key="'10' + key3"
                  row
                  wrap
                  class="padding5"
                >
                  <v-flex
                    md8
                    class="padding5"
                  >
                    <div>{{ removeUnderScores(key3) }}</div>
                  </v-flex>
                  <v-flex
                    md2
                    class="padding5">
                    {{ scores }}
                  </v-flex>
                </v-layout>
              </v-flex>
            </v-layout>
          </v-card>
          <br>
          <v-form v-model="isFormValid">
            <v-card class="padding5">
              <h2>Edited Scores</h2>
              <v-layout>
                <v-flex
                  md4
                  class="padding5">
                  <v-card-title primary-title><h2>Platform Unweighted</h2></v-card-title>
                  <v-layout
                    v-for="(platform, key1) in reportScoreUpdated.platforms"
                    :key="'10' + key1">
                    <v-layout>
                      <v-flex md2>
                        <span class="grey--text">{{ key1 }}</span>
                      </v-flex>
                      <v-flex md6>
                        <v-text-field
                          :value="platform.unweighted_platform_score_rounded"
                          label="Unweighted Scores"
                          filled
                          @input="updateStateScores( key1,'unweighted_platform_score_rounded', $event)"
                        />
                      </v-flex>
                    </v-layout>
                  </v-layout>
                  <v-layout>
                    <v-flex md3>
                      <v-list-tile-title><span class="grey--text">Total Risk</span></v-list-tile-title>
                    </v-flex>
                    <v-flex md4>
                      <v-text-field
                        :rules="riskScoreRules"
                        :value="reportScoreUpdated.risk_score"
                        :min="minValueRiskScore"
                        :max="maxValueRiskScore"
                        label="Risk Score %"
                        filled
                        @input="updateState('risk_score', $event)"
                      />
                    </v-flex>
                  </v-layout>
                </v-flex>
                <v-flex md4>

                  <v-card-title primary-title><h2>Platform Weighted</h2></v-card-title>
                  <v-layout
                    v-for="(platform, key2) in reportScoreUpdated.platforms"
                    :key="'10' + key2">
                    <v-layout>
                      <v-flex md4>
                        <span class="grey--text">{{ key2 }}</span>
                      </v-flex>
                      <v-flex md4>
                        <v-text-field
                          :disabled="true"
                          :value="platform.weighted_platform_score_rounded"
                          label="weighted Scores"
                          filled
                          @input="updateStateScores( key2,'weighted_platform_score_rounded', $event)"
                        />
                      </v-flex>
                    </v-layout>
                  </v-layout>
                  <v-layout>
                    <v-flex md4>
                      <v-list-tile-title><span class="grey--text">Weighted Social Media</span></v-list-tile-title>
                    </v-flex>
                    <v-flex md4>
                      <v-text-field
                        :disabled="true"
                        :value="reportScoreUpdated.weighted_social_media_score"
                        label="Weighted Social Media Score"
                        filled
                        @input="updateState('weighted_social_media_score', $event)"
                      />
                    </v-flex>
                  </v-layout>
                </v-flex>
                <v-flex
                  md4
                  class="padding5">

                  <v-card-title primary-title><h2>Overall Behavior Scores</h2></v-card-title>
                  <v-layout
                    v-for="(scores, key3) in reportScoreUpdated.overall_behavior_scores"
                    :key="'10' + key3"
                    row
                    wrap
                  >
                    <v-flex
                      md6
                      class="padding5">
                      {{ removeUnderScores(key3) }}
                    </v-flex>
                    <v-flex md2>
                      <v-text-field
                        :value="scores"
                        filled
                        @input="updateStateBehaviorScores( key3, $event)"
                      />
                    </v-flex>
                  </v-layout>
                </v-flex>
              </v-layout>
            </v-card>
            <span>
              <v-checkbox
                :input-value="reportScoreUpdated.over_write_report_scores === null ? false: reportScoreUpdated.over_write_report_scores"
                label="Overwrite Scores"
                @change="updateState('over_write_report_scores', $event)"
              />
              <v-btn
                @click="testScores()">
                Test Scores
              </v-btn>
            </span>

            <br>
            <br>
          </v-form>
        </div>
      </v-card>

      <v-card
        v-if="reportScoreOverriddenShow == true"
        class="padding5">

        <div class="container">
          <h2>Overridden Score</h2>
          <v-layout>
            <v-flex
              md4
              class="padding5">
              <v-card-title primary-title><h2>Platform Unweighted</h2></v-card-title>
              <v-layout
                v-for="(platform, key1) in reportScoreOverridden.platforms"
                :key="'10' + key1">
                <v-layout>
                  <v-flex md2>
                    <span class="grey--text">{{ key1 }}</span>
                  </v-flex>
                  <v-flex md6>
                    <v-text-field
                      :disabled="true"
                      :value="platform.unweighted_platform_score_rounded"
                      label="Unweighted Scores"
                      filled
                      @input="updateStateScores( key1,'unweighted_platform_score_rounded', $event)"
                    />
                  </v-flex>
                </v-layout>
              </v-layout>
              <v-layout>
                <v-flex md3>
                  <v-list-tile-title><span class="grey--text">Total Risk</span></v-list-tile-title>
                </v-flex>
                <v-flex md4>
                  <v-text-field
                    :disabled="true"
                    :rules="riskScoreRules"
                    :value="reportScoreOverridden.risk_score"
                    :min="minValueRiskScore"
                    :max="maxValueRiskScore"
                    label="Risk Score %"
                    filled
                  />
                </v-flex>
              </v-layout>
            </v-flex>
            <v-flex md4>

              <v-card-title primary-title><h2>Platform Weighted</h2></v-card-title>
              <v-layout
                v-for="(platform, key2) in reportScoreOverridden.platforms"
                :key="'10' + key2">
                <v-layout>
                  <v-flex md4>
                    <span class="grey--text">{{ key2 }}</span>
                  </v-flex>
                  <v-flex md4>
                    <v-text-field
                      :disabled="true"
                      :value="platform.weighted_platform_score_rounded"
                      label="weighted Scores"
                      filled
                    />
                  </v-flex>
                </v-layout>
              </v-layout>
              <v-layout>
                <v-flex md4>
                  <v-list-tile-title><span class="grey--text">Weighted Social Media</span></v-list-tile-title>
                </v-flex>
                <v-flex md4>
                  <v-text-field
                    :disabled="true"
                    :value="reportScoreOverridden.weighted_social_media_score"
                    label="Weighted Social Media Score"
                    filled
                    @input="updateState('weighted_social_media_score', $event)"
                  />
                </v-flex>
              </v-layout>
            </v-flex>
            <v-flex
              md4
              class="padding5">


              <v-card-title primary-title><h2>Overall Behavior Scores</h2></v-card-title>
              <v-layout
                v-for="(scores, key3) in reportScoreOverridden.overall_behavior_scores"
                :key="'10' + key3"
                row
                wrap
              >
                <v-flex
                  md6
                  class="padding5">
                  {{ removeUnderScores(key3) }}
                </v-flex>
                <v-flex md2>
                  <v-text-field
                    :disabled="true"
                    :value="scores"
                    filled
                  />
                </v-flex>
              </v-layout>

            </v-flex>

          </v-layout>
        </div>
      </v-card>

    </v-dialog>
  </div>
</template>
<script>
import { mapGetters } from 'vuex'

export default {
  async fetch({ store, route }) {
    //await store.dispatch('report/buildMaths', route.params.id)
    await store.dispatch('report/getScore', route.params.id)
    await store.dispatch('report/getScoreUpdated', route.params.id)
  },
  data() {
    return {
      isFormValid: false,
      dialog: true,
      platform: '',
      updatedReport: [],
      minValueRiskScore: 0,
      maxValueRiskScore: 100,
      reportScoreOverriddenShow: false
    }
  },
  computed: {
    ...mapGetters({
      reportScore: 'report/reportScore',
      reportScoreUpdated: 'report/reportScoreUpdated',
      reportScoreOverridden: 'report/reportScoreOverridden'
    }),
    riskScoreRules() {
      return [
        v => !!v || 'Risk Score is required and should be a number',
        v =>
          v >= this.minValueRiskScore ||
          'Risk Score should be greater than ' + this.minValueRiskScore,
        v =>
          v <= this.maxValueRiskScore ||
          'Risk Score should be less than ' + this.maxValueRiskScore
      ]
    }
  },
  mounted() {
    this.$store.dispatch('report/getScore', this.$route.params.id)
    this.$store.dispatch('report/getScoreUpdated', this.$route.params.id)
    this.updatedReport = this.reportScoreUpdated
  },
  methods: {
    removeUnderScores(value) {
      return value.replace(/_/g, ' ')
    },
    getFormattedText(value) {
      return _.startCase(value)
    },
    exitPage() {
      this.dialog = false
      this.$router.push('../' + this.$route.params.id)
    },
    updateState(prop, value) {
      console.log('prop')
      console.log(prop)

      console.log('value')
      console.log(value)
      this.$store
        .dispatch('report/updateOverWrittenScoresMain', { prop, value })
        .catch(() => {
          console.log('Could not update Scores data')
        })
    },
    updateStateScores(platform, prop, value) {
      let updateScore = {
        platform: platform,
        scoreType: prop,
        score: value
      }

      console.log(updateScore)

      this.$store
        .dispatch('report/updateOverWrittenScores', {
          dataInfo: {
            platform: platform,
            scoreType: prop,
            score: value
          }
        })
        .catch(() => {
          console.log('Could not update Scores data')
        })
    },
    updateStateBehaviorScores(prop, value) {
      console.log('prop')
      console.log(prop)

      console.log('value')
      console.log(value)

      this.$store
        .dispatch('report/updateOverWrittenBehaviorScores', {
          dataInfo: {
            overall_behavior_scores: 'overall_behavior_scores',
            scoreType: prop,
            score: value
          }
        })
        .catch(() => {
          console.log('Could not update Scores data')
        })
    },
    testScores() {
      this.$store
        .dispatch('report/overRideScoresTest', this.$route.params.id)
        .then(response => {
          this.reportScoreOverriddenShow = true

          //this.dialog = false
          // this.$router.push('../' + this.$route.params.id)
          // this.$toast.success('Scores successfully updated!')
        })
        .catch(() => {
          this.$toast.error('Scores Not updated!')
        })
    },
    updateScores() {
      this.$store
        .dispatch('report/updateWrittenScores', this.$route.params.id)
        .then(response => {
          this.dialog = false
          this.$router.push('../' + this.$route.params.id)
          this.$toast.success('Scores successfully updated!')
        })
        .catch(() => {
          this.$toast.error('Scores Not updated!')
        })
    },
    updatePdf() {
      let id = this.$route.params.id
      this.$store
        .dispatch('report/pdfRebuild', id)
        .then(data => {
          // this.loading = false

          this.dialog = false
          // this.$router.push('../' + this.$route.params.id)
          // this.$toast.success('Generated PDF successfully')
        })
        .catch(error => {
          this.$toast.error('Could not generate report. ' + error)
          this.loading = false
        })
    }
  }
}
</script>
<style scoped>
.padding20 {
  padding: 20px;
}

.padding5 {
  padding: 5px;
}

.float-left {
  float: left;
}

.updateButton {
  float: right;
  margin: 100px 0px 0px 100px;
}
</style>
