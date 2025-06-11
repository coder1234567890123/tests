<template>
  <div
    id="app"
    class="container">
    <h1 class="title mb-3">Edit User</h1>
    <v-card class="py-4">
      <v-form data-vv-scope="userForm">
        <v-layout
          row
          wrap>
          <v-flex md6>
            <v-text-field
              v-validate="'required|max:36'"
              :value="user.first_name"
              :error-messages="errors.collect('userForm.first_name')"
              name="first_name"
              data-vv-name="first_name"
              label="First Name"
              class="mx-3"
              @input="updateState('first_name', $event)"/>
          </v-flex>
          <v-flex md6>
            <v-text-field
              v-validate="'required|max:255'"
              :value="user.last_name"
              :error-messages="errors.collect('userForm.last_name')"
              name="last_name"
              data-vv-name="last_name"
              label="Last Name"
              class="mx-3"
              @input="updateState('last_name', $event)"/>
          </v-flex>
          <v-flex md6>
            <v-text-field
              v-validate="'required|email'"
              :value="user.email"
              :error-messages="errors.collect('userForm.email')"
              name="email"
              data-vv-name="email"
              label="Email Address"
              class="mx-3"
              @input="updateState('email', $event)"/>
          </v-flex>
          <v-flex md6>
            <v-text-field
              v-validate="`${user.mobile_number ? 'numeric|min:10|max:10' : ''}`"
              :value="user.mobile_number"
              :error-messages="errors.collect('userForm.mobile')"
              name="mobile"
              data-vv-name="mobile"
              label="Mobile Number"
              browser-autocomplete="off"
              autocomplete="off"
              class="mx-3"/>
          </v-flex>
          <v-flex
            v-if="$auth.hasScope('ROLE_SUPER_ADMIN')"
            md6>
            <v-select
              v-validate="`${['ROLE_ADMIN_USER', 'ROLE_USER_MANAGER', 'ROLE_USER_STANDARD'].some(el => user.roles.includes(el)) ? 'required' : ''}`"
              :value="user.company"
              :error-messages="errors.collect('userForm.company')"
              :items="companies"
              item-text="name"
              item-value="id"
              name="company"
              data-vv-name="company"
              label="Company"
              class="mx-3"
              @input="updateState('company', $event)"/>
          </v-flex>
          <v-flex
            v-if="$auth.hasScope('ROLE_SUPER_ADMIN') || $auth.hasScope('ROLE_ADMIN_USER') || $auth.hasScope('ROLE_USER_MANAGER')"
            md6>
            <v-select
              v-validate="'required'"
              :value="user.roles ? user.roles[0] : null"
              :error-messages="errors.collect('userForm.role')"
              :items="customRoles"
              item-text="name"
              item-value="value"
              name="role"
              data-vv-name="role"
              label="Role"
              class="mx-3"
              @input="updateState('roles', $event)"/>
          </v-flex>
        </v-layout>
        <v-layout row>
          <v-flex md6>
            <label class="left ml-3 py-3"><strong>Reset Company</strong></label>
            <v-btn
              v-if="$auth.hasScope('ROLE_SUPER_ADMIN')"
              slot="activator"
              color="blue darken-3"
              class="white--text right"
              @click="resetBusiness"><i class="material-icons">
                business
              </i> &nbsp; Update
            </v-btn>
          </v-flex>
          <v-flex md6>
            <label class="left ml-3 py-3"><strong>Reset Password</strong></label>
            <v-btn
              slot="activator"
              color="blue darken-3"
              class="white--text right"
              @click="resetPassword"><i class="material-icons">
                email
              </i> &nbsp;Send Email
            </v-btn>
          </v-flex>
        </v-layout>
        <v-layout row>
          <v-flex
            :nuxt="true"
            md12
            class="mx-2">
            <v-btn
              color="teal darken-3"
              class="white--text"
              @click="submit()">Update
            </v-btn>
            <v-btn
              to="/user"
              flat>Cancel
            </v-btn>
          </v-flex>
        </v-layout>
      </v-form>
    </v-card>
  </div>
</template>
<script>
import { mapGetters } from 'vuex'

export default {
  inject: ['$validator'],
  async fetch({ store, route }) {
    await store.dispatch('static/initRoles')
    await store.dispatch('company/queryCompanies')
    await store.dispatch('user/get', route.params.id)
  },
  head() {
    return {
      title: 'Edit user :: Farosian'
    }
  },
  data() {
    return {
      e1: 1,
      dictionary: {
        attributes: {
          first_name: 'First Name',
          last_name: 'Last Name',
          email: 'Email Address',
          mobile: 'Mobile Number',
          company: 'Company',
          role: 'Role'
        },
        custom: {}
      }
    }
  },
  computed: {
    ...mapGetters({
      roles: 'static/roles',
      companies: 'company/companies',
      user: 'user/user'
    }),
    customRoles() {
      if (this.$auth.hasScope('ROLE_ADMIN_USER')) {
        return this.roles.filter(function(sample) {
          return (
            sample.name === 'ROLE_ADMIN_USER' ||
            sample.name === 'ROLE_USER_MANAGER' ||
            sample.name === 'ROLE_USER_STANDARD'
          )
        })
      } else if (this.$auth.hasScope('ROLE_USER_MANAGER')) {
        return this.roles.filter(function(sample) {
          return sample.name === 'ROLE_USER_STANDARD'
        })
      }

      return this.roles
    }
  },
  mounted() {
    this.$validator.localize('en', this.dictionary)
  },
  methods: {
    async resetBusiness() {
      if (confirm('You are about to make this a Farosian user!')) {
        await this.$store.dispatch('user/resetCompany', this.user.id)
        this.$toast.success('Reset Company')
        this.$router.go()
      }
    },
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
        console.log('Could not update user data')
      })
    },
    submit() {
      this.$validator.validateAll('userForm').then(result => {
        if (result) {
          if (this.user.company instanceof Object) {
            this.updateState('company', this.user.company.id)
          }

          this.$store
            .dispatch('user/update', this.user)
            .then(() => {
              this.$toast.success('User successfully updated!')
              this.$router.push('/user')
            })
            .catch(() => {
              this.$toast.error(
                'Could not update user, please double check validation!'
              )
            })
        }
      })
    }
  }
}
</script>
