import { shallowMount } from '@vue/test-utils'
import CompanyAdd from '../../../pages/company/add'

describe('CompanyAdd', () => {
  let wrapper
  let $store
  beforeEach(() => {
    $store = {
      getters: {},
      dispatch: jest.fn(() => Promise.resolve({}))
    }
    $store.getters['static/countries'] = jest.fn(() => Promise.resolve({}))

    wrapper = shallowMount(CompanyAdd, {
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

  test('Test is Vue Instance(CompanyAdd)', () => {
    expect(wrapper.isVueInstance()).toBeTruthy()
  })

  // test('Test head title', () => {
  //   expect(wrapper.vm.head()).toBe('Add Company :: Farosian')
  // })
})
