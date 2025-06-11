<template>
  <div
    id="app"
    class="container">
    <h1 class="title mb-3">Global Settings</h1>
    <v-card
      class="d-flex pa-2"
      outlined
      tile>
      <table id="weightsTable">
        <tr>
          <th>
            <div class="tableHeaders">Social Platform</div>
          </th>
          <th>
            <div class="tableHeaders">Global Usage Weighting </div>
          </th>
          <th>
            <div class="tableHeaders">Ordering </div>
          </th>
          <th>
            <div class="tableHeaders">Standard Comments </div>
          </th>
          <th>
            <div class="tableHeaders"/>
          </th>
        </tr>
        <tr
          v-for="(weight, index) of weights"
          :key="index">
          <td>
            <div class="social_platform">{{ weight.social_platform }}</div>
          </td>
          <td>
            <v-text-field
              :value="weight.global_usage_weighting"
              solo
              @change="updateState(weight.id, 'global_usage_weighting', $event)"
            />
          </td>
          <td>
            <v-text-field
              :value="weight.ordering"
              solo
              @change="updateState(weight.id, 'ordering', $event)"
            />
          </td>
          <td @click="editStdComments(weight.std_comments, weight.id)">
            <v-text-field
              :value="weight.std_comments.length"
              :disabled="true"
              append-icon="edit"
              solo/>
          </td>

          <td>
            <v-btn
              class="updateButton"
              @click.native="updateWeights(weight.id)">Update</v-btn>
          </td>
        </tr>

      </table>
    </v-card>

    <v-dialog
      v-model="standardCommentDialog"
      max-width="800px">
      <v-card>
        <v-card-text>
          <v-card-title>
            <span class="headline"> Edit Standard Comments</span>
          </v-card-title>
          <v-container grid-list-md>
            <v-layout wrap>
              <v-flex 
                v-for="(comment, index) in comments"
                :key="index"
                md12>
                <v-textarea
                  :value="comments[index]"
                  :rows="2"
                  placeholder="Add a standard comment"
                  append-icon="close"
                  @input="changeStd(index, $event)"
                  @click:append="removeComment(index)"
                />
              </v-flex>
              <v-flex md12>
                <v-card-actions>
                  <v-spacer/>
                  <v-btn
                    flat
                    color="teal darken-3"
                    class="white--text"
                    @click.prevent="addComment()">Add Comment
                  </v-btn>
                </v-card-actions>
              </v-flex>
            </v-layout>
          </v-container>
        </v-card-text>
        <v-card-actions>
          <v-spacer/>
          <v-btn
            flat
            color="error"
            @click.prevent="cancel">Cancel
          </v-btn>
          <v-btn
            :loading="dialogLoading"
            flat
            color="teal darken-3"
            class="white--text"
            @click.prevent="cancel">Done
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>
<script>
import { mapGetters } from 'vuex'
export default {
  head() {
    return {
      title: 'Global Weights :: Farosian'
    }
  },
  async fetch({ store, route }) {
    await store.dispatch('global-weights/queryConfig')
  },
  data() {
    return {
      standardCommentDialog: false,
      dialogLoading: false,
      currentId: '',
      comments: []
    }
  },
  computed: {
    ...mapGetters({
      weights: 'global-weights/weights'
    })
  },
  mounted() {},
  methods: {
    updateState(id, prop, value) {
      this.$store
        .dispatch('global-weights/updateGlobalUsage', { id, prop, value })
        .catch(() => {
          this.$toast.error('Could not update update Global Usage data')
        })
    },

    updateWeights(id) {
      this.$store
        .dispatch('global-weights/update', id)
        .then(() => {
          this.$toast.success('Weights successfully updated!')
        })
        .catch(() => {
          this.$toast.error('Could not update update Global Usage data')
        })
    },
    addComment() {
      let id = this.currentId
      this.$store.dispatch('global-weights/addNewComment', id)
      let x = this.weights.filter(function(elem) {
        return elem.id == id
      })
      this.comments = x[0].std_comments
    },
    changeStd(index, $event) {
      let id = this.currentId
      let data = {
        id: id,
        index: index,
        value: $event
      }
      this.$store.dispatch('global-weights/changeComment', data)
      let x = this.weights.filter(function(elem) {
        return elem.id == id
      })
      this.comments = x[0].std_comments
    },
    removeComment(index) {
      let id = this.currentId
      let data = {
        id: id,
        index: index
      }
      this.$store.dispatch('global-weights/removeComment', data)
      let x = this.weights.filter(function(elem) {
        return elem.id == id
      })
      this.comments = x[0].std_comments
    },
    editStdComments(comms, id) {
      this.comments = comms
      this.currentId = id
      this.standardCommentDialog = true
    },
    cancel() {
      this.currentId = ''
      this.standardCommentDialog = !this.standardCommentDialog
    }
  }
}
</script>

<style>
.social_platform {
  font-size: 20px;
  text-transform: capitalize;
}

#weightsTable {
  width: 80%;
}

.updateButton {
  margin: 0 0 25px 10px;
}

.tableHeaders {
  font-size: 20px;
  margin: 0 0 10px 0;
}
</style>
