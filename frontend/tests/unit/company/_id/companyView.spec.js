import { shallowMount } from '@vue/test-utils'
import CompanyView from '../../../../pages/company/_id/view'

describe('CompanyView', () => {
  let wrapper
  let $store
  let $route
  let process
  let company = {
    id: 'companyId',
    name: 'company',
    image_front_page: 'test',
    country: { id: 'test', name: 'country' }
  }
  beforeEach(() => {
    $store = {
      getters: {},
      dispatch: jest.fn(() => Promise.resolve({}))
    }
    $store.getters['company/company'] = company
    $store.getters['static/countries'] = jest.fn(() => Promise.resolve({}))
    process = { env: { blogStoragePath: () => 'path/' } }
    $route = { params: { id: 2 } }

    wrapper = shallowMount(CompanyView, {
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

  test('Test is Vue Instance(CompanyView)', () => {
    expect(wrapper.isVueInstance()).toBeTruthy()
  })
})
