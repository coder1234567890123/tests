<template>
  <div
    id="app"
    class="container">
    <h1 class="title mb-3">View Company</h1>

    <v-card>
      <v-card-text>
        <v-subheader class="justify-center">Company Information</v-subheader>
        <v-layout
          row
          wrap
          px-3>
          <v-flex
            md5
            py-2>
            <strong>Name</strong>
          </v-flex>
          <v-flex
            md7
            py-2>
            <span>{{ company.name }}</span>
          </v-flex>
          <v-flex
            md5
            py-2>
            <strong>Address</strong>
          </v-flex>
          <v-flex
            md7
            py-2>
            <span>{{ company.street1 }}</span><br>
            <span>{{ company.street2 }}</span>
          </v-flex>
          <v-flex
            md5
            py-2>
            <strong>Suburb</strong>
          </v-flex>
          <v-flex
            md7
            py-2>
            <span>{{ company.suburb }}</span>
          </v-flex>
          <v-flex
            md5
            py-2>
            <strong>City</strong>
          </v-flex>
          <v-flex
            md7
            py-2>
            <span>{{ company.city }}</span>
          </v-flex>
          <v-flex
            md5
            py-2>
            <strong>Province</strong>
          </v-flex>
          <v-flex
            md7
            py-2>
            <span>{{ company.province }}</span>
          </v-flex>
          <v-flex
            md5
            py-2>
            <strong>Country</strong>
          </v-flex>
          <v-flex
            md7
            py-2>
            <span>{{ company.country.name }}</span>
          </v-flex>

          <v-flex
            md5
            py-2>
            <strong>Vat</strong>
          </v-flex>
          <v-flex
            md7
            py-2>
            <span>{{ company.vat_number }}</span>
          </v-flex>

          <v-flex
            md5
            py-2>
            <strong>Company Types</strong>
          </v-flex>
          <v-flex
            md7
            py-2>
            <span>{{ company.company_types }}</span>
          </v-flex>


        </v-layout>

        <v-spacer
          class="mt-4"/>
        <v-subheader class="justify-center">Contact Information</v-subheader>
        <v-layout
          row
          wrap
          px-3>
          <v-flex
            md5
            py-2>
            <strong>Email</strong>
          </v-flex>
          <v-flex
            md7
            py-2>
            <span>{{ company.email }}</span>
          </v-flex>
          <v-flex
            md5
            py-2>
            <strong>Mobile Number</strong>
          </v-flex>
          <v-flex
            md7
            py-2>
            <span>{{ company.mobile_number }}</span>
          </v-flex>
          <v-flex
            md5
            py-2>
            <strong>Telephone Number</strong>
          </v-flex>
          <v-flex
            md7
            py-2>
            <span>{{ company.tel_number }}</span>
          </v-flex>
          <v-flex
            md5
            py-2>
            <strong>Fax Number</strong>
          </v-flex>
          <v-flex
            md7
            py-2>
            <span>{{ company.fax_number }}</span>
          </v-flex>
        </v-layout>

        <v-spacer
          class="mt-4"/>
        <v-subheader class="justify-center">Contact Person Information</v-subheader>
        <v-layout
          row
          wrap
          px-3>
          <v-flex
            md5
            py-2>
            <strong>First Name</strong>
          </v-flex>
          <v-flex
            md7
            py-2>
            <span>{{ company.contact_firstname }}</span>
          </v-flex>
          <v-flex
            md5
            py-2>
            <strong>Last Name</strong>
          </v-flex>
          <v-flex
            md7
            py-2>
            <span>{{ company.contact_lastname }}</span>
          </v-flex>
          <v-flex
            md5
            py-2>
            <strong>Email Address</strong>
          </v-flex>
          <v-flex
            md7
            py-2>
            <span>{{ company.contact_email }}</span>
          </v-flex>
          <v-flex
            md5
            py-2>
            <strong>Contact Telephone</strong>
          </v-flex>
          <v-flex
            md7
            py-2>
            <span>{{ company.contact_telephone }}</span>
          </v-flex>
        </v-layout>

        <v-spacer
          class="mt-4"/>
        <v-subheader class="justify-center">Account Holder Information</v-subheader>
        <v-layout
          row
          wrap
          px-3>
          <v-flex
            md5
            py-2>
            <strong>First Name</strong>
          </v-flex>
          <v-flex
            md7
            py-2>
            <span>{{ company.account_holder_first_name }}</span>
          </v-flex>
          <v-flex
            md5
            py-2>
            <strong>Last Name</strong>
          </v-flex>
          <v-flex
            md7
            py-2>
            <span>{{ company.account_holder_last_name }}</span>
          </v-flex>
          <v-flex
            md5
            py-2>
            <strong>Email Address</strong>
          </v-flex>
          <v-flex
            md7
            py-2>
            <span>{{ company.account_holder_email }}</span>
          </v-flex>
          <v-flex
            md5
            py-2>
            <strong>Telephone Number</strong>
          </v-flex>
          <v-flex
            md7
            py-2>
            <span>{{ company.account_holder_phone }}</span>
          </v-flex>
        </v-layout>
        <v-spacer
          class="mt-4"/>
        <v-subheader class="justify-center">Company CI and Branding</v-subheader>
        <v-layout
          row
          wrap
          px-3>
          <v-flex
            md5
            py-2>
            <strong>Footer Link</strong>
          </v-flex>
          <v-flex
            md7
            py-2>
            <span>{{ company.footer_link }}</span>
          </v-flex>

          <v-flex
            md5
            py-2>
            <strong>Theme Color</strong>
          </v-flex>
          <v-flex
            md7
            py-2>
            <span>{{ company.theme_color }}</span>
            <span
              :style="company.theme_color ? { backgroundColor : company.theme_color }: ''"
              class="sp"/>
          </v-flex>
          <v-flex
            md5
            py-2>
            <strong>Front Page Image</strong>
          </v-flex>
          <v-flex
            md7
            py-2>
            <v-card
              v-if="company.image_front_page"
              flat
              tile
              class="d-flex">
              <v-img
                :src="`${blogStoragePath}/company-images/${company.id}/${company.image_front_page}`"
                :lazy-src="`${blogStoragePath}/company-images/${company.id}/${company.image_front_page}`"
                class="grey lighten-2 logo"/>
            </v-card>
          </v-flex>
          <v-flex
            md5
            py-2>
            <strong>Footer Logo Image</strong>
          </v-flex>
          <v-flex
            md7
            py-2>
            <v-card
              v-if="company.image_footer_logo"
              flat
              tile
              class="d-flex">
              <v-img
                :src="`${blogStoragePath}/company-images/${company.id}/${company.image_footer_logo}`"
                :lazy-src="`${blogStoragePath}/company-images/${company.id}/${company.image_footer_logo}`"
                class="grey lighten-2 logo"/>
            </v-card>
          </v-flex>
        </v-layout>
      </v-card-text>
      <v-card-actions>
        <v-layout
          row
          mx-3>
          <v-flex md6>
            <v-btn
              color="primary"
              to="/company">
              Back
            </v-btn>
          </v-flex>
          <v-flex
            md6
            class="text-md-right">
            <v-btn
              v-if="$auth.hasScope('ROLE_SUPER_ADMIN')"
              :to="'/company/' + this.$route.params.id + '/edit'"
              color="teal darken-3"
              class="white--text">
              Edit
            </v-btn>
          </v-flex>
        </v-layout>

      </v-card-actions>
    </v-card>
  </div>
</template>

<script>
import { mapGetters } from 'vuex'
import axios, * as others from 'axios'

export default {
  head() {
    return {
      title: 'View Company :: Farosian'
    }
  },
  async fetch({ store, route }) {
    await store.dispatch('company/get', route.params.id)
    await store.dispatch('static/initCountries')
  },
  data() {
    return {
      selectedFile: null
    }
  },
  computed: {
    ...mapGetters({
      company: 'company/company',
      countries: 'static/countries'
    }),
    blogStoragePath() {
      return process.env.blogStoragePath
    }
  },
  mounted() {
    process.env.blogStoragePath = this.$config.blogStoragePath
  },
  methods: {}
}
</script>

<style>
.v-card .v-card__text .flex {
  font-size: 15px;
  margin-bottom: -1px;
  border-bottom: 1px solid #ddd;
  border-top: 1px solid #ddd;
}

.v-subheader {
  font-size: 18px;
}
.uploader {
  padding: 6px 21px;
  border: solid 1px #ddd;
  width: 195px;
}
.sp {
  padding-left: 2px;
  padding-right: 15px;
  margin-left: 10px;
}

.logo {
  height: 150px !important;
  width: 160px !important;
  margin: 10px 10px 10px 15px;
}
</style>
