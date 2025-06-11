import { createLocalVue, shallowMount } from '@vue/test-utils'
import ImageInput from '../../../components/ImageInput'

describe('ImageInput', () => {
  let wrapper
  let $store
  let createObject
  let URL
  beforeEach(() => {
    $store = {
      getters: {},
      dispatch: jest.fn(() => Promise.resolve({}))
    }
    $store.getters['subject/updateSubject'] = jest.fn(() => Promise.resolve({}))
    createObject = jest.fn()
    URL = {
      createObjectURL: createObject
    }
    global.URL = URL

    wrapper = shallowMount(ImageInput, {})
  })
  afterEach(() => {
    jest.resetModules()
    jest.clearAllMocks()
    wrapper.vm.$destroy()
  })

  test('Test is Vue Instance(ImageInput)', () => {
    expect(wrapper.isVueInstance()).toBeTruthy()
  })

  test('Test method "launchFilePicker"', () => {
    wrapper.vm.launchFilePicker()
    expect(wrapper.vm.launchFilePicker).toBeDefined()
  })

  test('Test URL createObjectURL', () => {
    let spy = jest.spyOn(global.URL, 'createObjectURL')
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }
    const file = [
      {
        name: 'test.png',
        size: 20000,
        type: 'image/png'
      }
    ]
    wrapper = shallowMount(ImageInput, {
      mocks: {
        $toast: toast,
        $store
      }
    })
    wrapper.vm.onFileChange('test', file)
    expect(spy).toBeCalled()
    spy.mockRestore()
  })

  test('Test method "onFileChange" default', () => {
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }

    const file = [
      {
        name: 'test.png',
        size: 20000,
        type: 'image/png'
      }
    ]
    wrapper = shallowMount(ImageInput, {
      mocks: {
        $toast: toast,
        $store
      }
    })
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.onFileChange('test', file)
    expect(wrapper.vm.$store.dispatch()).toBeDefined()
    expect(spy).toBeCalled()
    spy.mockRestore()
  })

  test('Test method "onFileChange" not image', () => {
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }

    const file = [
      {
        name: 'test.png',
        size: 20000,
        type: 'document/pdf'
      }
    ]
    wrapper = shallowMount(ImageInput, {
      mocks: {
        $toast: toast,
        $store
      }
    })
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.onFileChange('test', file)
    expect(wrapper.vm.$store.dispatch()).toBeDefined()
    expect(spy).toBeCalled()
    spy.mockRestore()
  })

  test('Test method "onFileChange" incorrect file size', () => {
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }

    const file = [
      {
        name: 'test.png',
        size: 3000000,
        type: 'image/png'
      }
    ]
    wrapper = shallowMount(ImageInput, {
      mocks: {
        $toast: toast,
        $store
      }
    })
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.onFileChange('test', file)
    expect(wrapper.vm.$store.dispatch()).toBeDefined()
    expect(spy).toBeCalled()
    spy.mockRestore()
  })
})
