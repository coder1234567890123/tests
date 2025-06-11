<template>
  <div
    id="app"
    class="container">
    <h1 class="title mb-3">Edit Search Term</h1>
    <v-card>
      <v-form
        ref="form"
        lazy-validation
      >
        <v-layout
          row
          wrap>
          <v-flex md6>
            <v-subheader>Which field do you want to add to your search term?</v-subheader>
            <v-select
              v-model="token"
              :items="subjects"
              item-text="label"
              item-value="value"
              label="Select a field"
              class="mx-3"/>
          </v-flex>
          <v-flex md6>
            <v-subheader>Which platform do you want to search for?</v-subheader>
            <v-select
              :value="phrase.search_type"
              :rules="platformRules"
              :items="platforms"
              item-text="label"
              item-value="value"
              label="Select a platform"
              class="mx-3"
              @change="changePlatform"/>
          </v-flex>
        </v-layout>
        <v-layout
          v-if="phrase !== null"
          row>
          <v-flex>
            <v-subheader>Search Term</v-subheader>
            <v-textarea
              :value="phrase.phrase"
              :rules="searchTermRules"
              box
              name="phrase"
              hint="This is the search term that'll be used for the search"
              class="mx-3"
              @input="populate"/>
          </v-flex>
        </v-layout>
        <v-flex md6>
          <v-text-field
            :value="phrase.priority || 0"
            label="Search Term Priority"
            type="number"
            hint="This will be used for fine search ordering comparable to other search terms"
            class="mx-3"
            @input="priority=$event"/>
        </v-flex>
        <v-layout row>
          <v-flex
            md12>
            <v-card-actions class="mx-2">
              <v-btn
                color="teal darken-3"
                class="white--text"
                @click="add">
                Update
              </v-btn>
              <v-btn
                :nuxt="true"
                to="/search/view"
                flat>Cancel
              </v-btn>
            </v-card-actions>
          </v-flex>
        </v-layout>
      </v-form>
    </v-card>
  </div>
</template>
<script>
import { mapGetters } from 'vuex'

export default {
  async fetch({ store, route }) {
    await store.dispatch('phrase/get', route.params.id)
  },
  head() {
    return {
      title: 'Edit Search Term :: Farosian'
    }
  },
  data() {
    return {
      subjects: [
        { label: 'First Name', value: 'first_name' },
        { label: 'Last Name', value: 'last_name' },
        { label: 'Email', value: 'email' },
        { label: 'Phone', value: 'phone' },
        { label: 'Handles', value: 'handles' },
        { label: 'City', value: 'city' },
        { label: 'Province', value: 'province' },
        { label: 'Country', value: 'country' },
        { label: 'Company', value: 'company' },
        { label: 'Education Institute', value: 'education_institute' }
      ],
      token: '',
      priority: 0,
      platformRules: [v => !!v || 'Platform is required'],
      searchTermRules: [v => !!v || 'Search term is required ']
    }
  },
  computed: {
    ...mapGetters({
      phrase: 'phrase/phrase',
      platforms: 'static/platforms'
    })
  },
  watch: {
    token: function(value) {
      let token = '[' + value + ']'
      this.$store.dispatch('phrase/updateTerm', token)
    }
  },
  methods: {
    populate(value) {
      this.$store.dispatch('phrase/updatePhrase', value)
    },
    changePlatform(value) {
      this.$store.dispatch('phrase/updatePlatform', value)
    },

    add() {
      if (this.$refs.form.validate()) {
        let searchTerm = {
          phrase: this.phrase.phrase,
          search_type: this.phrase.search_type,
          priority: this.priority || 0
        }

        this.$store
          .dispatch('phrase/validate', searchTerm)
          .then(() => {
            this.$store.dispatch('phrase/update', searchTerm)
          })
          .then(() => {
            this.$toast.success('Phrase has been added.')
            this.$router.push('/search/view')
          })
          .catch(() => {
            this.$toast.error('Could not add the phrase entered.')
          })
      }
    }
  }
}
</script>
