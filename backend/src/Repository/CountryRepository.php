<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Country;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Class CountryRepository
 *
 * @package App\Repository
 */
final class CountryRepository
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
        $this->repository    = $entityManager->getRepository(Country::class);
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
     * @param string $name
     *
     * @return object|null
     */
    public function byName(string $name)
    {
        return $this->repository->findOneBy([
            'name' => $name
        ]);
    }

    /**
     * @return Country[]|array|object[]
     */
    public function all()
    {
        return $this->repository->findAll();
    }

    /**
     * @param Country $country
     */
    public function save(Country $country)
    {
        $this->entityManager->persist($country);
        $this->entityManager->flush();
    }
}