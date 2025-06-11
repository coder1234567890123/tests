import { shallowMount } from '@vue/test-utils'
import InvestigationIndex from '../../../../../pages/investigation/_id/index'

describe('InvestigationIndex', () => {
  let wrapper
  let $store
  let $route
  let $toast
  beforeEach(() => {
    $store = {
      getters: {},
      commit: jest.fn(),
      dispatch: jest.fn(() => Promise.resolve({}))
    }
    $toast = {
      success: jest.fn(),
      error: jest.fn()
    }
    $store.getters['subject/subject'] = jest.fn(() => Promise.resolve({}))
    $store.getters['investigation/questions'] = jest.fn(() =>
      Promise.resolve({})
    )
    $store.getters['investigation/investigateDialog'] = jest.fn(() =>
      Promise.resolve({})
    )
    $store.getters['investigation/currentQuestion'] = 0
    $store.getters['investigation/generalComment'] = {
      id: 'id',
      answer: 'general comment',
      proofs: []
    }

    $route = { params: { id: 1 } }
    wrapper = shallowMount(InvestigationIndex, {
      mocks: {
        $store,
        $toast,
        $route
      }
    })
  })
  afterEach(() => {
    jest.resetModules()
    jest.clearAllMocks()
    wrapper.vm.$destroy()
  })

  test('Test is Vue Instance(InvestigationIndex)', () => {
    expect(wrapper.isVueInstance()).toBeTruthy()
  })

  test('Test method (handleAnswer) default', () => {
    let answer = {
      id: 'id',
      answer: 'test',
      dirty: true,
      proofs: [],
      question: { id: 'qId' },
      report: {},
      comments: []
    }
    $store.getters['subject/subject'] = {
      id: 'subId',
      first_name: 'test',
      last_name: ' surname',
      status: 'under_investigation',
      reports: [{ id: 'reportId' }]
    }
    $store.getters['investigation/questions'] = [{ id: 'qId' }]
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.setMethods({ next: jest.fn() })
    wrapper.vm.handleAnswer(answer)
    expect(wrapper.vm.handleAnswer).toBeDefined()
    expect(spy).toBeCalled()
    spy.mockRestore()
  })

  test('Test method (handleAnswer) default rejected promise', () => {
    let answer = {
      id: 'id',
      answer: 'test',
      dirty: true,
      proofs: [],
      question: { id: 'qId' },
      report: {},
      comments: []
    }
    let p = Promise.reject({})
    $store.dispatch = jest.fn(() => p)
    $store.getters['subject/subject'] = {
      id: 'subId',
      first_name: 'test',
      last_name: ' surname',
      status: 'under_investigation',
      reports: [{ id: 'reportId' }]
    }
    $store.getters['investigation/questions'] = [{ id: 'qId' }]
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.setMethods({ next: jest.fn() })
    wrapper.vm.handleAnswer(answer)
    expect(wrapper.vm.handleAnswer).toBeDefined()
    expect(spy).toBeCalled()
    p.catch(error => {})
    spy.mockRestore()
  })

  test('Test method (handleAnswer) default dirty is false', () => {
    let answer = {
      id: 'id',
      answer: 'test',
      dirty: false,
      proofs: [],
      question: { id: 'qId' },
      report: {},
      comments: []
    }
    wrapper.setMethods({ next: jest.fn() })
    wrapper.vm.handleAnswer(answer)
    expect(wrapper.vm.handleAnswer).toBeDefined()
  })

  test('Test method (handleGeneralComment) default', () => {
    let answer = {
      id: 'id',
      answer: 'test',
      dirty: true,
      proofs: [],
      report: {},
      comments: []
    }
    $store.getters['subject/subject'] = {
      id: 'subId',
      first_name: 'test',
      last_name: ' surname',
      status: 'under_investigation',
      reports: [{ id: 'reportId' }]
    }
    $store.getters['investigation/questions'] = [{ id: 'qId' }]
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.handleGeneralComment(answer)
    expect(wrapper.vm.handleGeneralComment).toBeDefined()
    expect(spy).toBeCalled()
    spy.mockRestore()
  })

  test('Test method (handleGeneralComment) default rejected promise', () => {
    let answer = {
      id: 'id',
      answer: 'test',
      dirty: true,
      proofs: [],
      report: {},
      comments: []
    }
    let p = Promise.reject({})
    $store.dispatch = jest.fn(() => p)
    $store.getters['subject/subject'] = {
      id: 'subId',
      first_name: 'test',
      last_name: ' surname',
      status: 'under_investigation',
      reports: [{ id: 'reportId' }]
    }
    $store.getters['investigation/questions'] = [{ id: 'qId' }]
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.handleGeneralComment(answer)
    expect(wrapper.vm.handleGeneralComment).toBeDefined()
    expect(spy).toBeCalled()
    p.catch(error => {})
    spy.mockRestore()
  })

  test('Test method (handleGeneralComment) default dirty is false', () => {
    let answer = {
      id: 'id',
      answer: 'test',
      dirty: false,
      proofs: [],
      report: {},
      comments: []
    }
    wrapper.vm.handleGeneralComment(answer)
    expect(wrapper.vm.handleGeneralComment).toBeDefined()
  })

  test('Test method (next) default', () => {
    let spy = jest.spyOn($store, 'commit')
    wrapper.vm.next()
    expect(wrapper.vm.next).toBeDefined()
    expect(spy).toBeCalled()
    spy.mockRestore()
  })

  test('Test method (previous) default', () => {
    let spy = jest.spyOn($store, 'commit')
    wrapper.vm.previous()
    expect(wrapper.vm.previous).toBeDefined()
    expect(spy).toBeCalled()
    spy.mockRestore()
  })

  test('Test method (reset) default', () => {
    let spy = jest.spyOn($store, 'commit')
    wrapper.vm.reset()
    expect(wrapper.vm.reset).toBeDefined()
    expect(spy).toBeCalled()
    spy.mockRestore()
  })
})
