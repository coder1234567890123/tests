<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\ReportSection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class ReportSectionRepository
 *
 * @package App\Repository
 */
final class ReportSectionRepository
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
     * ReportSectionRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param TokenStorageInterface  $token
     */
    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $token)
    {

        $this->entityManager = $entityManager;
        $this->repository    = $entityManager->getRepository(ReportSection::class);
        $this->userToken     = $token->getToken()->getUser();
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
     * @return ReportSection[]|array|object[]
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
     * @return ReportSection[]|array|object[]
     */
    public function paginated(int $offset, int $limit, string $sort, bool $descending, string $search)
    {
        // Find Sort
        switch ($sort) {
            case 'name':
            default:
                $sort = 'name';
                break;
        }

        $qb = $this->repository->createQueryBuilder('s');

        if ($search != '') {
            $qb
                ->where('s.name LIKE :query')
                ->setParameter('query', "$search%");
        }

        $qb->andWhere('s.enabled = :enabled')->setParameter('enabled', true);

        $qb->orderBy("s.$sort", $descending === true ? 'DESC' : 'ASC')
           ->setFirstResult($offset)
           ->setMaxResults($limit);

        return $qb->getQuery()->execute();
    }

    /**
     * @param ReportSection $reportSection
     */

    public function enable(ReportSection $reportSection)
    {
        $reportSection->setEnabled(true);

        $this->save($reportSection);
    }

    /**
     * @param ReportSection $reportSection
     */

    public function disable(ReportSection $reportSection)
    {
        $reportSection->setEnabled(false);

        $this->save($reportSection);
    }

    /**
     * @param ReportSection $reportSection
     */
    public function save(ReportSection $reportSection)
    {
        $this->entityManager->persist($reportSection);
        $this->entityManager->flush();
    }
}
