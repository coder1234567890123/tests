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
          @click="riskCommentUpdate">
          Save
        </v-btn>
        <span
          class="putInline"
          style="float:right !important;"/>
        <v-list-tile-title>RISK</v-list-tile-title>
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
          <v-layout
            row
            wrap>
            <v-flex md4>
              <br>
              <v-select
                v-model="riskTypeItemsValue"
                :items="riskTypeItems"
                label="Risk Type"
                @change="riskTypeChange()"
              />
              <br>

              <!--  Side menu 1-->
              <div
                v-if="riskTypes == 1"
              >
                <v-expansion-panel>
                  <!--  Social media-->
                  <v-expansion-panel-content
                    v-for="(item,i) in 1"
                    :key="i + 'x'">
                    <div
                      slot="header"
                      class="socialCheckbox">Social media
                      <span
                        v-if="
                          facebookCheckbox == false &&
                            twitterCheckbox == false &&
                            instagramCheckbox == false &&
                            linkedInCheckbox == false &&
                            pinterestCheckbox == false &&
                            blogCheckbox == false &&
                            flickrCheckbox == false &&
                            tumblrCheckbox == false
                        "><v-icon color="error lighten-1">warning</v-icon> </span>
                    </div>
                    <v-card>
                      <v-card-text>
                        <v-checkbox
                          :label="`Facebook:`"
                          v-model="facebookCheckbox"
                        />
                        <v-checkbox
                          :label="`Twitter:`"
                          v-model="twitterCheckbox"
                        />
                        <v-checkbox
                          :label="`Instagram:`"
                          v-model="instagramCheckbox"
                        />
                        <v-checkbox
                          :label="`LinkedIn:`"
                          v-model="linkedInCheckbox"
                        />
                        <v-checkbox
                          :label="`Pinterest:`"
                          v-model="pinterestCheckbox"
                        />
                        <v-checkbox
                          :label="`You Tube:`"
                          v-model="youtubeCheckbox"
                        />
                        <v-checkbox
                          :label="`Web:`"
                          v-model="webCheckbox"
                        />
                        <v-checkbox
                          :label="`Blog:`"
                          v-model="blogCheckbox"
                        />
                        <v-checkbox
                          :label="`Flickr:`"
                          v-model="flickrCheckbox"
                        />
                        <v-checkbox
                          :label="`Tumblr:`"
                          v-model="tumblrCheckbox"
                        />
                      </v-card-text>
                    </v-card>
                  </v-expansion-panel-content>
                  <!--  Activity-->
                  <v-expansion-panel-content
                    v-for="(item,i) in 1"
                    :key="i + 'y'">
                    <div
                      slot="header"
                      class="activityRadios">Activity
                      <span v-if="activityRadios ==''"><v-icon color="error lighten-1">warning</v-icon> </span>
                    </div>
                    <v-card>
                      <v-card-text>
                        <v-radio-group
                          v-model="activityRadios"
                          :mandatory="false">
                          <v-radio
                            label="high"
                            value="high"/>
                          <v-radio
                            label="well above average"
                            value="well above average"/>
                          <v-radio
                            label="above average"
                            value="above average"/>
                          <v-radio
                            label="average"
                            value="average"/>
                          <v-radio
                            label="below average"
                            value="below average"/>
                          <v-radio
                            label="well below average"
                            value="well below average"/>
                          <v-radio
                            label="low"
                            value="low"/>
                        </v-radio-group>
                      </v-card-text>
                    </v-card>
                  </v-expansion-panel-content>
                  <!-- Privacy settings-->
                  <v-expansion-panel-content
                    v-for="(item,i) in 1"
                    :key="i + 'k'">
                    <div
                      slot="header"
                      class="privacySettingsRadios">Privacy settings
                      <span v-if="privacySettingsRadios == ''"><v-icon color="error lighten-1">warning</v-icon> </span>
                    </div>
                    <v-card>
                      <v-card-text>
                        <v-radio-group
                          v-model="privacySettingsRadios"
                          :mandatory="false">
                          <v-radio
                            label="high"
                            value="high"/>
                          <v-radio
                            label="well above average"
                            value="well above average"/>
                          <v-radio
                            label="above average"
                            value="above average"/>
                          <v-radio
                            label="average"
                            value="average"/>
                          <v-radio
                            label="below average"
                            value="below average"/>
                          <v-radio
                            label="well below average"
                            value="well below average"/>
                          <v-radio
                            label="low"
                            value="low"/>
                        </v-radio-group>
                      </v-card-text>
                    </v-card>
                  </v-expansion-panel-content>
                  <!--Nature Engagement-->
                  <v-expansion-panel-content
                    v-for="(item,i) in 1"
                    :key="i + 'u'">
                    <div
                      slot="header"
                      class="natureEngagementRadios">Nature Engagement
                      <span v-if="natureEngagementRadios == ''"><v-icon color="error lighten-1">warning</v-icon> </span>
                    </div>
                    <v-card>
                      <v-card-text>
                        <v-radio-group
                          v-model="natureEngagementRadios"
                          :mandatory="false">
                          <v-radio
                            label="high"
                            value="high"/>
                          <v-radio
                            label="positive"
                            value="positive"/>
                          <v-radio
                            label="negative"
                            value="negative"/>
                          <v-radio
                            label="confrontational"
                            value="confrontational"/>
                          <v-radio
                            label="reactive"
                            value="reactive"/>
                          <v-radio
                            label="neutral"
                            value="neutral"/>
                          <v-radio
                            label="low"
                            value="low"/>
                        </v-radio-group>
                      </v-card-text>
                    </v-card>
                  </v-expansion-panel-content>
                  <!-- Content available-->
                  <v-expansion-panel-content
                    v-for="(item,i) in 1"
                    :key="i + 'b'">
                    <div
                      slot="header"
                      class="contentAvailableRadios">Content available
                      <span v-if="contentAvailableRadios == ''"><v-icon color="error lighten-1">warning</v-icon> </span>
                    </div>
                    <v-card>
                      <v-card-text>
                        <v-radio-group
                          v-model="contentAvailableRadios"
                          :mandatory="false">
                          <v-radio
                            label="predominantly personal"
                            value="predominantly personal"/>
                          <v-radio
                            label="predominantly"
                            value="predominantly"/>
                          <v-radio
                            label="neutral"
                            value="neutral"/>
                          <v-radio
                            label="low"
                            value="low"/>
                        </v-radio-group>
                      </v-card-text>
                    </v-card>
                  </v-expansion-panel-content>
                  <!-- Comment By Investigator 1-->
                  <v-expansion-panel-content
                    v-for="(item,i) in 1"
                    :key="i + 'l'">
                    <div
                      slot="header"
                      class="commentByInvestigator1">Comment By Investigator 1
                      <span v-if="commentByInvestigator1 == ''"><v-icon color="error lighten-1">warning</v-icon> </span>
                    </div>
                    <v-card>
                      <v-card-text>
                        <v-textarea
                          v-model="commentByInvestigator1"
                          :rows="2"
                          label="Comment"
                        />
                      </v-card-text>
                    </v-card>
                  </v-expansion-panel-content>
                  <!-- Google Search-->
                  <v-expansion-panel-content
                    v-for="(item,i) in 1"
                    :key="i + 'v'">
                    <div
                      slot="header"
                      class="googleSearch">Google Search
                      <span v-if="googleSearch == ''">
                      <v-icon color="error lighten-1">warning</v-icon> </span>
                    </div>
                    <v-card>
                      <v-card-text>
                        <v-radio-group
                          v-model="googleSearch"
                          :mandatory="false">
                          <v-radio
                            label="active social media accounts"
                            value="active social media accounts"/>
                          <v-radio
                            label="an active social media account"
                            value="an active social media account"/>
                          <v-radio
                            label="active social media accounts and Google image results"
                            value="active social media accounts and Google image results"/>
                          <v-radio
                            label="Google image results"
                            value="Google image results"/>
                          <v-radio
                            label="additional accounts being found"
                            value="additional accounts being found"/>
                        </v-radio-group>
                      </v-card-text>
                    </v-card>
                  </v-expansion-panel-content>
                  <!-- Comment By Investigator 2-->
                  <v-expansion-panel-content
                    v-for="(item,a) in 1"
                    :key="a + 'o'">
                    <div
                      slot="header"
                      class="commentByInvestigator2">Comment By Investigator 2
                      <span v-if="commentByInvestigator2 == ''"><v-icon color="error lighten-1">warning</v-icon> </span>
                    </div>
                    <v-card>
                      <v-card-text>
                        <v-textarea
                          v-model="commentByInvestigator2"
                          :rows="2"
                          label="Comment"
                        />
                      </v-card-text>
                    </v-card>
                  </v-expansion-panel-content>
                  <!-- Final -->
                  <v-expansion-panel-content
                    v-for="(item,i) in 1"
                    :key="i + 'd'">
                    <div
                      slot="header"
                      class="finalInfo">Final
                      <span v-if="finalInfo == ''">
                      <v-icon color="error lighten-1">warning</v-icon> </span>
                    </div>
                    <v-card>
                      <v-card-text>
                        <v-radio-group
                          v-model="finalInfo"
                          :mandatory="false">
                          <v-radio
                            label="No negative, illicit or damaging content matched to the subject"
                            value="No negative, illicit or damaging content matched to the subject"/>
                          <v-radio
                            label="No negative content matched to the subject"
                            value="No negative content matched to the subject"/>
                          <v-radio
                            label="Negative content matched to the subject"
                            value="Negative content matched to the subject"/>
                          <v-radio
                            label="Unprofessional content matched to the subject"
                            value="Unprofessional content matched to the subject"/>
                          <v-radio
                            label="Potential for conflict"
                            value="Potential for conflict"/>
                        </v-radio-group>
                      </v-card-text>
                    </v-card>
                  </v-expansion-panel-content>
                  <!-- Comment By Investigator 3-->
                  <v-expansion-panel-content
                    v-for="(item,i) in 1"
                    :key="i + 's'">
                    <div
                      slot="header"
                      class="commentByInvestigator3">Comment By Investigator 3
                      <span v-if="commentByInvestigator3 == ''"><v-icon color="error lighten-1">warning</v-icon> </span>
                    </div>
                    <v-card>
                      <v-card-text>
                        <v-textarea
                          v-model="commentByInvestigator3"
                          rows="2"
                          label="Comment"
                        />
                      </v-card-text>
                    </v-card>
                  </v-expansion-panel-content>
                </v-expansion-panel>
              </div>

              <!--  Side menu 2-->
              <div
                v-if="riskTypes == 2"
              >
                <v-expansion-panel>
                  <!--  Subject info-->
                  <v-expansion-panel-content
                    v-for="(item,i) in 1"
                    :key="i + 'q'">
                    <div
                      slot="header"
                      class="socialCheckbox">Subject Info

                      <span
                        v-if="
                          subjectEmail == false &&
                            subjectNonEmail == false &&
                            subjectAddress == false &&
                            subjectEmployment == false &&
                            subjectAcademic == false &&
                            subjectContactNumber == false &&
                            subjectLocation == false &&
                            subjectDateOfBirth == false &&
                            subjectFirstName == false &&
                            subjectLastName == false
                        "><v-icon color="error lighten-1">warning</v-icon> </span>
                    </div>
                    <v-card>
                      <v-card-text>
                        <v-checkbox
                          :label="`First Name:`"
                          v-model="subjectFirstName"
                        />
                        <v-checkbox
                          :label="`Last Name:`"
                          v-model="subjectLastName"
                        />

                        <v-checkbox
                          :label="`Maiden Name:`"
                          v-model="subjectMaidenName"
                        />
                        <v-checkbox
                          :label="`Email:`"
                          v-model="subjectEmail"
                        />
                        <v-checkbox
                          :label="`Non Email:`"
                          v-model="subjectNonEmail"
                        />
                        <v-checkbox
                          :label="`Contact Number:`"
                          v-model="subjectContactNumber"
                        />

                        <v-checkbox
                          :label="`Date Of Birth:`"
                          v-model="subjectDateOfBirth"
                        />
                        <v-checkbox
                          :label="`Address:`"
                          v-model="subjectAddress"
                        />
                        <v-checkbox
                          :label="`Employment:`"
                          v-model="subjectEmployment"
                        />
                        <v-checkbox
                          :label="`Academic:`"
                          v-model="subjectAcademic"
                        />
                        <v-checkbox
                          :label="`Location:`"
                          v-model="subjectLocation"
                        />

                      </v-card-text>
                    </v-card>
                  </v-expansion-panel-content>

                  <!-- Comment By Investigator Subject-->
                  <v-expansion-panel-content
                    v-for="(item,i) in 1"
                    :key="i + 'm'">
                    <div
                      slot="header"
                      class="commentByInvestigatorSubject">Comment By Investigator 1
                      <span v-if="commentByInvestigatorSubject == ''"><v-icon color="error lighten-1">warning</v-icon> </span>
                    </div>
                    <v-card>
                      <v-card-text>
                        <v-textarea
                          v-model="commentByInvestigatorSubject"
                          :rows="2"
                          label="Comment"
                        />
                      </v-card-text>
                    </v-card>
                  </v-expansion-panel-content>

                </v-expansion-panel>
              </div>

              <!-- Side menu 3-->
              <div
                v-if="riskTypes == 3"
              >
                <v-expansion-panel>
                  <!--  Social media presence-->
                  <v-expansion-panel-content
                    v-for="(item,i) in 1"
                    :key="i + 'n'">
                    <div
                      slot="header"
                      class="socialCheckbox">Social media presence

                      <span
                        v-if="
                        socialPresenceRadios == ''"><v-icon color="error lighten-1">warning</v-icon> </span>
                    </div>
                    <v-card>
                      <v-card-text>
                        <v-radio-group
                          v-model="socialPresenceRadios"
                          :mandatory="false">
                          <v-radio
                            label="high"
                            value="high"/>
                          <v-radio
                            label="well above average"
                            value="well above average"/>
                          <v-radio
                            label="above average"
                            value="above average"/>
                          <v-radio
                            label="average"
                            value="average"/>
                          <v-radio
                            label="below average"
                            value="below average"/>
                          <v-radio
                            label="well below average"
                            value="well below average"/>
                          <v-radio
                            label="low"
                            value="low"/>
                        </v-radio-group>
                      </v-card-text>
                    </v-card>
                  </v-expansion-panel-content>
                </v-expansion-panel>
              </div>

              <!-- Side menu 4-->
              <div
                v-if="riskTypes == 4"
              ><v-expansion-panel>
                <!-- Comment By Investigator Subject-->
                <v-expansion-panel-content
                  v-for="(item,i) in 1"
                  :key="i + 'z'">
                  <div
                    slot="header"
                    class="commentByInvestigator3">Comment By Investigator 4
                    <span v-if="commentByInvestigatorSubject4 == ''"><v-icon color="error lighten-1">warning</v-icon> </span>
                  </div>
                  <v-card>
                    <v-card-text>
                      <v-textarea
                        v-model="commentByInvestigatorSubject4"
                        :rows="2"
                        label="Comment"
                      />
                    </v-card-text>
                  </v-card>
                </v-expansion-panel-content>
              </v-expansion-panel>
              </div>

            </v-flex>
            <v-flex md2/>
            <v-flex md6>
              <div
                v-if="riskTypes == 1"
                class="textBoxPadding">
                <v-card>
                  <v-card-text>
                    <h2>
                      Normal report:
                    </h2>
                  </v-card-text>
                </v-card>
                <br>
                The data subject is present on
                <span class="socialCheckbox backgroundLightYellow">{{ buildSocialMediaList }}</span>
                . The subject has a
                <span class="activityRadios backgroundLightYellow">{{ activityRadios }} </span>
                activity and
                <span class="privacySettingsRadios backgroundLightYellow">{{ privacySettingsRadios }} </span>
                privacy settings across all platforms. The nature of the engagement is
                <span class="natureEngagementRadios backgroundLightYellow">{{ natureEngagementRadios }} </span>.
                Content available is
                <span class="contentAvailableRadios backgroundLightYellow">{{ contentAvailableRadios }}</span>
                in nature.
                <span class="commentByInvestigator1 backgroundLightYellow">{{ commentByInvestigator1 }} </span>.
                A Google search provides links to <span
                class="googleSearch backgroundLightYellow">{{ googleSearch }} </span>.
                <span class="commentByInvestigator2 backgroundLightYellow">{{ commentByInvestigator2 }} </span>.
                <span class="finalInfo backgroundLightYellow">{{ finalInfo }} </span>.
                <span class="commentByInvestigator3 backgroundLightYellow">{{ commentByInvestigator3 }} </span>.
              </div>
              <div
                v-if="riskTypes == 2"
                class="textBoxPadding">
                <v-card>
                  <v-card-text>
                    <h2>
                      Limited content provided report:
                    </h2>
                  </v-card-text>
                </v-card>
                <br>

                <p>
                  The subject has a highly limited digital media and Google presence which demonstrates
                  low levels of risk, with limited social media profiles. There is limited mandatory
                  information provided and/or available to confirm matches;

                  <span class="socialCheckbox backgroundLightYellow">
                    {{ buildSubjectInfoList }}
                  </span>
                  . The subject has a high commonality of given names combinations, (<span class="commentByInvestigatorSubject backgroundLightYellow">{{ commentByInvestigatorSubject }} </span>,  matches).
                  Should the subject have accounts, these are linked to cell phone and email details which
                  have not been provided. These results do not include the use of pseudonyms or aliases.
                </p>
              </div>
              <div
                v-if="riskTypes == 3"
                class="textBoxPadding">
                <v-card>
                  <v-card-text>
                    <h2>
                      Limited content and/or high privacy:
                    </h2>
                  </v-card-text>
                </v-card>
                <br>
                <p>
                  The subject has a neutral,
                  <span class="privacySettingsRadios backgroundLightYellow">{{ socialPresenceRadios }} </span> social media presence.
                  The subject has high privacy settings across all platforms.
                  A Google search provides links to inactive social media accounts.
                  The subject demonstrates low levels of risk, with limited social media profiles.
                  These results do not include the use of pseudonyms or aliases.
                </p>
              </div>
              <div
                v-if="riskTypes == 4"
                class="textBoxPadding">
                <v-card>
                  <v-card-text>
                    <h2>
                      No accounts found:
                    </h2>
                  </v-card-text>
                </v-card>
                <br>
                <p>
                  The subject has a highly limited digital media and google presence which demonstrates
                  low levels of risk, with limited social media profiles. The provided information is not
                  matched/associated with any social media accounts, profiles or content. These results do
                  not include the use of pseudonyms or aliases, or any accounts associated with unproved
                  contact numbers or email addresses. <span class="commentByInvestigator3 backgroundLightYellow">{{ commentByInvestigatorSubject4 }} </span>.
                </p>
              </div>

              <div class="text-center">
                <v-dialog
                  v-model="dialog2"
                  width="800"
                >
                  <v-card>
                    <v-card-title
                      class="headline grey lighten-2"
                      primary-title
                    >
                      Risk Comment - Final Check
                    </v-card-title>

                    <v-card-text>
                      <div class="dialog2Box">
                        {{ riskComment }}
                      </div>
                    </v-card-text>

                    <v-divider/>

                    <v-card-actions>
                      <v-spacer/>

                      <v-btn
                        class="float-left"
                        color="red lighten-3"
                        text
                        @click="dialog2 = false"
                      >
                        Disagree
                      </v-btn>

                      <v-btn
                        color="green lighten-3"
                        text
                        @click="saveRiskComment"
                      >
                        Agree
                      </v-btn>
                    </v-card-actions>
                  </v-card>
                </v-dialog>
              </div>
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
  data() {
    return {
      value: null,
      dialog: false,
      dialog2: false,
      riskTypeItems: ['1', '2', '3', '4'],
      risk: '******',
      riskTypes: 1,
      checkbox: true,
      facebookCheckbox: false,
      twitterCheckbox: false,
      instagramCheckbox: false,
      linkedInCheckbox: false,
      pinterestCheckbox: false,
      youtubeCheckbox: false,
      webCheckbox: false,
      blogCheckbox: false,
      flickrCheckbox: false,
      tumblrCheckbox: false,
      digitalMediaRadios: '',
      activityRadios: '',
      privacySettingsRadios: '',
      natureEngagementRadios: '',
      contentAvailableRadios: '',
      commentByInvestigator1: '',
      googleSearch: '',
      commentByInvestigator2: '',
      finalInfo: '',
      commentByInvestigator3: '',
      riskTypeItemsValue: '',
      subjectEmail: false,
      subjectNonEmail: false,
      subjectAddress: false,
      subjectEmployment: false,
      subjectAcademic: false,
      subjectContactNumber: false,
      subjectDateOfBirth: false,
      subjectFirstName: false,
      subjectLastName: false,
      subjectMaidenName: false,
      subjectLocation: false,
      commentByInvestigatorSubject: '',
      socialPresenceRadios: '',
      commentByInvestigatorSubject4: '',
      riskComment: '',
      facebook: '',
      twitter: '',
      instagram: '',
      linkedin: '',
      pinterest: '',
      youtube: '',
      web: '',
      blog: '',
      flickr: '',
      tumblr: '',
      firstName: '',
      lastName: '',
      maidenName: '',
      dateOfBirth: '',
      email1: '',
      email2: '',
      telNumber: '',
      location: '',
      academicHistory: '',
      employmentHistory: '',
      buildSocialMediaList: '',
      buildSubjectInfoList: ''
    }
  },
  computed: {
    ...mapGetters({
      subjectInfo: 'risk/subjectInfo',
      getMediaValidated: 'risk/mediaValidated',
      getRiskComment: 'risk/riskComment'
    })
  },
  watch: {
    facebookCheckbox: function() {
      this.buildSocialMedia()
    },
    twitterCheckbox: function() {
      this.buildSocialMedia()
    },
    instagramCheckbox: function() {
      this.buildSocialMedia()
    },
    linkedInCheckbox: function() {
      this.buildSocialMedia()
    },
    pinterestCheckbox: function() {
      this.buildSocialMedia()
    },
    youtubeCheckbox: function() {
      this.buildSocialMedia()
    },
    webCheckbox: function() {
      this.buildSocialMedia()
    },
    blogCheckbox: function() {
      this.buildSocialMedia()
    },
    flickrCheckbox: function() {
      this.buildSocialMedia()
    },
    tumblrCheckbox: function() {
      this.buildSocialMedia()
    },
    subjectFirstName: function() {
      this.buildSubjectInfo()
    },
    subjectLastName: function() {
      this.buildSubjectInfo()
    },
    subjectMaidenName: function() {
      this.buildSubjectInfo()
    },
    subjectDateOfBirth: function() {
      this.buildSubjectInfo()
    },
    subjectNonEmail: function() {
      this.buildSubjectInfo()
    },
    subjectContactNumber: function() {
      this.buildSubjectInfo()
    },
    subjectAddress: function() {
      this.buildSubjectInfo()
    },
    subjectAcademic: function() {
      this.buildSubjectInfo()
    },
    subjectEmployment: function() {
      this.buildSubjectInfo()
    },
    subjectEmail: function() {
      this.buildSubjectInfo()
    },
    subjectLocation: function() {
      this.buildSubjectInfo()
    }
  },
  mounted() {},
  methods: {
    buildSubjectInfo() {
      let str1 = ''
      let firstName = this.subjectFirstName === false ? 'First Name, ' : ''
      let lastName = this.subjectLastName === false ? 'Last Name, ' : ''
      let maidenName = this.subjectMaidenName === false ? 'Maiden Name, ' : ''
      let dateOfBirth =
        this.subjectDateOfBirth === false ? 'Date of birth, ' : ''
      let email = this.subjectEmail === false ? 'no email address, ' : ''
      let nonEmail =
        this.subjectNonEmail === false ? 'non-personal email address, ' : ''
      let contactNumber =
        this.subjectContactNumber === false ? 'contact number, ' : ''
      let location = this.subjectAddress === false ? 'location, ' : ''
      let academicHistory =
        this.subjectAcademic === false ? 'no academic history, ' : ''
      let employmentHistory =
        this.subjectEmployment === false ? 'no employment history, ' : ''
      let locationHistory =
        this.subjectLocation === false ? 'no location history, ' : ''

      let subjectInfo = str1.concat(
        firstName,
        lastName,
        maidenName,
        dateOfBirth,
        email,
        nonEmail,
        contactNumber,
        location,
        academicHistory,
        employmentHistory,
        locationHistory
      )

      this.buildSubjectInfoList = this.removeLastComma(subjectInfo)
    },
    buildSocialMedia() {
      let str1 = ''
      let faceBook = this.facebookCheckbox === true ? 'Facebook, ' : ''
      let twitter = this.twitterCheckbox === true ? 'Twitter, ' : ''
      let instagram = this.instagramCheckbox === true ? 'Instagram, ' : ''
      let linkedIn = this.linkedInCheckbox === true ? 'LinkedIn, ' : ''
      let pinterest = this.pinterestCheckbox === true ? 'Pinterest, ' : ''
      let youtube = this.youtubeCheckbox === true ? 'Youtube, ' : ''
      let web = this.webCheckbox === true ? 'Web, ' : ''
      let blog = this.blogCheckbox === true ? 'Blog, ' : ''
      let flickr = this.flickrCheckbox === true ? 'Flickr, ' : ''
      let tumblr = this.tumblrCheckbox === true ? 'Tumblr, ' : ''

      let socialMedia = str1.concat(
        faceBook,
        twitter,
        instagram,
        linkedIn,
        pinterest,
        youtube,
        web,
        blog,
        flickr,
        tumblr
      )

      this.buildSocialMediaList = this.removeLastComma(socialMedia)
    },
    removeLastComma(socialMedia) {
      let removeLastComma = socialMedia.replace(/,\s*$/, '')
      let insertAndIntoString = removeLastComma.replace(/,(?=[^,]*$)/, ' and')
      let trimString = insertAndIntoString.trim()

      return trimString
    },
    updateRisk() {
      this.facebookCheckbox = this.getMediaValidated.facebook
      this.twitterCheckbox = this.getMediaValidated.twitter
      this.instagramCheckbox = this.getMediaValidated.instagram
      this.pinterestCheckbox = this.getMediaValidated.pinterest
      this.youtubeCheckbox = this.getMediaValidated.youtube
      this.flickrCheckbox = this.getMediaValidated.flickr
      this.webCheckbox = this.getMediaValidated.web
      this.linkedInCheckbox = this.getMediaValidated.linkedin

      this.subjectEmail = this.subjectInfo.email
      this.subjectNonEmail = this.subjectInfo.non_personal_email
      this.subjectAddress = this.subjectInfo.location
      this.subjectEmployment = this.subjectInfo.employment_history
      this.subjectAcademic = this.subjectInfo.academic_history
      this.subjectContactNumber = this.subjectInfo.contact_number
      this.subjectDateOfBirth = this.subjectInfo.date_of_birth
      this.subjectFirstName = this.subjectInfo.first_name
      this.subjectLastName = this.subjectInfo.surname
      this.subjectMaidenName = this.subjectInfo.maiden_name

      this.buildSocialMedia()
    },
    async saveRiskComment() {
      let data = {
        id: this.subjectInfo.report_id,
        risk_comment: this.riskComment
      }

      this.$store
        .dispatch('subject/addRiskComment', data)
        .then(response => {
          this.dialog2 = false
          this.dialog = false
          this.$router.push('/subjects/' + this.$route.params.id)
          this.$toast.success('Risk Comment saved successfully')
          this.$router.go()
        })
        .catch(error => {
          this.$toast.error('Failed to update Risk Comment')
        })
    },
    riskChange() {
      this.risk = this.riskItemsValue
    },
    riskCommentUpdate() {
      this.dialog2 = true

      if (this.riskTypes == 1) {
        this.riskComment =
          'The data subject is present on ' +
          this.buildSocialMediaList +
          '. The subject has a, ' +
          this.activityRadios +
          ' activity and ' +
          this.privacySettingsRadios +
          ' privacy settings across all platforms. The nature of the engagement is ' +
          this.natureEngagementRadios +
          ' Content available is ' +
          this.contentAvailableRadios +
          ' in nature. ' +
          this.commentByInvestigator1 +
          '. A Google search provides links to ' +
          this.googleSearch +
          '. ' +
          this.commentByInvestigator2 +
          '. ' +
          this.finalInfo +
          '. ' +
          this.commentByInvestigator3 +
          '.'
      }

      if (this.riskTypes == 2) {
        this.riskComment =
          'The subject has a highly limited digital media and Google presence which demonstrates low levels of risk, with limited social media profiles. There is limited mandatory information provided and/or available to confirm matches;' +
          this.buildSubjectInfoList +
          ' The subject has a high commonality of given names combinations,' +
          this.commentByInvestigatorSubject +
          ' matches). Should the subject have accounts, these are linked to cell phone and email details which have not been provided. These results do not include the use of pseudonyms or aliases. '
      }

      if (this.riskTypes == 3) {
        this.riskComment =
          'The subject has a neutral, ' +
          this.socialPresenceRadios +
          ' social media presence The subject has high privacy settings across all platforms. A Google search provides links to inactive social media accounts. The subject demonstrates low levels of risk, with limited social media profiles. These results do not include the use of pseudonyms or aliases. '
      }

      if (this.riskTypes == 4) {
        this.riskComment =
          'The subject has a highly limited digital media and google presence which demonstrates low levels of risk, with limited social media profiles. The provided information is not matched/associated with any social media accounts, profiles or content. These results do not include the use of pseudonyms or aliases, or any accounts associated with unproved contact numbers or email addresses. ' +
          this.commentByInvestigatorSubject4 +
          '.'
      }
    },
    riskTypeChange() {
      this.riskTypes = this.riskTypeItemsValue
    },
    async show() {
      await this.$store
        .dispatch('risk/getRiskComment', this.$route.params.id)
        .then(response => {
          this.riskComment = this.getRiskComment
          this.updateRisk()
          this.dialog = true
        })
    },
    hide() {
      this.dialog = false
    }
  }
}
</script>
  <style scoped>
.mouseOver {
  cursor: pointer;
}

.textBoxPadding {
  padding: 5px;
  font-size: 18px;
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

.backgroundLightYellow {
  background-color: lightyellow;
  border: 1px solid yellow;
}

.digitalMediaRadios {
  color: blue;
  font-weight: bolder;
}

.privacySettingsRadios {
  color: orangered;
  font-weight: bolder;
}

.natureEngagementRadios {
  color: green;
  font-weight: bolder;
}

.contentAvailableRadios {
  color: dodgerblue;
  font-weight: bolder;
}

.socialCheckbox {
  color: purple;
  font-weight: bolder;
}

.activityRadios {
  color: darkolivegreen;
  font-weight: bolder;
}

.commentByInvestigator1 {
  color: firebrick;
  font-weight: bolder;
}

.commentByInvestigator2 {
  color: darkblue;
  font-weight: bolder;
}

.googleSearch {
  color: coral;
  font-weight: bolder;
}

.finalInfo {
  color: brown;
  font-weight: bolder;
}

.commentByInvestigator3 {
  color: darkolivegreen;
  font-weight: bolder;
}

.commentByInvestigatorSubject {
  color: brown;
  font-weight: bolder;
}

.v-textarea textarea {
  line-height: 40px !important;
  font-size: 18px !important;
}

.dialog2Box {
  font-size: 18px;
}
</style>
