<template>
  <v-toolbar>
    <v-toolbar-title><img 
      id="mainLogo" 
      src="~/assets/images/adcorp_logo.png"></v-toolbar-title>
    <v-spacer />
    <v-toolbar-items class="hidden-sm-and-down">
      <v-btn 
        flat 
        to="/">Dashboard</v-btn>
      <v-btn 
        flat 
        to="/subjects/add">Add</v-btn>
      <v-btn 
        flat 
        to="/subjects">Results</v-btn>
      <template v-if="isAuthenticated">
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
<style>
#mainLogo {
  height: 50px;
  margin: 0px 0px 0px 60px;
}
</style>
