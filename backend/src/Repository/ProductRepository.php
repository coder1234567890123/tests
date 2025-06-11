<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class ProductRepository
 *
 * @package App\Repository
 */
final class ProductRepository
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
     * @var Filesystem
     */
    private $filesystem;

    /**
     * SubjectRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param TokenStorageInterface  $token
     * @param ParameterBagInterface  $params
     */
    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $token, ParameterBagInterface $params)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Product::class);
        $this->userToken = $token->getToken()->getUser();
    }

    /**
     * @return Product[]|array|object[]
     */
    public function all()
    {
        return $this->repository->findAll();
    }

    /**
     * @param Product $product
     */
    public function disable(Product $product)
    {
        $product->setEnable(false);

        $this->save($product);
    }

    /**
     * @param Product $product
     *
     * @return Product
     */
    public function save(Product $product)
    {
        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return $product;
    }

    /**
     * @param Product $product
     */
    public function enable(Product $product)
    {
        $product->setEnable(true);

        $this->save($product);
    }
}
