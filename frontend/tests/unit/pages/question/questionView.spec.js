import { shallowMount } from '@vue/test-utils'
import _ from 'lodash'
import QuestionView from '../../../../pages/question/view'

jest.mock('lodash', () => ({
  debounce: jest.fn(fn => {
    fn.cancel = jest.fn()
    return fn
  })
}))

describe('QuestionView', () => {
  let wrapper
  let $store
  let $auth
  let $toast
  beforeEach(() => {
    $store = {
      getters: {},
      dispatch: jest.fn(() => Promise.resolve({})),
      commit: jest.fn()
    }
    $auth = {
      hasScope: jest.fn(() => Promise.resolve({}))
    }
    $toast = { success: jest.fn(), error: jest.fn() }
    $store.getters['question/questions'] = jest.fn(() => Promise.resolve({}))
    $store.getters['question/pagination'] = { totalItems: 2 }
    $store.getters['question/search'] = jest.fn(() => Promise.resolve({}))

    wrapper = shallowMount(QuestionView, {
      mocks: {
        $toast,
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

  test('Test is Vue Instance(QuestionView)', () => {
    expect(wrapper.isVueInstance()).toBeTruthy()
  })

  test('Test computed value "pagination" default', () => {
    let spy = jest.spyOn($store, 'commit')
    let item = { totalItems: 3 }
    wrapper.vm.pagination = { totalItems: 3 }
    wrapper.setData({ pagination: item })
    expect(wrapper.vm.pagination.totalItems).toBe(3)
    expect(spy).toBeCalled()
    spy.mockRestore()
  })

  test('Test computed value "search"', () => {
    wrapper = shallowMount(QuestionView, {
      mocks: {
        $store,
        $auth
      }
    })
    let spy = jest.spyOn($store, 'commit')
    let search = 'test'
    wrapper.vm.search = search
    wrapper.setData({ search: search })
    expect(wrapper.vm.search).toBeDefined()
    expect(spy).toBeCalled()
    spy.mockRestore()
  })

  test('Test method "enableQuestion" default', () => {
    let $router = { go: jest.fn() }
    wrapper = shallowMount(QuestionView, {
      mocks: {
        $toast,
        $store,
        $auth,
        $router
      }
    })
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.enableQuestion('id')
    expect(wrapper.vm.enableQuestion).toBeDefined()
    expect(spy).toBeCalled()
    spy.mockRestore()
  })

  test('Test method "enableQuestion" default reject promise', () => {
    let $router = { go: jest.fn() }
    let p = Promise.reject({})
    $store.dispatch = jest.fn(() => p)
    wrapper = shallowMount(QuestionView, {
      mocks: {
        $toast,
        $store,
        $auth,
        $router
      }
    })
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.enableQuestion('id')
    expect(wrapper.vm.enableQuestion).toBeDefined()
    expect(spy).toBeCalled()
    p.catch(error => {})
    spy.mockRestore()
  })

  test('Test method "disableQuestion" default', () => {
    let $router = { go: jest.fn() }
    wrapper = shallowMount(QuestionView, {
      mocks: {
        $toast,
        $store,
        $auth,
        $router
      }
    })
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.disableQuestion('id')
    expect(wrapper.vm.disableQuestion).toBeDefined()
    expect(spy).toBeCalled()
    spy.mockRestore()
  })

  test('Test method "disableQuestion" default reject promise', () => {
    let $router = { go: jest.fn() }
    let p = Promise.reject({})
    $store.dispatch = jest.fn(() => p)
    wrapper = shallowMount(QuestionView, {
      mocks: {
        $toast,
        $store,
        $auth,
        $router
      }
    })
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.disableQuestion('id')
    expect(wrapper.vm.disableQuestion).toBeDefined()
    expect(spy).toBeCalled()
    p.catch(error => {})
    spy.mockRestore()
  })

  test('Test method "deleteQuestion" default', () => {
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.deleteQuestion('id')
    expect(wrapper.vm.deleteQuestion).toBeDefined()
    expect(spy).toBeCalled()
    spy.mockRestore()
  })

  test('Test method "deleteQuestion" default reject promise', () => {
    let p = Promise.reject({})
    $store.dispatch = jest.fn(() => p)
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.deleteQuestion('id')
    expect(wrapper.vm.deleteQuestion).toBeDefined()
    expect(spy).toBeCalled()
    p.catch(error => {})
    spy.mockRestore()
  })
})
