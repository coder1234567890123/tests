<template>
  <div
    class="row wrap"
    style="padding-top: 10px;">
    <v-card>
      <v-card-title>
        <h3>Select comment to add to platform: {{ platform.toUpperCase() }}</h3>
      </v-card-title>
      <v-card-text>
        <v-textarea
          v-model="comment"
          :rows="2"
          label="Add Comment"
          hint="Please make sure you add a minimum of 3 and a maximum of 6 comments per platform"/>
        <v-card-actions>
          <v-spacer/>
          <v-btn
            :disabled="!(comment.trim().length > 0) || savedComments.length >= 6"
            :loading="loading"
            color="teal darken-3"
            class="white--text"
            @click="submitComment">Save
          </v-btn>
        </v-card-actions>
      </v-card-text>
      <v-card-text>
        <v-list two-line>
          <v-list-group
            v-model="selected"
            multiple>
            <template v-for="(item, index) in savedComments">
              <v-list-tile :key="item + index">
                <template>
                  <v-list-tile-content>
                    <v-list-tile-title v-text="item.comment"/>
                  </v-list-tile-content>
                  <v-list-tile-action>
                    <v-list-tile-action-text v-text="'delete'"/>
                    <v-icon
                      color="grey lighten-1"
                      @click="deleteComment(item.id, index)">
                      delete
                    </v-icon>
                  </v-list-tile-action>
                </template>
              </v-list-tile>
              <v-divider
                v-if="index + 1 < savedComments.length"
                :key="index"
              />
            </template>
          </v-list-group>
        </v-list>
      </v-card-text>
    </v-card>
  </div>
</template>
<script>
import { mapGetters } from 'vuex'

export default {
  props: {
    reportId: {
      type: String,
      default: ''
    },
    platform: {
      type: String,
      default: ''
    },
    report: {
      type: Object,
      default: () => {}
    }
  },
  data: function() {
    return {
      comment: '',
      savedComments: [],
      loading: false,
      selected: [0]
    }
  },
  computed: {},
  watch: {
    platform: {
      handler: function() {
        let plat = this.platform
        if (this.report.comments && this.report.comments.length > 0) {
          this.savedComments = this.report.comments.filter(function(com) {
            return com.comment_type === plat
          })
        } else {
          this.savedComments = []
        }
        this.comment = ''
      },
      deep: true
    }
  },
  mounted() {
    let plat = this.platform
    if (this.report.comments && this.report.comments.length > 0) {
      this.savedComments = this.report.comments.filter(function(com) {
        return com.comment_type === plat
      })
    } else {
      this.savedComments = []
    }
  },

  methods: {
    submitComment() {
      if (this.savedComments.length > 0) {
        let selectedComment = this.comment
        let values = this.savedComments.filter(function(com) {
          return com.comment == selectedComment
        })
        if (values.length > 0) {
          this.$toast.success('Comment already selected')
          return
        }
      }
      this.loading = true
      let data = {
        comment: this.comment,
        report: { id: this.reportId },
        comment_type: this.platform
      }
      this.$store
        .dispatch('subject/createPlatformComment', data)
        .then(response => {
          this.savedComments.push({
            id: response.id,
            comment: response.comment
          })
          this.$toast.success('Comment added successfully')
        })
        .catch(() => {
          this.$toast.error('Could not create comment.')
        })
      this.loading = false
      this.comment = ''
    },
    deleteComment(id, index) {
      this.$store
        .dispatch('subject/deletePlatformComment', id)
        .then(response => {
          this.savedComments.splice(index, 1)
          this.$toast.success('Comment removed successfully')
        })
        .catch(() => {
          this.$toast.error('Could not create comment.')
        })
    }
  }
}
</script>
<style scoped>
.mousePointer :hover {
  cursor: pointer;
}
</style>
