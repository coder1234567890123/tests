<template>
  <v-layout
    row
    justify-center>
    <v-dialog
      v-model="dialog"
      max-width="300px">
      <v-card>
        <v-card-text>
          <v-container
            grid-list-md>
            <v-layout
              row
              wrap
              justify-center>
              <image-input
                v-validate.reject="'required|ext:png,gif,pdf,jpg,jpeg,txt|size:20'"
                v-model="avatar">
                <div slot="activator">
                  <v-avatar
                    v-ripple
                    v-if="!avatar"
                    size="150px"
                    class="grey lighten-3 mb-3">
                    <span>Click to add image</span>
                  </v-avatar>
                  <v-avatar
                    v-ripple
                    v-else
                    size="150px"
                    class="mb-3">
                    <img
                      :src="avatar.imageURL"
                      alt="avatar">
                  </v-avatar>
                </div>
              </image-input>
            </v-layout>
          </v-container>
        </v-card-text>
        <v-divider/>
        <v-card-actions>
          <v-slide-x-transition>
            <div v-if="avatar && saved === false">
              <v-btn
                :loading="saving"
                class="white--text"
                color="teal darken-3"
                @click="uploadImage">Save Image</v-btn>
            </div>
          </v-slide-x-transition>
          <v-spacer />
          <v-btn
            color="blue darken-1"
            flat
            @click.native="close">Close</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-layout>
</template>

<script>
import ImageInput from './ImageInput.vue'
import { mapGetters } from 'vuex'

export default {
  components: { ImageInput },
  props: {
    dialog: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      avatar: null,
      saving: false,
      saved: false
    }
  },
  computed: {
    ...mapGetters({
      subject: 'subject/subject'
    })
  },
  watch: {
    avatar: {
      handler: function() {
        this.saved = false
      },
      deep: true
    }
  },
  methods: {
    close() {
      this.$emit('update:dialog', false)
    },

    uploadImage() {
      if (this.subject.image.size > 1024 * 1024) {
        e.preventDefault()
        alert('File too big (> 1MB)')
        return
      }

      this.saving = true
      this.$store
        .dispatch('subject/uploadImage', this.subject.image)
        .then(() => {
          this.saved = true
          this.close()
          this.$toast.success('Subject image successfully uploaded!')
          this.$router.go()
        })
        .catch(() => {
          this.saving = false
          this.close()
          this.$toast.error(
            'Could not upload subject image, please double check validation!'
          )
        })
    }
  }
}
</script>
