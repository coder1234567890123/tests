<template>
  <div
    id="app"
    class="container">
    <h1 class="title mb-3">Browser Plugins</h1>
    <v-card>
      <v-card-text>
        <v-alert
          :value="true"
          type="info"
        >
          The Farosian Screenshot plugin allows you to easily attach proof screenshots while performing an investigation.
          Use the links below to install it in your browser.
        </v-alert>
        <v-btn
          large
          color="primary"
          href="/farosian_screenshot-2.0.xpi">
          Download For FireFox
        </v-btn>
        <v-btn
          large
          color="primary"
          href="https://chrome.google.com/webstore/detail/farosian-screenshot/cnlocfnmfpcnhjnkdfephkeifmcmhpbe"
          target="_blank">
          Download For Chrome
        </v-btn>
      </v-card-text>
    </v-card>
  </div>
</template>

<script>
export default {
  head() {
    return {
      title: 'Browser Plugins :: Farosian'
    }
  }
}
</script>
