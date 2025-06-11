export default function({ $axios, app }) {
  $axios.onError(error => {
    const code = parseInt(error.response && error.response.status)
    if ([403].includes(code)) {
      alert('Not authorized to perform this action')
      window.location.href = '/'
    } else if ([401].includes(code)) {
      app.$auth.logout()
      //window.location.href = '/logoout'
    }
    return Promise.reject(error)
  })

  // On each request add user-type header to log frontend
  $axios.onRequest(config => {
    config.headers['user-type'] = 'farosian-frontend'
  })
}
