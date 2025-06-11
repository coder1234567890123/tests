import Vue from 'vue'

// ROOTS
const SUBJECTS_ROOT = 'subjects'
const COMPANY_ROOT = 'company'

// System Routes
const FAROSIAN_ROUTES = {
  // SUBJECTS
  SUBJECTS_INDEX: SUBJECTS_ROOT,
  SUBJECTS_ADD: SUBJECTS_ROOT + '-add',
  SUBJECTS_EDIT: SUBJECTS_ROOT + '-id-edit',
  SUBJECTS_VIEW: SUBJECTS_ROOT + '-id',
  SUBJECTS_PROFILES: SUBJECTS_ROOT + '-id-profiles',
  SUBJECTS_PROFILES_ADD: SUBJECTS_ROOT + '-id-profiles-add',
  SUBJECTS_PROFILES_EDIT: SUBJECTS_ROOT + '-id-profiles-profile-edit',
  COMPANY_EDIT: COMPANY_ROOT + '-id-edit'
}

FAROSIAN_ROUTES.install = function(Vue, options) {
  Vue.prototype.$getRoute = key => {
    return FAROSIAN_ROUTES[key]
  }
}

Vue.use(FAROSIAN_ROUTES)
