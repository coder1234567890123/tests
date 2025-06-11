<?php

namespace App\Service;

use App\Controller\ReportController;
use App\Entity\Accounts;
use App\Entity\CompanyProduct;
use App\Repository\AccountsRepository;
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
class ApiAccountsService
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
        ParameterBagInterface $params

    )
    {
        $this->entityManager = $entityManager;
        $this->params = $params;
        $this->repository = $entityManager->getRepository(Accounts::class);
    }

    /**
     * @param $listReturn
     *
     * @return array
     */
    public function getById($listReturn)
    {
        $response = [];
        if ($listReturn) {
            foreach ($listReturn as $getData) {
                $response[] = $this->listReturn($getData);
            }

            return $response;
        } else {
            return [];
        }
    }

    /**
     * @param $getData
     *
     * @return array
     */
    public function listReturn($getData)
    {
        $response = [];

        if ($getData) {
            $response = [
                "id" => $getData->getId(),
                "add_unit" => $getData->getAddUnit(),
                "unit_used" => $getData->getUnitUsed(),
                "monthly_reset" => $getData->isMonthlyReset(),
                "monthly_recurring" => $getData->isMonthlyReset(),
                "monthly_reset_amounts" => $getData->isMonthlyResetAmounts(),
                "reset_monthly_amounts" => $getData->isResetMonthlyAmounts(),
                "subject" => [
                    "first_name" => $this->firstNameCheck($getData->getSubject()),
                    "last_name" => $this->lastNameCheck($getData->getSubject()),
                ],
                "company" => [
                    "name" => $getData->getCompany()->getName()
                ],
                "total_units_used" => $getData->getTotalUnitUsed(),
                "monthly_units" => $getData->getMonthlyUnits(),
                "request_type" => $getData->getRequestType(),
                "created_at" => $getData->getCreatedAt()
            ];
            return $response;
        } else {
            return [];
        }
    }

    /**
     * @param $getData
     *
     * @return string
     */
    private function lastNameCheck($getData)
    {
        if ($getData) {
            return $getData->getLastName();
        } else {
            return '';
        }
    }

    /**
     * @param $getData
     *
     * @return string
     */
    private function firstNameCheck($getData)
    {
        if ($getData) {
            return $getData->getFirstName();
        } else {
            return '';
        }
    }

}