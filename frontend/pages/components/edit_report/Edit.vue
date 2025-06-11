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
            @click="dialog = false">
            <v-icon class="mr-1">fa fa-sign-out</v-icon>
            Exit
          </v-btn>
        </v-toolbar-items>
      </v-toolbar>
      <v-card>
        <div class="container">
          <v-card-title primary-title>
            <h1>Current Scores</h1>
          </v-card-title>
          <br>
          <v-card>
            <v-layout
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
                          <v-list-tile-title><span class="grey--text">Weighted Social Media Score</span>
                          </v-list-tile-title>
                        </v-list-tile-content>
                        <v-list-tile-action>
                          <v-list-tile-title>
                            <span
                              :class="(reportScore.weighted_social_media_score <= 450) ? 'red--text' : 'green--text'">
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
          <v-card-title primary-title>
            <h1>Edited Scores</h1>
          </v-card-title>
          <br>
          <v-card class="padding20">
            <v-layout>
              <v-flex md6>
                <!--  Grid 1 start-->
                <v-card-title primary-title><h1>Report Score (Platform Unweighted)</h1></v-card-title>
                <v-layout
                  v-for="(platform, key1) in reportScoreUpdated.platforms"
                  :key="'10' + key1">
                  <v-layout>
                    <v-flex md2>
                      <span class="grey--text">{{ key1 }}</span>
                    </v-flex>
                    <v-flex md6>
                      <v-text-field
                        :value="platform.unweighted_platform_score"
                        label="Unweighted Scores"
                        filled
                        @input="updateStateScores( key1,'unweighted_platform_score', $event)"
                      />
                    </v-flex>
                  </v-layout>
                </v-layout>
                <v-layout>
                  <v-flex md3>
                    <v-list-tile-title><span class="grey--text">Total Risk Score</span></v-list-tile-title>
                  </v-flex>
                  <v-flex md4>
                    <v-text-field
                      :value="reportScore.risk_score"
                      label="Risk Score %"
                      filled
                      @input="updateState('risk_score', $event)"
                    />
                  </v-flex>
                </v-layout>
                <!-- Grid 1 end-->
              </v-flex>
              <v-flex md6>
                <!-- Grid 2 start-->
                <v-card-title primary-title><h1>Report Score (Platform Weighted)</h1></v-card-title>
                <v-layout
                  v-for="(platform, key2) in reportScoreUpdated.platforms"
                  :key="'10' + key2">
                  <v-layout>
                    <v-flex md2>
                      <span class="grey--text">{{ key2 }}</span>
                    </v-flex>
                    <v-flex md6>
                      <v-text-field
                        :value="platform.weighted_platform_score"
                        label="weighted Scores"
                        filled
                        @input="updateStateScores( key2,'weighted_platform_score', $event)"
                      />
                    </v-flex>
                  </v-layout>
                </v-layout>
                <v-layout>
                  <v-flex md3>
                    <v-list-tile-title><span class="grey--text">Weighted Social Media Score</span></v-list-tile-title>
                  </v-flex>
                  <v-flex md4>
                    <v-text-field
                      :value="reportScoreUpdated.weighted_social_media_score"
                      label="Weighted Social Media Score"
                      filled
                      @input="updateState('weighted_social_media_score', $event)"
                    />
                  </v-flex>
                </v-layout>
                <!--  Grid 2 end-->
              </v-flex>
            </v-layout>
            <span>
              <v-checkbox
                :input-value="reportScoreUpdated.over_write_report_scores === null ? false: reportScoreUpdated.over_write_report_scores"
                label="Overwrite Scores"
                @change="updateState('over_write_report_scores', $event)"
              />
            </span>
          </v-card>
        </div>
      </v-card>
    </v-dialog>
  </div>
</template>
<script>
import { mapGetters } from 'vuex'

export default {
  data() {
    return {
      dialog: false,
      platform: '',
      updatedReport: []
    }
  },
  computed: {
    ...mapGetters({
      reportScore: 'report/reportScore',
      reportScoreUpdated: 'report/reportScoreUpdated'
    })
  },
  mounted() {
    this.$store.dispatch('report/getScore', this.$route.params.id)
    this.$store.dispatch('report/getScoreUpdated', this.$route.params.id)
    this.updatedReport = this.reportScoreUpdated
  },
  methods: {
    show() {
      this.dialog = true
    },
    updateState(prop, value) {
      this.$store
        .dispatch('report/updateOverWrittenScoresMain', { prop, value })
        .catch(() => {
          console.log('Could not update Scores data')
        })
    },
    updateStateScores(platform, prop, value) {
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
    updateScores() {
      this.$store
        .dispatch('report/updateWrittenScores', this.$route.params.id)
        .then(response => {
          this.dialog = false
          this.$toast.success('Scores successfully updated!')
        })
        .catch(() => {
          this.$toast.error('Scores Not updated!')
        })
    }
  }
}
</script>
<style scoped>
.padding20 {
  padding: 20px;
}
</style>
