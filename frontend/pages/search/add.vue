<template>
  <div
    id="app"
    class="container">
    <h1 class="title mb-3">Add Search Term</h1>
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
              :items="subjects"
              item-text="label"
              item-value="value"
              label="Select a field"
              class="mx-3"
              @change="populate"/>
          </v-flex>
          <v-flex md6>
            <v-subheader>Which platform do you want to search for?</v-subheader>
            <v-select
              v-model="platform"
              :rules="platformRules"
              :items="platforms"
              item-text="label"
              item-value="value"
              label="Select a platform"
              class="mx-3"/>
          </v-flex>
        </v-layout>
        <v-layout row>
          <v-flex>
            <v-subheader>Search Term</v-subheader>
            <v-textarea
              v-model="phrase"
              :rules="searchTermRules"
              box
              name="phrase"
              hint="This is the search term that'll be used for the search"
              class="mx-3"/>
          </v-flex>
        </v-layout>
        <v-layout
          row
          wrap>
          <v-flex md6>
            <v-text-field
              v-model="priority"
              label="Search Term Priority"
              type="number"
              hint="This will be used for fine search ordering comparable to other search terms"
              class="mx-3"
            />
          </v-flex>
        </v-layout>
        <v-layout row>
          <v-flex
            md12>
            <v-card-actions class="mx-2">
              <v-btn
                color="teal darken-3"
                class="white--text"
                @click="add">
                Add
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
  head() {
    return {
      title: 'Add Search Term :: Farosian'
    }
  },
  data() {
    return {
      subjects: [
        { label: 'First Name', value: 'first_name' },
        { label: 'Last Name', value: 'last_name' },
        { label: 'Maiden Name', value: 'maiden_name' },
        { label: 'Middle Name', value: 'middle_name' },
        { label: 'Email', value: 'email' },
        { label: 'Email Handle', value: 'email_handle' },
        { label: 'Phone', value: 'phone' },
        { label: 'DOB', value: 'date_of_birth' },
        { label: 'Nickname', value: 'nickname' },
        { label: 'Handles', value: 'handles' },
        { label: 'Country', value: 'country' },
        { label: 'Province', value: 'province' },
        { label: 'City', value: 'city' },
        { label: 'Company', value: 'company' },
        { label: 'Education Institute', value: 'education_institute' }
      ],
      phrase: '',
      platform: '',
      priority: 0,
      platformRules: [v => !!v || 'Platform is required'],
      searchTermRules: [v => !!v || 'Search term is required ']
    }
  },
  computed: {
    ...mapGetters({
      platforms: 'static/platforms'
    })
  },
  methods: {
    populate(val) {
      if (val !== null) {
        let phrase = '[' + val + ']'
        this.phrase = !this.phrase
          ? this.phrase + phrase
          : this.phrase + ' ' + phrase
      }
    },

    add() {
      if (this.$refs.form.validate()) {
        let customSearch = {
          phrase: this.phrase,
          search_type: this.platform,
          priority: this.priority || 0
        }

        this.$store
          .dispatch('phrase/validate', customSearch)
          .then(() => {
            this.$store.dispatch('phrase/create', customSearch)
          })
          .then(() => {
            this.$toast.success('Phrase has been added.')
            this.$store.dispatch('phrase/queryPhrases')
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
