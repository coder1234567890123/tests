import { shallowMount } from '@vue/test-utils'
import Notification from '../../../components/Notification'

describe('Notification', () => {
  let wrapper
  beforeEach(() => {
    wrapper = shallowMount(Notification)
  })
  afterEach(() => {
    jest.resetModules()
    jest.clearAllMocks()
    wrapper.vm.$destroy()
  })

  test('Test is Vue Instance(Notification)', () => {
    expect(wrapper.isVueInstance()).toBeTruthy()
  })
})
