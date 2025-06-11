<template>
  <div
    id="app"
    class="container">
    <h1 class="title mb-3">Profile</h1>
    <v-card>
      <v-form data-vv-scope="userForm">
        <v-layout
          v-if="user !== null"
          row
          wrap>
          <v-flex
            md12
            class="user-info">
            <v-subheader class="justify-center">Personal Information</v-subheader>
            <v-layout
              row
              wrap
              px-3>
              <v-flex md3><label class="right py-3"><strong>First Name</strong></label></v-flex>
              <v-flex md8>
                <v-text-field
                  v-validate="'required|max:36'"
                  :value="user.first_name"
                  :error-messages="errors.collect('userForm.first_name')"
                  name="first_name"
                  data-vv-name="first_name"
                  label="First Name"
                  class="mx-3"
                  solo
                  @input="updateState('first_name', $event)"/>
              </v-flex>
              <v-flex md3><label class="right py-3"><strong>Last Name</strong></label></v-flex>
              <v-flex md8>
                <v-text-field
                  v-validate="'required|max:255'"
                  :value="user.last_name"
                  :error-messages="errors.collect('userForm.last_name')"
                  name="last_name"
                  data-vv-name="last_name"
                  label="Last Name"
                  class="mx-3"
                  solo
                  @input="updateState('last_name', $event)"/>
              </v-flex>
              <v-flex md3><label class="right py-3"><strong>Email</strong></label></v-flex>
              <v-flex md8>
                <v-text-field
                  :value="user.email"
                  class="mx-3"
                  solo-inverted
                  readonly/>
              </v-flex>
              <v-flex md3><label class="right py-3"><strong>Telephone Number</strong></label></v-flex>
              <v-flex md8>
                <v-text-field
                  v-validate="`${user.tel_number ? 'numeric|min:10|max:10' : ''}`"
                  :value="user.tel_number"
                  :error-messages="errors.collect('userForm.telephone')"
                  name="telephone"
                  data-vv-name="telephone"
                  label="Telephone Number"
                  browser-autocomplete="off"
                  autocomplete="off"
                  class="mx-3"
                  solo
                  @input="updateState('tel_number', $event)"/>
              </v-flex>
              <v-flex md3><label class="right py-3"><strong>Mobile Number</strong></label></v-flex>
              <v-flex md8>
                <v-text-field
                  v-validate="`${user.mobile_number ? 'numeric|min:10|max:10' : ''}`"
                  :value="user.mobile_number"
                  :error-messages="errors.collect('userForm.mobile')"
                  name="mobile"
                  data-vv-name="mobile"
                  label="Mobile Number"
                  browser-autocomplete="off"
                  autocomplete="off"
                  class="mx-3"
                  solo
                  @input="updateState('mobile', $event)"/>
              </v-flex>
              <v-flex md3><label class="right py-3"><strong>Fax Number</strong></label></v-flex>
              <v-flex md8>
                <v-text-field
                  :value="user.fax_number"
                  class="mx-3"
                  solo-inverted
                  readonly/>
              </v-flex>
              <v-flex md3><label class="right py-3"><strong>Role</strong></label></v-flex>
              <v-flex md8>
                <v-text-field
                  :value="user.roles.length ? getFormattedText(user.roles.join(', '), 'ROLE') : 'N/A'"
                  class="mx-3"
                  solo-inverted
                  readonly/>
                <div v-if="$auth.hasScope('ROLE_SUPER_ADMIN')">
                  <v-btn
                    v-if="user.roles.includes('ROLE_ANALYST')"
                    slot="activator"
                    small
                    flat
                    color="blue"
                    class="ma-0 left"
                    @click="openTeamDialog">{{ user.team_id ? 'Re-Assign' : 'Assign' }} to a team
                  </v-btn>
                </div>
              </v-flex>
              <v-flex md3><label class="right py-3"><strong>Reset Password</strong></label></v-flex>
              <v-flex md8>
                <v-btn
                  slot="activator"
                  color="blue darken-3"
                  class="white--text ma-0 right"
                  @click="resetPassword"><i class="material-icons">
                    email
                  </i> Send Email
                </v-btn>
              </v-flex>
            </v-layout>
            <v-divider/>
            <v-layout
              row
              px-3/>
          </v-flex>
        </v-layout>
      </v-form>
    </v-card>
    <v-dialog
      v-model="teamDialog"
      scrollable
      persistent
      max-width="320px">
      <v-card>
        <v-card-title>Select a Team Lead</v-card-title>
        <v-divider/>
        <v-card-text style="height: 300px;">
          <v-radio-group
            v-model="assignedTeam"
            column>
            <v-radio
              v-for="item in teams"
              :key="item.id"
              :label="item.team_name"
              :value="item.id"/>
          </v-radio-group>
          <v-flex v-if="undefined === teams">
            <v-alert
              slot="no-results"
              :value="true"
              color="error"
              icon="warning">
              No Team Lead to assign.
            </v-alert>
          </v-flex>
        </v-card-text>
        <v-divider/>
        <v-card-actions>
          <v-btn
            color="grey darken-1"
            flat
            @click.native="teamDialog = !teamDialog">Close
          </v-btn>
          <v-btn
            color="blue darken-1"
            flat
            @click="assignTeam">Assign
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>
<script>
import { mapGetters } from 'vuex'
import _ from 'lodash'

export default {
  inject: ['$validator'],
  head() {
    return {
      title: 'User Profile :: Farosian'
    }
  },
  data() {
    return {
      teamDialog: false,
      teams: [],
      assignedTeam: null
    }
  },
  computed: {
    ...mapGetters({
      user: 'user/user'
    })
  },
  mounted() {
    this.$store.dispatch('user/get', this.$route.params.id)
  },
  methods: {
    resetPassword() {
      let data = { email: this.user.email }
      this.$store
        .dispatch('user/forgot', data)
        .then(() => {
          this.$toast.success('Reset Password Email sent')
        })
        .catch(() => {
          this.$toast.error('Could not send Email')
        })
    },
    updateState(prop, value) {
      value = prop === 'roles' ? [value] : value
      this.$store.dispatch('user/updateUser', { prop, value }).catch(() => {
        console.log('Could not update profile data')
      })
    },
    submit() {
      this.$validator.validateAll('userForm').then(result => {
        if (result) {
          this.$store
            .dispatch('user/updateProfile', this.user)
            .then(() => {
              this.$toast.success('Profile successfully updated!')
            })
            .catch(() => {
              this.$toast.error(
                'Could not update profile, please double check validation!'
              )
            })
        }
      })
    },

    getFormattedText(value, trim = '') {
      return _.startCase(_.replace(value, new RegExp(trim, 'gi'), ''))
    },
    assignTeam() {
      this.$store
        .dispatch('user/assignTeam', this.assignedTeam)
        .then(() => {
          this.$toast.success('User assigned...')
          this.teamDialog = false
        })
        .catch(() => {
          this.$toast.error('Could not assign user...')
        })
    },
    openTeamDialog() {
      this.teamDialog = true
      this.$store.dispatch('user/queryTeams').then(response => {
        this.teams = response
      })
    }
  }
}
</script>
