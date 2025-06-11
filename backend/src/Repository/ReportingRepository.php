<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Company;
use App\Entity\Report;
use App\Entity\Subject;
use App\Entity\User;
use App\Entity\UserTracking;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Exception;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class ReportingRepository
 *
 * @package App\Repository
 */
final class ReportingRepository
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
     * ReportingRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param TokenStorageInterface  $token
     * @param ParameterBagInterface  $params
     */
    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $token, ParameterBagInterface $params)
    {
        $this->entityManager = $entityManager;
        $this->repositoryUserTracking = $entityManager->getRepository(UserTracking::class);
        $this->repositoryReports = $entityManager->getRepository(Report::class);
        $this->userToken = $token->getToken()->getUser();
    }

    /**
     * @param User $user
     *
     * @return UserTracking[]|array|object[]
     * @throws Exception
     */
    public function userTrackerMonthly(User $user)
    {
        $dateStart = new DateTime('midnight first day of this month');

        $qb = $this->repositoryUserTracking->createQueryBuilder('a')
            ->where('a.user = :user_id')
            ->andWhere('a.createdAt >= :date')
            ->setParameter('user_id', $user->getId())
            ->setParameter('date', $dateStart)
            ->getQuery();

        return $qb->execute();
    }

    /**
     * @param Company $company
     *
     * @return mixed
     * @throws Exception
     */
    public function companyTrackerMonthly(Company $company)
    {
        $dateStart = new DateTime('midnight first day of this month');

        $qb = $this->repositoryUserTracking->createQueryBuilder('a')
            ->where('a.company = :company_id')
            ->andWhere('a.createdAt >= :date')
            ->setParameter('company_id', $company->getId())
            ->setParameter('date', $dateStart)
            ->getQuery();

        return $qb->execute();
    }

    /**
     * @param Company $company
     * @param         $data
     *
     * @return mixed
     * @throws Exception
     */
    public function companyTrackerDateRange(Company $company, $data)
    {
        $db = $this->repositoryUserTracking->createQueryBuilder('a')
            ->where('a.company = :company_id')
            ->andWhere('a.createdAt >= :date1')
            ->andWhere('a.createdAt <= :date2')
            ->setParameter('company_id', $company->getId())
            ->setParameter('date1', $data['date_start'])
            ->setParameter('date2', $data['date_end']);

        $resultsReturned = $db->getQuery()->execute();

        $resultsCount =
            $db->select('count(a.id)')
                ->getQuery()
                ->getSingleScalarResult();

        $results = [
            "result_count" => $resultsCount,
            "results" => $resultsReturned
        ];
        return $results;
    }

    /**
     * @param User $user
     * @param      $data
     *
     * @return mixed
     */
    public function userTrackerDateRange(User $user, $data)
    {
        $db = $this->repositoryUserTracking->createQueryBuilder('a')
            ->where('a.user = :user_id')
            ->andWhere('a.createdAt >= :date1')
            ->andWhere('a.createdAt <= :date2')
            ->setParameter('user_id', $user->getId())
            ->setParameter('date1', $data['date_start'])
            ->setParameter('date2', $data['date_end']);

        $resultsReturned = $db->getQuery()->execute();

        $resultsCount =
            $db->select('count(a.id)')
                ->getQuery()
                ->getSingleScalarResult();

        $results = [
            "result_count" => $resultsCount,
            "results" => $resultsReturned
        ];
        return $results;
    }

    /**
     * @param Company $company
     *
     * @return mixed
     */
    public function pdfCompanyUsers(Company $company)
    {
        $db = $this->repositoryReports->createQueryBuilder('a')
            ->where('a.company = :company_id')
            ->andWhere('a.status = :status')
            ->setParameter('status', 'completed')
            ->setParameter('company_id', $company);

        return $db->getQuery()->execute();
    }

    /**
     * @param Company $company
     *
     * @return mixed
     */
    public function pdfCompanySubject(Company $company)
    {
        $db = $this->repositoryReports->createQueryBuilder('a')
            ->where('a.company = :company_id')
            ->andWhere('a.status = :status')
            ->setParameter('status', 'completed')
            ->setParameter('company_id', $company)
            ->getQuery()->execute();

        foreach ($db as $subject) {
            $getSubject[] = [

                "name" => trim($subject->getSubject()->getfirstName()) . " " . trim($subject->getSubject()->getlastName()),
                "social_score" => $this->socialScore($subject->getReportScores()),
                "risk_score" => $this->riskScore($subject->getReportScores())
            ];
        }

        if (!isset($getSubject)) {
            return [];
        }
        return $getSubject;
    }

    /**
     * @param $score
     *
     * @return mixed
     */
    private function socialScore($score)
    {
        return $score['totalScore'];
    }

    /**
     * @param $score
     *
     * @return mixed
     */
    private function riskScore($score)
    {
        return $score['totalScore'];
    }
}
