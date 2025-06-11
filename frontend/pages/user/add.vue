<template>
  <div
    id="app"
    class="container">
    <h1 class="title mb-3">Add User</h1>
    <v-card class="py-4">
      <v-form data-vv-scope="userForm">
        <v-layout
          row
          wrap>
          <v-flex md6>
            <v-text-field
              v-validate="'required|max:36'"
              v-model="user.first_name"
              :error-messages="errors.collect('userForm.first_name')"
              name="first_name"
              data-vv-name="first_name"
              label="First Name"
              class="mx-3"/>
          </v-flex>
          <v-flex md6>
            <v-text-field
              v-validate="'required|max:255'"
              v-model="user.last_name"
              :error-messages="errors.collect('userForm.last_name')"
              name="last_name"
              data-vv-name="last_name"
              label="Last Name"
              class="mx-3"/>
          </v-flex>
          <v-flex md6>
            <v-text-field
              v-validate="'required|email'"
              v-model="user.email"
              :error-messages="errors.collect('userForm.email')"
              name="email"
              data-vv-name="email"
              label="Email Address"
              browser-autocomplete="off"
              autocomplete="off"
              class="mx-3"/>
          </v-flex>
          <v-flex md6>
            <v-text-field
              v-validate="`${user.mobile_number ? 'numeric|min:10|max:10' : ''}`"
              v-model="user.mobile_number"
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
              v-model="user.company"
              :error-messages="errors.collect('userForm.company')"
              :items="companies"
              item-text="name"
              item-value="id"
              name="company"
              data-vv-name="company"
              label="Company"
              class="mx-3"/>
          </v-flex>
          <v-flex
            v-if="$auth.hasScope('ROLE_SUPER_ADMIN') || $auth.hasScope('ROLE_ADMIN_USER') || $auth.hasScope('ROLE_USER_MANAGER')"
            md6>
            <v-select
              v-validate="'required'"
              v-model="user.roles"
              :error-messages="errors.collect('userForm.role')"
              :items="customRoles"
              item-text="name"
              item-value="value"
              name="role"
              data-vv-name="role"
              label="Role"
              class="mx-3"/>
          </v-flex>
        </v-layout>
        <v-layout row>
          <v-flex
            md12
            class="mx-2">
            <v-btn
              color="teal darken-3"
              class="white--text"
              @click="submit">Add
            </v-btn>
            <v-btn
              :nuxt="true"
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
  async fetch({ store }) {
    await store.dispatch('static/initRoles')
    await store.dispatch('company/queryCompanies')
  },
  head() {
    return {
      title: 'Add user :: Farosian'
    }
  },
  data() {
    return {
      e1: 1,
      user: {
        email: '',
        first_name: '',
        last_name: '',
        mobile_number: '',
        roles: [],
        company: ''
      },
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
      companies: 'company/companies'
    }),
    customRoles() {
      console.log(this.$auth.hasScope('ROLE_USER_MANAGER'))
      if (this.$auth.hasScope('ROLE_ADMIN_USER')) {
        return this.roles.filter(function(sample) {
          return (
            sample.value === 'ROLE ADMIN_USER' ||
            sample.value === 'ROLE_USER_MANAGER' ||
            sample.value === 'ROLE_USER_STANDARD'
          )
        })
      } else if (this.$auth.hasScope('ROLE_USER_MANAGER')) {
        return this.roles.filter(function(sample) {
          return sample.value === 'ROLE_USER_STANDARD'
        })
      }
      return this.roles
    }
  },
  mounted() {
    this.$validator.localize('en', this.dictionary)
  },
  methods: {
    submit() {
      this.$validator.validateAll('userForm').then(result => {
        if (result) {
          if (typeof this.user.roles === 'string') {
            this.user.roles = [this.user.roles]
          }

          if (
            this.$auth.hasScope('ROLE_ADMIN_USER') ||
            this.$auth.hasScope('ROLE_USER_MANAGER')
          ) {
            this.user.company = { id: this.$auth.user.company.id }

            this.$store
              .dispatch('user/createCompanyUser', this.user)
              .then(() => {
                this.$toast.success('User successfully created!')
                this.$router.push('/user')
              })
              .catch(() => {
                this.$toast.error(
                  'Could not create user, please double check validation!'
                )
              })
          } else {
            this.$store
              .dispatch('user/create', this.user)
              .then(() => {
                this.$toast.success('User successfully created!')
                this.$router.push('/user')
              })
              .catch(() => {
                this.$toast.error(
                  'Could not create user, please double check validation!'
                )
              })
          }
        }
      })
    }
  }
}
</script>
