<?php declare(strict_types=1);

namespace App\Repository;

use App\Contracts\GlobalWeightsRepositoryInterface;
use App\Entity\GlobalWeights;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class GlobalWeightsRepository
 *
 * @package App\Repository
 */
final class GlobalWeightsRepository implements GlobalWeightsRepositoryInterface
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
     * GlobalWeightsRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param TokenStorageInterface  $token
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(GlobalWeights::class);
    }

    /**
     * @return GlobalWeights[]|array|object[]
     */
    public function all()
    {
        return $this->repository->findBy([], ['ordering' => 'ASC']);
    }

    /**
     * @param string  $platform
     *
     * @return GlobalWeights|object
     */
    public function getByPlatform(string $platform)
    {
        return $this->repository->findOneBy([
            'socialPlatform'    => $platform
        ]);
    }

    /**
     * @param GlobalWeights $globalWeights
     */
    public function save(GlobalWeights $globalWeights)
    {
        $this->entityManager->persist($globalWeights);
        $this->entityManager->flush();
    }
}