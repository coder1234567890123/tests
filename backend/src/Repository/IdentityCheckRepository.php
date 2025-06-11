<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\IdentityConfirm;
use App\Service\ApiReturnService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class IdentityCheckRepository
 *
 * @package App\Repository
 */
final class IdentityCheckRepository extends AbstractController
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
     * $param ApiReturnService       $apiReturnService
     */
    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $token, ApiReturnService $apiReturnService)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(IdentityConfirm::class);
        $this->apiReturnService = $apiReturnService;
        $this->userToken = $token->getToken()->getUser();
    }

    /**
     * @param $subject
     *
     * @return array|object[]
     */
    public function all($subject)
    {
        return $this->apiReturnService->getIdentityConfirm($subject);
    }

    /**
     * @param $identityConfirm
     * @param $subject
     *
     * @return array
     */
    public function getPlatform($identityConfirm, $subject)
    {
        $qb = $this->repository->createQueryBuilder('p')
            ->andWhere('p.subject = :subject_id')
            ->setParameter('subject_id', $subject)
            ->andWhere('p.platform = :platform_type')
            ->setParameter('platform_type', $identityConfirm->getPlatform())
            ->getQuery();

        return $this->apiReturnService->showIdentityConfirm($qb->execute());
    }

    /**
     * @param $identityConfirm
     * @param $subject
     */
    public function checkId($identityConfirm, $subject)
    {
        $qb = $this->repository->createQueryBuilder('p')
            ->andWhere('p.subject = :subject_id')
            ->setParameter('subject_id', $subject)
            ->andWhere('p.platform = :platform')
            ->setParameter('platform', $identityConfirm->getPlatform())
            ->getQuery();

        if ($qb->execute()) {
            $identity = $this->repository->find($qb->execute()[0]->getId());

            $identity->setIdentityName($identityConfirm->isIdentityName());
            $identity->setIdentityMiddleName($identityConfirm->isIdentityMiddleName());
            $identity->setIdentityInitials($identityConfirm->isIdentityInitials());
            $identity->setIdentitySurname($identityConfirm->isIdentitySurname());
            $identity->setIdentityImage($identityConfirm->isIdentityImage());
            $identity->setIdentityLocation($identityConfirm->isIdentityLocation());
            $identity->setIdentityEmploymentHistory($identityConfirm->isIdentityEmploymentHistory());
            $identity->setIdentityAcademicHistory($identityConfirm->isIdentityAcademicHistory());
            $identity->setIdentityCountry($identityConfirm->isIdentityCountry());
            $identity->setIdentityProfileImage($identityConfirm->isIdentityProfileImage());
            $identity->setIdentityIdNumber($identityConfirm->isIdentityIdNumber());
            $identity->setIdentityContactNumber($identityConfirm->isIdentityContactNumber());
            $identity->setIdentityEmailAddress($identityConfirm->isIdentityEmailAddress());
            $identity->setIdentityPhysicalAddress($identityConfirm->isIdentityPhysicalAddress());
            $identity->setIdentityTag($identityConfirm->isIdentityTag());
            $identity->setIdentityAlias($identityConfirm->isIdentityAlias());
            $identity->setIdentityLink($identityConfirm->isIdentityLink());
            $identity->setIdentityLocationHistory($identityConfirm->isIdentityLocationHistory());
            $identity->setIdentityHandle($identityConfirm->isIdentityHandle());
            $identity->setIdentityTitle($identityConfirm->isIdentityTitle());

            $this->entityManager->flush();
        } else {
            $this->save($identityConfirm);
        }
    }


    /**
     *
     */
    public
    function save($identityConfirm)
    {
        $this->entityManager->persist($identityConfirm);
        $this->entityManager->flush();
    }
}