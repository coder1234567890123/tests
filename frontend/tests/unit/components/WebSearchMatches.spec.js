import { shallowMount } from '@vue/test-utils'
import WebSearchMatches from '../../../components/WebSearchMatches'

describe('WebSearchMatches', () => {
  let wrapper
  let $store
  beforeEach(() => {
    $store = {
      getters: {},
      dispatch: jest.fn(() => Promise.resolve({}))
    }
    $store.getters['subject/webSearchProfiles'] = jest.fn(() =>
      Promise.resolve({})
    )

    wrapper = shallowMount(WebSearchMatches, {
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

  test('Test is Vue Instance(WebSearchMatches)', () => {
    expect(wrapper.isVueInstance()).toBeTruthy()
  })

  test('Test method "validate" default', () => {
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }

    wrapper = shallowMount(WebSearchMatches, {
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

    wrapper = shallowMount(WebSearchMatches, {
      mocks: {
        $toast: toast,
        $store
      }
    })
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.validate('id')
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

    wrapper = shallowMount(WebSearchMatches, {
      propsData: { investigationMode: true },
      mocks: {
        $toast: toast,
        $store
      }
    })
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.validate('id')
    expect(wrapper.vm.$store.dispatch()).toBeDefined()
    expect(spy).toBeCalled()
    spy.mockRestore()
  })

  test('Test method "invalidate" default', () => {
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }

    wrapper = shallowMount(WebSearchMatches, {
      mocks: {
        $toast: toast,
        $store
      }
    })
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.invalidate('id')
    expect(wrapper.vm.$store.dispatch()).toBeDefined()
    expect(spy).toBeCalled()
    spy.mockRestore()
  })

  test('Test method "invalidate" default reject', () => {
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }
    let p = Promise.reject({})
    $store.dispatch = jest.fn(() => p)

    wrapper = shallowMount(WebSearchMatches, {
      mocks: {
        $toast: toast,
        $store
      }
    })
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.invalidate('id')
    expect(wrapper.vm.$store.dispatch()).toBeDefined()
    expect(spy).toBeCalled()
    p.catch(error => {})
    spy.mockRestore()
  })
})
