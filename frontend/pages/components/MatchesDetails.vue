<template>
  <v-layout
    row
    justify-center>
    <v-dialog
      v-model="dialog"
      max-width="600px">
      <v-card>
        <v-card-title>
          <span class="headline">Validation Details</span>
        </v-card-title>
        <v-card-text>
          <v-container grid-list-md>
            <v-layout
              wrap
              align-content-space-around>
              <v-flex sm8>
                <v-subheader class="floatLeft">Profile Link</v-subheader>
                <a
                  href="https://facebook.com/angelica_ramos"
                  class="col"
                  target="_blank">https://facebook.com/angelica_ramos</a>
              </v-flex>
              <v-flex
                sm4
                text-center>
                <v-subheader class="floatLeft">Confidence Score</v-subheader>
                <h1>82%</h1>
              </v-flex>
            </v-layout>
            <v-layout wrap>
              <v-list subheader>
                <v-subheader>Reasons for match</v-subheader>
                <v-list-tile>
                  <v-list-tile-action>
                    <v-checkbox v-model="firstname" />
                  </v-list-tile-action>
                  <v-list-tile-content>
                    <v-list-tile-title>First Name</v-list-tile-title>
                  </v-list-tile-content>
                </v-list-tile>
                <v-list-tile>
                  <v-list-tile-action>
                    <v-checkbox v-model="lastname" />
                  </v-list-tile-action>
                  <v-list-tile-content>
                    <v-list-tile-title>Last Name</v-list-tile-title>
                  </v-list-tile-content>
                </v-list-tile>
                <v-list-tile>
                  <v-list-tile-action>
                    <v-checkbox v-model="email" />
                  </v-list-tile-action>
                  <v-list-tile-content>
                    <v-list-tile-title>Email</v-list-tile-title>
                  </v-list-tile-content>
                </v-list-tile>
                <v-list-tile>
                  <v-list-tile-action>
                    <v-checkbox v-model="phone" />
                  </v-list-tile-action>
                  <v-list-tile-content>
                    <v-list-tile-title>Phone</v-list-tile-title>
                  </v-list-tile-content>
                </v-list-tile>
              </v-list>
            </v-layout>
          </v-container>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn
            class="white--text"
            color="teal darken-3">Accept</v-btn>
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
export default {
  props: {
    dialog: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      firstname: true,
      lastname: true,
      email: false,
      phone: false
    }
  },
  methods: {
    close() {
      this.$emit('update:dialog', false)
    }
  }
}
</script>
<style>
.floatLeft {
  margin-left: -17px !important;
}
</style>
