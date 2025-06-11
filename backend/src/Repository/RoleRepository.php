<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Role;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class RoleRepository
 *
 * @package App\Repository
 */
final class RoleRepository
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
     * RoleRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository    = $entityManager->getRepository(Role::class);

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
     * @return Role[]|array|object[]
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
     * @param string $name
     *
     * @return Role|null
     */
    public function byName(string $name)
    {
        return $this->repository->findOneBy([
            'name' => $name
        ]);
    }

    /**
     * @param Role $role
     */
    public function save(Role $role)
    {
        $this->entityManager->persist($role);
        $this->entityManager->flush();
    }
}