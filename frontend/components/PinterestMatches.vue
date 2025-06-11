<template>
  <div class="row wrap">
    <v-card>
      <v-card-title>
        <v-text-field
          v-model="search"
          append-icon="search"
          label="Search"
          single-line
          hide-details/>
        <ConfirmationOfIdentity platform="pinterest"/>
        <v-spacer/>
      </v-card-title>
      <v-data-table
        :headers="headers"
        :items="matches"
        :search="search">
        <v-progress-linear
          slot="progress"
          color="blue"
          indeterminate/>
        <template
          slot="items"
          slot-scope="props">
          <td>
            <a
              :href="props.item.link"
              target="_blank">
              {{ resizeUrl(props.item.link) }}
            </a>
          </td>
          <td>{{ props.item.phrase }}</td>
          <td>{{ props.item.priority }}</td>
          <td v-if="!investigationMode">
            <v-switch
              :input-value="props.item.valid"
              class="mt-4"
              @change="validate(props.item.id, $event)"
            />
          </td>
          <td v-if="!investigationMode">
            <nuxt-link :to="{ name: $getRoute('SUBJECTS_PROFILES_EDIT'), params: { id: $route.params.id, profile: props.item.id } }">
              Edit
            </nuxt-link>
          </td>
        </template>
        <v-alert
          slot="no-results"
          :value="true"
          color="error"
          icon="warning">
          Your search for "{{ search }}" found no results.
        </v-alert>
      </v-data-table>
    </v-card>

    <matches-details
      :dialog.sync="dialog"/>
  </div>
</template>

<script>
import MatchesDetails from './MatchesDetails'
import ConfirmationOfIdentity from './ConfirmationOfIdentity'
import { mapGetters } from 'vuex'

export default {
  components: { MatchesDetails, ConfirmationOfIdentity },
  props: {
    investigationMode: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      search: '',
      dialog: false,
      switch2: false
    }
  },
  computed: {
    matches() {
      return this.$store.getters['subject/pinterestProfiles'](
        this.investigationMode
      )
    },
    headers() {
      if (this.investigationMode === false) {
        return [
          { text: 'Profile Link', value: 'link' },
          { text: 'Search Phrase', value: 'phrase' },
          { text: 'Priority', value: 'priority' },
          {
            text: 'Validate',
            sortable: false,
            value: 'validate'
          },
          {
            text: 'Edit',
            sortable: false,
            value: 'edit'
          }
        ]
      }

      return [
        { text: 'Profile Link', value: 'link' },
        { text: 'Search Phrase', value: 'phrase' },
        { text: 'Priority', value: 'priority' }
      ]
    }
  },
  methods: {
    resizeUrl(str) {
      let length = 60
      let ending
      if (length == null) {
        length = 100
      }
      if (ending == null) {
        ending = ' ...'
      }
      if (str.length > length) {
        return str.substring(0, length - ending.length) + ending
      } else {
        return str
      }
    },
    validate(id, value) {
      this.switch2 = !value
      if (!this.switch2) {
        this.$store
          .dispatch('profile/validate', id)
          .then(() => {
            this.$toast.success('Profile successfully validated!')
            this.switch2 = true
          })
          .catch(() => {
            this.$toast.error('Could not update profile data')
            this.switch2 = false
          })
      } else {
        this.$store
          .dispatch('profile/invalidate', id)
          .then(() => {
            this.$toast.success('Profile successfully invalidated!')
            this.switch2 = false
          })
          .catch(() => {
            this.$toast.error('Could not update profile data')
            this.switch2 = true
          })
      }
    }
  }
}
</script>
