<template>
  <v-layout justify="center">
    <v-btn
      style="float: right"
      title="Confirmation Of Identity"
      @click="confirmationOfIdentity()"><span class="material-icons">
        perm_identity
      </span> Confirmation
    </v-btn>
    <v-dialog
      v-model="dialog2"
      scrollable
      max-width="800px">
      <v-card>
        <v-card-title>Select Identity Confirmation for: {{ platform }}</v-card-title>
        <v-divider/>
        <v-card-text style="height: 300px;">

          <v-container class="grey lighten-5">
            <v-layout
              row
              wrap>
              <v-flex md6>
                <v-checkbox
                  v-model="identityName"
                  class="identityCheckBox"
                  label="Name"
                  @change="save()"
                />
                <v-checkbox
                  v-model="identityMiddleName"
                  class="identityCheckBox"
                  label="Middle Name"
                  @change="save()"
                />
                <v-checkbox
                  v-model="identityInitials"
                  class="identityCheckBox"
                  label="Initials"
                  @change="save()"
                />
                <v-checkbox
                  v-model="identitySurname"
                  class="identityCheckBox"
                  label="Surname"
                  @change="save()"
                />
                <v-checkbox
                  v-model="identityImage"
                  class="identityCheckBox"
                  label="Image"
                  @change="save()"
                />
                <v-checkbox
                  v-model="identityLocation"
                  class="identityCheckBox"
                  label="Location"
                  @change="save()"
                />
                <v-checkbox
                  v-model="identityEmploymentHistory"
                  class="identityCheckBox"
                  label="Employment History"
                  @change="save()"
                />
                <v-checkbox
                  v-model="identityAcademicHistory"
                  class="identityCheckBox"
                  label="Academic history"
                  @change="save()"
                />
                <v-checkbox
                  v-model="identityCountry"
                  class="identityCheckBox"
                  label="Country"
                  @change="save()"
                />
                <v-checkbox
                  v-model="identityProfileImage"
                  class="identityCheckBox"
                  label="Profile Image"
                  @change="save()"
                />

              </v-flex>
              <v-flex md6>
                <v-checkbox
                  v-model="identityIdNumber"
                  class="identityCheckBox"
                  label="Id Number"
                  @change="save()"
                />
                <v-checkbox
                  v-model="identityContactNumber"
                  class="identityCheckBox"
                  label="Contact Number"
                  @change="save()"
                />
                <v-checkbox
                  v-model="identityEmailAddress"
                  class="identityCheckBox"
                  label="Email Address"
                  @change="save()"
                />
                <v-checkbox
                  v-model="identityPhysicalAddress"
                  class="identityCheckBox"
                  label="Physical Address"
                  @change="save()"
                />
                <v-checkbox
                  v-model="identityTag"
                  class="identityCheckBox"
                  label="Tag"
                  @change="save()"
                />
                <v-checkbox
                  v-model="identityAlias"
                  class="identityCheckBox"
                  label="Known as/Alias"
                  @change="save()"
                />
                <v-checkbox
                  v-model="identityLink"
                  class="identityCheckBox"
                  label="Link"
                  @change="save()"
                />
                <v-checkbox
                  v-model="identityLocationHistory"
                  class="identityCheckBox"
                  label="Location History"
                  @change="save()"
                />
                <v-checkbox
                  v-model="identityHandle"
                  class="identityCheckBox"
                  label="Handle"
                  @change="save()"
                />
                <v-checkbox
                  v-model="identityTitle"
                  class="identityCheckBox"
                  label="Title eg Dr"
                  @change="save()"
                />
              </v-flex>
            </v-layout>
          </v-container>


        </v-card-text>
        <v-divider/>
        <v-card-actions>
          <v-btn
            text
            @click="dialog2 = false">Close
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

</v-layout></template>
<script>
import { mapGetters } from 'vuex'

export default {
  props: {
    platform: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      dialog2: false,
      identityName: false,
      identityMiddleName: false,
      identityInitials: false,
      identitySurname: false,
      identityImage: false,
      identityLocation: false,
      identityEmploymentHistory: false,
      identityAcademicHistory: false,
      identityCountry: false,
      identityProfileImage: false,
      identityIdNumber: false,
      identityContactNumber: false,
      identityEmailAddress: false,
      identityPhysicalAddress: false,
      identityTag: false,
      identityAlias: false,
      identityLink: false,
      identityLocationHistory: false,
      identityHandle: false,
      identityTitle: false
    }
  },
  computed: {
    ...mapGetters({
      details: 'report/duplicateDetails'
    })
  },

  methods: {
    confirmationOfIdentity() {
      this.dialog2 = true

      let data = {
        id: this.$route.params.id,
        platform: this.platform
      }

      this.$store
        .dispatch('profile/getConfirmationOfIdentity', data)
        .then(response => {
          this.identityName = response[0].identity_name
          this.identityMiddleName = response[0].identity_middle_name
          this.identityInitials = response[0].identity_initials
          this.identitySurname = response[0].identity_surname
          this.identityImage = response[0].identity_image
          this.identityLocation = response[0].identity_location
          this.identityEmploymentHistory =
            response[0].identity_employment_history
          this.identityAcademicHistory = response[0].identity_academic_history
          this.identityCountry = response[0].identity_country
          this.identityProfileImage = response[0].identity_profile_image
          this.identityIdNumber = response[0].identity_id_number
          this.identityContactNumber = response[0].identity_contact_number
          this.identityEmailAddress = response[0].identity_email_address
          this.identityPhysicalAddress = response[0].identity_physical_address
          this.identityTag = response[0].identity_tag
          this.identityAlias = response[0].identity_alias
          this.identityLink = response[0].identity_link
          this.identityLocationHistory = response[0].identity_location_history
          this.identityHandle = response[0].identity_handle
          this.identityTitle = response[0].identity_title
        })
        .catch(() => {
          // this.$toast.error('Error getting Confirmation Of Identity')
        })
    },
    save() {
      let data = {
        id: this.$route.params.id,
        platform: this.platform,
        identity_name: this.identityName,
        identity_middle_name: this.identityMiddleName,
        identity_initials: this.identityInitials,
        identity_surname: this.identitySurname,
        identity_image: this.identityImage,
        identity_location: this.identityLocation,
        identity_employment_history: this.identityEmploymentHistory,
        identity_academic_history: this.identityAcademicHistory,
        identity_country: this.identityCountry,
        identity_profile_image: this.identityProfileImage,
        identity_id_number: this.identityIdNumber,
        identity_contact_number: this.identityContactNumber,
        identity_email_address: this.identityEmailAddress,
        identity_physical_address: this.identityPhysicalAddress,
        identity_tag: this.identityTag,
        identity_alias: this.identityAlias,
        identity_link: this.identityLink,
        identity_location_history: this.identityLocationHistory,
        identity_handle: this.identityHandle,
        identity_title: this.identityTitle
      }

      this.$store
        .dispatch('profile/confirmationOfIdentity', data)
        .then(() => {
          this.$toast.success('Confirmation Of Identity Updated')
        })
        .catch(error => {
          this.$toast.error('Could not update Confirmation Of Identity')
        })
    }
  }
}
</script>
<style scoped>
.mouseOver {
  cursor: pointer;
}

.identityCheckBox {
  margin: 0px 0px 0px 0px !important;
  padding: 0px 0px 0px 0px !important;
}
</style>
