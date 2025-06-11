import { shallowMount } from '@vue/test-utils'
import _ from 'lodash'
import QuestionAdd from '../../../../pages/question/add'

jest.mock('lodash', () => ({
  debounce: jest.fn(fn => {
    fn.cancel = jest.fn()
    return fn
  })
}))

describe('QuestionAdd', () => {
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
    $store.getters['question/sections'] = jest.fn(() => Promise.resolve({}))
    $store.getters['static/platforms'] = [
      { label: 'All', value: 'all', disabled: false },
      { label: 'Facebook', value: 'facebook', disabled: false }
    ]
    $store.getters['static/reportTypes'] = [
      { label: 'High Profile', value: 'high_profile', disabled: false },
      { label: 'Global', value: 'global', disabled: false }
    ]
    $store.getters['static/answerTypes'] = jest.fn(() => Promise.resolve({}))

    wrapper = shallowMount(QuestionAdd, {
      mocks: {
        $toast,
        $store
      }
    })
  })
  afterEach(() => {
    jest.resetModules()
    jest.clearAllMocks()
    wrapper.vm.$destroy()
  })

  test('Test is Vue Instance(QuestionAdd)', () => {
    expect(wrapper.isVueInstance()).toBeTruthy()
  })

  test('Test computed value "answerTypesRules" default', () => {
    expect(wrapper.vm.answerTypesRules).toBeDefined()
  })

  test('Test computed value "answerScoreRules" default', () => {
    expect(wrapper.vm.answerScoreRules).toBeDefined()
    expect(wrapper.vm.answerScoreRules.length).toBe(3)
  })

  test('Test computed value "platformList" default', () => {
    expect(wrapper.vm.platformList).toBeDefined()
  })

  test('Test computed value "platformList" default two platforms', () => {
    wrapper.setData({ question: { platforms: ['twitter', 'facebook'] } })
    expect(wrapper.vm.platformList).toBeDefined()
    expect(wrapper.vm.question.platforms).toHaveLength(2)
    expect(wrapper.vm.question.platforms[0]).toBe('twitter')
  })

  test('Test computed value "platformList" default one platform and all', () => {
    wrapper.setData({ question: { platforms: ['twitter', 'all'] } })
    expect(wrapper.vm.platformList).toBeDefined()
    expect(wrapper.vm.question.platforms).toHaveLength(1)
    expect(wrapper.vm.question.platforms[0]).toBe('all')
  })

  test('Test computed value "reportTypeList" default', () => {
    expect(wrapper.vm.reportTypeList).toBeDefined()
  })

  test('Test computed value "reportTypeList" default two types', () => {
    wrapper.setData({ question: { report_types: ['basic', 'full'] } })
    expect(wrapper.vm.reportTypeList).toBeDefined()
    expect(wrapper.vm.question.report_types).toHaveLength(2)
    expect(wrapper.vm.question.report_types[0]).toBe('basic')
  })

  test('Test computed value "reportTypeList" default one type and global', () => {
    wrapper.setData({ question: { report_types: ['full', 'global'] } })
    expect(wrapper.vm.reportTypeList).toBeDefined()
    expect(wrapper.vm.question.report_types).toHaveLength(1)
    expect(wrapper.vm.question.report_types[0]).toBe('global')
  })

  test('Test method "checkLeadingZero" default', () => {
    expect(wrapper.vm.checkLeadingZero(12)).toBe(true)
  })

  test('Test method "checkLeadingZero" default leading 0', () => {
    expect(wrapper.vm.checkLeadingZero('02')).toBe(false)
  })

  test('Test method "removeAnswerOption" default', () => {
    wrapper.setData({ question: { answer_options: ['test', 'test1'] } })
    wrapper.setData({ question: { answer_score: ['2', '3'] } })
    wrapper.vm.removeAnswerOption(1)
    expect(wrapper.vm.question.answer_options).toHaveLength(1)
    expect(wrapper.vm.question.answer_score).toHaveLength(1)
  })

  test('Test method "answerTypeChange" default', () => {
    wrapper.vm.answerTypeChange('yes_no')
    expect(wrapper.vm.question.answer_options).toHaveLength(2)
    expect(wrapper.vm.question.answer_score).toHaveLength(2)
  })

  test('Test method "answerTypeChange" default text', () => {
    wrapper.vm.answerTypeChange('text')
    expect(wrapper.vm.question.answer_options).toHaveLength(0)
    expect(wrapper.vm.question.answer_score).toHaveLength(0)
  })

  test('Test method "addAnswerOption" default', () => {
    wrapper.vm.addAnswerOption()
    expect(wrapper.vm.question.answer_options).toHaveLength(1)
    expect(wrapper.vm.question.answer_score).toHaveLength(1)
  })

  test('Test method "add" default', () => {
    let $router = { push: jest.fn() }
    const VueFormStub = {
      render: () => {},
      methods: {
        validate: () => true
      }
    }

    wrapper = shallowMount(QuestionAdd, {
      stubs: {
        'v-form': VueFormStub
      },
      mocks: {
        $toast,
        $store,
        $router
      }
    })
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.add()
    expect(wrapper.vm.add).toBeDefined()
    expect(spy).toBeCalled()
    spy.mockRestore()
  })

  test('Test method "add" default validate false', () => {
    let $router = { push: jest.fn() }
    const VueFormStub = {
      render: () => {},
      methods: {
        validate: () => false
      }
    }

    wrapper = shallowMount(QuestionAdd, {
      stubs: {
        'v-form': VueFormStub
      },
      mocks: {
        $toast,
        $store,
        $router
      }
    })
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.add()
    expect(wrapper.vm.add).toBeDefined()
    expect(spy).toBeCalledTimes(0)
    spy.mockRestore()
  })

  test('Test method "add" default rejected promise', () => {
    let $router = { push: jest.fn() }
    const VueFormStub = {
      render: () => {},
      methods: {
        validate: () => true
      }
    }

    let p = Promise.reject({})
    $store.dispatch = jest.fn(() => p)

    wrapper = shallowMount(QuestionAdd, {
      stubs: {
        'v-form': VueFormStub
      },
      mocks: {
        $toast,
        $store,
        $router
      }
    })
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.add()
    expect(wrapper.vm.add).toBeDefined()
    expect(spy).toBeCalled()
    p.catch(error => {})
    spy.mockRestore()
  })
})
