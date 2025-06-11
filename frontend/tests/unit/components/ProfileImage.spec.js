import { createLocalVue, shallowMount } from '@vue/test-utils'
import ProfileImage from '../../../components/ProfileImage'

describe('ProfileImage', () => {
  let wrapper
  let $store
  let mockedDirective
  beforeEach(() => {
    mockedDirective = {
      bind: jest.fn(),
      inserted: jest.fn(),
      update: jest.fn()
    }
    $store = {
      getters: {},
      dispatch: jest.fn(() => Promise.resolve({}))
    }
    $store.getters['subject/uploadImage'] = jest.fn(() => Promise.resolve({}))
    $store.getters['subject/subject'] = { id: 'test', image: {} }

    wrapper = shallowMount(ProfileImage, {
      directives: {
        ripple: mockedDirective
      }
    })
  })
  afterEach(() => {
    jest.resetModules()
    jest.clearAllMocks()
    wrapper.vm.$destroy()
  })

  test('Test is Vue Instance(ProfileImage)', () => {
    expect(wrapper.isVueInstance()).toBeTruthy()
  })

  test('Test watch Handler', () => {
    wrapper.setData({ saved: true })
    wrapper.vm.avatar = { imageurl: 'test' }
    expect(wrapper.vm.saved).toBe(false)
  })

  test('Test emit ', () => {
    wrapper.vm.close()
    expect(wrapper.emitted()['update:dialog']).toBeTruthy()
  })

  test('Test method "uploadImage" default', () => {
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }
    wrapper = shallowMount(ProfileImage, {
      mocks: {
        $toast: toast,
        $store
      },
      directives: {
        ripple: mockedDirective
      }
    })
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.uploadImage()
    expect(wrapper.vm.$store.dispatch()).toBeDefined()
    expect(spy).toBeCalled()
    spy.mockRestore()
  })

  test('Test method "uploadImage" reject promise', () => {
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }
    let p = Promise.reject({})
    $store.dispatch = jest.fn(() => p)
    wrapper = shallowMount(ProfileImage, {
      mocks: {
        $toast: toast,
        $store
      },
      directives: {
        ripple: mockedDirective
      }
    })
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.uploadImage()
    expect(wrapper.vm.$store.dispatch()).toBeDefined()
    expect(spy).toBeCalled()
    p.catch(error => {})
    spy.mockRestore()
  })
})
