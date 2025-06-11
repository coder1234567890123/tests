<template>
  <div>
    <v-alert
      v-if="alert_type === 'success'"
      :value="true"
      type="success"
    >
      {{ message }}
    </v-alert>
    <v-alert
      v-if="alert_type === 'error'"
      :value="true"
      type="error"
    >
      {{ message }}
    </v-alert>
  </div>
</template>

<script>
export default {
  name: 'Notification',
  props: {
    message: {
      type: String,
      default: ''
    },
    alert_type: {
      type: String,
      default: ''
    }
  }
}
</script>
