<template>
  <div class="text-center">
    <v-dialog
      div
      class="text-center">
      <v-dialog
        v-model="dialog"
        fullscreen
        hide-overlay
        transition="dialog-bottom-transition">
        <v-card>
          <v-layout
            row
            wrap/>
          <div class="text-center">
            <v-dialog
              v-model="dialog"
              fullscreen
              hide-overlay
              transition="dialog-bottom-transition">
              <v-card>

                <v-toolbar
                  grey
                  color="grey lighten-5">
                  <v-list-tile-title>NOTEWORTHY FINDINGS</v-list-tile-title>
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

                <v-layout
                  row
                  wrap>

                  <v-flex md4>

                    <table width="550px">
                      <tr
                        v-for="proof in proofs"
                        :key="proof.value">
                        <td>

                          <v-flex
                            md6
                          >

                            <v-card-text>
                              <v-row>
                                <v-img
                                  :src="`${blogStoragePath}/profile-images/${proof.proof_storage.subject.blob_folder}/${proof.proof_storage.image_file}`"
                                  :lazy-src="``"
                                  class="mouseOver grey lighten-2"
                                  aspect-ratio="1.3"
                                  @click="getData(proof.id)"
                                >
                                  <template v-slot:placeholder>
                                    <v-row
                                      class="fill-height ma-0"
                                      align="center"
                                      justify="center"
                                    />
                                  </template>
                                </v-img>
                              </v-row>
                              <br>
                              <v-btn
                                @click="deleteProof(proof.proof_storage.id)"
                              >
                                <i class="material-icons">
                                  delete
                                </i> Delete Proof
                              </v-btn>

                              <v-divider/>
                            </v-card-text>
                          </v-flex>
                        </td>

                      </tr>
                    </table>

                  </v-flex>
                  <v-flex
                    v-if="showProof == true"
                    md6>
                    <Proof
                      :show-poof="editView"
                      :comments="comments"
                      :get-proof="proofget"
                      :allow-traits="allowTraits"
                    />

                  </v-flex>

                  <v-flex
                    v-if="showProof === false"
                    md6>
                    <br>
                    <br>
                    <br>
                    <v-card
                      max-width="344"
                      class="mx-auto"
                    >
                      <v-card-title><h1>Please click on image. </h1></v-card-title>
                    </v-card>

                  </v-flex>
                </v-layout>
                <v-divider/>
              </v-card>
            </v-dialog>
          </div>
</v-card></v-dialog></v-dialog></div></template>

<script>
import { mapGetters } from 'vuex'

import Proof from '~/components/proof/Proof'

export default {
  components: {
    Proof
  },
  props: {
    allowTraits: {
      type: Boolean,
      default: true
    },
    comments: {
      type: Array,
      default: () => [{}]
    },
    answerId: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      dialog: false,
      creativity: 0,
      proofget: null,
      communicationSkills: 0,
      businessWritingWritingAbility: 0,
      professionalImage: 0,
      professionalEngagement: 0,
      networkReach: 0,
      networkEngagement: 0,
      teamworkCollaboration: 0,
      proofComment: '',
      editView: false,
      showProof: false
    }
  },
  computed: {
    ...mapGetters({
      proofs: 'question/proofs'
    }),
    blogStoragePath() {
      return process.env.blogStoragePath
    }
  },
  watch: {
    answerId: {
      handler: function() {
        this.editView = false
        this.showProof = false
        this.$store.dispatch('question/getProofByAnswer', this.answerId)
      },
      deep: true
    }
  },
  mounted() {
    process.env.blogStoragePath = this.$config.blogStoragePath
  },
  methods: {
    getData(id) {
      this.editView = true
      this.showProof = true
      let pr = this.proofs.filter(function(pf) {
        return pf.id === id
      })
      this.proofget = pr.length > 0 ? pr[0] : {}
    },
    show(id) {
      this.dialog = true
    },
    hide() {
      this.dialog = false
    },
    deleteProof(id) {
      var r = confirm('Warning You are about to delete Proof')
      if (r == true) {
        this.$store
          .dispatch('question/deleteProof', id)
          .then(() => {
            this.$toast.success('Deleted Proof')
            this.$router.go()
          })
          .catch(() => {
            this.$toast.error('There was an error Deleting Proof')
          })
      }
    }
  }
}
</script>

<style scoped>
.mouseOver {
  cursor: pointer;
}

.mouseOver :hover {
  border: green 3px solid;
  -webkit-box-shadow: 7px 13px 5px -6px rgba(0, 0, 0, 0.27) !important;
  -moz-box-shadow: 7px 13px 5px -6px rgba(0, 0, 0, 0.27) !important;
  box-shadow: 7px 13px 5px -6px rgba(0, 0, 0, 0.27) !important;

  -webkit-box-shadow: 3px 3px 5px 6px #ccc; /* Safari 3-4, iOS 4.0.2 - 4.2, Android 2.3+ */
  -moz-box-shadow: 3px 3px 5px 6px #ccc; /* Firefox 3.5 - 3.6 */
  box-shadow: 3px 3px 5px 6px #ccc; /* Opera 10.5, IE 9, Firefox 4+, Chrome 6+, iOS 5 */
}
</style>
