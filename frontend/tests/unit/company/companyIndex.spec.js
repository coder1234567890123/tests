import { shallowMount } from '@vue/test-utils'
import _ from 'lodash'
import CompanyIndex from '../../../pages/company/index'

jest.mock('lodash', () => ({
  debounce: jest.fn(fn => {
    fn.cancel = jest.fn()
    return fn
  })
}))

describe('CompanyIndex', () => {
  let wrapper
  let $store
  let $auth
  beforeEach(() => {
    $store = {
      getters: {},
      dispatch: jest.fn(() => Promise.resolve({})),
      commit: jest.fn()
    }
    $auth = {
      hasScope: jest.fn(() => true)
    }
    $store.getters['company/pagination'] = { totalItems: 2 }
    $store.getters['company/search'] = jest.fn(() => Promise.resolve({}))
    $store.getters['company/companies'] = [{ id: 'id1' }, { id: 'id2' }]

    wrapper = shallowMount(CompanyIndex, {
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

  test('Test is Vue Instance(CompanyIndex)', () => {
    expect(wrapper.isVueInstance()).toBeTruthy()
  })

  test('Test computed value "pagination"', () => {
    let spy = jest.spyOn($store, 'commit')
    let item = { totalItems: 3 }
    wrapper.vm.pagination = { totalItems: 3 }
    wrapper.setData({ pagination: item })
    expect(wrapper.vm.pagination.totalItems).toBe(3)
    expect(spy).toBeCalled()
    spy.mockRestore()
  })

  test('Test computed value "search"', () => {
    wrapper = shallowMount(CompanyIndex, {
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
})
