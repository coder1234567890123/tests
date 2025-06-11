<template>
  <div
    id="app"
    class="container">
    <h1 class="title mb-3">Profiles: {{ subject !== null ? subject.first_name + " " + subject.last_name : 'Loading...'
    }}</h1>
    <v-layout row>
      <v-flex
        md12>
        <v-card-actions
          class="pl-0">
          <v-btn
            :to="{ name: $getRoute('SUBJECTS_VIEW'), params : { id: this.$route.params.id } }"
            color="blue-grey"
            class="white--text mb-3">
            <v-icon>keyboard_arrow_left</v-icon>
            Back
          </v-btn>
          <v-btn
            :to="{ name: $getRoute('SUBJECTS_PROFILES_ADD'), params: { id: this.$route.params.id } }"
            color="blue-grey"
            class="white--text mb-3">
            <v-icon>add</v-icon>
            Add a profile
          </v-btn>
          <v-btn
            color="blue-grey"
            class="white--text mb-3"
            @click="viewSubject()">
            <v-icon>perm_identity</v-icon>
            View Subject
          </v-btn>
        </v-card-actions>
      </v-flex>
    </v-layout>
    <v-tabs
      slot="extension"
      color="#ddd"
    >
      <v-tabs-slider color="#a3a3a3"/>
      <v-tab href="#facebook-tab">
        Facebook
      </v-tab>
      <v-tab href="#instagram-tab">
        Instagram
      </v-tab>
      <v-tab href="#twitter-tab">
        Twitter
      </v-tab>
      <v-tab href="#linkedin-tab">
        LinkedIn
      </v-tab>
      <v-tab href="#pinterest-tab">
        Pinterest
      </v-tab>
      <v-tab href="#flickr-tab">
        Flickr
      </v-tab>
      <v-tab href="#youtube-tab">
        Youtube
      </v-tab>
      <v-tab href="#web-search-tab">
        Web Search
      </v-tab>
      <v-tabs-items>
        <v-tab-item value="facebook-tab">
          <facebook-matches/>
        </v-tab-item>
        <v-tab-item value="instagram-tab">
          <instagram-matches/>
        </v-tab-item>
        <v-tab-item value="twitter-tab">
          <twitter-matches/>
        </v-tab-item>
        <v-tab-item value="linkedin-tab">
          <linkedin-matches/>
        </v-tab-item>
        <v-tab-item value="pinterest-tab">
          <pinterest-matches/>
        </v-tab-item>
        <v-tab-item value="flickr-tab">
          <flickr-matches/>
        </v-tab-item>
        <v-tab-item value="youtube-tab">
          <youtube-matches/>
        </v-tab-item>
        <v-tab-item value="web-search-tab">
          <WebSearchMatches/>
        </v-tab-item>
      </v-tabs-items>
    </v-tabs>
    <SubjectInfo ref="SubjectInfo" />
  </div>
</template>
<style>
.v-tabs__item:hover {
  text-decoration: none;
}
</style>
<script>
import { mapGetters } from 'vuex'
import FacebookMatches from '~/components/FacebookMatches'
import InstagramMatches from '~/components/InstagramMatches'
import TwitterMatches from '~/components/TwitterMatches'
import LinkedinMatches from '~/components/LinkedinMatches'
import PinterestMatches from '~/components/PinterestMatches'
import FlickrMatches from '~/components/FlickrMatches'
import YoutubeMatches from '~/components/YoutubeMatches'
import WebSearchMatches from '~/components/WebSearchMatches'
import SubjectInfo from '~/components/SubjectInfo'

export default {
  components: {
    FacebookMatches,
    InstagramMatches,
    TwitterMatches,
    LinkedinMatches,
    PinterestMatches,
    FlickrMatches,
    YoutubeMatches,
    WebSearchMatches,
    SubjectInfo
  },
  head() {
    return {
      title: 'Profiles :: Farosian'
    }
  },
  computed: {
    ...mapGetters({
      subject: 'subject/subject'
    })
  },
  mounted() {
    this.$store.dispatch('subject/getAll', this.$route.params.id).catch(() => {
      console.log('Could not get the specified subject')
    })
  },
  methods: {
    viewSubject() {
      this.$refs.SubjectInfo.openSubjectDialog(this.$route.params.id)
    }
  }
}
</script>
<style scoped>
.cofButton {
  border-bottom-width: 10px !important;
}
.floatRight {
  float: right !important;
}
</style>
