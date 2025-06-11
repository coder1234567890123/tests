import test from 'ava'
import { Nuxt, Builder } from 'nuxt'
import { resolve } from 'path'

let nuxt = null

test.before('Init Nuxt.js', async t => {
  const config = {
    dev: false,
    rootDir: resolve(__dirname, '../../')
  }
  nuxt = new Nuxt(config)
  await nuxt.listen(4000, 'localhost')
})

test('Route /login exists and renders HTML', async t => {
  let context = {}

  const { html } = await nuxt.renderRoute('/login', context)
  t.true(html.includes('Login'))
})

test.after('Closing server and nuxt.js', t => {
  nuxt.close()
})
