<template>
  <v-app>
    <v-navigation-drawer
      :clipped="clipped"
      v-model="drawer"
      fixed
      app>
      <v-list
        class="pt-0">
        <v-list-tile class="tile">
          <v-list-tile-action>
            <v-icon>search</v-icon>
          </v-list-tile-action>
          <v-list-tile-content>
            <v-list-tile-title>Investigation mode</v-list-tile-title>
            Section: {{ question.platform }}
          </v-list-tile-content>
        </v-list-tile>
        <!-- Screenshot plugin start -->
        <div class="text-center">
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
                      <span
                        class="putInline"
                        style="float:right !important;">
                        <v-btn
                          v-if="saveButton == false"
                          class="float-right"
                          disabled
                          @click="saveImages()">
                          <v-progress-circular
                            indeterminate
                            color="primary"
                          />
                          Uploading
                        </v-btn>
                        <v-btn
                          v-if="saveButton == true"
                          @click="saveImages()">Save
                        </v-btn>
                      </span>
                      <v-list-tile-title>CROPPING TOOL</v-list-tile-title>
                      <v-spacer/>
                      <v-toolbar-items>
                        <v-btn
                          flat
                          small
                          router
                          @click="exitCroppsection()">
                          <v-icon class="mr-1">fa fa-sign-out</v-icon>
                          Exit
                        </v-btn>
                      </v-toolbar-items>
                    </v-toolbar>
                    <v-container-fluid class="grey lighten-5">
                      <v-card-text>
                        <v-breadcrumbs
                          :items="items"
                          divider=">"/>
                      </v-card-text>
                      <v-layout
                        row
                        wrap>
                        <v-flex md6>
                          <v-card-text>
                            <croppa
                              v-model="myCroppa"
                              :width="550"
                              :height="320"
                              :show-remove-button="false"
                              :prevent-white-space="true"
                              :show-loading="true"
                              initial-position="center"
                              @zoom="onZoom"
                              @new-image-drawn="onNewImage"
                            >
                              <img
                                slot="initial"
                                :src="proofImage"
                              >
                              <br><br>
                            </croppa>
                            <br><br>
                            <input
                              :min="sliderMin"
                              :max="sliderMax"
                              v-model="sliderVal"
                              :step="sliderStep"
                              type="range"
                              @input="onSliderChange">
                          </v-card-text>
                          <v-card-actions>
                            <v-btn @click="generateImage()">Generate</v-btn>
                          </v-card-actions>
                          <div class="editor-tools">
                            <div class="tool-undo">
                              <rotate-ccw-icon 
                                :size="size_icon" 
                                @click="undo()"/>
                            </div>
                            <div class="tool-redo">
                              <rotate-cw-icon 
                                :size="size_icon" 
                                @click="redo()"/>
                            </div>
                            <div class="tool-trash">
                              <trash-2-icon 
                                :size="size_icon" 
                                @click="deleteEditable()"/>
                            </div>
                            <div class="tool-freeDrawing">
                              <edit-2-icon
                                :size="size_icon" 
                                 
                                @click="freeDrawing()"/>
                            </div>
                            <div class="tool-addText">
                              <italic-icon 
                                :size="size_icon" 
                                @click="addText()"/>
                            </div>
                            <div class="tool-addCircle">
                              <circle-icon 
                                :size="size_icon" 
                                @click="addCicle()"/>
                            </div>
                            <div class="tool-addSquare">
                              <square-icon 
                                :size="size_icon" 
                                class="icon"
                                @click="addSquare()"/>
                            </div>
                            <div class="tool-arrow">
                              <arrow-up-right-icon 
                                :size="size_icon" 
                                @click="addArrow()"/>
                            </div>
                            <div class="tool-drag">
                              <move-icon 
                                :size="size_icon" 
                                @click="drag()"/>
                            </div>
                            <div class="tool-crop">
                              <maximize-icon 
                                v-if="stateCrop" 
                                :size="size_icon" 
                                @click="crop()"/>
                              <check-icon 
                                v-else 
                                :size="size_icon" 
                                @click="applyCrop()"/>
                            </div>
                            <div class="tool-upload">
                              <label for="file">
                                <upload-icon :size="size_icon"/>
                              </label>
                              <input
                                id="file"
                                ref="file"
                                :v-model="file"
                                type="file"
                                accept="image/*"
                                @change="uploadImg"
                              >
                            </div>
                            <div class="save-upload">
                              <save-icon 
                                :size="size_icon" 
                                @click="saveImg()"/>
                            </div>
                          </div>

                          <Editor 
                            ref="editor"
                            :canvas-width="canvasWidth" 
                            :canvas-height="canvasHeight" 
                            class="upper-canvas croppaImages" 
                            editor-id="canvasId"/>
                          <div class="commentSpacing"/>
                        </v-flex>
                        <v-flex md6>
                          <v-card-text>
                            
                            <img
                              :src="croppedImage"
                              class="croppaImages"
                              width="550"
                              height="320">
                            <div class="commentSpacing"/>
                            <v-combobox
                              v-model="proofComment"
                              :items="commentsx"
                              :rows="5"
                              label="Select a comment or create a new one"
                              placeholder="Comment"/>
                            <br>
                            <br>
                          </v-card-text>
                          <v-card-actions/>
                        </v-flex>
                      </v-layout>
                    </v-container-fluid>
                    <v-card-text>
                      <v-card v-if="$parent.subject && $parent.subject.allow_trait">
                        <span class="putInline checkboxAlign">
                          <v-checkbox
                            v-model="traitsCheck"
                          />
                        </span>
                        <span class="putInline textAlign">
                          <b>Trait:  </b>
                        </span>
                        <span class="putInline textAlign">
                          <i>To add traits to an image check the box</i>
                        </span>
                      </v-card>
                      <table
                        v-if="($parent.subject && $parent.subject.allow_trait) && traitsCheck == true"
                        width="300">
                        <tr>
                          <th>Creativity</th>
                          <th>Network Reach</th>
                          <th>Network Engagement</th>
                          <th>Professional Image</th>
                          <th>Communication Skills</th>
                          <th>Teamwork & Collaboration</th>
                          <th>Professional Engagement</th>
                          <th>Business Writing & Writing Ability</th>
                        </tr>
                        <tr>
                          <!-- creativity start-->
                          <td>
                            <v-btn
                              @click="creativity += 1">
                              <i class="material-icons">
                                add
                              </i>
                            </v-btn>
                            <div class="traitSpacesTop"/>
                            <v-text-field
                              v-model="creativity"
                              label="0"
                              single-line
                              solo
                              disabled
                            />
                            <v-btn
                              @click="creativity -= 1">
                              <i class="material-icons">
                                remove
                              </i>
                            </v-btn>
                          </td>
                          <!-- creativity end-->

                          <!-- networkReach start-->
                          <td>
                            <v-btn
                              @click="networkReach += 1">
                              <i class="material-icons">
                                add
                              </i>
                            </v-btn>
                            <div class="traitSpacesTop"/>
                            <v-text-field
                              v-model="networkReach"
                              label="0"
                              single-line
                              solo
                              disabled
                            />
                            <v-btn
                              @click="networkReach -= 1">
                              <i class="material-icons">
                                remove
                              </i>
                            </v-btn>
                          </td>
                          <!-- networkReach end-->

                          <!-- networkEngagement start-->
                          <td>
                            <v-btn
                              @click="networkEngagement += 1">
                              <i class="material-icons">
                                add
                              </i>
                            </v-btn>
                            <div class="traitSpacesTop"/>
                            <v-text-field
                              v-model="networkEngagement"
                              label="0"
                              single-line
                              solo
                              disabled
                            />
                            <v-btn
                              @click="networkEngagement -= 1">
                              <i class="material-icons">
                                remove
                              </i>
                            </v-btn>
                          </td>
                          <!-- networkEngagement end-->

                          <!-- professionalImage start-->
                          <td>
                            <v-btn
                              @click="professionalImage += 1">
                              <i class="material-icons">
                                add
                              </i>
                            </v-btn>
                            <div class="traitSpacesTop"/>
                            <v-text-field
                              v-model="professionalImage"
                              label="0"
                              single-line
                              solo
                              disabled
                            />
                            <v-btn
                              @click="professionalImage -= 1">
                              <i class="material-icons">
                                remove
                              </i>
                            </v-btn>
                          </td>
                          <!-- professionalImage end-->

                          <!-- communicationSkills start-->
                          <td>
                            <v-btn
                              @click="communicationSkills += 1">
                              <i class="material-icons">
                                add
                              </i>
                            </v-btn>
                            <div class="traitSpacesTop"/>
                            <v-text-field
                              v-model="communicationSkills"
                              label="0"
                              single-line
                              solo
                              disabled
                            />
                            <v-btn
                              @click="communicationSkills -= 1">
                              <i class="material-icons">
                                remove
                              </i>
                            </v-btn>
                          </td>
                          <!-- communicationSkills end-->

                          <!-- teamworkCollaboration start-->
                          <td>
                            <v-btn
                              @click="teamworkCollaboration += 1">
                              <i class="material-icons">
                                add
                              </i>
                            </v-btn>
                            <div class="traitSpacesTop"/>
                            <v-text-field
                              v-model="teamworkCollaboration"
                              label="0"
                              single-line
                              solo
                              disabled
                            />
                            <v-btn
                              @click="teamworkCollaboration -= 1">
                              <i class="material-icons">
                                remove
                              </i>
                            </v-btn>
                          </td>
                          <!-- teamworkCollaboration end-->

                          <!-- professionalEngagement start-->
                          <td>
                            <v-btn
                              @click="professionalEngagement += 1">
                              <i class="material-icons">
                                add
                              </i>
                            </v-btn>
                            <div class="traitSpacesTop"/>
                            <v-text-field
                              v-model="professionalEngagement"
                              label="0"
                              single-line
                              solo
                              disabled
                            />
                            <v-btn
                              @click="professionalEngagement -= 1">
                              <i class="material-icons">
                                remove
                              </i>
                            </v-btn>
                          </td>
                          <!-- professionalEngagement end-->

                          <!-- businessWritingAbility start-->
                          <td>
                            <v-btn
                              @click="businessWritingAbility += 1">
                              <i class="material-icons">
                                add
                              </i>
                            </v-btn>
                            <div class="traitSpacesTop"/>
                            <v-text-field
                              v-model="businessWritingAbility"
                              label="0"
                              single-line
                              solo
                              disabled
                            />
                            <v-btn
                              @click="businessWritingAbility -= 1">
                              <i class="material-icons">
                                remove
                              </i>
                            </v-btn>
                          </td>
                          <!-- businessWritingAbility end-->

                        </tr>
                      </table>
                    </v-card-text>
                  </v-card>
                </v-dialog>
              </div>
              <v-divider/>
            </v-card>
          </v-dialog>
        </div>
        <!-- Screenshot plugin End -->
        <!-- Question -->
        <v-container v-if="question.id && report.status !== 'completed'">
          <!-- Question Text -->
          <v-layout row>
            <v-flex md12>
              <v-subheader class="px-0">Question {{ currentQuestion }}</v-subheader>
            </v-flex>
          </v-layout>
          <v-divider/>
          <v-layout row>
            <v-flex md12>
              <p class="mt-3">{{ question.question }}</p>
            </v-flex>
          </v-layout>
          <v-divider/>
          <!-- Answers Header -->
          <v-layout row>
            <v-flex md12>
              <v-subheader class="px-0">Answers</v-subheader>
            </v-flex>
          </v-layout>
          <v-divider/>
          <!-- Multiple Choice -->
          <v-layout
            v-if="question.answer_type === 'multiple_choice'"
            row>
            <v-flex md12>
              <v-checkbox
                v-for="(option, index) in question.answer_options"
                v-model="answer"
                :key="index"
                :label="option"
                :value="option"
                hide-details/>
            </v-flex>
          </v-layout>
          <!-- Yes/No -->
          <v-layout
            v-if="question.answer_type === 'yes_no'"
            row>
            <v-flex md12>
              <v-radio-group v-model="answer">
                <v-radio
                  label="Yes"
                  value="yes"
                  class="mt-1"/>
                <v-radio
                  label="No"
                  value="no"
                  class="mt-1"/>
              </v-radio-group>
            </v-flex>
          </v-layout>
          <v-divider/>
          <!--          slider do not remove code will add later-->
          <!--          &lt;!&ndash; Text &ndash;&gt;-->
          <!--          <v-layout-->
          <!--            v-if="question.slider === true"-->
          <!--            row>-->
          <!--            <v-flex md12>-->
          <!--              <v-subheader class="px-0">Slider {{ question.slider_values[0] }} - {{ question.slider_values[1] }}-->
          <!--              </v-subheader>-->
          <!--              <hr>-->
          <!--              <v-slider-->
          <!--                v-model="slider_value"-->
          <!--                :min="question.slider_values[0]"-->
          <!--                :max="question.slider_values[1]"-->
          <!--                thumb-label-->
          <!--                append-icon="add"-->
          <!--                prepend-icon="remove"-->
          <!--              />-->
          <!--            </v-flex>-->
          <!--          </v-layout>-->
          <!--          <v-layout-->
          <!--            v-if="question.slider === true"-->
          <!--          >-->
          <!--            <v-flex/>-->
          <!--            <flex>-->
          <!--              <v-text-field-->
          <!--                v-model="slider_value"-->
          <!--                class="liferInput"-->
          <!--                required-->
          <!--              />-->
          <!--            </flex>-->
          <!--          </v-layout>-->
          <!--          <v-divider/>-->
          <!--          &lt;!&ndash; Proceed &ndash;&gt;-->
          <br>
          <br>
          <v-layout row>
            <v-flex
              v-if="currentQuestion > 1"
              md6>
              <v-btn
                block
                class="ma-0"
                @click="this.$parent.previous">prev
              </v-btn>
            </v-flex>
            <v-flex :class="[currentQuestion > 1 ? 'md6' : 'md12']">
              <v-btn
                :disabled="answer == null || answer.trim().length == 0"
                block
                color="teal darken-3"
                class="white--text ma-0"
                @click="submitAnswer">Proceed
              </v-btn>
            </v-flex>
          </v-layout>
          <br>
          <br>
          <v-layout row/>
        </v-container>
        <v-container
          v-else
          class="pt-0">
          <v-layout row>
            <v-flex
              class="text-xs-center mt-4"
              md12>
              <p>You have completed the investigation report.</p>
              <p>What would you like to do next?</p>
              <p
                v-if="questionsLength <= 0"
                class="red--text">Please ensure that you have questions that correspond with
                this subject's profiles and report type</p>
            </v-flex>
          </v-layout>
          <v-layout row>
            <v-flex md12>
              <!--              TODO-->
              <!--              <v-btn-->
              <!--                v-if="report.status !== 'complete'"-->
              <!--                block-->
              <!--                color="teal darken-3 ma-0"-->
              <!--                dark-->
              <!--                @click="generalCommentFlag = !generalCommentFlag">{{ generalComment ? "Edit General Comment": "Add General Comment" }}-->
              <!--              </v-btn>-->
              <v-btn
                v-if="(questionsLength > 0 && report.status !== 'complete')"
                block
                color="darken-3 ma-0  mt-2"
                dark
                @click="this.$parent.reset">Start over
              </v-btn>
              <v-tooltip
                v-if="checkForCurrentComplete"
                slot="append"
                right>
                <v-btn
                  slot="activator"
                  block
                  class="ma-0  mt-2"
                  @click="completeInvestigation">Complete Investigation
                </v-btn>
                <span>Complete investigation for subject. <br><small>NB: No more investigations will be performed for this subject.</small></span>
              </v-tooltip>
              <!--              TODO-->
              <v-btn
                v-if="report.status !== 'completed'"
                block
                color="teal darken-3"
                dark
                class="ma-0  mt-2"
                @click="reportCommentDialog = !reportCommentDialog">Add Report Comments/Notes
              </v-btn>
            </v-flex>
          </v-layout>
          <v-layout
            v-if="generalCommentFlag"
            row
            wrap>
            <v-flex
              md12
              block>
              <v-subheader class="pl-5">General Comment</v-subheader>
            </v-flex>
          </v-layout>
          <v-layout
            v-if="generalCommentFlag"
            row
            wrap>
            <v-flex
              md12
              block>
              <v-textarea
                v-model="gm"
                box
                class="pt-2"
                label="Comment"
              />
              <v-divider/>
            </v-flex>
            <v-flex md12>
              <v-btn
                :disabled="gm == null"
                block
                color="teal darken-3 ma-0"
                class="white--text"
                @click="submitGeneralComment"
              >Save
              </v-btn>
            </v-flex>
          </v-layout>
          <v-layout
            v-if="generalCommentFlag"
            row
            wrap/>
          <v-layout
            v-if="reportComments && reportComments.length > 0"
            row
            wrap>
            <v-flex
              md12
              block>
              <v-subheader class="px-0">Report Comments/Notes</v-subheader>
            </v-flex>
          </v-layout>
          <v-layout
            v-if="report.status !== 'completed' && reportComments && reportComments.length > 0"
            row
            wrap>
            <v-flex
              xs12>
              <v-list>
                <v-list-tile
                  v-for="(note, index) in getReportComments"
                  :key="index">
                  <v-icon
                    small
                    class="left messageIcon"
                  >comment
                  </v-icon>
                  <v-list-tile-content>
                    <v-list-tile-title
                      slot="activator"
                      class="commentList"
                      @click="editReportComment = note; editReportCommentDialog = !editReportCommentDialog, setEditComment(note)">
                    <small>{{ note.comment.slice(0,25) + '...' }}</small></v-list-tile-title>
                  </v-list-tile-content>
                  <v-list-tile-action>
                    <v-icon
                      small
                      class="mt-0 right"
                      @click="deleteReportComment(note.id, index)">delete
                    </v-icon>
                  </v-list-tile-action>
                </v-list-tile>
              </v-list>
            </v-flex>
          </v-layout>
        </v-container>
      </v-list>
    </v-navigation-drawer>
    <v-dialog
      v-model="reportCommentDialog"
      max-width="800px">
      <v-card>
        <v-card-title>
          <span class="headline"> Add Report comment</span>
        </v-card-title>
        <v-container grid-list-md>
          <v-layout wrap>
            <!--            <v-flex>-->
            <v-textarea
              v-model="reportComment"
              placeholder="Add a comment/note for this report"/>
              <!--            </v-flex>-->
              <!--            <v-flex>-->
              <!--              <v-checkbox-->
              <!--                v-model="reportCommentPrivate"-->
              <!--                label="Make comment private"-->
              <!--              />-->
              <!--            </v-flex>-->
          </v-layout>
        </v-container>
        <v-card-actions>
          <v-spacer/>
          <v-btn
            flat
            color="error"
            @click.prevent="reportCommentDialog = !reportCommentDialog">Cancel
          </v-btn>
          <v-btn
            :loading="dialogLoading"
            flat
            color="primary"
            @click.prevent="createReportComment">Add
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog
      v-model="editReportCommentDialog"
      max-width="800px">
      <v-card>
        <v-card-text>
          <v-card-title>
            <span class="headline"> Edit Report comment</span>
          </v-card-title>
          <v-container grid-list-md>
            <v-layout wrap>
              <v-flex>
                <v-textarea
                  v-model="editDialogX.comment"
                  :value="editReportComment.comment"
                  placeholder="Add a comment/note for this report"
                />
              </v-flex>
              <!--              <v-flex>-->
              <!--                <v-checkbox-->
              <!--                  v-model="editDialogX.private"-->
              <!--                  :value="editReportComment.private"-->
              <!--                  label="Make comment private"-->
              <!--                />-->
              <!--              </v-flex>-->
            </v-layout>
          </v-container>
        </v-card-text>
        <v-card-actions>
          <v-spacer/>
          <v-btn
            flat
            color="error"
            @click.prevent="editReportCommentDialog = !editReportCommentDialog">Cancel
          </v-btn>
          <v-btn
            :loading="dialogLoading"
            flat
            color="primary"
            @click.prevent="customUpdate()">Save
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-layout
      row
      justify-center>
      <v-dialog
        v-model="imageDialog"
        persistent
        max-width="600">
        <v-card>
          <v-card-title
            class="headline grey lighten-2"
            primary-title
          >
            Delete Image
          </v-card-title>
          <v-card-text>
            <v-layout
              row
              wrap>
              <v-flex md12>
                <v-img
                  v-if="imageObject"
                  :src="`${blogStoragePath}/profile-images/${imageObject.proof_storage.subject.blob_folder}/${imageObject.proof_storage.image_file}`"
                  aspect-ratio="1.3"
                  cover/>
              </v-flex>
            </v-layout>
          </v-card-text>
          <v-card-actions>
            <v-spacer/>
            <v-btn
              color="red darken-1"
              flat
              @click="deleteProofImage(imageObject)">Delete
            </v-btn>
            <v-btn
              color="primary"
              flat
              @click="imageDialog = false">Cancel
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-layout>
  </v-app>
</template>
<script>
import { mapGetters } from 'vuex'
import Editor from 'vue-image-markup'
import {
  AirplayIcon,
  CircleIcon,
  RotateCcwIcon,
  RotateCwIcon,
  Trash2Icon,
  Edit2Icon,
  ItalicIcon,
  SquareIcon,
  ArrowUpRightIcon,
  MoveIcon,
  MaximizeIcon,
  UploadIcon,
  SaveIcon,
  CheckIcon
} from 'vue-feather-icons'

export default {
  name: 'App',
  components: {
    Editor,
    AirplayIcon, // Icons start
    CircleIcon,
    RotateCcwIcon,
    RotateCwIcon,
    Trash2Icon,
    Edit2Icon,
    ItalicIcon,
    SquareIcon,
    ArrowUpRightIcon,
    MoveIcon,
    MaximizeIcon,
    UploadIcon,
    CheckIcon,
    SaveIcon
  },
  props: {
    question: {
      type: Object,
      default: () => ({})
    },
    currentQuestion: {
      type: Number,
      default: 0
    },
    questionsLength: {
      type: Number,
      default: 0
    },
    report: {
      type: Object,
      default: () => ({})
    },
    commentsx: {
      type: Array,
      default: () => [{}]
    },
    generalComment: {
      type: Object,
      default: () => ({})
    }
  },
  data: function() {
    return {
      items: [
        {
          text: 'Investigation',
          disabled: true
        },
        {
          text: 'Question',
          disabled: true
        },
        {
          text: 'Screenshot Tool',
          disabled: true
        }
      ],
      traitsCheck: true,
      sendQuestionId: '',
      useData: '',
      usePlugin: '',
      proofComment: '',
      proofStorageId: '',
      saveButton: true,
      saveImageToFile: '',
      creativity: 0,
      communicationSkills: 0,
      businessWritingAbility: 0,
      professionalImage: 0,
      professionalEngagement: 0,
      networkReach: 0,
      networkEngagement: 0,
      teamworkCollaboration: 0,
      imageCheck: null,
      croppa: '',
      myCroppa: [],
      imgUrl: '',
      croppedImage: '',
      dataUrl: '',
      sliderVal: '0',
      sliderMin: '0.4',
      sliderMax: '5',
      sliderStep: '.001',
      proofImage: '',
      dialog: false,
      reportCommentPrivate: '',
      myProofs: [],
      newAnswer: null,
      newSliderValue: 0,
      newReportComments: [],
      newGeneralComment: null,
      reportCommentDialog: false,
      editReportCommentDialog: false,
      dialogLoading: false,
      reportComment: '',
      editReportComment: { comment: '', private: '' },
      imageDialog: false,
      imageObject: null,
      proofEmpty: false,
      generalCommentFlag: false,
      reportCommentFlag: false,
      clipped: true,
      drawer: true,
      fixed: false,
      dirty: false,
      editDialogX: { private: '', comment: '' },
      api_url: '',
      size_icon: '2x',
      canvasWidth: '550',
      canvasHeight: '320',
      stateCrop: true,
      // blobUrl: '',
      // blogStoragePath: 'https://stofarosiandev.blob.core.windows.net',
      file: '',
      editor: {
        mode: 'FreeDearaw'
      },
      imageUrl: ''
    }
  },
  computed: {
    ...mapGetters({
      getReportComments: 'investigation/reportComment'
    }),
    answer: {
      get: function() {
        return this.question.answers &&
          this.question.answers.length > 0 &&
          this.newAnswer === null
          ? this.question.answers[0].answer
          : this.newAnswer
      },
      set: function(value) {
        this.dirty = true
        this.newAnswer = value
      }
    },
    slider_value: {
      get: function() {
        return this.question.answers.length > 0 && this.newSliderValue === 0
          ? this.question.answers[0].slider_value
          : this.newSliderValue
      },
      set: function(value) {
        this.dirty = true
        this.newSliderValue = value
      }
    },
    reportComments: {
      get: function() {
        return this.report &&
          this.report.comments &&
          this.report.comments.length > 0 &&
          this.newReportComments.length === 0
          ? this.report.comments
          : this.newReportComments
      },
      set: function(value) {
        this.newReportComments.push(value)
      }
    },
    gm: {
      get: function() {
        return this.generalComment !== null &&
          this.generalComment.answer &&
          this.newGeneralComment === null
          ? this.generalComment.answer
          : this.newGeneralComment
      },
      set: function(value) {
        this.dirty = true
        this.newGeneralComment = value
      }
    },
    proofs: {
      get: function() {
        let currentProofs = []
        if (this.question.id && !this.proofEmpty) {
          currentProofs =
            this.question.answers &&
            this.question.answers.length > 0 &&
            this.myProofs.length === 0
              ? this.question.answers[0].proofs
              : this.myProofs
        }
        return currentProofs
      },
      set: function(value) {
        this.myProofs.push(value)
        this.dirty = true
      }
    },
    generalCommentProofs: {
      get: function() {
        let currentProofs = []
        if (!this.proofEmpty) {
          currentProofs =
            this.generalComment &&
            this.generalComment.id &&
            this.myProofs.length === 0
              ? this.generalComment.proofs
              : this.myProofs
        }
        return currentProofs
      },
      set: function(value) {
        this.myProofs.push(value)
        this.dirty = true
        this.gm = this.gm ? this.gm : ''
      }
    },
    blogStoragePath() {
      // console.log(this.$config.blogStoragePath)
      // const blob_url = '' + this.$config.blogStoragePath
      // return blob_url
      return process.env.blogStoragePath
    }
  },
  mounted() {
    window.farosianEventBus.$on('image-cropped', data =>
      this.imageCropperHandler(data)
    )
    this.$refs.editor.set(this.editor.mode, this.editor.options)
    // console.log('Ayo1', this.$config)
    process.env.blogStoragePath = this.$config.blogStoragePath
    // console.log('Ayo2', blogStoragePath())
    // console.log('this.editor', this.editor.mode)

    this.loadReportComments()
  },
  methods: {
    undo() {
      this.$refs.editor.undo()
    },
    redo() {
      this.$refs.editor.redo()
    },
    deleteEditable() {
      this.$refs.editor.clear()
    },
    freeDrawing() {
      let customizeFreeDrawing = { stroke: 'white', strokeWidth: '20' }
      this.$refs.editor.set('freeDrawing', customizeFreeDrawing)
    },
    // savePhoto() {
    //   console.log(this.$refs.editor)
    //   this.$refs.editor.saveImage()
    // },
    addText() {
      console.log(this.$refs.editor)
      let textModeOptions = {
        fill: 'black',
        // fontFamily: "Verdana",
        fontSize: 16,
        placeholder: 'Type something'
      }
      this.$refs.editor.set('text', textModeOptions)
    },
    addCicle() {
      let circleModeParams = { fill: 'blue', stroke: 'white' }
      this.$refs.editor.set('circle')
    },
    sicleMode() {
      let circleModeParams = { fill: 'blue', stroke: 'white' }
      this.$refs.editor.set('circle')
    },
    addSquare() {
      let customizeRectangle = {
        fill: 'white',
        stroke: 'white',
        strokeWidth: 2
      }
      this.$refs.editor.set('rect', customizeRectangle)
    },
    addArrow() {
      let customizeArrow = { stroke: 'red', strokeWidth: '3' }
      this.$refs.editor.set('arrow', customizeArrow)
    },
    drag() {
      this.$refs.editor.set('selectMode')
    },
    crop() {
      let cropModeOptions = {
        width: '50',
        height: '100',
        overlayOpacity: '1'
      }
      this.$refs.editor.set('crop', cropModeOptions)
      this.stateCrop = false
    },
    applyCrop() {
      this.$refs.editor.applyCropping()
      this.stateCrop = true
    },
    uploadImg: function(event) {
      this.$refs.editor.uploadImage(event)
    },
    saveImg() {
      // console.log(this.$refs.editor.saveImage());
      const url = this.api_url
      const file = this.$refs.editor.saveImage()
      var bufferValue = Buffer.from(file, 'base64')
      if (file) {
        // this.croppedImage = reader.readAsDataURL(blob)
        this.croppedImage = file
      }
      // console.log(this.myCroppa.generateDataUrl())
      // console.log('space')
      // console.log(this.$refs.editor.saveImage())
      // axios
      //   .post(url, { bufferValue })
      //   .then(response => {
      //     console.log('response', response)
      //   })
      //   .catch(error => {
      //     console.log('error', error)
      //   })
    },
    exitCroppsection() {
      this.creativity = 0
      this.networkReach = 0
      this.networkEngagement = 0
      this.professionalImage = 0
      this.communicationSkills = 0
      this.teamworkCollaboration = 0
      this.professionalEngagement = 0
      this.businessWritingAbility = 0

      //refesh page
      this.$router.go()

      //this.dialog = false
    },
    saveImages() {
      //saves image to blob storage
      this.saveButton = false
      this.myCroppa.generateDataUrl()

      //converts to file
      const dataURLtoFile = dataurl => {
        let ts = Math.round(new Date().getTime() / 1000)
        let filename = ts + '.png'

        const arr = dataurl.split(',')
        const mime = arr[0].match(/:(.*?);/)[1]
        const bstr = atob(arr[1])

        let n = bstr.length

        const u8arr = new Uint8Array(n)

        while (n) {
          u8arr[n - 1] = bstr.charCodeAt(n - 1)
          n -= 1
        }

        return new File([u8arr], filename, { type: mime })
      }

      // const file = dataURLtoFile(this.myCroppa.generateDataUrl())
      const file = dataURLtoFile(this.$refs.editor.saveImage())
      // this.$refs.editor.saveImage()
      const dataFile = new FormData()

      dataFile.append('file', file, file.name)

      let id = this.$route.params.id

      //saves to blob storage
      return this.$axios
        .$post('/proofstorage/' + id + '/image', dataFile, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })
        .then(
          function(response) {
            this.dialog = false

            let proofItem = { proof_storage: response }

            //returns Proof storage id and to add to proof
            this.AddProof(response.id)
            this.addImage(proofItem)

            this.creativity = 0
            this.networkReach = 0
            this.networkEngagement = 0
            this.professionalImage = 0
            this.communicationSkills = 0
            this.teamworkCollaboration = 0
            this.professionalEngagement = 0
            this.businessWritingAbility = 0

            //refesh page
            this.$router.go()
          }.bind(this)
        )
        .catch(
          function(error) {
            this.$toast.error(error)
          }.bind(this)
        )
    },
    AddProof(proofStorageId) {
      //data data to proof after get information from proof storage
      let bScores = {}
      if (this.$parent.subject && this.$parent.subject.allow_trait) {
        bScores = {
          creativity: this.creativity,
          network_reach: this.networkReach,
          network_engagement: this.networkEngagement,
          professional_image: this.professionalImage,
          communication_skills: this.communicationSkills,
          teamwork_collaboration: this.teamworkCollaboration,
          professional_engagement: this.professionalEngagement,
          business_writing_ability: this.businessWritingAbility
        }
      }
      let data = {
        answer: {
          id: this.question.answers[0].id
        },
        proof_storage: {
          id: proofStorageId
        },
        comment: this.proofComment,
        behaviour_scores: bScores,
        trait: this.traitsCheck
      }

      this.proofComment = ''
      this.creativity = 0
      this.networkReach = 0
      this.networkEngagement = 0
      this.professionalImage = 0
      this.communicationSkills = 0
      this.teamworkCollaboration = 0
      this.professionalEngagement = 0
      this.businessWritingAbility = 0

      this.$store.dispatch('question/createProof', data)
    },
    generateImage() {
      let url = this.myCroppa.generateDataUrl()
      if (!url) {
        alert('no image')
        return
      }
      const dataURLtoFile = dataurl => {
        let ts = Math.round(new Date().getTime() / 1000)
        let filename = ts + '.png'

        const arr = dataurl.split(',')
        const mime = arr[0].match(/:(.*?);/)[1]
        const bstr = atob(arr[1])

        let n = bstr.length

        const u8arr = new Uint8Array(n)

        while (n) {
          u8arr[n - 1] = bstr.charCodeAt(n - 1)
          n -= 1
        }

        return new File([u8arr], filename, { type: mime })
      }
      const file = dataURLtoFile(this.myCroppa.generateDataUrl())
      this.$refs.editor.setBackgroundImage(this.myCroppa.generateDataUrl())

      // this.croppedImage = url
      // this.imageUrl = url
    },
    onNewImage() {
      this.sliderVal = this.myCroppa.scaleRatio
      this.sliderMin = this.myCroppa.scaleRatio / 2
      this.sliderMax = this.myCroppa.scaleRatio * 6
    },

    onSliderChange(evt) {
      var increment = evt.target.value
      this.myCroppa.scaleRatio = +increment
    },

    onZoom() {
      //To prevent zooming out of range when using scrolling to zoom
      if (this.sliderMax && this.croppa.scaleRatio >= this.sliderMax) {
        this.croppa.scaleRatio = this.sliderMax
      } else if (this.sliderMin && this.croppa.scaleRatio <= this.sliderMin) {
        this.croppa.scaleRatio = this.sliderMin
      }

      this.sliderVal = this.myCroppa.scaleRatio
    },
    loadReportComments() {
      this.$store.dispatch('investigation/reportComment', this.$route.params.id)
    },
    showCroppa(data) {
      //add cropped image to croppa
      this.onNewImage()
      this.onZoom()

      this.sliderVal = '0'
      this.sliderMin = '0.4'
      this.sliderMax = '5'
      this.sliderStep = '.001'

      this.dialog = true

      this.clearPlugin()
      this.proofImage = data

      //converts image to file
      const dataURLtoFile = dataurl => {
        let ts = Math.round(new Date().getTime() / 1000)
        let filename = ts + '.png'

        const arr = dataurl.split(',')
        const mime = arr[0].match(/:(.*?);/)[1]
        const bstr = atob(arr[1])

        let n = bstr.length

        const u8arr = new Uint8Array(n)

        while (n) {
          u8arr[n - 1] = bstr.charCodeAt(n - 1)
          n -= 1
        }

        return new File([u8arr], filename, { type: mime })
      }

      const file = dataURLtoFile(data)
      const dataFile = new FormData()

      dataFile.append('file', file, file.name)

      let id = this.$route.params.id
    },
    hide() {
      this.onNewImage()
      this.dialog = false
      this.clearPlugin()
    },
    imageCropperHandler(data) {
      //gets Screenshot plugin entry

      this.sliderVal = '0'
      this.sliderMin = '0.4'
      this.sliderMax = '5'
      this.sliderStep = '.001'

      // sends data to the Cropping section
      this.showCroppa(data)
      this.dialog = true

      this.saveButton = true
    },
    clearPlugin() {
      this.sliderVal = '0'
      this.sliderMin = '0.4'
      this.sliderMax = '5'
      this.sliderStep = '.001'

      this.croppedImage = ''
      this.proofImage = ''
      this.onNewImage()
      this.dialog = true
    },
    submitAnswer() {
      //Daniel
      let proofStore = []
      if (this.proofs) {
        this.proofs.forEach(proof => {
          let ps = {
            proof_storage: { id: proof.proof_storage.id }
          }

          if (proof.id) {
            // if Id is available update proof item
            ps.id = proof.id
          }

          proofStore.push(ps)
        })
      }

      let ans = {
        question: { id: this.question.id },
        answer: this.answer,
        slider_value: this.question.slider === true ? this.slider_value : 0,
        default_name: this.question.default_name,
        proofs: proofStore,
        dirty: this.dirty,
        platform: this.question.platform
      }
      if (this.question.answers.length > 0 && this.question.answers[0].id) {
        ans.id = this.question.answers[0].id // if updating answer include answer ID
      }

      this.$emit('answer', ans)

      // Reset Values
      this.newAnswer = null
      this.newSliderValue = 0
      this.comment = null
      this.myProofs = []
      this.dirty = false
      this.proofEmpty = false
      this.imageObject = null
    },
    addImage(proofImage) {
      if (this.question.hasOwnProperty('id')) {
        this.proofs = proofImage
      } else {
        this.generalCommentProofs = proofImage
      }

      this.proofEmpty = false
    },
    showImage(proofImage, index) {
      this.imageDialog = !this.imageDialog
      this.imageObject = proofImage
      this.imageObject.index = index
    },
    deleteProofImage(proofImage) {
      if (proofImage.hasOwnProperty('id')) {
        //already linked to proof
        this.disable('/proof/' + proofImage.id, proofImage.index) // soft delete proof
      } else {
        this.disable(
          '/proofstorage/' + proofImage.proof_storage.id + '/image',
          proofImage.index //only blob storage, hard delete
        )
      }
    },
    disable(url, index) {
      let self = this
      return this.$axios
        .$delete(url)
        .then(function(response) {
          self.removeItem(index, response)
        })
        .catch(function(error) {
          self.$toast.error(error)
        })
    },
    customUpdate() {
      let formInfo = {
        id: this.editDialogX.id,
        comment: this.editDialogX.comment,
        private: this.editDialogX.private
      }

      this.updateState()
    },
    setEditComment(note) {
      this.editDialogX.comment = note.comment
      this.editDialogX.id = note.id
      this.editDialogX.private = note.private
    },
    clearCommentInputs() {
      this.reportComment = ''
      this.reportCommentPrivate = ''
    },
    updateState() {
      this.$store
        .dispatch('report/editComment', this.editDialogX)
        .then(response => {
          this.setComments()
          this.reportComments
          response.dirty = true
          let updateComment = { id: '', value: '', prvt: '' }
          updateComment.id = response.id
          updateComment.value = response.comment
          updateComment.prvt = this.editDialogX.private
          this.$store.dispatch(
            'subject/updateSubjectReportComment',
            updateComment
          )
          // this.reportComments = response
          this.dialogLoading = false
          this.editReportCommentDialog = !this.editReportCommentDialog
          this.loadReportComments()
          this.$toast.success('Comment saved successfully')
        })
        .catch(error => {
          this.dialogLoading = false
          this.editReportCommentDialog = !this.editReportCommentDialog
          this.$toast.error('Failed to update report comment')
          this.$toast.error(error)
        })
    },
    createReportComment() {
      this.dialogLoading = true
      let comment = {
        comment: this.reportComment,
        private: this.reportCommentPrivate,
        report: {
          id: this.report.id
        }
      }
      this.$store
        .dispatch('report/createComment', comment)
        .then(response => {
          this.setComments()
          response.dirty = true
          this.reportComments = response
          this.dialogLoading = false
          this.reportCommentDialog = !this.reportCommentDialog

          this.clearCommentInputs()
          this.loadReportComments()
          this.$toast.success('Comment added successfully')
        })
        .catch(() => {
          this.dialogLoading = false
          this.reportCommentDialog = !this.reportCommentDialog
          this.$toast.error('Failed to save report comment')
        })
    },
    setComments() {
      if (this.newReportComments.length > 0) {
        return
      }
      if (this.report.comments.length > 0) {
        this.report.comments.forEach(comment => {
          this.reportComments = comment
        })
      }
    },
    updateReportComment() {
      this.$store
        .dispatch('report/editComment', this.editDialogX)
        .then(() => {
          this.dialogLoading = false
          this.editReportCommentDialog = !this.editReportCommentDialog
          this.$toast.success('Comment saved successfully')
        })
        .catch(() => {
          this.dialogLoading = false
          this.editReportCommentDialog = !this.editReportCommentDialog
          this.$toast.error('Failed to save report comment')
        })
    },
    deleteReportComment(id, index) {
      if (confirm('Are you sure you want delete comment')) {
        this.$store
          .dispatch('report/deleteComment', id)
          .then(() => {
            this.setComments()
            this.newReportComments.splice(index, 1)
            this.loadReportComments()
            this.$toast.success('Comment deleted successfully')
          })
          .catch(error => {
            this.$toast.error('Failed to delete report comment')
          })
      }
    },
    submitGeneralComment() {
      let proofStore = []
      if (this.generalCommentProofs) {
        this.generalCommentProofs.forEach(proof => {
          let ps = {
            proof_storage: { id: proof.proof_storage.id }
          }
          if (proof.id) {
            ps.id = proof.id
          }
          proofStore.push(ps)
        })
      }

      let comment = {
        answer: this.gm,
        proofs: proofStore,
        dirty: this.dirty
      }
      if (this.generalComment && this.generalComment.id) {
        comment.id = this.generalComment.id
      }
      if (this.gm !== null) {
        this.$emit('general-comment-handler', comment)
      }
    },
    checkForCurrentComplete() {
      let report = this.$parent.subject
        ? this.$parent.subject.current_report
        : {}
      let userAllowed =
        this.$auth.hasScope('ROLE_SUPER_ADMIN') ||
        this.$auth.hasScope('ROLE_TEAM_LEAD') ||
        this.$auth.hasScope('ROLE_ANALYST')
      return (
        this.$parent.subject.status === 'under_investigation' &&
        report &&
        userAllowed
      )
    },
    completeInvestigation() {
      this.$store
        .dispatch('report/completeInvestigation', this.$route.params.id)
        .then(() => {
          this.$toast.success('Investigation completed')
          this.$router.push({
            name: this.$getRoute('SUBJECTS_VIEW'),
            params: this.$route.params.id
          })
        })
        .catch(() => {
          this.$toast.error('Could not refresh search. Please check error.')
        })
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

.croppa-container {
  cursor: -webkit-grab;
  cursor: grab;
}

.croppaBorder {
  width: 560px;
  -webkit-box-shadow: 7px 13px 5px -6px rgba(0, 0, 0, 0.27) !important;
  -moz-box-shadow: 7px 13px 5px -6px rgba(0, 0, 0, 0.27) !important;
  box-shadow: 7px 13px 5px -6px rgba(0, 0, 0, 0.27) !important;
}

.float-right {
  float-right: right !important;
}

.float-left {
  float-left: right !important;
}

.plusMinusButtons {
  margin: 0px 0px 0px 0px;
  padding: 0px 0px 0px 0px;
}

.plusMinusInput {
  margin: 0px 0px 0px 0px;
  padding: 0px 0px 0px 0px;
}

input[type='range'] {
  width: 550px;
}

.croppaImages {
  -webkit-box-shadow: 7px 13px 5px -6px rgba(0, 0, 0, 0.27) !important;
  -moz-box-shadow: 7px 13px 5px -6px rgba(0, 0, 0, 0.27) !important;
  box-shadow: 7px 13px 5px -6px rgba(0, 0, 0, 0.27) !important;
  max-width: 560px !important;
}

.putInline {
  display: inline-block;
}

.checkboxAlign {
  margin: 10px 0px 0px 20px;
}

.textAlign {
  margin: 0px 10px 10px 0px !important;
}

.clearBoth {
  clear: both;
}

.v-text-field {
  padding-top: -10px !important;
  margin-top: -27px !important;
}

.commentSpacing {
  clear: both;
  height: 50px !important;
}

.traitSpaces {
  clear: both;
  height: 50px !important;
}

.traitSpacesTop {
  clear: both;
  height: 50px !important;
}

.traitSpacesBottom {
}

.messageIcon {
  margin: 0px 5px 0px 0px;
}

.commentList:hover {
  cursor: pointer;
}

.editor-tools {
  display: flex;
  width: 100%;
  justify-content: space-around;
  max-width: 560px;
}
#file {
  width: 1px;
  height: 1px;
  visibility: hidden;
}
.upper-canvas {
  border: 1px solid;
  margin: 5px;
  padding-left: 10px;
}
.icon {
  color: blue;
}

.icon:focus {
  color: blue;
}

.icon::before {
  color: red;
}

.icon::after {
  color: #00ff00;
}

.icon:hover {
  color: #00ff00;
}

.icon:active {
  color: #0000ff;
}
</style>
