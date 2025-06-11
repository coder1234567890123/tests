<template>
  <div
    id="app"
    class="container">
    <h1 class="title mb-3">Test page</h1>
    <img 
      :src="`${process.env.blogStoragePath}/profile-images/0061cd46-0dce-4a55-b5df-a3385fdc420b/test.png`"
      alt="">

  </div>
</template>


<script>
export default {
  head() {
    return {
      title: 'Profiles :: Farosian'
    }
  },
  props: {
    value: {
      type: Object,
      default: () => ({})
    }
  },
  mounted() {},
  methods: {
    close() {
      this.$emit('update:dialog', false)
    },

    uploadImageTest() {
      // console.log('Date')
      // var d = new Date()
      // console.log(d)
      // console.log('hello world - test Storage')
      // var testLocalStorge = localStorage.getItem('test_storage')

      //console.log(testLocalStorge)
      //localStorage.removeItem('test_storage')

      // let fieldName = 'test_file.png'
      // let formData = new FormData()
      // let imageURL = '/home/daniel/Downloads/clean/profile_test/2.png'
      // let imageFile = '2.png'
      //
      // formData.append(fieldName, imageFile)
      //
      // this.$emit('input', { formData, imageURL })
      //
      // this.$store.dispatch('subject/uploadImageProof', {
      //   prop: 'image',
      //   value: formData
      // })

      // this.$store.dispatch('subject/uploadImageProof').then(() => {
      //   this.saved = true
      //   this.close()
      //   this.$toast.success('Subject image successfully uploaded!')
      //   //this.$router.push('/subjects')
      // })

      // var image = new Image()
      // image.src = localStorage.getItem('test_storage')
      //document.body.appendChild(image);

      // var formData = new FormData()

      function dataURItoBlob(dataURI) {
        // convert base64/URLEncoded data component to raw binary data held in a string
        var byteString
        if (dataURI.split(',')[0].indexOf('base64') >= 0)
          byteString = atob(dataURI.split(',')[1])
        else byteString = unescape(dataURI.split(',')[1])

        // separate out the mime component
        var mimeString = dataURI
          .split(',')[0]
          .split(':')[1]
          .split(';')[0]

        // write the bytes of the string to a typed array
        var ia = new Uint8Array(byteString.length)
        for (var i = 0; i < byteString.length; i++) {
          ia[i] = byteString.charCodeAt(i)
        }

        return new Blob([ia], { type: mimeString })
      }

      // var dataURI = localStorage.getItem('test_storage') //Get img data
      // // console.log('dataURI')
      // // console.log(dataURI)
      // var blob = dataURItoBlob(localStorage.getItem('test_storage')) //Converts to blob using link above
      // var formData = new FormData(document.forms[0])
      // formData.append('image', blob)
      //
      // console.log('formData')
      // console.log(blob)

      //var imageFile = getBase64Image(localStorage.getItem('test_storage'))

      // console.log(imageFile)
      // let formData = new FormData()
      // let imageURL = URL.createObjectURL(imageFile)
      // //console.log(imageURL)
      // formData.append('image', imageFile)
      //
      // //this.$emit('input', { formData, imageURL })
      //
      // console.log(imageURL)
      //
      // console.log(formData)

      // this.$store.dispatch('subject/updateSubject', {
      //   prop: 'image',
      //   value: formData
      // })

      //var imagefile = document.querySelector('#file')
      //var imagefile = image

      //formData.append('image', imagefile)

      // axios.post('upload_file', formData, {
      //   headers: {
      //     'Content-Type': 'multipart/form-data'
      //   }
      // })

      // helper function: generate a new file from base64 String
      const dataURLtoFile = (dataurl, filename) => {
        var filename = 'test.png'
        const arr = dataurl.split(',')
        const mime = arr[0].match(/:(.*?);/)[1]
        const bstr = atob(arr[1])
        let n = bstr.length
        const u8arr = new Uint8Array(n)
        while (n) {
          u8arr[n - 1] = bstr.charCodeAt(n - 1)
          n -= 1 // to make eslint happy
        }
        return new File([u8arr], filename, { type: mime })
      }

      // generate file from base64 string
      const file = dataURLtoFile(localStorage.getItem('test_storage'))
      // put file into form data
      const data = new FormData()
      data.append('file', file, file.name)

      return this.$axios.$post(
        '/proofstorage/0061cd46-0dce-4a55-b5df-a3385fdc420b/image',
        data,
        {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        }
      )
    }
  }
}
</script>
