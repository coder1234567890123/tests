<template>
  <v-card
    v-if="quickCheck = true"
    class="elevation-12">
    <v-toolbar
      dark
      color="primary">
      <v-toolbar-title>Reset Password</v-toolbar-title>
      <v-spacer/>
    </v-toolbar>
    <v-card-text>

      <div >
        <v-form>
          <v-text-field
            v-model="password"
            prepend-icon="lock"
            name="password"
            label="New Password"
            type="password"
            autofocus/>
          <v-text-field
            v-model="password_confirm"
            :rules="passwordRules"
            prepend-icon="lock"
            name="password_confirm"
            label="Confirm New Password"
            type="password"/>
        </v-form>

    </div></v-card-text>
    <v-card-actions>
      <v-btn
        color="secondary"
        to="/login">
        Back To Login
      </v-btn>
      <v-spacer/>
      <v-btn
        ref="submit-btn"
        color="teal darken-3"
        class="white--text"
        @click="submit">
        Submit
      </v-btn>
    </v-card-actions>
  </v-card>

  <v-card
    v-else
    class="elevation-12">
    <v-card-text>
      <h1>Sorry you cant reset emails</h1>
    </v-card-text>
    <v-card-actions>
      <v-btn
        color="secondary"
        @click="goHomeClick">
        Back To Login
      </v-btn>
      <v-spacer/>
    </v-card-actions>
  </v-card>


</template>

<script>
export default {
  auth: false,
  layout: 'login',
  head() {
    return {
      title: 'Reset Password :: Farosian'
    }
  },
  data() {
    return {
      loading: false,
      error: null,
      email: '',
      password: '',
      password_confirm: '',
      passwordRules: [
        v => !!v || 'Password is required.',
        v => v === this.password || 'Passwords do not match'
      ]
    }
  },
  mounted() {
    if (this.$route.params.token) {
      if (this.$route.params.token.length >= '30') {
        this.quickCheck = true
      }
    }
  },
  methods: {
    goHomeClick() {
      this.$router.push('/login')
    },
    async submit() {
      this.loading = true
      this.$store
        .dispatch('user/reset', { password: this.password })
        .then(() => {
          this.loading = false
          this.$toast.success('Password was reset successfully.')
          this.$router.push('/login')
        })
        .catch(() => {
          this.loading = false
          this.$toast.error(
            'Could not reset your password. Please try again later.'
          )
        })
    }
  }
}
</script>
