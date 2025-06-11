import { shallowMount } from '@vue/test-utils'
import plugins from '../../../../pages/admin/plugins'

describe('plugins', () => {
  let wrapper
  beforeEach(() => {
    wrapper = shallowMount(plugins)
  })
  afterEach(() => {
    jest.resetModules()
    jest.clearAllMocks()
    wrapper.vm.$destroy()
  })

  test('Test is Vue Instance(plugins)', () => {
    expect(wrapper.isVueInstance()).toBeTruthy()
  })
})
