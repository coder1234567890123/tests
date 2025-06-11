import { shallowMount } from '@vue/test-utils'
import AdminAdd from '../../../../pages/admin/add'

describe('AdminAdd', () => {
  let wrapper
  let $store
  let process
  beforeEach(() => {
    $store = {
      getters: {},
      dispatch: jest.fn(() => Promise.resolve({}))
    }
    $store.getters['settings/configurations'] = jest.fn(() =>
      Promise.resolve({})
    )
    $store.getters['settings/footerLogo'] = jest.fn(() => Promise.resolve({}))
    $store.getters['settings/frontPage'] = jest.fn(() => Promise.resolve({}))
    wrapper = shallowMount(AdminAdd, {
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

  test('Test is Vue Instance(AdminAdd)', () => {
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

  test('Test method "pickFile" default', () => {
    $store.getters['settings/footerLogo'] = {
      id: 'test',
      system_type: 2,
      opt: 'footer_logo',
      val: 'test'
    }
    wrapper = shallowMount(AdminAdd, {
      mocks: {
        $store
      }
    })
    wrapper.vm.pickFile('footer')
    expect(wrapper.vm.pickFile).toBeDefined()
  })

  test('Test method "pickFile" default front', () => {
    $store.getters['settings/frontPage'] = {
      id: 'test',
      system_type: 2,
      opt: 'front_page',
      val: 'test'
    }
    wrapper = shallowMount(AdminAdd, {
      mocks: {
        $store
      }
    })
    wrapper.vm.pickFile('front')
    expect(wrapper.vm.pickFile).toBeDefined()
  })

  test('Test method "updateState" default', () => {
    let p = Promise.reject({})
    $store.dispatch = jest.fn(() => p)
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }

    wrapper = shallowMount(AdminAdd, {
      mocks: {
        $toast: toast,
        $store
      }
    })
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.updateState('id', 'val')
    expect(spy).toBeCalled()
    p.catch(error => {})
    spy.mockRestore()
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

  test('Test method "submit" default', () => {
    let config = {
      id: 'id',
      opt: 'logo',
      val: 'logo.png',
      system_type: 2
    }
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }

    wrapper = shallowMount(AdminAdd, {
      mocks: {
        $toast: toast,
        $store
      }
    })

    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.submit(config)
    expect(wrapper.vm.submit).toBeDefined()
    expect(spy).toBeCalled()
    spy.mockRestore()
  })

  test('Test method "submit" default rejected promise', () => {
    let config = {
      id: 'id',
      opt: 'logo',
      val: 'logo.png',
      system_type: 2
    }
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }
    let p = Promise.reject({})
    $store.dispatch = jest.fn(() => p)

    wrapper = shallowMount(AdminAdd, {
      mocks: {
        $toast: toast,
        $store
      }
    })

    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.submit(config)
    expect(wrapper.vm.submit).toBeDefined()
    expect(spy).toBeCalled()
    p.catch(error => {})
    spy.mockRestore()
  })

  test('Test method "upload" default', () => {
    let blob = new Blob([20000], { type: 'image/png' })
    blob.lastModifiedDate = new Date()
    blob.name = 'test.png'

    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }

    wrapper = shallowMount(AdminAdd, {
      mocks: {
        $toast: toast,
        $store
      }
    })

    let spy = jest.spyOn($store, 'dispatch')
    wrapper.setData({ footerImage: blob })
    wrapper.setMethods({ getImage: jest.fn() })
    wrapper.vm.upload('id', 'footer')
    expect(wrapper.vm.upload).toBeDefined()
    expect(wrapper.vm.footerImage.name).toBe('test.png')
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

    wrapper = shallowMount(AdminAdd, {
      mocks: {
        $toast: toast,
        $store
      }
    })

    let spy = jest.spyOn($store, 'dispatch')
    wrapper.setData({ frontImage: blob })
    wrapper.setMethods({ getImage: jest.fn() })
    wrapper.vm.upload('idx', 'front')
    expect(wrapper.vm.upload).toBeDefined()
    expect(wrapper.vm.frontImage.name).toBe('test.png')
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

    wrapper = shallowMount(AdminAdd, {
      mocks: {
        $toast: toast,
        $store
      }
    })

    let spy = jest.spyOn($store, 'dispatch')
    wrapper.setData({ frontImage: blob })
    wrapper.setMethods({ getImage: jest.fn() })
    wrapper.vm.upload('idx', 'front')
    expect(wrapper.vm.upload).toBeDefined()
    expect(wrapper.vm.frontImage.name).toBe('test.png')
    expect(spy).toBeCalled()
    p.catch(error => {})
    spy.mockRestore()
  })
})
