<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Profile;
use App\Entity\Subject;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class ProfileRepository
 *
 * @package App\Repository
 */
final class ProfileRepository
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
     * ProfileRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param TokenStorageInterface  $token
     */
    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $token)
    {
        $this->entityManager = $entityManager;
        $this->repository    = $entityManager->getRepository(Profile::class);
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
     * @param Subject $subject
     * @param string  $url
     *
     * @return object|null
     */
    public function byLink(Subject $subject, string $url)
    {
        return $this->repository->findOneBy([
            'subject' => $subject,
            'link'    => $url
        ]);
    }

    /**
     * @param Profile $profile
     *
     * @return void
     */
    public function delete(Profile $profile)
    {
        $this->entityManager->remove($profile);
        $this->entityManager->flush();
    }

    /**
     * @param Subject $subject
     */
    public function deleteAll(Subject $subject)
    {
        foreach ($subject->getProfiles() as $profile) {
            $this->entityManager->remove($profile);
        }

        $this->entityManager->flush();
    }

    /**
     * @param Profile $profile
     */
    public function validate(Profile $profile)
    {
        $profile->setValid(true);

        $this->save($profile);
    }

    /**
     * @param Profile $profile
     */
    public function invalidate(Profile $profile)
    {
        $profile->setValid(false);

        $this->save($profile);
    }

    /**
     * @param Profile $profile
     */
    public function save(Profile $profile)
    {
        $this->entityManager->persist($profile);
        $this->entityManager->flush();
    }
}
