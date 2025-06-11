<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Queued;
use App\Entity\Report;
use App\Entity\Subject;
use App\Entity\User;
use App\Entity\UserTracking;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Exception;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class UserTrackingRepository
 *
 * @package App\Repository
 */
final class UserTrackingRepository
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
     * QueuedRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param TokenStorageInterface  $token
     * @param ParameterBagInterface  $params
     */
    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $token, ParameterBagInterface $params)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(UserTracking::class);
        $this->userToken = $token->getToken()->getUser();
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
     * @return UserTracking[]|array|object[]
     */
    public function all()
    {
        return $this->repository->findAll();
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->repository->count([]);
    }

    /**
     * @param int    $offset
     * @param int    $limit
     * @param string $sort
     * @param bool   $descending
     * @param string $search
     *
     * @param User   $user
     *
     * @return UserTracking[]|array|object[]
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

        if ($search != '') {
            $searchNew = explode(" ", trim($search));

            foreach ($searchNew as $search) {
                $qb = $this->repository->createQueryBuilder('ut');

                $qb->where('ut.action LIKE :query')
                    ->join('ut.company', 'c')
                    ->join('ut.user', 'u')
                    ->orWhere('c.name LIKE :query')
                    ->orWhere('u.firstName LIKE :query')
                    ->orWhere('u.lastName LIKE :query')
                    ->setParameter('query', "%$search%");
            }
        }

        $qb->orderBy("ut.$sort", $descending === true ? 'DESC' : 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $qb->getQuery()->execute();
    }

    /**
     * @param int $limit
     *
     * @return UserTracking[]|array|object[]
     */
    public function getLatest(int $limit)
    {
        return $this->repository->findBy(array(), ['createdAt' => 'DESC'], $limit);
    }

    /**
     * @param string $reportId
     * @param string $reportStatus
     *
     * @return null|UserTracking|object
     */
    public function getApprovedBy(string $reportId, string $reportStatus)
    {
        return $this->repository->findOneBy([
            'report' => $reportId,
            'reportStatus' => $reportStatus
        ]);
    }

    /**
     * @param User $user
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function forRole(User $user)
    {
        $queryBuilder = $this->repository->createQueryBuilder('ut');
        switch ($user->getRoles()[0]) {
            case 'ROLE_SUPER_ADMIN':
                break;
            case 'ROLE_ADMIN_USER': // get company based on team assigned
                $queryBuilder->innerJoin('ut.user', 'u')
                    ->andWhere('u.company = :uid')
                    ->setParameter('uid', $user->getCompany()->getId());
                break;
        }
        return $queryBuilder;
    }

    /**
     * @param UserTracking $tracking
     */
    public function save(UserTracking $tracking)
    {
        $this->entityManager->persist($tracking);
        $this->entityManager->flush();
    }
}
