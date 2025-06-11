<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Team;
use App\Entity\User;
use App\Service\ApiTeamsService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class TeamRepository
 *
 * @package App\Repository
 */
final class TeamRepository
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @var TokenStorageInterface
     */
    private $userToken;

    /**
     * TeamRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param TokenStorageInterface  $token
     * @param ApiTeamsService        $apiTeamsService
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $token,
        ApiTeamsService $apiTeamsService
    )
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Team::class);
        $this->repositoryUser = $entityManager->getRepository(User::class);
        $this->userToken = $token->getToken()->getUser();

        $this->apiTeamsService = $apiTeamsService;
    }

    /**
     * @param string $id
     *
     * @return null|object
     */
    public function find(string $id)
    {
        return $this->repository->find($id);
    }

    /**
     * @return Team[]|array|object[]
     */
    public function all()
    {
        return $this->apiTeamsService->teamsIndex($this->repository->findAll());
    }

    /**
     * @param int    $offset
     * @param int    $limit
     * @param string $sort
     * @param bool   $descending
     * @param string $search
     * @param User   $user
     *
     * @return Team[]|array|object[]
     */
    public function paginated(int $offset, int $limit, string $sort, bool $descending, string $search, User $user)
    {
        // Find Sort
        switch ($sort) {
            default:
                $sort = 'createdAt';
                break;
        }

        $qb = $this->forRole($user);

        if ($search !== '') {
            $searchNew = explode(" ", trim($search));

            foreach ($searchNew as $search) {
                $qb = $this->repository->createQueryBuilder('t');

                $qb
                    ->join('t.teamLeader', 'u')
                    ->andWhere('u.firstName LIKE :query')
                    ->orWhere('u.lastName LIKE :query')
                    ->orWhere('u.id LIKE :query')
                    ->orWhere('u.email LIKE :query')
                    ->setParameter('query', "%$search%");
            }
        }

        $qb->orderBy("t.$sort", $descending === true ? 'DESC' : 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $this->apiTeamsService->teamsIndex($qb->getQuery()->execute());
    }

    /**
     * @param string $teamLead
     *
     * @return Team|NULL|object
     */
    public function findByTeamLead(string $teamLead)
    {
        return $this->repository->findOneBy([
            'teamLeader' => $teamLead,
        ]);
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->repository->count([]);
    }

    /**
     * @param Team $team
     */
    public function save(Team $team)
    {
        $this->entityManager->persist($team);
        $this->entityManager->flush();
    }

    /**
     * @param Team $team
     */
    public function delete(Team $team)
    {
        $this->entityManager->remove($team);
        $this->entityManager->flush();
    }

    /**
     * @param User $user
     *
     * @return QueryBuilder
     */
    private function forRole(User $user)
    {
        $queryBuilder = $this->repository->createQueryBuilder('t');
        switch ($user->getRoles()[0]) {
            case 'ROLE_SUPER_ADMIN':
                $queryBuilder->innerJoin('t.teamLeader', 'u');
                break;
            case 'ROLE_TEAM_LEAD': // get reports based on team lead assigned to company
                $queryBuilder->innerJoin('t.teamLeader', 'u')
                    ->andWhere('u.id = :id')
                    ->setParameter('id', $user->getId());
                break;
            case 'ROLE_ANALYST': // get reports based on analyst assigned to report
                $queryBuilder->andWhere('t.id = :id')
                    ->setParameter('id', $user->getTeam()->getId());
                break;
            case 'ROLE_ADMIN_USER': // get reports based on company
            case 'ROLE_USER_MANAGER':
            case 'ROLE_USER_STANDARD':
                $queryBuilder->andWhere('t.id = :id')
                    ->setParameter('id', $user->getCompany()->getTeam()->getId());
                break;
        }
        return $queryBuilder;
    }
}