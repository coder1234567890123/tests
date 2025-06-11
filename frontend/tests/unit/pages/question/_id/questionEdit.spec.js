import { shallowMount } from '@vue/test-utils'
import QuestionEdit from '../../../../../pages/question/_id/edit'

describe('QuestionAdd', () => {
  let wrapper
  let $store
  let $toast
  let $route
  beforeEach(() => {
    $store = {
      getters: {},
      dispatch: jest.fn(() => Promise.resolve({})),
      commit: jest.fn()
    }
    $route = { params: { id: 1 } }
    $toast = { success: jest.fn(), error: jest.fn() }
    $store.getters['question/question'] = {
      id: 'id',
      question: 'question',
      report_section: { id: 'sectionId' }
    }
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

    wrapper = shallowMount(QuestionEdit, {
      mocks: {
        $toast,
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
    wrapper.setMethods({ updateState: jest.fn() })
    expect(wrapper.vm.platformList).toBeDefined()
    expect(wrapper.vm.question.platforms).toHaveLength(2)
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
    wrapper.setMethods({ updateState: jest.fn() })
    expect(wrapper.vm.reportTypeList).toBeDefined()
    expect(wrapper.vm.question.report_types).toHaveLength(2)
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
    let p = Promise.reject({})
    $store.dispatch = jest.fn(() => p)
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.removeAnswerOption(1)
    expect(wrapper.vm.question.answer_options).toHaveLength(2)
    expect(wrapper.vm.question.answer_score).toHaveLength(2)
    expect(spy).toBeCalled()
    p.catch(error => {})
    spy.mockRestore()
  })

  test('Test method "answerTypeChange" default', () => {
    let p = Promise.reject({})
    $store.dispatch = jest.fn(() => p)
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.answerTypeChange('yes_no')
    expect(spy).toBeCalled()
    p.catch(error => {})
    spy.mockRestore()
  })

  test('Test method "addAnswerOption" default', () => {
    let p = Promise.reject({})
    $store.dispatch = jest.fn(() => p)
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.addAnswerOption()
    expect(spy).toBeCalled()
    p.catch(error => {})
    spy.mockRestore()
  })

  test('Test method "updateAnswerOption" default', () => {
    let p = Promise.reject({})
    $store.dispatch = jest.fn(() => p)
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.updateAnswerOption(0, 'test')
    expect(spy).toBeCalled()
    p.catch(error => {})
    spy.mockRestore()
  })

  test('Test method "updateAnswerScore" default', () => {
    let p = Promise.reject({})
    $store.dispatch = jest.fn(() => p)
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.updateAnswerScore(0, 'test')
    expect(spy).toBeCalled()
    p.catch(error => {})
    spy.mockRestore()
  })

  test('Test method "updateState" default', () => {
    let p = Promise.reject({})
    $store.dispatch = jest.fn(() => p)
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.updateState(0, 'test')
    expect(spy).toBeCalled()
    p.catch(error => {})
    spy.mockRestore()
  })

  test('Test method "validateForm" default', () => {
    let $router = { push: jest.fn() }
    const VueFormStub = {
      render: () => {},
      methods: {
        validate: () => true
      }
    }

    wrapper = shallowMount(QuestionEdit, {
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
    wrapper.vm.validateForm()
    expect(wrapper.vm.validateForm).toBeDefined()
    expect(spy).toBeCalled()
    spy.mockRestore()
  })

  test('Test method "validateForm" default false', () => {
    const VueFormStub = {
      render: () => {},
      methods: {
        validate: () => false
      }
    }

    wrapper = shallowMount(QuestionEdit, {
      stubs: {
        'v-form': VueFormStub
      },
      mocks: {
        $toast,
        $store
      }
    })
    wrapper.vm.validateForm()
    expect(wrapper.vm.validateForm).toBeDefined()
  })

  test('Test method "validateForm" default rejected promise', () => {
    let $router = { push: jest.fn() }
    const VueFormStub = {
      render: () => {},
      methods: {
        validate: () => true
      }
    }

    let p = Promise.reject({})
    $store.dispatch = jest.fn(() => p)

    wrapper = shallowMount(QuestionEdit, {
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
    wrapper.vm.validateForm()
    expect(wrapper.vm.validateForm).toBeDefined()
    expect(spy).toBeCalled()
    p.catch(error => {})
    spy.mockRestore()
  })
})
