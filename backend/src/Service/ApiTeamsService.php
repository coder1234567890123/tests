<?php

namespace App\Service;

use App\Entity\Answer;
use App\Entity\Comment;
use App\Entity\Company;
use App\Entity\DefaultBranding;
use App\Entity\IdentityConfirm;
use App\Entity\Proof;
use App\Entity\ProofStorage;
use App\Entity\Question;
use App\Entity\Profile;
use App\Repository\DefaultBrandingRepository;
use App\Service\PdfProofService;
use App\Service\ApiReturnService;
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
class ApiTeamsService
{

    /**
     * ProfileRepository constructor.
     *
     * @param EntityManagerInterface       $entityManager
     * @param \App\Service\PdfProofService $pdfProofService
     * @param ParameterBagInterface        $params
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        PdfProofService $pdfProofService,
        ParameterBagInterface $params
    )
    {
        $this->entityManager = $entityManager;
        $this->pdfProofService = $pdfProofService;
        $this->params = $params;
        $this->repositoryCompany = $entityManager->getRepository(Company::class);
    }

    /**
     * @param $team
     *
     * @return array
     */
    public function companyIndex($team)
    {
        $qb = $this->repositoryCompany->createQueryBuilder('p')
            ->andWhere('p.team = :id')
            ->setParameter('id', $team->getId())
            ->getQuery();

        $response = [];

        if ($qb->execute()) {
            foreach ($qb->execute() as $getData) {
                $response[] = [
                    'id' => $getData->getId(),
                    'name' => $getData->getName()
                ];
            }
            return $response;
        } else {
            return [''];
        }
    }

    /**
     * @param $teams
     *
     * @return array
     */
    public function teamsIndex($teams)
    {
        $response = [];

        if ($teams) {
            foreach ($teams as $getData) {
                $response[] = [
                    'id' => $getData->getId(),
                    'team_name' => $getData->getTeamLeader()->getFullName(),
                    'team_lead_email' => $getData->getTeamLeader()->getEmail()
                ];
            }
            return $response;
        } else {
            return [];
        }
    }

    /**
     * @param $teams
     *
     * @return array
     */
    public function teamMembers($teams)
    {
        if ($teams) {
            return [
                'id' => $teams->getId(),
                'users' => $this->usersFilter($teams->getUsers()),
                'companies' => $this->companyFilter($teams->getCompanies())
            ];
        } else {
            return [];
        }
    }

    /**
     * @param $teamUsers
     *
     * @return array
     */
    private function companyFilter($teamCompany)
    {
        $response = [];
        if ($teamCompany) {
            foreach ($teamCompany as $company) {
                $response[] = [
                    'id' => $company->getId(),
                    'name' => $company->getName()
                ];
            }

            return $response;
        } else {
            return [];
        }
    }


    /**
     * @param $teamUsers
     *
     * @return array
     */
    private function usersFilter($teamUsers)
    {
        $response = [];
        if ($teamUsers) {
            foreach ($teamUsers as $user) {
                $response[] = [
                    'id' => $user->getId(),
                    'first_name' => $user->getFirstName(),
                    'last_name' => $user->getLastName()
                ];
            }

            return $response;
        } else {
            return [];
        }
    }

}