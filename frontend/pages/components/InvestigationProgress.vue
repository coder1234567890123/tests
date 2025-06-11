<template>
  <div 
    class="row wrap" 
    style="padding-top: 50px;">
    <modal 
      ref="modal" 
      :allow-traits="allowTraits"
      :comments="comments" 
      :answer-id="answer"/>
    <v-card>
      <v-data-table
        :headers="headers"
        :items="questions"
        class="elevation-1">
        <template
          v-if="props.item"
          slot="items"
          slot-scope="props">
          <td><v-icon 
            :color="checkIfAnswered(props.item) ? 'green darken-2' : 'red darken-2'" 
            medium 
            left>{{ checkIfAnswered(props.item) ? 'check_circle': 'block' }}</v-icon></td>
          <td><a 
            class="mousePointer" 
            style="text-decoration: none;"
            href="#" 
            @click.prevent="questionClick(props.item, props.item.id)">{{ props.index + 1 }}. {{ props.item.question }}</a></td>
          <td 
            :class="checkProof(props.item) > 0 ? 'mousePointer': ''" 
            @click="viewProof(props.item)"><v-chip color="green"><span style="color:white;">{{ checkProof(props.item) }}</span></v-chip></td>
        </template>
      </v-data-table>
    </v-card>
  </div>
</template>

<script>
import { mapGetters } from 'vuex'
import modal from './proof/ProofView.vue'

export default {
  components: { modal },
  props: {
    questions: {
      type: Array,
      default: () => [{}]
    },
    comments: {
      type: Array,
      default: () => [{}]
    },
    currentQuestion: {
      type: Number,
      default: 0
    },
    allowTraits: {
      type: Boolean,
      default: false
    }
  },
  data: function() {
    return {
      loading: false,
      answer: '',
      headers: this.getHeaders()
    }
  },
  computed: {},

  methods: {
    checkIfAnswered(question) {
      if (
        question.answers.length > 0 &&
        question.answers[0].answer.trim().length > 0
      ) {
        return true
      }
      return false
    },
    questionClick(question, index) {
      if (this.checkIfAnswered(question)) {
        this.$parent.setCurrentQuetion(index)
      }
    },
    getHeaders() {
      return [
        { text: '', value: 'answered', sortable: false, width: '1%' },
        { text: 'Question', value: 'question', sortable: false },
        { text: 'Noteworthy Findings', value: 'findings', sortable: false }
      ]
    },
    viewProof(question) {
      if (
        question &&
        question.answers.length > 0 &&
        question.answers[0].proofs &&
        question.answers[0].proofs.length > 0
      ) {
        this.answer = question.answers[0].id
        this.$refs.modal.show()
      }
    },
    checkProof(question) {
      if (
        question &&
        question.answers.length > 0 &&
        question.answers[0].proofs &&
        question.answers[0].proofs.length > 0
      ) {
        return question.answers[0].proofs.length
      } else {
        return 0
      }
    }
  }
}
</script>
<style scoped>
.mousePointer :hover {
  cursor: pointer;
}
</style>
