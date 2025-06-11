<?php

namespace App\Service;

use App\Entity\Accounts;
use App\Entity\AccountsTracker;
use App\Entity\Company;
use App\Entity\CompanyProduct;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class ApiErrorsService
 *
 * @package App\Service
 */
class ApiErrorsService
{
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    /**
     * @var mixed
     */
    private $debug;


    /**
     * ApiErrorsService constructor.
     *
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(
        ParameterBagInterface $parameterBag
    ) {
        $this->parameterBag = $parameterBag;
        $this->debug = $this->parameterBag->get('API_ERROR_DEV');
    }

    /**
     * @return JsonResponse
     */
    public function errorFourHundred($e)
    {
        if ($this->debug === 'true') {
            return new JsonResponse([
                'message' => $e->getMessage()

            ], 401);
        } else {
            return new JsonResponse([
                "error" => [
                    "code" => "401",
                    "status" => "Unauthorized",
                    "message" => "invalid auth (bad username/password) bad auth" //$e
                ]

            ], 401);
        }
    }


    /**
     * @return JsonResponse
     */
    public function errorFiveHundred($e)
    {
        if ($this->debug === 'true') {
            return new JsonResponse([
                'message' => $e->getMessage()

            ], 500);
        }
        // else {
        //     return new JsonResponse([
        //         "error" => [
        //             "code" => "500",
        //             "status" => "Internal Server Error",
        //             // "message" =>  "Server Error" // $e->getMessage() // for troubleshooting when API_ERROR_DEV="false" 
        //             "message" =>  $e->getMessage() // for troubleshooting when API_ERROR_DEV="false"
        //         ]

        //     ], 500);
        // }
        else {
            return new JsonResponse([
                "error" => [
                    "code" => "500",
                    "status" => "Internal Server Error",
                    "message" => $e->getMessage(), // for troubleshooting when API_ERROR_DEV="false"
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }
}
