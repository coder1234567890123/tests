<template>
  <v-toolbar>
    <v-toolbar-side-icon />
    <v-toolbar-title>Adcorp Profiling</v-toolbar-title>
    <v-spacer />
    <v-toolbar-items class="hidden-sm-and-down">
      <template v-if="isAuthenticated">
        <v-btn
          flat
          to="/">Dashboard</v-btn>
        <v-btn
          flat
          to="/subjects/add">Add</v-btn>
        <v-btn
          flat
          to="/subject/current">Current</v-btn>
        <v-btn
          flat
          to="/subject/history">History</v-btn>
        <v-btn
          flat
          to="/subject/search">Search</v-btn>
        <v-btn
          flat
          to="/profile">{{ loggedInUser.first_name }}</v-btn>
        <v-btn
          flat
          @click="logout">Logout</v-btn>
      </template>
      <v-btn
        v-else
        flat
        to="/login">Login</v-btn>
    </v-toolbar-items>
  </v-toolbar>
</template>
<script>
import { mapGetters } from 'vuex'

export default {
  computed: {
    ...mapGetters(['isAuthenticated', 'loggedInUser'])
  },
  methods: {
    async logout() {
      await this.$auth.logout()
      return this.$auth.redirect('login')
    }
  }
}
</script>
