<template>
  <v-app
  >
    <v-toolbar
      class="pl-0"
      app>
      <v-btn
        icon
      />
      <v-toolbar-title>
        <img
          class="logo-text"
          src="~/assets/images/farosian_logo.png">
      </v-toolbar-title>
      <v-spacer />
      <v-toolbar-items class="hidden-sm-and-down">
        <v-btn
          :to="{ name: $getRoute('SUBJECTS_VIEW'), params: { id: $route.params.id } }"
          flat
          small
          router>
          <v-icon class="mr-1">fa fa-sign-out</v-icon>
          Exit
        </v-btn>
      </v-toolbar-items>
    </v-toolbar>
    <v-content>
      <v-container grid-list-md>
        <nuxt />
      </v-container>
    </v-content>
    <v-footer
      :fixed="true"
      app>
      <span>&copy; 2021</span>
    </v-footer>
  </v-app>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  clipped: true,
  drawer: true,
  right: true,
  rightDrawer: false,
  title: 'Adcorp Social Profiling'
}
</script>

<style scoped>
.logo-text {
  width: 160px;
  vertical-align: middle;
}
</style>
