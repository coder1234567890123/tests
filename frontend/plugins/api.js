import createRepository from '~/api/repository'
export default (ctx, inject) => {
  const repositoryWithAxios = createRepository(ctx.$axios)
  inject('usersRepository', repositoryWithAxios('/users'))
  inject('profileRepository', repositoryWithAxios('/profile'))
  inject('subjectRepository', repositoryWithAxios('/subject'))
  inject('questionRepository', repositoryWithAxios('/question'))
  inject('commentRepository', repositoryWithAxios('/comment'))
  inject('rolesRepository', repositoryWithAxios('/roles'))
  inject('countryRepository', repositoryWithAxios('/country'))
  inject('provincesRepository', repositoryWithAxios('/provinces'))
  inject('phraseRepository', repositoryWithAxios('/phrase'))
  inject('companiesRepository', repositoryWithAxios('/companies'))
}
