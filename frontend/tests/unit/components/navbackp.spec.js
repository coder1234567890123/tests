import { shallowMount } from '@vue/test-utils'
import navbackp from '../../../components/navbackp'

describe('navbackp', () => {
  let wrapper
  let $store
  let $auth
  beforeEach(() => {
    $store = {
      getters: {}
    }

    $auth = {
      logout: jest.fn(),
      redirect: jest.fn(() => 3)
    }
    $store.getters['isAuthenticated'] = jest.fn(() => Promise.resolve({}))

    $store.getters['loggedInUser'] = jest.fn(() => Promise.resolve({}))

    wrapper = shallowMount(navbackp, {
      mocks: {
        $store,
        $auth
      }
    })
  })
  afterEach(() => {
    jest.resetModules()
    jest.clearAllMocks()
    wrapper.vm.$destroy()
  })

  test('Test is Vue Instance(navbackp)', () => {
    expect(wrapper.isVueInstance()).toBeTruthy()
  })

  test('Test method "logout" default', async () => {
    let spy = jest.spyOn($auth, 'logout')
    await wrapper.vm.logout()
    expect(spy).toBeCalled()
    spy.mockRestore()
  })

  test('Test method "logout" default redirect', async () => {
    let spy = jest.spyOn($auth, 'redirect')
    await wrapper.vm.logout()
    expect(wrapper.vm.$auth.redirect()).toBe(3)
    expect(spy).toBeCalled()
    spy.mockRestore()
  })
})
