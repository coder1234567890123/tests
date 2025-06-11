<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Role;
use App\Entity\RoleGroup;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class RoleGroupRepository
 *
 * @package App\Repository
 */
final class RoleGroupRepository
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
     * RoleGroupRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository    = $entityManager->getRepository(RoleGroup::class);

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
     * @return RoleGroup[]|array|object[]
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
     * @param RoleGroup $roleGroup
     */
    public function save(RoleGroup $roleGroup)
    {
        $this->entityManager->persist($roleGroup);
        $this->entityManager->flush();
    }

    /**
     * @param string $name
     *
     * @return RoleGroup|null
     */
    public function byName(string $name)
    {
        return $this->repository->findOneBy([
            'name' => $name
        ]);
    }
}