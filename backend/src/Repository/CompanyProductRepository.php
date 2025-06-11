<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Accounts;
use App\Entity\AccountsTracker;
use App\Entity\Company;
use App\Entity\CompanyProduct;
use App\Service\AccountsService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class CompanyProductRepository
 *
 * @package App\Repository
 */
final class CompanyProductRepository
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
     * CompanyProductRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param TokenStorageInterface  $token
     * @param ParameterBagInterface  $params
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $token,
        ParameterBagInterface $params,
        AccountsService $accountsService
    )
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(CompanyProduct::class);
        $this->repositoryCompany = $entityManager->getRepository(Company::class);
        $this->repositoryAccountsTracker = $entityManager->getRepository(AccountsTracker::class);
        $this->repositoryAccounts = $entityManager->getRepository(Accounts::class);
        $this->userToken = $token->getToken()->getUser();
        $this->accountsService = $accountsService;
    }

    /**
     * @return CompanyProduct[]|array|object[]
     */
    public function all()
    {
        return $this->repository->findAll();
    }

    /**
     * @param CompanyProduct $companyProduct
     *
     * @return CompanyProduct
     */
    public function save(CompanyProduct $companyProduct)
    {
        if ($companyProduct->getId()) {
            $companyProduct->setUpdatedBy($this->userToken);
        } else {
            $companyProduct->setCreatedBy($this->userToken);
            $companyProduct->setUpdatedBy($this->userToken);
        }

        $this->entityManager->persist($companyProduct);
        $this->entityManager->flush();

        $this->addToBundleNewAccount($companyProduct->getCompany(), $companyProduct, 0, $companyProduct->getBundleAmount());

        return $companyProduct;
    }

    /**
     * @param Company $company
     *
     * @return mixed
     */
    public function getId(Company $company)
    {
        return $this->repository->findOneBy(['company' => $company->getId()]);
    }

    /**
     * @param Company $company
     *
     * @return array
     * @throws NonUniqueResultException
     */
    public function getById(Company $company)
    {
        $qb = $this->repository->createQueryBuilder('p')
            ->where('p.company = :id')
            ->setParameter('id', $company->getId())
            ->getQuery();

        $count = count($qb->execute());

        if ($count == 1) {
            $companyProductDetails = $this->repository->findOneBy(['company' => $company->getId()]);
            $response = [
                "id" => $companyProductDetails->getId(),
                "account_suspended" => $companyProductDetails->getSuspended(),
                "company" => $companyProductDetails->getCompany(),
                "product_type" => $companyProductDetails->getProductType(),
                "created_by" => $companyProductDetails->getCreatedBy(),
                "updated_by" => $companyProductDetails->getUpdatedBy(),
                "bundle_amount" => $companyProductDetails->getBundleAmount(),
                "normal_unit_price" => $companyProductDetails->getNormalUnitPrice(),
                "rushed_unit_price" => $companyProductDetails->getRushedUnitPrice(),
                "test_unit_price" => $companyProductDetails->getTestUnitPrice(),
                "additional_requested" => $companyProductDetails->getAdditionalRequested(),
                "monthly_recurring" => $companyProductDetails->getMonthlyRecurring(),
                "units_carry_over" => $companyProductDetails->isUnitsCarryOver(),
                "bundle_total" => $this->accountsService->getTotalUnits($companyProductDetails),
                "bundle_remaining" => $this->unitsUsed($companyProductDetails),
                "bundle_used" => $this->unitsUsed($companyProductDetails),
                "bundle_add" => $this->accountsService->getTotalUnits($companyProductDetails),
                "bundle_total_used" => $this->accountsService->getTotalUnitsUsed($companyProductDetails)
            ];

            return $response;
        } else {
            $companyProduct = new CompanyProduct();

            $companyProduct->setSuspended(false);
            $companyProduct->setCompany($company);
            $companyProduct->setBundleAmount('0');
            $companyProduct->setAdditionalRequested('0');
            $companyProduct->setMonthlyRecurring(true);
            $companyProduct->setUnitsCarryOver(false);
            $companyProduct->setNormalUnitPrice('1');
            $companyProduct->setRushedUnitPrice('2');
            $companyProduct->setTestUnitPrice('0');
            $companyProduct->setProductType('pre_paid');
            $companyProduct->setCreatedBy($this->userToken);
            $companyProduct->setUpdatedBy($this->userToken);

            $this->entityManager->persist($companyProduct);
            $this->entityManager->flush();

            $companyProduct->getId();

            $this->addToBundle($company, $companyProduct, 0, 0);

            return $companyProduct;
        }
    }

    /**
     * @param Company $company
     *
     * @return bool
     */
    public function checkIfBundleActive(Company $company)
    {
        $qb = $this->repository->createQueryBuilder('p')
            ->where('p.company = :id')
            ->andWhere('p.productType = :product')
            ->setParameter('product', 'pre_paid')
            ->setParameter('id', $company->getId())
            ->getQuery();

        $count = count($qb->execute());

        if ($count == 1) {
            return true;
        } else {
            return false;
        }
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
     * @param Company        $company
     * @param CompanyProduct $companyProduct
     * @param                $units
     */
    public function addToBundleNewAccount(Company $company, CompanyProduct $companyProduct, $units, $monthlyUnits)
    {
        $accountsTracker = new AccountsTracker();
        $accountsTracker->setCompanyProduct($companyProduct);
        $accountsTracker->setAddUnit($units);
        $accountsTracker->setMonthlyUnits($monthlyUnits);
        $accountsTracker->setCompany($company);
        $this->entityManager->persist($accountsTracker);
        $this->entityManager->flush();
    }

    /**
     * @param CompanyProduct $companyProduct
     * @param                $units
     *
     * @return AccountsTracker
     */
    public function addToBundle(CompanyProduct $companyProduct, $units)
    {
        $addBundle = new Accounts();
        $addBundle->setMonthlyUnits($companyProduct->getBundleAmount());
        $addBundle->setAddUnit($units);
        $addBundle->setUnitUsed(0);
        $addBundle->setRejectUnit(0);
        $addBundle->setCompany($companyProduct->getCompany());
        $addBundle->setCompanyProduct($companyProduct);
        $addBundle->setTotalUnitUsed($this->accountsService->getTotalUnitsUsed($companyProduct));
        $addBundle->setTotalUnitAdd($this->accountsService->getTotalUnits($companyProduct) + $units);
        $addBundle->setCreatedBy($this->userToken);
        $this->entityManager->persist($addBundle);
        $this->entityManager->flush();

        return $addBundle;
    }

    /**
     * @param $companyProduct
     *
     * @return int
     */
    public function unitsAdded($companyProduct)
    {
        $qb = $this->repositoryAccountsTracker->createQueryBuilder('p')
            ->where('p.companyProduct = :companyProduct_id')
            ->select('sum(p.addUnit)')
            ->setParameter('companyProduct_id', $companyProduct->getId())
            ->getQuery()
            ->getSingleScalarResult();

        if ($qb) {
            return $qb;
        } else {
            return 0;
        }
    }

    /**
     * @param $companyProduct
     *
     * @return int
     */
    public function unitsUsed($companyProduct)
    {
        if ($companyProduct) {
            $qb = $this->repositoryAccounts->createQueryBuilder('p')
                ->andWhere('p.companyProduct = :companyProduct_id')
                ->setParameter('companyProduct_id', $companyProduct)
                ->orderBy('p.createdAt', 'DESC')
                ->setMaxResults(1)
                ->getQuery();

            $newAccountCheck = $this->accountsService->newAccountCheck($companyProduct);

            if ($newAccountCheck) {
                if (count($qb->execute()) >= 1) {
                    return ($companyProduct->getBundleAmount() + $qb->execute()[0]->getTotalUnitAdd()) - $qb->execute()[0]->getTotalUnitUsed();
                } else {
                    return 0;
                }
            } else {
                return $companyProduct->getBundleAmount();
            }
        } else {
            return 0;
        }
    }

    /**
     * @param $companyProduct
     * @param $bundle
     *
     * @return int
     */
    public function totalBundleAmount($companyProduct, $bundle)
    {
        $amount = $this->unitsAdded($companyProduct) + $bundle;

        return $amount;
    }

    /**
     * @param $company
     */
    public function createCompanyProduct($company)
    {
        $companyProduct = new CompanyProduct();

        $companyProduct->setSuspended(false);
        $companyProduct->setCompany($company);
        $companyProduct->setBundleAmount('0');
        $companyProduct->setAdditionalRequested('0');
        $companyProduct->setMonthlyRecurring(true);
        $companyProduct->setUnitsCarryOver(false);
        $companyProduct->setNormalUnitPrice('1');
        $companyProduct->setRushedUnitPrice('2');
        $companyProduct->setTestUnitPrice('0');
        $companyProduct->setProductType('pre_paid');
        $companyProduct->setCreatedBy($this->userToken);
        $companyProduct->setUpdatedBy($this->userToken);

        $this->entityManager->persist($companyProduct);
        $this->entityManager->flush();
    }

    /**
     * @param $companyProduct
     *
     * @return string
     */
    public function accountStatus($companyProduct)
    {

        if ($companyProduct) {
            if ($this->unitsUsed($companyProduct) >= 1) {
                return 'open';
            } else {
                return 'suspended';
            }
        } else {
            return 'suspended';
        }
    }
}
