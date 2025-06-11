<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Accounts;
use App\Entity\AccountsTracker;
use App\Entity\Company;
use App\Entity\CompanyProduct;
use App\Service\AccountsService;
use App\Controller\CompanyProductRepository;
use App\Service\ApiAccountsService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class AccountsRepository
 *
 * @package App\Repository
 */
final class AccountsRepository
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
     * AccountsRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param TokenStorageInterface  $token
     * @param ParameterBagInterface  $params
     * @param AccountsService        $accountsService
     * @param ApiAccountsService     $apiAccountsService
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $token,
        ParameterBagInterface $params,
        AccountsService $accountsService,
        ApiAccountsService $apiAccountsService

    )
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Accounts::class);
        $this->userToken = $token->getToken()->getUser();
        $this->accountsService = $accountsService;
        $this->apiAccountsService = $apiAccountsService;
    }

    /**
     * @return Accounts[]|array|object[]
     */
    public function all()
    {
        $returnlist = $this->repository->findAll();

        return $returnlist;
    }

    /**
     * @param Accounts $account
     *
     * @return Accounts
     */
    public function addUnit(Accounts $account)
    {
        $account->setAddUnit(1);
        $account->setRejectUnit(0);

        $this->save($account);

        return $account;
    }

    /**
     * @param Accounts $accounts
     */
    public function save(Accounts $accounts)
    {
        $this->entityManager->persist($accounts);
        $this->entityManager->flush();
    }

    /**
     * @param Accounts $account
     *
     * @return Accounts
     */
    public function rejectUnit(Accounts $account)
    {
        $account->setAddUnit(0);
        $account->setRejectUnit(1);

        $this->save($account);

        return $account;
    }

    /**
     * @param CompanyProduct $companyProduct
     *
     * @return array
     * @throws NonUniqueResultException
     */
    public function getUnits(CompanyProduct $companyProduct)
    {
        $total = $this->unitsTotal($companyProduct) - $this->getUnitsLeft($companyProduct);
        $response = ["units_left" => $total];
        return $response;
    }

    /**
     * @param Company     $company
     * @param int         $offset
     * @param int         $limit
     * @param string      $sort
     * @param bool        $descending
     *
     *
     * @param string|null $date_from
     * @param string|null $date_to
     *
     * @return Accounts[]|array|object[]
     */
    public function getCompanyUsage(
        Company $company,
        int $offset,
        int $limit,
        string $sort,
        bool $descending,
        string $date_from = null,
        string $date_to = null
    )
    {

        switch ($sort) {
            default:
                $sort = 'createdAt';
                break;
        }

        $currentMonth = date('Y-m-01');

        $queryBuilder = $this->repository->createQueryBuilder('r');

        $queryBuilder
            ->andWhere('r.company = :company_id')
            ->setParameter('company_id', $company);

        if($date_from){
            $queryBuilder->andWhere('r.createdAt >= :date1')
                ->setParameter('date1', $currentMonth);
        }

        if ($date_from && $date_to) {
            $queryBuilder->andWhere('r.createdAt >= :date1')
                ->andWhere('r.createdAt <= :date2')
                ->setParameter('date1', $date_from)
                ->setParameter('date2', $date_to);
        } elseif ($date_from) {
            $queryBuilder->andWhere('r.createdAt >= :date1')
                ->setParameter('date1', $date_from);
        }

        $queryBuilder->orderBy("r.$sort", $descending === true ? 'DESC' : 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $this->apiAccountsService->getById($queryBuilder->getQuery()->execute());
    }

    /**
     * @param CompanyProduct $companyProduct
     *
     * @return mixed
     */
    private function unitsTotal(CompanyProduct $companyProduct)
    {
        return $companyProduct->getBundleAmount() + $companyProduct->getAdditionalRequested();
    }

    /**
     * @param CompanyProduct $companyProduct
     *
     * @return mixed
     * @throws NonUniqueResultException
     */
    private function getUnitsLeft(CompanyProduct $companyProduct)
    {
        $totalUnitAdd = $this->repository->createQueryBuilder('a')
            ->where('a.addUnit = 1')
            ->andWhere('a.companyProduct = :company_id')
            ->select('count(a.id)')
            ->setParameter('company_id', $companyProduct->getId())
            ->getQuery()
            ->getSingleScalarResult();

        $totalUnitReject = $this->repository->createQueryBuilder('a')
            ->where('a.rejectUnit = 1')
            ->andWhere('a.companyProduct = :company_id')
            ->select('count(a.id)')
            ->setParameter('company_id', $companyProduct->getId())
            ->getQuery()
            ->getSingleScalarResult();

        return $totalUnitAdd - $totalUnitReject;
    }

    /**
     * @param $companyProduct
     *
     * @return Accounts
     */
    public function monthlyReset($companyProduct)
    {
        $addBundle = new Accounts();
        $addBundle->setMonthlyUnits($companyProduct->getBundleAmount());
        $addBundle->setAddUnit(0);
        $addBundle->setUnitUsed(0);
        $addBundle->setRejectUnit(0);
        $addBundle->setCompany($companyProduct->getCompany());
        $addBundle->setCompanyProduct($companyProduct);
        $addBundle->setTotalUnitUsed(0);

        if ($companyProduct->isUnitsCarryOver() === false) {
            $addBundle->setTotalUnitAdd(0);
        } else {
            $addBundle->setTotalUnitAdd($this->accountsService->bundleCarryOver($companyProduct));
        }
        $addBundle->setCreatedBy($this->userToken);
        $addBundle->setResetMonthlyAmounts(true);
        $this->entityManager->persist($addBundle);
        $this->entityManager->flush();

        return $addBundle;
    }
}
