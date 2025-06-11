<template>
  <v-app>
    <v-navigation-drawer
      :mini-variant="miniVariant"
      :clipped="clipped"
      v-model="drawer"
      fixed
      app
    >
      <v-list>
        <v-list-tile
          to="/"
          router
          exact
        >
          <v-list-tile-action>
            <v-icon>dashboard</v-icon>
          </v-list-tile-action>
          <v-list-tile-content>
            <v-list-tile-title>Dashboard</v-list-tile-title>
          </v-list-tile-content>
        </v-list-tile>

        <v-list-group
          :value="false"
          prepend-icon="face">
          <v-list-tile slot="activator">
            <v-list-tile-title>Subjects</v-list-tile-title>
          </v-list-tile>
          <v-list-tile
            v-if="this.$auth && (this.$auth.hasScope('ROLE_SUPER_ADMIN') || this.$auth.hasScope('ROLE_TEAM_LEAD')
              || this.$auth.hasScope('ROLE_USER_STANDARD') || this.$auth.hasScope('ROLE_USER_MANAGER')
            || this.$auth.hasScope('ROLE_ADMIN_USER'))"
            :to="{ name: $getRoute('SUBJECTS_ADD') }"
            router
            exact>
            <v-list-tile-action>
              <v-icon>add</v-icon>
            </v-list-tile-action>
            <v-list-tile-content>
              <v-list-tile-title>Add</v-list-tile-title>
            </v-list-tile-content>
          </v-list-tile>
          <v-list-tile
            :to="{ name: $getRoute('SUBJECTS_INDEX') }"
            router
            exact>
            <v-list-tile-action>
              <v-icon>search</v-icon>
            </v-list-tile-action>
            <v-list-tile-content>
              <v-list-tile-title>View</v-list-tile-title>
            </v-list-tile-content>
          </v-list-tile>
        </v-list-group>

        <v-list-group
          v-if="this.$auth && (this.$auth.hasScope('ROLE_SUPER_ADMIN'))"
          :value="false"
          prepend-icon="search">
          <v-list-tile slot="activator">
            <v-list-tile-title>Search Terms</v-list-tile-title>
          </v-list-tile>

          <v-list-tile
            to="/search/add"
            router
            exact>
            <v-list-tile-action>
              <v-icon>add</v-icon>
            </v-list-tile-action>
            <v-list-tile-content>
              <v-list-tile-title>Add</v-list-tile-title>
            </v-list-tile-content>
          </v-list-tile>


          <v-list-tile
            to="/search/view"
            router
            exact>
            <v-list-tile-action>
              <v-icon>search</v-icon>
            </v-list-tile-action>
            <v-list-tile-content>
              <v-list-tile-title>View</v-list-tile-title>
            </v-list-tile-content>
          </v-list-tile>
        </v-list-group>

        <v-list-group
          v-if="this.$auth && (this.$auth.hasScope('ROLE_SUPER_ADMIN') || this.$auth.hasScope('ROLE_TEAM_LEAD') || this.$auth.hasScope('ROLE_ANALYST'))"
          :value="false"
          prepend-icon="business">
          <v-list-tile slot="activator">
            <v-list-tile-title>Companies</v-list-tile-title>
          </v-list-tile>
          <v-list-tile
            v-if="this.$auth && (this.$auth.hasScope('ROLE_SUPER_ADMIN'))"
            to="/company/add"
            router
            exact>
            <v-list-tile-action>
              <v-icon>add</v-icon>
            </v-list-tile-action>
            <v-list-tile-content>
              <v-list-tile-title>Add</v-list-tile-title>
            </v-list-tile-content>
          </v-list-tile>
          <v-list-tile
            v-if="this.$auth && (this.$auth.hasScope('ROLE_SUPER_ADMIN') || this.$auth.hasScope('ROLE_TEAM_LEAD') || this.$auth.hasScope('ROLE_ANALYST'))"
            to="/company"
            router-f
            exact>
            <v-list-tile-action>
              <v-icon>search</v-icon>
            </v-list-tile-action>
            <v-list-tile-content>
              <v-list-tile-title>View</v-list-tile-title>
            </v-list-tile-content>
          </v-list-tile>
        </v-list-group>

        <v-list-group
          v-if="this.$auth && (this.$auth.hasScope('ROLE_SUPER_ADMIN'))"
          :value="false"
          prepend-icon="chat">
          <v-list-tile slot="activator">
            <v-list-tile-title>Questions</v-list-tile-title>
          </v-list-tile>

          <v-list-tile
            to="/question/add"
            router
            exact>
            <v-list-tile-action>
              <v-icon>add</v-icon>
            </v-list-tile-action>
            <v-list-tile-content>
              <v-list-tile-title>Add</v-list-tile-title>
            </v-list-tile-content>
          </v-list-tile>

          <v-list-tile
            to="/question/view"
            router
            exact>
            <v-list-tile-action>
              <v-icon>search</v-icon>
            </v-list-tile-action>
            <v-list-tile-content>
              <v-list-tile-title>View</v-list-tile-title>
            </v-list-tile-content>
          </v-list-tile>
        </v-list-group>
        <!--Todo check if code still need can do the everything from the dash board-->
        <!--        <v-list-group-->
        <!--          v-if="$auth.hasScope('ROLE_SUPER_ADMIN')"-->
        <!--          :value="false"-->
        <!--          prepend-icon="notes">-->
        <!--          <v-list-tile slot="activator">-->
        <!--            <v-list-tile-title>Reports</v-list-tile-title>-->
        <!--          </v-list-tile>-->
        <!--          <v-list-tile-->
        <!--            to="/report/history"-->
        <!--            router-->
        <!--            exact>-->
        <!--            <v-list-tile-action>-->
        <!--              <v-icon>search</v-icon>-->
        <!--            </v-list-tile-action>-->
        <!--            <v-list-tile-content>-->
        <!--              <v-list-tile-title>History</v-list-tile-title>-->
        <!--            </v-list-tile-content>-->
        <!--          </v-list-tile>-->
        <!--        </v-list-group>-->


        <v-list-group
          v-if="$auth.hasScope('ROLE_SUPER_ADMIN') || $auth.hasScope('ROLE_ADMIN_USER')"
          :value="false"
          prepend-icon="notes">
          <v-list-tile slot="activator">
            <v-list-tile-title>User tracking</v-list-tile-title>
          </v-list-tile>
          <v-list-tile
            to="/tracking"
            router
            exact>
            <v-list-tile-action>
              <v-icon>search</v-icon>
            </v-list-tile-action>
            <v-list-tile-content>
              <v-list-tile-title>View</v-list-tile-title>
            </v-list-tile-content>
          </v-list-tile>
        </v-list-group>

        <v-list-group
          v-if="$auth.hasScope('ROLE_SUPER_ADMIN') || $auth.hasScope('ROLE_ADMIN_USER')"
          :value="false"
          prepend-icon="assignment_ind">
          <v-list-tile slot="activator">
            <v-list-tile-title>Teams</v-list-tile-title>
          </v-list-tile>
          <v-list-tile
            to="/team"
            router
            exact>
            <v-list-tile-action>
              <v-icon>search</v-icon>
            </v-list-tile-action>
            <v-list-tile-content>
              <v-list-tile-title>View</v-list-tile-title>
            </v-list-tile-content>
          </v-list-tile>
        </v-list-group>
        <v-list-group
          v-if="$auth.hasScope('ROLE_SUPER_ADMIN') || $auth.hasScope('ROLE_ADMIN_USER') || $auth.hasScope('ROLE_USER_MANAGER')"
          :value="false"
          prepend-icon="group">
          <v-list-tile slot="activator">
            <v-list-tile-title>Users</v-list-tile-title>
          </v-list-tile>
          <v-list-tile
            to="/user/add"
            router
            exact>
            <v-list-tile-action>
              <v-icon>add</v-icon>
            </v-list-tile-action>
            <v-list-tile-content>
              <v-list-tile-title>Add</v-list-tile-title>
            </v-list-tile-content>
          </v-list-tile>
          <v-list-tile
            to="/user"
            router
            exact>
            <v-list-tile-action>
              <v-icon>search</v-icon>
            </v-list-tile-action>
            <v-list-tile-content>
              <v-list-tile-title>View</v-list-tile-title>
            </v-list-tile-content>
          </v-list-tile>
        </v-list-group>

        <v-list-group
          v-if="$auth.hasScope('ROLE_SUPER_ADMIN') || $auth.hasScope('ROLE_TEAM_LEAD') || $auth.hasScope('ROLE_ANALYST')"
          :value="false"
          prepend-icon="settings">
          <v-list-tile slot="activator">
            <v-list-tile-title>Admin</v-list-tile-title>
          </v-list-tile>
          <v-list-tile
            v-if="$auth.hasScope('ROLE_SUPER_ADMIN')"
            to="/admin/add"
            router
            exact>
            <v-list-tile-action>
              <v-icon>edit</v-icon>
            </v-list-tile-action>
            <v-list-tile-content>
              <v-list-tile-title>Scoring Adjustments</v-list-tile-title>
            </v-list-tile-content>
          </v-list-tile>

          <v-list-tile
            v-if="$auth.hasScope('ROLE_SUPER_ADMIN')"
            to="/admin/branding"
            router
            exact>
            <v-list-tile-action>
              <v-icon>picture_as_pdf</v-icon>
            </v-list-tile-action>
            <v-list-tile-content>
              <v-list-tile-title>Branding</v-list-tile-title>
            </v-list-tile-content>
          </v-list-tile>

          <v-list-tile
            v-if="$auth.hasScope('ROLE_SUPER_ADMIN')"
            to="/globalweighting"
            router
            exact>
            <v-list-tile-action>
              <v-icon>build</v-icon>
            </v-list-tile-action>
            <v-list-tile-content>
              <v-list-tile-title>Global Weighting</v-list-tile-title>
            </v-list-tile-content>
          </v-list-tile>

          <v-list-tile
            v-if="$auth.hasScope('ROLE_SUPER_ADMIN') || $auth.hasScope('ROLE_TEAM_LEAD') || $auth.hasScope('ROLE_ANALYST')"
            to="/admin/plugins"
            router
            exact>
            <v-list-tile-action>
              <v-icon>get_app</v-icon>
            </v-list-tile-action>
            <v-list-tile-content>
              <v-list-tile-title>Browser Plugins</v-list-tile-title>
            </v-list-tile-content>
          </v-list-tile>
        </v-list-group>

      </v-list>
    </v-navigation-drawer>

    <v-toolbar
      :clipped-left="clipped"
      fixed
      app>
      <v-btn
        icon
        @click.stop="miniVariant = !miniVariant"
      >
        <v-icon v-html="miniVariant ? 'chevron_right' : 'chevron_left'"/>
      </v-btn>
      <v-toolbar-title>
        <img
          class="logo-text"
          src="~/assets/images/farosian_logo.png">
      </v-toolbar-title>
      <v-spacer/>

      <!--      new code start-->
      <v-toolbar-items class="hidden-sm-and-down">
        <v-menu
          bottom
        >


          <v-list>
            <v-list-tile
              @click="() => {}">

              <v-list-tile-title @click="profile">

                <span>
                  <i class="material-icons">
                    message
                  </i>

                </span>
                <span class="menuText"> A Search has been completed </span>

                <br><br>
              </v-list-tile-title>
            </v-list-tile>

            <v-divider />
          </v-list>
        </v-menu>
      </v-toolbar-items>

      <!--      new code end-->
      <v-spacer/>


      <v-toolbar-title>
        <small>{{ $auth.user.first_name }} {{ $auth.user.last_name }} | {{ $auth.user.roles ? removeUnderscore($auth.user.roles[0]) : 'None' }}</small>
      </v-toolbar-title>
      <v-toolbar-items class="hidden-sm-and-down">
        <v-menu
          bottom
          transition="slide-y-transition"
          left>
          <v-btn
            slot="activator"
            icon
          >
            <v-icon>more_vert</v-icon>
          </v-btn>
          <v-list>
            <v-list-tile
              @click="() => {}">
              <v-list-tile-title @click="profile">
                <v-icon>person</v-icon>
                Profile
              </v-list-tile-title>
            </v-list-tile>
            <v-list-tile
              v-if="this.$auth && (this.$auth.hasScope('ROLE_ADMIN_USER'))"
              @click="() => {}">
              <v-list-tile-title
                @click="myCompany">
                <v-icon>business</v-icon>
                Company
              </v-list-tile-title>
            </v-list-tile>
            <v-divider />
            <v-list-tile
              @click="() => {}">
              <v-list-tile-title @click="logout">
                <v-icon>lock</v-icon>
                Logout
              </v-list-tile-title>
            </v-list-tile>
          </v-list>
        </v-menu>
      </v-toolbar-items>
    </v-toolbar>
    <v-content>
      <v-container grid-list-md>
        <nuxt/>
      </v-container>
    </v-content>
    <v-footer
      :fixed="fixed"
      app
    >
      <span>&copy; 2021</span>
    </v-footer>
  </v-app>
</template>

<script>
import _ from 'lodash'
export default {
  data() {
    return {
      clipped: true,
      drawer: true,
      fixed: false,
      items: [
        { icon: 'apps', title: 'Dashboard', to: '/' },
        { icon: 'call_split', title: 'Licenses', to: '/subscriptions' },
        { icon: 'present_to_all', title: 'Azure', to: '/azure' }
      ],
      miniVariant: false,
      right: true,
      rightDrawer: false,
      title: 'Adcorp Social Profiling'
    }
  },
  methods: {
    async logout() {
      await this.$auth.logout()
      this.$router.push('/login')
    },
    removeUnderscore(name) {
      let role = _.startCase(name)
      return role.substr(role.indexOf(' ') + 1)
    },
    profile() {
      let user = this.$auth.$state.user.id
      this.$router.push('/user/' + user + '/myProfile')
    },
    myCompany() {
      let user = this.$auth.$state.user.id
      this.$router.push('/company/myCompany')
    }
  }
}
</script>

<style>
.logo-text {
  width: 160px;
  vertical-align: middle;
}
.menuText {
  padding: 0px 0px 50px 0px !important;
  margin: 0px 0px 50px 0px !important;
}
</style>
