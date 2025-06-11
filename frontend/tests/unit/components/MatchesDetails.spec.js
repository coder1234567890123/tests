import { shallowMount } from '@vue/test-utils'
import MatchesDetails from '../../../components/MatchesDetails'

describe('MatchesDetails', () => {
  let wrapper
  beforeEach(() => {
    wrapper = shallowMount(MatchesDetails)
  })
  afterEach(() => {
    jest.resetModules()
    jest.clearAllMocks()
    wrapper.vm.$destroy()
  })

  test('Test is Vue Instance(MatchDetails)', () => {
    expect(wrapper.isVueInstance()).toBeTruthy()
  })

  test('Test emit ', () => {
    wrapper.vm.close()
    expect(wrapper.emitted()['update:dialog']).toBeTruthy()
  })
})
