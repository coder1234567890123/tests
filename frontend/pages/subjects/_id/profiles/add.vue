<template>
  <div
    id="app"
    class="container">
    <h1 class="title mb-3">Add Profile</h1>
    <v-card>
      <v-form
        ref="profileForm"
        v-model="valid"
        lazy-validation
      >
        <v-layout row>
          <v-flex md6>
            <v-text-field
              v-model="profile.link"
              label="Link"
              class="mx-3"/>
          </v-flex>
          <v-flex md6>
            <v-select
              v-model="profile.platform"
              :rules="platformRules"
              :items="platforms"
              item-text="label"
              item-value="value"
              label="Select a platform"
              class="mx-3"/>
          </v-flex>
        </v-layout>
        <v-layout row>
          <v-flex md6>
            <v-text-field
              v-model="profile.priority"
              label="Priority"
              type="number"
              class="mx-3"/>
          </v-flex>
        </v-layout>
        <v-layout row>
          <v-flex
            md12>
            <v-card-actions class="mx-2">
              <v-btn
                color="teal darken-3"
                class="white--text"
                @click="add">
                Add
              </v-btn>
              <v-btn
                :nuxt="true"
                :to="{ name: $getRoute('SUBJECTS_PROFILES'), params: $route.params.id }"
                flat>Cancel
              </v-btn>
            </v-card-actions>
          </v-flex>
        </v-layout>
      </v-form>
    </v-card>
  </div>
</template>
<script>
import { mapGetters } from 'vuex'

export default {
  data() {
    return {
      profile: {
        first_name: '',
        last_name: '',
        email_address: '',
        phone: '',
        link: '',
        platform: ''
      },
      valid: true,
      firstNameRules: [
        v => !!v || 'First name required ',
        v => (v && v.length <= 36) || 'Name must be less than 36 characters'
      ],
      lastNameRules: [
        v => !!v || 'Last name required',
        v =>
          (v && v.length <= 255) || 'Last name must be less than 255 characters'
      ],
      phoneRules: [
        v =>
          !(v && v.length <= 10) ||
          'Phone number must be at least 10 characters long'
      ],
      emailRules: [
        v =>
          !v ||
          /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(
            v
          ) ||
          'Email must be valid'
      ],
      platformRules: [v => !!v || 'Platform is required']
    }
  },
  computed: {
    ...mapGetters({
      platforms: 'static/platforms'
    })
  },
  methods: {
    add() {
      if (this.$refs.profileForm.validate()) {
        this.$store
          .dispatch('profile/create', this.profile)
          .then(() => {
            this.$toast.success('Profile successfully added!')
            this.$router.push({
              name: this.$getRoute('SUBJECTS_PROFILES'),
              params: this.$route.params.id
            })
            this.$router.go()
          })
          .catch(() => {
            this.$toast.error(
              'Could not add profile. please double check validation!'
            )
          })
      }
    }
  }
}
</script>
