<template>
  <div>
    <div @click="launchFilePicker()">
      <slot name="activator"/>
    </div>
    <input
      ref="file"
      :name="uploadFieldName"
      type="file"
      style="display:none"
      @change="onFileChange(
      $event.target.name, $event.target.files)">
  </div>
</template>

<script>
export default {
  name: 'ImageInput',
  props: {
    value: {
      type: Object,
      default: () => ({})
    }
  },
  data() {
    return {
      uploadedFiles: [],
      uploadError: null,
      currentStatus: null,
      uploadFieldName: 'file',
      maxSize: 1024
    }
  },
  methods: {
    launchFilePicker() {
      this.$refs.file.click()
    },

    onFileChange(fieldName, file) {
      const { maxSize } = this
      let imageFile = file[0]

      if (file.length > 0) {
        let size = imageFile.size / maxSize / maxSize
        if (!imageFile.type.match('image.*')) {
          this.$toast.error('Please choose an image file')
        } else if (size > 1) {
          this.$toast.error(
            'Your file is too big! Please select an image under 1MB'
          )
        } else {
          let formData = new FormData()
          let imageURL = URL.createObjectURL(imageFile)
          formData.append(fieldName, imageFile)

          this.$emit('input', { formData, imageURL })

          this.$store.dispatch('subject/updateSubject', {
            prop: 'image',
            value: formData
          })
        }
      }
    }
  }
}
</script>
