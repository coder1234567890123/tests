import Vue from 'vue'

window.farosianEventBus = new Vue()
window.addEventListener('message', function receiveImage(event) {
  if (event.data.type === 'PLUGIN_IMAGE') {
    console.log('Processing Image...')
    window.farosianEventBus.$emit('image-cropped', event.data.image)
  }
})
