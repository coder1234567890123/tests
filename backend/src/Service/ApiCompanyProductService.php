<?php

namespace App\Service;

use App\Controller\ReportController;
use App\Entity\CompanyProduct;
use App\Repository\CompanyProductRepository;
use App\Service\ApiCompanyService;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Exception;
use phpDocumentor\Reflection\Types\Context;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class ApiTeamsService
 *
 * @package App\Service
 */
class ApiCompanyProductService
{
    /**
     * @var ObjectRepository
     */
    private $repository;

    /**
     * ProfileRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ParameterBagInterface  $params
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ParameterBagInterface $params,
        CompanyProductRepository $companyProductRepository,
        AccountsService $accountsService
    )
    {
        $this->entityManager = $entityManager;
        $this->params = $params;
        $this->repository = $entityManager->getRepository(CompanyProduct::class);
        $this->companyProductRepository = $companyProductRepository;
        $this->accountsService = $accountsService;
    }


    /**
     * @param $getData
     *
     * @return string[]
     */
    public function basicAccountDetails($type, $getData)
    {
        if ($type === 'subject') {
            $companyId = $getData[0]->getCompany()->getId();
        } else {
            $companyId = $getData->getId();
        }

        $emptyResponse = [
            'product_type' => 'pre_paid',
            'bundle_remaining' => 0,
            'account_status' => 'suspended',
            'normal_report_allowed' => false,
            'rushed_report_allowed' => false,
            'test_report_allowed' => false
        ];

        $suspendedResponse = [
            'product_type' => 'suspended',
            'bundle_remaining' => 0,
            'account_status' => 'suspended',
            'normal_report_allowed' => false,
            'rushed_report_allowed' => false,
            'test_report_allowed' => false,

        ];

        if ($getData) {
            $companyProductDetails = $this->repository->findOneBy(['company' => $companyId]);

            if ($companyProductDetails) {
                $unitUsed = $this->companyProductRepository->unitsUsed($companyProductDetails);
                $normalReportPrice = $companyProductDetails->getNormalUnitPrice();
                $rushedReportPrice = $companyProductDetails->getRushedUnitPrice();
                $testReportPrice = $companyProductDetails->getTestUnitPrice();

                switch ($companyProductDetails->getProductType()) {
                    case 'pre_paid':
                        return [
                            'product_type' => $companyProductDetails->getProductType(),
                            'bundle_remaining' => $unitUsed,
                            'account_status' => $this->companyProductRepository->accountStatus($companyProductDetails),
                            'normal_report_allowed' => $this->accountsService->rushedReportAllowed($unitUsed, $normalReportPrice),
                            'rushed_report_allowed' => $this->accountsService->rushedReportAllowed($unitUsed, $rushedReportPrice),
                            'test_report_allowed' => $this->accountsService->testReportAllowed($unitUsed, $testReportPrice)
                        ];
                        break;
                    case 'suspended':
                        return $suspendedResponse;
                        break;
                    case 'retainer':
                        return [
                            'product_type' => $companyProductDetails->getProductType(),
                            'bundle_remaining' => 'N/A',
                            'account_status' => 'open',
                            'normal_report_allowed' => true,
                            'rushed_report_allowed' => true,
                            'test_report_allowed' => true
                        ];
                        break;
                    default:
                        return $emptyResponse;
                }
            } else {
                return $emptyResponse;
            }
        } else {
            return $emptyResponse;
        }
    }

}