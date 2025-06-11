<template>
  <v-card class="elevation-12">
    <v-toolbar class="login-toolbar">
      <v-toolbar-title>
        <img
          class="logo-text"
          src="~/assets/images/farosian_logo.png">
      </v-toolbar-title>
    </v-toolbar>
    <v-card-text>
      <v-form>
        <v-alert
          v-model="showError"
          dismissible
          type="error"
        >
          {{ error }}
        </v-alert>
        <v-text-field
          v-model="email"
          prepend-icon="person"
          name="email"
          label="Email"
          type="text"
          autofocus/>
        <v-text-field
          id="password"
          v-model="password"
          prepend-icon="lock"
          name="password"
          label="Password"
          type="password"
          @keyup.enter="login"/>
      </v-form>
    </v-card-text>
    <v-card-actions>
      <v-btn
        color="teal darken-3"
        class="white--text"
        to="/forgot-password"
      >
        Forgot Password
      </v-btn>
      <v-spacer/>
      <v-btn
        ref="login-btn"
        :loading="loading"
        color="primary"
        @click="login"
      >
        Login
      </v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
export default {
  layout: 'login',
  head() {
    return {
      title: 'Login :: Farosion'
    }
  },
  data() {
    return {
      loading: false,
      error: null,
      email: '',
      password: ''
    }
  },
  computed: {
    showError() {
      return this.error != null
    }
  },
  methods: {
    async login() {
      try {
        console.log('First')
        console.log(process.env.blogStoragePath)
        console.log('Second')
        // console.log(blogStoragePath)
        console.log('Third')
        // console.log($config.blogStoragePath)
        // console.log(process.env.BASE_URL)
        // console.log($config.BaseURL)
        // console.log(baseURL)
        // console.log(process.env.BLOB_URL)
        // console.log($config.BlobURL)
        // console.log(blobURL)
        // console.log(process.env.toString())
        this.loading = true
        this.error = null
        await this.$auth.loginWith('local', {
          data: {
            email: this.email,
            password: this.password
          }
        })
        this.$router.push('/')
      } catch (e) {
        this.loading = false
        if (
          typeof e.response !== 'undefined' &&
          typeof e.response.data.message !== 'undefined'
        ) {
          this.error = e.response.data.message
        } else {
          this.error = 'An unknown error occurred! Please try again later?'
        }
      }
    }
  }
}
</script>

<style>
.login-toolbar .v-toolbar__title {
  width: 100%;
  text-align: center;
}
.logo-text {
  width: 160px;
  vertical-align: middle;
}
</style>
