<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Proof;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class ProofRepository
 *
 * @package App\Repository
 */
final class ProofRepository
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
     * ProofRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param TokenStorageInterface  $token
     */
    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $token)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Proof::class);
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
     * @return Proof[]|array|object[]
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
     * @return Proof[]|array|object[]
     */
    public function paginated(int $offset, int $limit, string $sort, bool $descending, string $search)
    {
        // Find Sort
        switch ($sort) {
            case 'url':
            default:
                $sort = 'url';
                break;
        }

        $qb = $this->repository->createQueryBuilder('s');

        if ($search != '') {
            $qb
                ->where('s.url LIKE :query')
                ->setParameter('query', "$search%");
        }

        $qb->orderBy("s.$sort", $descending === true ? 'DESC' : 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $qb->getQuery()->execute();
    }

    /**
     * @param Proof $proof
     */

    public function enable(Proof $proof)
    {
        $proof->setEnabled(true);

        $this->save($proof);
    }

    /**
     * @param Proof $proof
     */

    public function disable(Proof $proof)
    {
        $proof->setEnabled(false);

        $this->save($proof);
    }

    /**
     * @param $answer
     *
     * @return mixed
     */
    public function answers($answer)
    {
        $qb = $this->repository->createQueryBuilder('p')
            ->andWhere('p.answer = :id')
            ->setParameter('id', $answer->getId())
            ->getQuery();

        return $qb->execute();
    }

    /**
     * @param Proof $proof
     */
    public function save(Proof $proof)
    {
        $this->entityManager->persist($proof);
        $this->entityManager->flush();
    }
}
