export default function(context) {
  let route = context.route
  let params = context.params
  let auth = context.store.$auth

  if (auth.hasScope('ROLE_SUPER_ADMIN')) {
    return
  }

  switch (route.fullPath) {
    case '/company/add':
      if (!auth.hasScope('ROLE_SUPER_ADMIN')) {
        return context.redirect('/')
      }
      break
    case '/company/' + params.id + '/edit':
      if (!auth.hasScope('ROLE_SUPER_ADMIN')) {
        return context.redirect('/')
      }
      break
    case '/search/add':
      if (!auth.hasScope('ROLE_SUPER_ADMIN')) {
        return context.redirect('/')
      }
      break
    case '/search/' + params.id + '/edit':
      if (!auth.hasScope('ROLE_SUPER_ADMIN')) {
        return context.redirect('/')
      }
      break
    case '/search/view':
      if (!auth.hasScope('ROLE_SUPER_ADMIN')) {
        return context.redirect('/')
      }
      break
    case '/user/add':
      if (
        !auth.hasScope('ROLE_ADMIN_USER') &&
        !auth.hasScope('ROLE_USER_MANAGER')
      ) {
        return context.redirect('/')
      }
      break
    case '/user/' + params.id + '/edit':
      if (
        !auth.hasScope('ROLE_ADMIN_USER') &&
        !auth.hasScope('ROLE_USER_MANAGER')
      ) {
        return context.redirect('/')
      }
      break
    case '/user/' + params.id + '/profile':
      if (
        !auth.hasScope('ROLE_ADMIN_USER') &&
        !auth.hasScope('ROLE_TEAM_LEAD') &&
        params.id !== auth.user.id
      ) {
        return context.redirect('/')
      }
      break
    case '/user':
      if (
        !auth.hasScope('ROLE_ADMIN_USER') &&
        !auth.hasScope('ROLE_USER_MANAGER')
      ) {
        return context.redirect('/')
      }
      break
    case '/investigation/' + params.id:
      if (!auth.hasScope('ROLE_TEAM_LEAD') && !auth.hasScope('ROLE_ANALYST')) {
        return context.redirect('/')
      }
      break
    default:
  }
}
