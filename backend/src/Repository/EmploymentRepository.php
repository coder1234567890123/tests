<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Employment;
use App\Entity\Subject;
use App\Service\ApiReturnService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class EmploymentRepository
 *
 * @package App\Repository
 */
final class EmploymentRepository extends AbstractController
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
     * EmploymentRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param TokenStorageInterface  $token
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $token,
        ApiReturnService $apiReturnService
    )
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Employment::class);
        $this->userToken = $token->getToken()->getUser();

        $this->apiReturnService = $apiReturnService;
    }

    /**
     * @param string $id
     *
     * @return null|object
     */
    public function find(string $id)
    {
        return $this->repository->where('subject = ' . $id);
    }

    /**
     * @return Employment[]|array|object[]
     */
    public function all()
    {
        return $this->repository->findAll();
    }

    /**
     * @param Employment $employment
     *
     *
     */
    public function delete(Employment $employment)
    {
        $qb = $this->repository->createQueryBuilder('s');
        $qb->where('s.id = :id')
            ->delete()
            ->setParameter('id', $employment->getId());
        $qb->getQuery()->execute();
    }

    /**
     * @param Employment $employment
     *
     * @return array
     */
    public function save(Employment $employment, Subject $subject)
    {
        $this->entityManager->persist($employment);
        $this->entityManager->flush();

        return $this->apiReturnService->getSubject($subject);
    }


}