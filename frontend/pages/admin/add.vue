<template>
  <div
    id="app"
    class="container">
    <h1 class="title mb-3">Farosian Report Settings</h1>
    <hr>
    <v-card>
      <v-form
        ref="form"
        v-model="valid"
        lazy-validation
      >
        <v-layout
          v-for="(config, index) in configs"
          :key="index"
          row
          wrap>
          <v-flex
            v-if="config.system_type === 1"
            md10>
            <v-subheader
              class="capitalize">
              {{ config.opt.replace(/[_]/g, ' ') }}
            </v-subheader>
            <v-text-field
              :value="config.val"
              :hint="'This changes the ' + config.opt.replace(/[_]/g, ' ') + ' of the report' "
              box
              class="mx-3"
              @input="updateState($event, config.id)"
            />
          </v-flex>
          <v-flex
            v-if="config.system_type === 1"
            md2
            py-2>
            <v-subheader
              class="capitalize"/>
            <v-tooltip
              slot="append"
              right>
              <v-btn
                slot="activator"
                color="teal darken-3"
                class="ma-0  mt-2"
                @click="submit(config)">
                <v-icon
                  color="white"
                  medium>
                  fa fa-edit
                </v-icon>
              </v-btn>
              <span>Click to update this setting</span>
            </v-tooltip>
          </v-flex>
        </v-layout>
      </v-form>
    </v-card>
  </div>
</template>
<script>
import { mapGetters } from 'vuex'
import _ from 'lodash'

export default {
  head() {
    return {
      title: 'Settings :: Farosian'
    }
  },
  async fetch({ store, route }) {
    await store.dispatch('settings/queryConfig')
  },
  data() {
    return {
      valid: true
    }
  },
  computed: {
    ...mapGetters({
      configs: 'settings/configurations'
    })
  },
  mounted() {
    this.$store.dispatch('settings/queryConfig').catch(() => {
      this.$toast.error('Could not get the specified subject')
    })
  },
  methods: {
    updateState(val, id) {
      this.$store.dispatch('settings/updateSettings', { id, val }).catch(() => {
        this.$toast.error('Could not get the specified subject')
      })
    },
    submit(config) {
      this.$store
        .dispatch('settings/update', config)
        .then(() => {
          this.$toast.success(config.opt + ' successfully updated!')
        })
        .catch(() => {
          this.$toast.error('Could not update settings data')
        })
    }
  }
}
</script>
<style scoped>
.capitalize {
  text-transform: uppercase;
}

.logo {
  height: 150px;
  margin: 10px 10px 10px 15px;
}
</style>
