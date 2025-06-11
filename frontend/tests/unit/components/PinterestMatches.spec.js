import { shallowMount } from '@vue/test-utils'
import PinterestMatches from '../../../components/PinterestMatches'

describe('PinterestMatches', () => {
  let wrapper
  let $store
  beforeEach(() => {
    $store = {
      getters: {},
      dispatch: jest.fn(() => Promise.resolve({}))
    }
    $store.getters['subject/pinterestProfiles'] = jest.fn(() =>
      Promise.resolve({})
    )

    wrapper = shallowMount(PinterestMatches, {
      mocks: {
        $store
      }
    })
  })
  afterEach(() => {
    jest.resetModules()
    jest.clearAllMocks()
    wrapper.vm.$destroy()
  })

  test('Test is Vue Instance(PinterestMatches)', () => {
    expect(wrapper.isVueInstance()).toBeTruthy()
  })

  test('Test method "validate" default', () => {
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }

    wrapper = shallowMount(PinterestMatches, {
      mocks: {
        $toast: toast,
        $store
      }
    })
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.validate('id', true)
    expect(wrapper.vm.$store.dispatch()).toBeDefined()
    expect(spy).toBeCalled()
    spy.mockRestore()
  })

  test('Test method "validate" default reject', () => {
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }
    let p = Promise.reject({})
    $store.dispatch = jest.fn(() => p)

    wrapper = shallowMount(PinterestMatches, {
      mocks: {
        $toast: toast,
        $store
      }
    })
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.validate('id', true)
    expect(wrapper.vm.$store.dispatch()).toBeDefined()
    expect(spy).toBeCalled()
    p.catch(error => {})
    spy.mockRestore()
  })

  test('Test method "validate" true', () => {
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }

    wrapper = shallowMount(PinterestMatches, {
      propsData: { investigationMode: true },
      mocks: {
        $toast: toast,
        $store
      }
    })
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.validate('id', false)
    expect(wrapper.vm.$store.dispatch()).toBeDefined()
    expect(spy).toBeCalled()
    spy.mockRestore()
  })

  test('Test method "validate" true reject', () => {
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }
    let p = Promise.reject({})
    $store.dispatch = jest.fn(() => p)

    wrapper = shallowMount(PinterestMatches, {
      mocks: {
        $toast: toast,
        $store
      }
    })
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.validate('id', false)
    expect(wrapper.vm.$store.dispatch()).toBeDefined()
    expect(spy).toBeCalled()
    p.catch(error => {})
    spy.mockRestore()
  })
})
