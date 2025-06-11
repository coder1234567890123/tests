import { shallowMount } from '@vue/test-utils'
import InvestigationLayout from '../../../components/InvestigationLayout'

describe('InvestigationLayout', () => {
  let wrapper
  let $store
  beforeEach(() => {
    $store = {
      getters: {},
      dispatch: jest.fn(() => Promise.resolve({}))
    }

    $store.getters['investigation/questions'] = jest.fn(() =>
      Promise.resolve({})
    )

    wrapper = shallowMount(InvestigationLayout, {
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

  test('Test is Vue Instance(InvestigationLayout)', () => {
    expect(wrapper.isVueInstance()).toBeTruthy()
  })

  test('Test method "next" default', () => {
    wrapper.vm.next()
    expect(wrapper.vm.count).toBe(1)
  })

  test('Test method "previous" default', () => {
    wrapper.setData({ count: 5 })
    wrapper.vm.previous()
    expect(wrapper.vm.count).toBe(4)
  })

  test('Test method "reset" default', () => {
    wrapper.setData({ count: 5 })
    wrapper.vm.reset()
    expect(wrapper.vm.count).toBe(0)
  })

  test('Test method "skipQuestions" default', () => {
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }

    wrapper = shallowMount(InvestigationLayout, {
      mocks: {
        $toast: toast,
        $store
      }
    })
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.skipQuestions('id')
    expect(wrapper.vm.$store.dispatch()).toBeDefined()
    expect(spy).toBeCalled()
    spy.mockRestore()
  })

  test('Test method "skipQuestions" default reject', () => {
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }
    let p = Promise.reject({})
    $store.dispatch = jest.fn(() => p)

    wrapper = shallowMount(InvestigationLayout, {
      mocks: {
        $toast: toast,
        $store
      }
    })
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.skipQuestions('id')
    expect(wrapper.vm.$store.dispatch()).toBeDefined()
    expect(spy).toBeCalled()
    p.catch(error => {})
    spy.mockRestore()
  })

  test('Test method "questNotAvailable" default', () => {
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }

    wrapper = shallowMount(InvestigationLayout, {
      mocks: {
        $toast: toast,
        $store
      }
    })
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.questNotAvailable('id')
    expect(wrapper.vm.$store.dispatch()).toBeDefined()
    expect(spy).toBeCalled()
    spy.mockRestore()
  })

  test('Test method "questNotAvailable" default reject', () => {
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }
    let p = Promise.reject({})
    $store.dispatch = jest.fn(() => p)

    wrapper = shallowMount(InvestigationLayout, {
      mocks: {
        $toast: toast,
        $store
      }
    })
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.questNotAvailable('id')
    expect(wrapper.vm.$store.dispatch()).toBeDefined()
    expect(spy).toBeCalled()
    p.catch(error => {})
    spy.mockRestore()
  })

  test('Test method "AnswerQuestion" default', () => {
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }

    wrapper = shallowMount(InvestigationLayout, {
      propsData: {
        answer: {}
      },
      mocks: {
        $toast: toast,
        $store
      }
    })
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.AnswerQuestion('id', 'yes')
    expect(wrapper.vm.$store.dispatch()).toBeDefined()
    expect(spy).toBeCalled()
    expect(wrapper.vm.answer.answer).toBe('yes')
    spy.mockRestore()
  })

  test('Test method "AnswerQuestion" question', () => {
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }

    wrapper = shallowMount(InvestigationLayout, {
      propsData: {
        answer: { question: { id: 'q1' } }
      },
      mocks: {
        $toast: toast,
        $store
      }
    })
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.AnswerQuestion('', 'yes')
    expect(wrapper.vm.$store.dispatch()).toBeDefined()
    expect(spy).toBeCalled()
    expect(wrapper.vm.answer.answer).toBe('yes')
    spy.mockRestore()
  })

  test('Test method "AnswerQuestion" default reject', () => {
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }
    let p = Promise.reject({})
    $store.dispatch = jest.fn(() => p)

    wrapper = shallowMount(InvestigationLayout, {
      propsData: {
        answer: {}
      },
      mocks: {
        $toast: toast,
        $store
      }
    })
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.AnswerQuestion('id', 'no')
    expect(wrapper.vm.$store.dispatch()).toBeDefined()
    expect(spy).toBeCalled()
    expect(wrapper.vm.answer.answer).toBe('no')
    p.catch(error => {})
    spy.mockRestore()
  })

  test('Test method "toggle" default', () => {
    wrapper.vm.toggle('testt', 0)
    wrapper.vm.toggle('no', 1)
    expect(wrapper.vm.answerSelected['X0']).toBe('testt')
    expect(wrapper.vm.answerSelected['X1']).toBe('no')
    expect(wrapper.vm.answer.answer).toBe('testt,no')
  })

  test('Test method "addComment" default', () => {
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }

    wrapper = shallowMount(InvestigationLayout, {
      mocks: {
        $toast: toast,
        $store
      }
    })
    wrapper.setData({ answerRespId: 'testId' })
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.addComment()
    expect(wrapper.vm.$store.dispatch()).toBeDefined()
    expect(spy).toBeCalled()
    spy.mockRestore()
  })

  test('Test method "addComment" default no answer id', () => {
    let $toast = {
      success: jest.fn(),
      error: jest.fn()
    }

    wrapper = shallowMount(InvestigationLayout, {
      mocks: {
        $toast,
        $store
      }
    })
    wrapper.setData({ answerRespId: '' })
    let spy = jest.spyOn($toast, 'error')
    wrapper.vm.addComment()
    expect(spy).toBeCalled()
    spy.mockRestore()
  })

  test('Test method "addComment" default reject', () => {
    let $toast = {
      success: jest.fn(),
      error: jest.fn()
    }
    let p = Promise.reject({})
    $store.dispatch = jest.fn(() => p)

    wrapper = shallowMount(InvestigationLayout, {
      mocks: {
        $toast,
        $store
      }
    })
    wrapper.setData({ answerRespId: 'testId' })
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.addComment()
    expect(wrapper.vm.$store.dispatch()).toBeDefined()
    expect(spy).toBeCalled()
    p.catch(error => {})
    spy.mockRestore()
  })
})
