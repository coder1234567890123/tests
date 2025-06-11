<template>
  <v-card class="elevation-12">
    <v-toolbar
      dark
      color="primary">
      <v-toolbar-title>Forgot Password</v-toolbar-title>
      <v-spacer/>
    </v-toolbar>
    <v-card-text>
      <v-form>
        <v-text-field
          v-model="email"
          prepend-icon="person"
          name="email"
          label="Email"
          autofocus/>
      </v-form>
    </v-card-text>
    <v-card-actions>
      <v-btn
        color="secondary"
        to="/login">
        Back To Login
      </v-btn>
      <v-spacer/>
      <v-btn
        ref="submit-btn"
        :loading="loading"
        color="primary"
        @click="submit">
        Submit
      </v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
export default {
  auth: false,
  layout: 'login',
  head() {
    return {
      title: 'Forgot Password :: Farosian'
    }
  },
  data() {
    return {
      loading: false,
      error: null,
      email: ''
    }
  },
  computed: {},
  methods: {
    async submit() {
      this.loading = true
      this.$store
        .dispatch('user/forgot', { email: this.email })
        .then(() => {
          this.loading = false
          this.$toast.success('Email sent successfully.')
          this.$router.push('/login')
        })
        .catch(() => {
          this.loading = false
          this.$toast.error('Could not send email. Please try again later.')
        })
    }
  }
}
</script>
