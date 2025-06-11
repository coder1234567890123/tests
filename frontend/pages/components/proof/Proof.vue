<template>
  <v-card-text v-if="editView == true">
    <v-card v-if="allowTraits === true">
      <span class="putInline checkboxAlign">
        <v-checkbox
          :input-value="getProof.trait === null ? false: getProof.trait"
          @change="changeValues(getProof.id, 'trait', $event)"/>
      </span>
      <span class="putInline textAlign">
        <b>Trait:  </b>
      </span>
      <span class="putInline textAlign">
        <i>To add traits to an image check the box </i>
      </span>
    </v-card>
    <br>
    <br>
    <table
      v-if="allowTraits === true && getProof.trait == true"
      width="300">
      <tr>
        <th>Creativity</th>
        <th>Communication Skills</th>
        <th>Business Writing & Writing Ability</th>
        <th>Professional Image</th>
        <th>Professional Engagement</th>
        <th>Network Reach</th>
        <th>Network Engagement</th>
        <th>Teamwork & Collaboration</th>
      </tr>
      <tr>
        <td
          v-for="(value, propertyName) in getProof.behaviour_scores"
          :key="propertyName">
          <v-btn
            @click="changeBehavior(getProof.id, propertyName, value + 1)">
            <i class="material-icons">
              add
            </i>
          </v-btn>
          <br>
          <br>
          <v-text-field
            :value="value"
            disabled
            label="0"
            single-line
            solo
          />
          <v-btn
            @click="changeBehavior(getProof.id, propertyName, value - 1)">
            <i class="material-icons">
              remove
            </i>
          </v-btn>
        </td>
      </tr>
    </table>
    <br>
    <v-combobox
      :value="getProof.comment"
      :items="comments"
      :rows="5"
      label="Select a comment or create a new one"
      placeholder="Comment"
      @change="changeValues(getProof.id, 'comment', $event)"/>
    <v-flex>
      <v-btn
        block
        color="teal darken-3"
        class="white--text ma-0"
        @click="updateProof(getProof.id)">Update
      </v-btn>
    </v-flex>
  </v-card-text>
  <v-card-text v-else>
    <br>
    <br>
    <br>
    <v-card
      max-width="344"
      class="mx-auto"
    >
      <v-card-title><h1>Please click on image. </h1></v-card-title>
    </v-card>
  </v-card-text>
</template>
<script>
import { mapGetters } from 'vuex'

import Proof from '~/components/proof/Proof'

export default {
  components: {
    Proof
  },
  props: {
    getProof: {
      type: Object,
      default: () => ({})
    },
    comments: {
      type: Array,
      default: () => [{}]
    },
    showProof: {
      type: Object,
      default: () => ({})
    },
    allowTraits: {
      type: Boolean,
      default: true
    }
  },
  data() {
    return {
      test: [],
      dirty: false,
      dialog: false,
      behaviour: {
        creativity: 0,
        communicationSkills: 0,
        businessWritingAbility: 0,
        professionalImage: 0,
        professionalEngagement: 0,
        networkReach: 0,
        networkEngagement: 0,
        teamworkCollaboration: 0
      },
      proofComment: '',
      editView: true,
      message: ''
    }
  },
  computed: {},
  methods: {
    updateProof(id) {
      this.$store
        .dispatch('question/updateProof', id)
        .then(() => {
          this.message = 'Updated'
          this.$toast.success('Updated')
          this.editView = true
        })
        .catch(() => {
          this.message = 'There was an error'
          this.$toast.error('There was an error')
        })
    },
    changeValues(id, prop, value) {
      let data = {
        id: id,
        prop: prop,
        value: value
      }
      this.$store.dispatch('question/changeValues', data)
    },
    changeBehavior(id, prop, value) {
      let data = {
        id: id,
        prop: prop,
        value: value
      }
      this.$store.dispatch('question/changeBehavior', data)
    }
  }
}
</script>
<style scoped>
.putInline {
  display: inline-block;
}

.checkboxAlign {
  margin: 10px 0px 0px 20px;
}

.textAlign {
  margin: 0px 10px 10px 0px !important;
}
</style>
