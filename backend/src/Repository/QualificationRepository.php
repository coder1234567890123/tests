<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Qualification;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class QualificationRepository
 *
 * @package App\Repository
 */
final class QualificationRepository extends AbstractController
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
     * CountryRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param TokenStorageInterface  $token
     */
    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $token)
    {
        $this->entityManager = $entityManager;
        $this->repository    = $entityManager->getRepository(Qualification::class);
        $this->userToken     = $token->getToken()->getUser();
    }

    /**
     * @return Qualification[]|array|object[]
     */
    public function all()
    {
        return $this->repository->findAll();
    }

    /**
     * @param Qualification $qualification
     *
     *
     */
    public function delete(Qualification $qualification)
    {
        $qb = $this->repository->createQueryBuilder('s');
        $qb->where('s.id = :id')
           ->delete()
           ->setParameter('id', $qualification);
        $qb->getQuery()->execute();
    }

    /**
     * @param Qualification $qualification
     */
    public
    function save(Qualification $qualification)
    {
        $this->entityManager->persist($qualification);
        $this->entityManager->flush();

    }
}