<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Country;
use App\Entity\Province;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Class CountryRepository
 *
 * @package App\Repository
 */
final class ProvinceRepository
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
     * CountryRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository    = $entityManager->getRepository(Province::class);
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
     * @return Province[]|array|object[]
     */
    public function all()
    {
        return $this->repository->findAll();
    }

    /**
     * @param Province $province
     */
    public function save(Province $province)
    {
        $this->entityManager->persist($province);
        $this->entityManager->flush();
    }
}