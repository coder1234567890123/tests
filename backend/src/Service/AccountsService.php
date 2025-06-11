<?php

namespace App\Service;

use App\Entity\Accounts;
use App\Entity\AccountsTracker;
use App\Entity\Company;
use App\Entity\CompanyProduct;
use App\Repository\CompanyProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class AccountsService
 *
 * @package App\Service
 */
class AccountsService
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
     * @var ApiCompanyProductService
     */
    private $apiCompanyProductService;

    /**
     * AccountsService constructor.
     *
     * @param EntityManagerInterface   $entityManager
     * @param TokenStorageInterface    $token
     * @param CompanyProductRepository $companyProductRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $token
    )
    {
        $this->entityManager = $entityManager;
        $this->repositoryCompanyProduct = $entityManager->getRepository(CompanyProduct::class);
        $this->repositoryCompany = $entityManager->getRepository(Company::class);
        $this->repositoryAccountsTracker = $entityManager->getRepository(AccountsTracker::class);
        $this->repositoryAccounts = $entityManager->getRepository(Accounts::class);
        $this->userToken = $token->getToken()->getUser();
    }

    /**
     * @param $subject
     * @param $report
     */
    public function addUnit($subject, $report)
    {
        $companyProductId = $this->getCompanyId($subject->getCompany());
        $unitAmount = $this->getUnitPrice($subject->getCompany(), $report->getRequestType());

        $addBundle = new Accounts();
        $addBundle->setMonthlyUnits($companyProductId->getBundleAmount());
        $addBundle->setAddUnit(0);
        $addBundle->setUnitUsed($unitAmount);
        $addBundle->setRejectUnit(0);
        $addBundle->setCompany($subject->getCompany());
        $addBundle->setSubject($subject);
        $addBundle->setCompanyProduct($companyProductId);
        $addBundle->setRequestType($report->getRequestType());
        $addBundle->setTotalUnitAdd($this->getTotalUnits($companyProductId));
        $addBundle->setTotalUnitUsed($this->addUnits($companyProductId) + $unitAmount);
        $addBundle->setCreatedBy($this->userToken);
        $this->save($addBundle);
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
     * @param Company $company
     *
     * @return mixed
     */
    public function getCompanyId(Company $company)
    {
        return $this->repositoryCompanyProduct->findOneBy(['company' => $company->getId()]);
    }

    /**
     * @param $company
     * @param $unitType
     *
     * @return mixed
     */
    public function getUnitPrice($company, $unitType)
    {
        $qb = $this->repositoryCompanyProduct->createQueryBuilder('p')
            ->where('p.company = :id')
            ->setParameter('id', $company->getId())
            ->getQuery();

        $product = $qb->execute();

        switch ($unitType) {
            case 'normal':
                return $product[0]->getNormalUnitPrice();
                break;

            case 'rush':
                return $product[0]->getRushedUnitPrice();
                break;

            case 'test':
                return $product[0]->getTestUnitPrice();
                break;
        }
    }

    /**
     * @param Company $company
     *
     * @return mixed
     */
    public function getProductType(Company $company)
    {
        $qb = $this->repositoryCompanyProduct->createQueryBuilder('p')
            ->where('p.company = :id')
            ->setParameter('id', $company->getId())
            ->getQuery();

        $product = $qb->execute();

        return $product[0]->getProductType();
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
     * @param $companyProduct
     *
     * @return mixed
     */
    public function getTotalUnits($companyProduct)
    {
        $qb = $this->repositoryAccounts->createQueryBuilder('p')
            ->andWhere('p.companyProduct = :companyProduct_id')
            ->setParameter('companyProduct_id', $companyProduct)
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery();

        if (count($qb->execute()) >= 1) {
            return $qb->execute()[0]->getTotalUnitAdd();
        } else {
            return 0;
        }
    }

    /**
     * @param $companyProduct
     *
     * @return int
     */
    public function addUnits($companyProduct)
    {
        $qb = $this->repositoryAccounts->createQueryBuilder('p')
            ->andWhere('p.companyProduct = :companyProduct_id')
            ->setParameter('companyProduct_id', $companyProduct)
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery();

        if (count($qb->execute()) >= 1) {
            return $qb->execute()[0]->getTotalUnitUsed();
        } else {
            return 0;
        }
    }

    /**
     * @param $companyProduct
     *
     * @return mixed
     */
    public function getTotalUnitsUsed($companyProduct)
    {
        $qb = $this->repositoryAccounts->createQueryBuilder('p')
            ->andWhere('p.companyProduct = :companyProduct_id')
            ->setParameter('companyProduct_id', $companyProduct)
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery();

        if (count($qb->execute()) >= 1) {
            if ($qb->execute()[0]->getTotalUnitUsed() == null) {
                return 0;
            } else {
                return $qb->execute()[0]->getTotalUnitUsed();
            }
        } else {
            return 0;
        }
    }

    /**
     * @param $companyProduct
     *
     * @return mixed
     */
    public function bundleCarryOver($companyProduct)
    {
        if ($this->getTotalUnitsUsed($companyProduct) > $companyProduct->getBundleAmount()) {
            $sum1 = $this->getTotalUnitsUsed($companyProduct) - $companyProduct->getBundleAmount();
            $totalLeft = $this->getTotalUnits($companyProduct) - $sum1;

            return $totalLeft;
        } else {
            return $this->getTotalUnits($companyProduct);
        }
    }


    /**
     * @param $companyProduct
     *
     * @return bool
     */
    public function newAccountCheck($companyProduct)
    {
        $qb = $this->repositoryAccounts->createQueryBuilder('p')
            ->andWhere('p.companyProduct = :id')
            ->setParameter('id', $companyProduct)
            ->getQuery();

        if ($qb->execute()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $unitUsed
     *
     * @return bool
     */
    public function normalReportAllowed($unitUsed, $rushedReportPrice)
    {
        if ($unitUsed && $rushedReportPrice) {
            if ($unitUsed >= $rushedReportPrice) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param $unitUsed
     *
     * @return bool
     */
    public function rushedReportAllowed($unitUsed, $rushedReportPrice)
    {
        if ($unitUsed && $rushedReportPrice) {
            if ($unitUsed >= $rushedReportPrice) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param $unitUsed
     *
     * @return bool
     */
    public function testReportAllowed($unitUsed, $testReportPrice)
    {
        if ($unitUsed && $testReportPrice >= 0) {
            if ($testReportPrice === '0') {
                return true;
            } else {
                if ($unitUsed >= $testReportPrice) {
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }
}