import { shallowMount } from '@vue/test-utils'
import CompanyEdit from '../../../../pages/company/_id/edit'

describe('CompanyEdit', () => {
  let wrapper
  let $store
  let $route
  let process
  let company = { id: 'company', image_front_page: 'test' }
  beforeEach(() => {
    $store = {
      getters: {},
      dispatch: jest.fn(() => Promise.resolve({}))
    }
    $route = { query: { continue: 0 } }
    $store.getters['company/company'] = jest.fn(() => Promise.resolve({}))
    $store.getters['static/countries'] = jest.fn(() => Promise.resolve({}))
    $store.getters['static/brandingTypes'] = jest.fn(() => Promise.resolve({}))
    process = { env: { blogStoragePath: () => 'path/' } }

    wrapper = shallowMount(CompanyEdit, {
      mocks: {
        $store,
        $route
      }
    })
  })
  afterEach(() => {
    jest.resetModules()
    jest.clearAllMocks()
    wrapper.vm.$destroy()
  })

  test('Test is Vue Instance(CompanyEdit)', () => {
    expect(wrapper.isVueInstance()).toBeTruthy()
  })

  test('Test computed value "frontImageName" default', () => {
    wrapper.setData({ frontImageName: 'front' })
    expect(wrapper.vm.frontImageName).toBe('front')
  })

  test('Test computed value "footerImageName" default', () => {
    wrapper.setData({ footerImageName: 'footer' })
    expect(wrapper.vm.footerImageName).toBe('footer')
  })

  test('Test method "updateState" default rejected promise', () => {
    let p = Promise.reject({})
    $store.dispatch = jest.fn(() => p)
    wrapper = shallowMount(CompanyEdit, {
      mocks: {
        $store,
        $route
      }
    })
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.updateState('id', 'test')
    expect(spy).toBeCalled()
    p.catch(error => {})
    spy.mockRestore()
  })

  test('Test method "validateStep1" default', () => {
    const VueFormStub = {
      render: () => {},
      methods: {
        validate: () => true
      }
    }
    wrapper = shallowMount(CompanyEdit, {
      stubs: {
        'v-form': VueFormStub
      },
      mocks: {
        $store,
        $route
      }
    })
    wrapper.vm.validateStep1()
    expect(wrapper.vm.validateStep1).toBeDefined()
  })

  test('Test method "validateStep2" default', () => {
    const VueFormStub = {
      render: () => {},
      methods: {
        validate: () => true
      }
    }
    wrapper = shallowMount(CompanyEdit, {
      stubs: {
        'v-form': VueFormStub
      },
      mocks: {
        $store,
        $route
      }
    })
    wrapper.vm.validateStep2()
    expect(wrapper.vm.validateStep2).toBeDefined()
  })

  test('Test method "validateStep3" default', () => {
    const VueFormStub = {
      render: () => {},
      methods: {
        validate: () => true
      }
    }
    wrapper = shallowMount(CompanyEdit, {
      stubs: {
        'v-form': VueFormStub
      },
      mocks: {
        $store,
        $route
      }
    })
    wrapper.vm.validateStep3()
    expect(wrapper.vm.validateStep3).toBeDefined()
  })

  test('Test method "validateStep4" default', () => {
    const VueFormStub = {
      render: () => {},
      methods: {
        validate: () => true
      }
    }
    wrapper = shallowMount(CompanyEdit, {
      stubs: {
        'v-form': VueFormStub
      },
      mocks: {
        $store,
        $route
      }
    })
    wrapper.vm.validateStep4()
    expect(wrapper.vm.validateStep4).toBeDefined()
  })

  test('Test method "validateStep5" default', () => {
    const VueFormStub = {
      render: () => {},
      methods: {
        validate: () => true
      }
    }
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }
    company.country = { id: 'test' }
    $store.getters['company/company'] = company
    wrapper = shallowMount(CompanyEdit, {
      stubs: {
        'v-form': VueFormStub
      },
      mocks: {
        $toast: toast,
        $store,
        $route
      }
    })
    wrapper.setMethods({ updateState: jest.fn() })
    wrapper.vm.validateStep5()
    expect(wrapper.vm.validateStep5).toBeDefined()
  })

  test('Test method "validateStep5" default rejected promise', () => {
    const VueFormStub = {
      render: () => {},
      methods: {
        validate: () => true
      }
    }
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }
    company.country = { id: 'test' }
    $store.getters['company/company'] = company
    let p = Promise.reject({})
    $store.dispatch = jest.fn(() => p)
    wrapper = shallowMount(CompanyEdit, {
      stubs: {
        'v-form': VueFormStub
      },
      mocks: {
        $toast: toast,
        $store,
        $route
      }
    })
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.setMethods({ updateState: jest.fn() })
    wrapper.vm.validateStep5()
    expect(wrapper.vm.validateStep5).toBeDefined()
    expect(spy).toBeCalled()
    p.catch(error => {})
    spy.mockRestore()
  })

  test('Test method "pickFile" default', () => {
    wrapper = shallowMount(CompanyEdit, {
      mocks: {
        $store,
        $route
      }
    })
    wrapper.vm.pickFile('footer')
    expect(wrapper.vm.pickFile).toBeDefined()
  })

  test('Test method "pickFile" default front', () => {
    wrapper = shallowMount(CompanyEdit, {
      mocks: {
        $store,
        $route
      }
    })
    wrapper.vm.pickFile('front')
    expect(wrapper.vm.pickFile).toBeDefined()
  })

  test('Test method "onFilePicked" default', () => {
    const createFile = (
      size = 44320,
      name = 'ecp-logo.png',
      type = 'image/png'
    ) =>
      new File([new ArrayBuffer(size)], name, {
        type: type
      })
    const footerLogo = wrapper.find({ ref: 'footerLogo' })
    footerLogo.files = [createFile()]
    footerLogo.trigger('change')
    expect(wrapper.vm.onFilePicked).toBeDefined()
  })

  test('Test method "onFilePicked" default no extension', () => {
    let blob = new Blob([20000], { type: 'image/png' })
    blob.lastModifiedDate = new Date()
    blob.name = 'test'
    let e = {
      target: {
        files: [blob]
      }
    }
    wrapper.vm.onFilePicked(e, 'footer')
    expect(wrapper.vm.onFilePicked).toBeDefined()
  })

  test('Test method "onFilePicked" default front', () => {
    const createFile = (size = 44320, name = 'test.png', type = 'image/png') =>
      new File([new ArrayBuffer(size)], name, {
        type: type
      })
    let e = {
      target: {
        files: [createFile()]
      }
    }
    wrapper.vm.onFilePicked(e, 'footer')
    expect(wrapper.vm.onFilePicked).toBeDefined()
  })

  test('Test method "upload" default', () => {
    let blob = new Blob([20000], { type: 'image/png' })
    blob.lastModifiedDate = new Date()
    blob.name = 'test.png'

    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }

    wrapper = shallowMount(CompanyEdit, {
      mocks: {
        $toast: toast,
        $store,
        $route
      }
    })

    let spy = jest.spyOn($store, 'dispatch')
    wrapper.setData({ footerImage: blob })
    wrapper.vm.upload('footer')
    expect(wrapper.vm.upload).toBeDefined()
    expect(wrapper.vm.uploadAction['footer']).toBe(true)
    expect(spy).toBeCalled()
    spy.mockRestore()
  })

  test('Test method "upload" default front', () => {
    let blob = new Blob([20000], { type: 'image/png' })
    blob.lastModifiedDate = new Date()
    blob.name = 'test.png'

    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }

    wrapper = shallowMount(CompanyEdit, {
      mocks: {
        $toast: toast,
        $store,
        $route
      }
    })

    let spy = jest.spyOn($store, 'dispatch')
    wrapper.setData({ frontImage: blob })
    wrapper.vm.upload('front')
    expect(wrapper.vm.upload).toBeDefined()
    expect(wrapper.vm.uploadAction['front']).toBe(true)
    expect(spy).toBeCalled()
    spy.mockRestore()
  })

  test('Test method "upload" default reject promise', () => {
    let blob = new Blob([20000], { type: 'image/png' })
    blob.lastModifiedDate = new Date()
    blob.name = 'test.png'

    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }

    let p = Promise.reject({ response: { data: { message: 'error' } } })
    $store.dispatch = jest.fn(() => p)

    wrapper = shallowMount(CompanyEdit, {
      mocks: {
        $toast: toast,
        $store,
        $route
      }
    })

    let spy = jest.spyOn($store, 'dispatch')
    wrapper.setData({ frontImage: blob })
    wrapper.vm.upload('front')
    expect(wrapper.vm.upload).toBeDefined()
    expect(wrapper.vm.uploadAction['front']).toBe(true)
    expect(spy).toBeCalled()
    p.catch(error => {})
    spy.mockRestore()
  })
})
