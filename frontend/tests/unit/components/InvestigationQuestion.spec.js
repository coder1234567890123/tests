import { shallowMount } from '@vue/test-utils'
import axios from 'axios'
import InvestigationQuestions from '../../../components/InvestigationQuestions'

jest.mock('axios', () => ({
  $post: jest.fn(() =>
    Promise.resolve({
      id: 'proof',
      proof_storage: {
        id: 'id',
        image_file: 'image',
        subject: { id: 'b4a09600-4179-431b-a48d-ba6dc2f3d608' }
      }
    })
  ),
  $delete: jest.fn(() =>
    Promise.resolve({
      message: 'testing'
    })
  )
}))

describe('InvestigationQuestions', () => {
  let wrapper
  let propertyData
  const on = jest.fn()
  const farosianEventBus = {
    $on: on
  }
  global.farosianEventBus = farosianEventBus
  let process

  beforeEach(() => {
    process = { env: { blogStoragePath: () => 'path/' } }
    wrapper = shallowMount(InvestigationQuestions)
    propertyData = {
      question: {
        id: 'b4a09600-4179-431b-a48d-ba6dc2f3d605',
        question: 'Question test?',
        report_section: {
          id: '3afee68e-d94d-4186-aabb-1db3562a6cef',
          name: 'Question Test',
          enabled: true
        },
        answers: [],
        report_label: 'Test Label',
        answer_type: 'multiple_choice',
        report_types: ['basic', 'high_profile'],
        answer_options: ['Test', 'Test1'],
        platforms: ['all'],
        enabled: true,
        order_number: 2
      },
      subject: {
        id: 'b4a09600-4179-431b-a48d-ba6dc2f3d608',
        first_name: 'test',
        last_name: 'testing'
      },
      report: {
        id: 'b4a06600-4279-031b-a48r-ba6dc2f3e508',
        sequence: 'RPT1',
        comments: []
      }
    }
  })

  afterEach(() => {
    jest.resetModules()
    jest.clearAllMocks()
    wrapper.vm.$destroy()
  })
  test('Test is Vue Instance(InvestigationQuestions)', () => {
    wrapper.setData({ report: propertyData.report })
    expect(wrapper.isVueInstance()).toBeTruthy()
  })

  test('Test event bus default', () => {
    wrapper = shallowMount(InvestigationQuestions, {
      mocks: {
        process
      }
    })
    let spy = jest.spyOn(global.farosianEventBus, '$on')
    wrapper.setMethods({ addImage: jest.fn() })
    wrapper.setMethods({ loadReportComments: jest.fn() })
    wrapper.trigger('image-cropped')
    expect(spy).toBeCalled()
    spy.mockRestore()
  })

  test('Test imageCropperHandler method', () => {
    const data =
      'data:image/png;base64,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'
    let $route = { params: { id: 1 } }

    wrapper = shallowMount(InvestigationQuestions, {
      mocks: {
        $route,
        $axios: axios
      },
      propsData: {
        generalComment: { id: 'gc', proofs: [] }
      }
    })

    wrapper.trigger('image-cropped')
    expect(wrapper.vm.imageCropperHandler(data)).toBeDefined()
  })

  test('Test event bus rejected promise', () => {
    const data =
      'data:image/png;base64,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'
    let $route = { params: { id: 1 } }
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }
    let mockP = Promise.reject({})
    jest.mock('axios', () => ({
      $post: jest.fn(() => mockP)
    }))

    wrapper = shallowMount(InvestigationQuestions, {
      mocks: {
        $toast: toast,
        $axios: axios,
        process,
        $route
      },
      propsData: {
        generalComment: { id: 'gc', proofs: [] }
      }
    })
    let addImage = jest.fn()
    let spy = jest.spyOn(axios, '$post')
    wrapper.setMethods({ addImage: jest.fn() })
    wrapper.setMethods({ loadReportComments: jest.fn() })
    //wrapper.trigger('image-cropped')
    wrapper.vm.imageCropperHandler(data)
    expect(spy).toBeCalled()
    mockP.catch(error => {})
    spy.mockRestore()
  })

  test('Test computed value "answer" default', () => {
    wrapper.setData({ question: propertyData.question })
    expect(wrapper.vm.answer).toBe(null)
  })

  test('Test computed value "answer" set', () => {
    let answer = { answer: 'test', comments: [] }
    propertyData.question.answers.push(answer)
    wrapper.setData({ question: propertyData.question })
    expect(wrapper.vm.answer).toBe('test')
  })

  test('Test computed value "answer" set computed', () => {
    let answer = { answer: 'test', comments: [] }
    propertyData.question.answers.push(answer)
    wrapper.setData({ question: propertyData.question })
    wrapper.setData({ answer: 'test1' })
    expect(wrapper.vm.answer).toBe('test1')
  })

  test('Test computed value "comment" default', () => {
    wrapper.setData({ question: propertyData.question })
    expect(wrapper.vm.comment).toBe(null)
  })

  test('Test computed value "comment" set', () => {
    let answer = { comments: [{ comment: 'comment' }] }
    propertyData.question.answers[0] = answer
    wrapper.setData({ question: propertyData.question })
    expect(wrapper.vm.comment).toBe('comment')
  })

  test('Test computed value "comment" set computed', () => {
    let answer = { comments: [{ comment: 'comment' }] }
    propertyData.question.answers.push(answer)
    wrapper.setData({ question: propertyData.question })
    wrapper.setData({ comment: 'comment 1' })
    expect(wrapper.vm.comment).toBe('comment 1')
  })

  test('Test computed value "gm" default', () => {
    expect(wrapper.vm.gm).toBe(null)
  })

  test('Test computed value "comment" set', () => {
    wrapper.setData({ generalComment: { answer: 'test gm' } })
    expect(wrapper.vm.gm).toBe('test gm')
  })

  test('Test computed value "comment" set computed', () => {
    wrapper.setData({ generalComment: { answer: 'test gm' } })
    wrapper.setData({ gm: 'gm 1' })
    expect(wrapper.vm.gm).toBe('gm 1')
  })

  test('Test computed value "proof" default', () => {
    expect(wrapper.vm.proofs).toEqual([])
  })

  test('Test computed value "comment" set', () => {
    let answer = {
      comments: [],
      proofs: [
        {
          proof_storage: {
            id: 'id',
            image_file: 'image',
            subject: propertyData.subject
          }
        }
      ]
    }
    wrapper = shallowMount(InvestigationQuestions, {
      mocks: {
        process
      }
    })
    propertyData.question.answers[0] = answer
    wrapper.setData({ question: propertyData.question })
    expect(wrapper.vm.proofs[0].proof_storage.id).toBe('id')
  })

  test('Test computed value "comment" set computed', () => {
    let answer = {
      comments: [],
      proofs: [
        {
          proof_storage: {
            id: 'id',
            image_file: 'image',
            subject: propertyData.subject
          }
        }
      ]
    }

    let proof = {
      proof_storage: {
        id: 'id3',
        image_file: 'image',
        subject: propertyData.subject
      }
    }
    wrapper = shallowMount(InvestigationQuestions, {
      mocks: {
        process
      }
    })
    propertyData.question.answers[0] = answer
    wrapper.setData({ question: propertyData.question })
    wrapper.setData({ proofs: proof })
    expect(wrapper.vm.proofs[0].proof_storage.id).toBe('id3')
  })

  test('Test computed value "generalCommentProofs" default', () => {
    expect(wrapper.vm.generalCommentProofs).toEqual([])
  })

  test('Test computed value "generalCommentProofs" set', () => {
    let answer = {
      id: 'id',
      proofs: [
        {
          proof_storage: {
            id: 'id',
            image_file: 'image',
            subject: propertyData.subject
          }
        }
      ]
    }
    wrapper = shallowMount(InvestigationQuestions, {
      mocks: {
        process
      }
    })
    wrapper.setData({ generalComment: answer })
    expect(wrapper.vm.generalCommentProofs[0].proof_storage.id).toBe('id')
  })

  test('Test computed value "reportComments" default', () => {
    expect(wrapper.vm.reportComments).toEqual([])
  })

  test('Test computed value "reportComments" set', () => {
    let comment = {
      id: 'id',
      comment: 'comment',
      comment_by: {
        first_name: 'test',
        last_name: 'testing'
      }
    }
    let $store = {
      getters: {},
      dispatch: jest.fn(() => Promise.resolve({}))
    }
    $store.getters['investigation/reportComment'] = jest.fn(() =>
      Promise.resolve({})
    )
    wrapper = shallowMount(InvestigationQuestions, {
      mocks: {
        $store,
        process
      }
    })
    wrapper.setData({ reportComments: comment })
    expect(wrapper.vm.reportComments[0].comment).toBe('comment')
  })

  test('Test computed value "generalCommentProofs" set computed', () => {
    let answer = {
      id: 'id',
      proofs: [
        {
          proof_storage: {
            id: 'id',
            image_file: 'image',
            subject: propertyData.subject
          }
        }
      ]
    }

    let proof = {
      proof_storage: {
        id: 'id3',
        image_file: 'image',
        subject: propertyData.subject
      }
    }
    wrapper = shallowMount(InvestigationQuestions, {
      mocks: {
        process
      }
    })
    wrapper.setData({ generalComment: answer })
    wrapper.setData({ generalCommentProofs: proof })
    expect(wrapper.vm.generalCommentProofs[0].proof_storage.id).toBe('id3')
  })

  test('Test method "setImages" Proofs', () => {
    let proof = {
      id: '123',
      proof_storage: {
        id: 'id',
        image_file: 'image',
        subject: propertyData.subject
      }
    }
    wrapper = shallowMount(InvestigationQuestions, {
      mocks: {
        process
      }
    })
    propertyData.question.answers.push({ comments: [], proofs: [proof] })
    wrapper.setData({ question: propertyData.question })
    wrapper.vm.setImages(proof)
    expect(wrapper.vm.proofs).toHaveLength(1)
  })

  test('Test method "setImages" generalCommentProofs', () => {
    let proof = {
      id: '123',
      proof_storage: {
        id: 'id',
        image_file: 'image',
        subject: propertyData.subject
      }
    }
    wrapper = shallowMount(InvestigationQuestions, {
      mocks: {
        process
      }
    })
    wrapper.setData({ generalComment: { id: 'gc', proofs: [proof] } })
    wrapper.vm.setImages(proof)
    expect(wrapper.vm.generalCommentProofs).toHaveLength(1)
  })

  test('Test method "showImage" default', () => {
    let image = {
      id: '123',
      proof_storage: {
        id: 'id',
        image_file: 'image',
        subject: propertyData.subject
      }
    }
    wrapper = shallowMount(InvestigationQuestions, {
      mocks: {
        process
      }
    })
    wrapper.vm.showImage(image, 1)
    expect(wrapper.vm.imageDialog).toBe(true)
    expect(wrapper.vm.imageObject).toEqual(image)
    expect(wrapper.vm.imageObject.index).toBe(1)
  })

  test('Test method "deleteProofImage" Proof', () => {
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }
    wrapper = shallowMount(InvestigationQuestions, {
      mocks: {
        $axios: axios,
        $toast: toast,
        process
      },
      propsData: {
        generalComment: { id: 'gc', proofs: [] }
      }
    })
    let image = {
      id: '123',
      proof_storage: {
        id: 'id',
        image_file: 'image',
        subject: propertyData.subject
      }
    }
    wrapper.setData({ proofs: [image] })
    wrapper.vm.deleteProofImage(image)
    expect(wrapper.vm.proofs).toHaveLength(0)
  })

  test('Test method "deleteProofImage" ProofStorage', () => {
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }
    wrapper = shallowMount(InvestigationQuestions, {
      mocks: {
        $axios: axios,
        $toast: toast,
        process
      },
      propsData: {
        generalComment: { id: 'gc', proofs: [] }
      }
    })
    let image = {
      proof_storage: {
        id: 'id',
        image_file: 'image',
        subject: propertyData.subject
      }
    }
    wrapper.setData({ proofs: [image] })
    wrapper.vm.deleteProofImage(image)
    expect(wrapper.vm.proofs).toHaveLength(0)
  })

  test('Test method "submitAnswer" default', () => {
    let image = {
      id: '123',
      proof_storage: {
        id: 'id',
        image_file: 'image',
        subject: propertyData.subject
      }
    }
    wrapper = shallowMount(InvestigationQuestions, {
      propsData: {
        question: propertyData.question,
        subject: propertyData.subject
      },
      mocks: { process }
    })
    wrapper.setData({ proofs: image })
    wrapper.setData({ answer: 'test' })
    wrapper.setData({ comment: 'comment' })
    wrapper.vm.submitAnswer()
    expect(wrapper.vm.answer).toBe(null)
    expect(wrapper.vm.proofs).toEqual([])
    wrapper.vm.$emit('answer')
    expect(wrapper.emitted().answer).toBeTruthy()
  })

  test('Test method "submitAnswer" with answer', () => {
    let image = {
      id: '123',
      proof_storage: {
        id: 'id',
        image_file: 'image',
        subject: propertyData.subject
      }
    }
    propertyData.question.answers.push({
      id: 'ans',
      answer: 'answer',
      comments: [],
      proofs: []
    })
    wrapper = shallowMount(InvestigationQuestions, {
      propsData: {
        question: propertyData.question,
        subject: propertyData.subject
      },
      mocks: { process }
    })
    wrapper.setData({ proofs: image })
    wrapper.setData({ answer: 'test' })
    wrapper.setData({ comment: 'comment' })
    wrapper.vm.submitAnswer()
    expect(wrapper.vm.answer).toBe('answer')
    expect(wrapper.vm.newAnswer).toBe(null)
    expect(wrapper.vm.proofs).toEqual([])
    wrapper.vm.$emit('answer')
    expect(wrapper.emitted().answer).toBeTruthy()
  })

  test('Test method "submitGeneralComment" default', () => {
    let image = {
      id: '123',
      proof_storage: {
        id: 'id',
        image_file: 'image',
        subject: propertyData.subject
      }
    }
    wrapper = shallowMount(InvestigationQuestions, {
      propsData: {
        subject: propertyData.subject
      }
    })
    wrapper.setData({ proofs: image })
    wrapper.setData({ answer: 'test' })
    wrapper.setData({ comment: 'comment' })
    wrapper.vm.submitGeneralComment()
    expect(wrapper.vm.answer).toBe('test')
    expect(wrapper.vm.proofs).toEqual([])
    wrapper.vm.$emit('general-comment-handler')
    expect(wrapper.emitted()['general-comment-handler']).toBeTruthy()
  })

  test('Test method "submitGeneralComment" with answer', () => {
    let image = {
      id: '123',
      proof_storage: {
        id: 'id',
        image_file: 'image',
        subject: propertyData.subject
      }
    }
    wrapper = shallowMount(InvestigationQuestions, {
      propsData: {
        generalComment: { id: 'gc', answer: 'answer' },
        subject: propertyData.subject
      }
    })
    wrapper.setData({ proofs: image })
    wrapper.setData({ answer: 'test' })
    wrapper.setData({ comment: 'comment' })
    wrapper.vm.submitGeneralComment()
    expect(wrapper.vm.answer).toBe('test')
    expect(wrapper.vm.newAnswer).toBe('test')
    expect(wrapper.vm.proofs).toEqual([])
    wrapper.vm.$emit('general-comment-handler')
    expect(wrapper.emitted()['general-comment-handler']).toBeTruthy()
  })

  test('Test method "completeInvestigation" default', () => {
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }
    let $route = { params: { id: 1 }, push: jest.fn() }
    let $store = {
      dispatch: jest.fn(() => Promise.resolve({}))
    }
    wrapper = shallowMount(InvestigationQuestions, {
      mocks: {
        $toast: toast,
        $getRoute: jest.fn(),
        $route,
        $store
      }
    })
    wrapper.vm.completeInvestigation()
    expect(wrapper.vm.$store.dispatch()).toBeDefined()
  })

  test('Test method "disable" default reject', () => {
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }
    let mockP = Promise.reject({})
    jest.mock('axios', () => ({
      $post: jest.fn(() => mockP),
      $delete: jest.fn(() => mockP)
    }))

    wrapper = shallowMount(InvestigationQuestions, {
      mocks: {
        $toast: toast,
        $axios: axios
      }
    })
    let spy = jest.spyOn(axios, '$delete')
    wrapper.vm.disable('test', 1)
    expect(wrapper.vm.$axios.$delete()).toBeDefined()
    expect(spy).toBeCalled()
    mockP.catch(error => {})
    spy.mockRestore()
  })

  test('Test method "updateState" default', () => {
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }
    let $store = {
      dispatch: jest.fn(() => Promise.resolve({}))
    }
    wrapper = shallowMount(InvestigationQuestions, {
      mocks: {
        $toast: toast,
        $store
      }
    })
    wrapper.setMethods({ setComments: jest.fn() })
    wrapper.vm.updateState('id', 'test')
    expect(wrapper.vm.editReportComment.comment).toBe('')
    expect(wrapper.vm.editReportComment.dirty).toBeUndefined()
  })

  test('Test method "updateState" default dirty true', () => {
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }
    let $store = {
      dispatch: jest.fn(() => Promise.resolve({}))
    }
    wrapper = shallowMount(InvestigationQuestions, {
      mocks: {
        $toast: toast,
        $store
      }
    })
    wrapper.setData({ editReportComment: { comment: 'comment', dirty: true } })
    wrapper.setMethods({ setComments: jest.fn() })
    wrapper.setMethods({ loadReportComments: jest.fn() })
    wrapper.vm.updateState('id', 'test')
    expect(wrapper.vm.editReportComment.comment).toBe('comment')
    expect(wrapper.vm.editReportComment.dirty).toBe(true)
  })

  test('Test method "updateState" default rejected promise', () => {
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }

    let p = Promise.reject({})
    let $store = {
      dispatch: jest.fn(() => p)
    }
    wrapper = shallowMount(InvestigationQuestions, {
      mocks: {
        $toast: toast,
        $store
      }
    })
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.setData({ editReportComment: { comment: 'comment', dirty: true } })
    wrapper.vm.updateState('id', 'test')
    expect(wrapper.vm.editReportComment.comment).toBe('comment')
    expect(wrapper.vm.editReportComment.dirty).toBe(true)
    expect(spy).toBeCalled()
    p.catch(error => {})
    spy.mockRestore()
  })

  test('Test method "updateState" default rejected promise mock method', () => {
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }

    let p = Promise.reject({})
    let $store = {
      dispatch: jest.fn(() => p)
    }
    wrapper = shallowMount(InvestigationQuestions, {
      mocks: {
        $toast: toast,
        $store
      }
    })
    wrapper.setData({ editReportComment: { comment: 'comment' } })
    wrapper.vm.updateState('id', 'test')
    expect(wrapper.vm.editReportComment.comment).toBe('comment')
    expect(wrapper.vm.editReportComment.dirty).toBeUndefined()
    p.catch(error => {})
  })

  test('Test method "createReportComment" default', () => {
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }
    let $store = {
      getters: {},
      dispatch: jest.fn(() =>
        Promise.resolve({
          id: 'testId',
          comment: 'comment',
          comment_by: {
            id: 'id',
            first_name: 'name',
            last_name: 'test'
          }
        })
      )
    }
    $store.getters['investigation/reportComment'] = jest.fn(() =>
      Promise.resolve({})
    )
    wrapper = shallowMount(InvestigationQuestions, {
      mocks: {
        $toast: toast,
        $store
      }
    })
    wrapper.setData({ reportComment: { comment: 'test' }, dialogLoading: true })
    wrapper.setMethods({ setComments: jest.fn() })
    wrapper.setMethods({ clearCommentInputs: jest.fn() })
    wrapper.setMethods({ loadReportComments: jest.fn() })
    wrapper.vm.createReportComment()
    expect(wrapper.vm.reportComment.comment).toBe('test')
    expect(wrapper.vm.dialogLoading).toBe(true)
  })

  test('Test method "createReportComment" default rejected promise', () => {
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }
    let p = Promise.reject({})
    let $store = {
      dispatch: jest.fn(() => p)
    }
    wrapper = shallowMount(InvestigationQuestions, {
      mocks: {
        $toast: toast,
        $store
      }
    })
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.setMethods({ setComments: jest.fn() })
    wrapper.setData({ reportComment: { comment: 'test' }, dialogLoading: true })
    wrapper.vm.createReportComment()
    expect(wrapper.vm.reportComment.comment).toBe('test')
    expect(wrapper.vm.dialogLoading).toBe(true)
    expect(spy).toBeCalled()
    p.catch(error => {})
    spy.mockRestore()
  })

  test('Test method "setComments" default', () => {
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }
    let $store = {
      getters: {},
      dispatch: jest.fn(() => Promise.resolve({}))
    }
    $store.getters['investigation/reportComment'] = jest.fn(() =>
      Promise.resolve({})
    )
    wrapper = shallowMount(InvestigationQuestions, {
      propsData: {
        report: {
          id: 'id',
          comments: []
        }
      },
      mocks: {
        $toast: toast,
        $store
      }
    })
    wrapper.setData({
      newReportComments: [
        {
          id: 'id',
          comment: 'test',
          comment_by: {
            first_name: 'test',
            last_name: 'test'
          }
        }
      ]
    })
    wrapper.vm.setComments()
    expect(wrapper.vm.reportComments.length).toBe(1)
  })

  test('Test method "setComments" default report', () => {
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }
    let $store = {
      getters: {},
      dispatch: jest.fn(() => Promise.resolve({}))
    }
    $store.getters['investigation/reportComment'] = jest.fn(() =>
      Promise.resolve({})
    )
    wrapper = shallowMount(InvestigationQuestions, {
      propsData: {
        report: {
          id: 'id',
          comments: [
            {
              id: 'id',
              comment: 'test',
              comment_by: {
                first_name: 'test',
                last_name: 'test'
              }
            }
          ]
        }
      },
      mocks: {
        $toast: toast,
        $store
      }
    })
    wrapper.vm.setComments()
    expect(wrapper.vm.reportComments.length).toBe(1)
  })

  test('Test method "deleteReportComment" default report', () => {
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }
    let $store = {
      getters: {},
      dispatch: jest.fn(() => Promise.resolve({}))
    }
    $store.getters['investigation/reportComment'] = jest.fn(() =>
      Promise.resolve({})
    )
    global.confirm = () => true
    wrapper = shallowMount(InvestigationQuestions, {
      propsData: {
        report: {
          id: 'id',
          comments: [
            {
              id: 'id',
              comment: 'test',
              comment_by: {
                first_name: 'test',
                last_name: 'test'
              }
            }
          ]
        }
      },
      mocks: {
        $toast: toast,
        $store
      }
    })
    wrapper.setMethods({ setComments: jest.fn() })
    wrapper.setMethods({ loadReportComments: jest.fn() })
    wrapper.vm.deleteReportComment('id', 0)
    expect(wrapper.vm.newReportComments.length).toBe(0)
  })

  test('Test method "deleteReportComment" default rejected promise', () => {
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }
    let p = Promise.reject({})
    let $store = {
      getters: {},
      dispatch: jest.fn(() => p)
    }
    $store.getters['investigation/reportComment'] = jest.fn(() =>
      Promise.resolve({})
    )
    global.confirm = () => true
    wrapper = shallowMount(InvestigationQuestions, {
      propsData: {
        report: {
          id: 'id',
          comments: [
            {
              id: 'id',
              comment: 'test',
              comment_by: {
                first_name: 'test',
                last_name: 'test'
              }
            }
          ]
        }
      },
      mocks: {
        $toast: toast,
        $store
      }
    })
    wrapper.setMethods({ setComments: jest.fn() })
    wrapper.setMethods({ loadReportComments: jest.fn() })
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.deleteReportComment('id', 0)
    expect(wrapper.vm.newReportComments.length).toBe(0)
    expect(spy).toBeCalled()
    p.catch(error => {})
    spy.mockRestore()
  })

  test('Test method "completeReport" default', () => {
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }
    let $store = {
      dispatch: jest.fn(() => Promise.resolve({}))
    }
    let $router = {
      go: jest.fn()
    }

    wrapper = shallowMount(InvestigationQuestions, {
      mocks: {
        $toast: toast,
        $store,
        $router
      }
    })
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.completeReport()
    expect(spy).toBeCalled()
    spy.mockRestore()
  })

  test('Test method "completeReport" reject promise', () => {
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }
    let p = Promise.reject({})
    let $store = {
      dispatch: jest.fn(() => p)
    }
    let $router = {
      go: jest.fn()
    }

    wrapper = shallowMount(InvestigationQuestions, {
      mocks: {
        $toast: toast,
        $store,
        $router
      }
    })
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.completeReport()
    expect(spy).toBeCalled()
    p.catch(error => {})
    spy.mockRestore()
  })

  test('Test method "loadReportComments" default', () => {
    let $store = {
      dispatch: jest.fn()
    }
    let $route = { params: { id: 1 } }

    wrapper = shallowMount(InvestigationQuestions, {
      mocks: {
        $store,
        $route
      }
    })
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.loadReportComments()
    expect(spy).toBeCalled()
    spy.mockRestore()
  })

  test('Test method "customUpdate" default', () => {
    wrapper.setData({
      editDialogX: {
        id: 'test',
        comment: 'comment',
        private: false
      }
    })
    let updateState = jest.fn()
    wrapper.setMethods({ updateState: updateState })
    let spy = jest.spyOn(wrapper.vm, 'updateState')
    wrapper.vm.customUpdate()
    expect(spy).toBeCalled()
    spy.mockRestore()
  })

  test('Test method "setEditComment" default', () => {
    let note = {
      id: 'test1',
      comment: 'comment1',
      private: true
    }
    wrapper.setData({
      editDialogX: {
        id: 'test',
        comment: 'comment',
        private: false
      }
    })
    wrapper.vm.setEditComment(note)
    expect(wrapper.vm.editDialogX.id).toBe('test1')
    expect(wrapper.vm.editDialogX.comment).toBe('comment1')
    expect(wrapper.vm.editDialogX.private).toBe(true)
  })

  test('Test method "clearCommentInputs" default', () => {
    wrapper.setData({
      reportComment: 'test',
      reportCommentPrivate: 'private'
    })
    wrapper.vm.clearCommentInputs()
    expect(wrapper.vm.reportComment).toBe('')
    expect(wrapper.vm.reportCommentPrivate).toBe('')
  })

  test('Test method "updateReportComment" default', () => {
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }
    let $store = {
      dispatch: jest.fn(() => Promise.resolve({}))
    }

    wrapper = shallowMount(InvestigationQuestions, {
      mocks: {
        $toast: toast,
        $store
      }
    })
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.updateReportComment()
    expect(wrapper.vm.dialogLoading).toBe(false)
    expect(spy).toBeCalled()
    spy.mockRestore()
  })

  test('Test method "updateReportComment" reject promise', () => {
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }
    let p = Promise.reject({})
    let $store = {
      dispatch: jest.fn(() => p)
    }

    wrapper = shallowMount(InvestigationQuestions, {
      mocks: {
        $toast: toast,
        $store
      }
    })
    let spy = jest.spyOn($store, 'dispatch')
    wrapper.vm.updateReportComment()
    expect(wrapper.vm.dialogLoading).toBe(false)
    expect(spy).toBeCalled()
    p.catch(error => {})
    spy.mockRestore()
  })

  test('Test method "removeItem" default', () => {
    let toast = {
      success: jest.fn(),
      error: jest.fn()
    }

    wrapper = shallowMount(InvestigationQuestions, {
      mocks: {
        $toast: toast
      }
    })
    let setImages = jest.fn()
    wrapper.setData({
      proofs: {
        id: 'proof',
        proof_storage: {
          id: 'id',
          image_file: 'image',
          subject: { id: 'b4a09600-4179-431b-a48d-ba6dc2f3d608' }
        }
      }
    })
    wrapper.setMethods({ setImages: setImages })
    let spy = jest.spyOn(wrapper.vm, 'setImages')
    wrapper.vm.removeItem(0, { test: 'test' })
    expect(spy).toBeCalled()
    spy.mockRestore()
  })
})
