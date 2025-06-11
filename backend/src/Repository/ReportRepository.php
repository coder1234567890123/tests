<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Employment;
use App\Entity\Report;
use App\Entity\User;
use App\Entity\Subject;
use App\Service\ApiReturnService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\DBAL\Exception\DeadlockException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter;
use League\Flysystem\Filesystem;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use Twig_Error_Loader;
use Twig_Error_Runtime;
use Twig_Error_Syntax;

/**
 * Class ReportRepository
 *
 * @package App\Repository
 */
final class ReportRepository
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
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var TokenStorageInterface
     */
    private $userToken;

    private $managerRegistry;

    /**
     * ReportRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param TokenStorageInterface  $token
     * @param ParameterBagInterface  $params
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $token,
        ParameterBagInterface $params,
        ApiReturnService $apiReturnService,
        MessageSystemRepository $messageSystemRepository,
        ManagerRegistry $managerRegistry
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Report::class);
        $this->userToken = $token->getToken()->getUser();
        $this->managerRegistry = $managerRegistry;

        $client = BlobRestProxy::createBlobService($params->get('BLOB_ENDPOINTS_PROTOCOL'));
        $adapter = new AzureBlobStorageAdapter($client, 'profile-images');

        $adapterPdf = new AzureBlobStorageAdapter($client, 'pdf');

        $this->pdfBlobUrl = $params->get('BLOB_URL') . '/pdf';

        $this->filesystem = new Filesystem($adapter);

        $this->filesystemPdf = new Filesystem($adapterPdf);

        $this->repositoryEmployment = $entityManager->getRepository(Employment::class);

        $this->apiReturnService = $apiReturnService;
        $this->messageSystemRepository = $messageSystemRepository;
    }

    /**
     * @param string $id
     *
     * @return null|object
     */
    public function find(string $id)
    {
        return $this->repository->find($id);
    }

    /**
     * @return Report[]|array|object[]
     */
    public function all()
    {
        return $this->repository->findAll();
    }

    /**
     * @param string $subjectId
     *
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Runtime
     * @throws Twig_Error_Syntax
     */
    public function closeOpenReport(string $subjectId)
    {
        $qb = $this->repository->createQueryBuilder('s');

        $qb->where('s.subject = :query')
            ->setParameter('query', $subjectId)
            ->andWhere('s.open = :open')
            ->setParameter('open', true)
            ->setMaxResults(1);
        $results = $qb->getQuery()->execute();

        if (count($results) > 0) {
            $report = $results[0];
            $this->closeReport($report);
        }
    }

    /**
     * @param Report $report
     *
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Runtime
     * @throws Twig_Error_Syntax
     */
    public function closeReport(Report $report)
    {
        $report->setOpen(false);

        $this->save($report);
    }

    /**
     * @param Report $report
     *
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Runtime
     * @throws Twig_Error_Syntax
     */
    public function save(Report $report)
    {
        //Daniel
        $this->messageSystemRepository->messageStatusFilterSave($report);

        // $em instanceof EntityManager
        $this->entityManager->getConnection()->beginTransaction(); // suspend auto-commit
        try {
            $this->entityManager->persist($report);
            $this->entityManager->flush();
            $this->entityManager->getConnection()->commit();
        } catch (Exception $e) {
            $this->entityManager->getConnection()->rollBack();
            throw $e;
        }

        // $this->entityManager->persist($report);
        // $this->entityManager->flush();
    }
    // public function save(Report $report)
    // {
    //     $retryCount = 0;
    //     $maxRetries = 3;
    //     $saved = false;

    //     while (!$saved && $retryCount < $maxRetries) {
    //         try {
    //             // Save report
    //             // $this->messageSystemRepository->messageStatusFilterSave($report);

    //             $this->entityManager->persist($report);
    //             sleep(1);
    //             $this->entityManager->flush();
    //             $saved = true;
    //             // } catch (Doctrine\DBAL\Exception\DeadlockException $e) {
    //         } catch (Exception $e) {
    //             $retryCount++;
    //             if ($retryCount >= $maxRetries) {
    //                 throw $e; // Rethrow the exception if max retries reached
    //             }
    //             sleep(1); // Wait before retrying (could use exponential backoff)
    //         }
    //     }
    // }

    // function saveWithExponentialBackoff($report, $maxRetries = 5)
    // {

    //     if (!$this->entityManager->isOpen()) {
    //         $this->entityManager = $this->entityManager::create(
    //             $this->entityManager->getConnection(),
    //             $this->entityManager->getConfiguration()
    //         );
    //     }

    //     $retryCount = 0;
    //     $initialWaitTime = 1; // 1 second
    //     $backoffFactor = 2; // Exponential factor

    //     while ($retryCount < $maxRetries) {
    //         try {
    //             // $this->repository->clear(Report::class);
    //             // $this->repository =  $this->entityManager->getRepository(Report::class);
    //             // Attempt to save the report
    //             // $this->entityManager->refresh(Report::class);

    //             $this->entityManager->clear(Report::class);
    //             $this->repository = $this->entityManager->getRepository(Report::class);

    //             // Ensure the user entity is attached to the EntityManager
    //             $report = $this->entityManager->merge($report);

    //             $this->entityManager->persist($report);
    //             $this->entityManager->flush();
    //             return true; // Success
    //         } catch (DeadlockException $e) {
    //             $this->managerRegistry->resetManager();
    //             // $this->managerRegistry->getManager(Report::class);
    //             // $this->entityManager->refresh($report);
    //             // $this->repository =  $this->entityManager->getRepository(Report::class);
    //             $retryCount++;
    //             if ($retryCount >= $maxRetries) {
    //                 throw $e; // Rethrow the exception if max retries reached
    //             }
    //             $waitTime = $initialWaitTime * pow($backoffFactor, $retryCount - 1);
    //             $jitter = rand(0, 1000) / 1000; // Adding jitter to avoid thundering herd problem
    //             $sleepTime = ($waitTime + $jitter) * 1000000; // Convert to microseconds
    //             usleep((int)$sleepTime); // Sleep for the calculated time
    //         } catch (Exception $e) {
    //             throw $e; // Rethrow the exception if max retries reached
    //         }
    //     }
    // }


    function saveWithExponentialBackoff($report, $maxRetries = 3)
    {
        if (!$this->entityManager->isOpen()) {
            $this->entityManager = $this->entityManager::create(
                $this->entityManager->getConnection(),
                $this->entityManager->getConfiguration()
            );
        }

        $retryCount = 0;
        $initialWaitTime = 1; // 1 second
        $backoffFactor = 2; // Exponential factor

        while ($retryCount < $maxRetries) {
            try {
                $this->entityManager->clear(Report::class);
                $this->repository = $this->entityManager->getRepository(Report::class);

                // Ensure the report entity is attached to the EntityManager
                $report = $this->entityManager->merge($report);

                $this->entityManager->persist($report);
                $this->entityManager->flush();
                return true; // Success
            } catch (DeadlockException $e) {
                $this->managerRegistry->resetManager();

                $retryCount++;
                if ($retryCount >= $maxRetries) {
                    throw $e; // Rethrow the exception if max retries reached
                }
                $waitTime = $initialWaitTime * pow($backoffFactor, $retryCount - 1);
                $jitter = rand(0, 1000) / 1000; // Adding jitter to avoid thundering herd problem
                $sleepTime = ($waitTime + $jitter) * 1000000; // Convert to microseconds
                usleep((int)$sleepTime); // Sleep for the calculated time
            } catch (Exception $e) {
                throw $e; // Rethrow the exception if max retries reached
            }
        }
    }


    /**
     * @return int
     */
    public function count()
    {
        return $this->repository->count([]);
    }

    /**
     * @return Report[]|array|object[]
     */
    public function enabled()
    {
        return $this->repository->findBy([
            'enabled' => true
        ]);
    }

    /**
     * @param string|null $filter
     * @param string|null $company
     * @param string|null $date_from
     * @param string|null $date_to
     * @param int         $offset
     * @param int         $limit
     * @param string      $sort
     * @param bool        $descending
     * @param string      $search
     * @param User        $user
     * @param string|null $reportType
     * @param string|null $reportStatus
     * @param bool|null   $spreadSheetCheck
     *
     * @return array|null
     */
    public function paginatedBySubject(
        string $filter = null,
        string $company = null,
        string $date_from = null,
        string $date_to = null,
        int $offset,
        int $limit,
        string $sort,
        bool $descending,
        $search = '',
        User $user,
        string $reportType = null,
        string $reportStatus = null,
        bool $spreadSheetCheck = null

    ) {
        // Find Sort
        switch ($sort) {
            case 'due_date':
                $sort = 'dueDate';
                break;
            case 'completed_date':
                $sort = 'completedDate';
                break;
            case 'open':
                $sort = 'open';
                break;
            default:
                $sort = 'createdAt';
                break;
        }

        $qb = $this->forRole($user);

        if ($filter) {
            $qb = $this->getFilter(
                $user,
                $reportType,
                $reportStatus,
                $company,
                $date_from,
                $date_to
            );
        } else {
            if ($reportType == 'rush') {
                switch ($reportStatus) {
                    case 'needs_approval':
                        $qb = $this->queueFor($user, 'rush', 'needs_approval');
                        break;
                    case 'report_type_approved':
                        $qb = $this->queueForApprovedRush($user, $reportType);
                        break;
                }
            } elseif ($reportType == 'test') {
                switch ($reportStatus) {
                    case 'needs_approval':
                        $qb = $this->queueFor($user, 'test', 'needs_approval');
                        break;
                    case 'report_type_approved':
                        $qb = $this->queueForApprovedTest($user, $reportType);
                        break;
                }
            } elseif ($reportType === 'normal') {
                $qb = $this->queueForNormal($user, 'normal', 'new_request');
            } elseif ($reportType === 'all') {
                if ($user->getRoles()[0]) {
                    $qb = $this->queueForALL($user, 'normal', 'new_request');
                }
            } elseif ($reportStatus === 'unassigned') {
                $qb = $this->unassigned($user);
            } elseif ($reportStatus === 'new_request' || $reportStatus === 'report_type_approved') {
                $qb = $this->queueGetNewRequest($user, $reportType);
            } elseif (
                $reportStatus === 'search_completed' ||
                $reportStatus === 'validated' ||
                $reportStatus === 'under_investigation' ||
                $reportStatus === 'investigation_completed' ||
                $reportStatus === 'team_lead_approved' ||
                $reportStatus === 'completed' ||
                $reportStatus === 'abandoned'
            ) {
                $qb->andWhere('r.status = :status')
                    ->setParameter('status', $reportStatus);
            } else {
                $qb->andWhere('r.requestType = :type')
                    ->setParameter('type', $reportType)
                    ->andWhere('r.status = :status')
                    ->setParameter('status', $reportStatus);
            }
        }

        $qb->orderBy("r.$sort", $descending === true ? 'DESC' : 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $this->apiReturnService->queuesReturns($qb->getQuery()->execute(), $reportStatus, $spreadSheetCheck);
    }

    /**
     * @param User $user
     *
     * @return QueryBuilder
     */
    private function forRole(User $user)
    {
        $queryBuilder = $this->repository->createQueryBuilder('r');
        switch ($user->getRoles()[0]) {
            case 'ROLE_SUPER_ADMIN':
                $queryBuilder->innerJoin('r.subject', 's')
                    ->innerJoin('s.company', 'c');
                break;
            case 'ROLE_TEAM_LEAD': // get reports based on team lead assigned to company
                $queryBuilder->innerJoin('r.subject', 's')
                    ->innerJoin('s.company', 'c')
                    ->innerJoin('c.team', 't')
                    ->andWhere('t.teamLeader = :id')
                    ->setParameter('id', $user->getId());
                break;
            case 'ROLE_ANALYST': // get reports based on analyst assigned to report
                $queryBuilder->innerJoin('r.subject', 's')
                    ->innerJoin('s.company', 'c')
                    ->innerJoin('r.assignedTo', 'a')
                    ->andWhere('a.id = :id')
                    ->setParameter('id', $user->getId());
                break;
            case 'ROLE_ADMIN_USER': // get reports based on company
            case 'ROLE_USER_MANAGER':
                $queryBuilder->join('r.subject', 's')
                    ->join('s.company', 'c')
                    ->andWhere('c.id = :id')
                    ->setParameter('id', $user->getCompany());
                break;
            default: // get reports based on company (standard) user requested reports

                $queryBuilder->innerJoin('r.subject', 's')
                    ->innerJoin('s.company', 'c')
                    ->andWhere('c.id = :cid')
                    ->setParameter('cid', $user->getCompany())
                    ->andWhere('r.user = :id')
                    ->setParameter('id', $user->getId());
                break;
        }

        return $queryBuilder;
    }

    /**
     * @param User        $user
     * @param string|null $reportType
     * @param string|null $reportStatus
     * @param string|null $company
     * @param string|null $date_from
     * @param string|null $date_to
     *
     * @return QueryBuilder
     */
    public function getFilter(
        User $user,
        string $reportType = null,
        string $reportStatus = null,
        string $company = null,
        string $date_from = null,
        string $date_to = null

    ) {
        $queryBuilder = $this->forRole($user);

        if ($reportStatus) {
            $queryBuilder->andWhere('r.status = :status')
                ->setParameter('status', $reportStatus);
        }

        if ($reportType) {
            $queryBuilder->andWhere('r.requestType = :type')
                ->setParameter('type', $reportType);
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

        if ($company) {
            $queryBuilder->andWhere('r.company = :company')
                ->setParameter('company', $company);
        }

        return $queryBuilder;
    }

    /**
     * @param User        $user
     * @param string|null $reportType
     * @param string|null $reportStatus
     *
     * @return Report[]|array|object[]
     */
    public function queueFor(User $user, string $reportType = null, string $reportStatus = null)
    {
        $queryBuilder = $this->forRole($user);

        if ($reportType) {
            $queryBuilder->andWhere('r.requestType = :type')
                ->setParameter('type', $reportType);
        }

        if ($reportStatus) {
            $queryBuilder->andWhere('r.status = :status')
                ->setParameter('status', $reportStatus);
        }

        return $queryBuilder;
    }

    /**
     * @param User        $user
     * @param string|null $reportType
     *
     * @return Report[]|array|object[]
     */
    public function queueForApprovedRush(User $user, string $reportType = null)
    {
        $queryBuilder = $this->forRole($user);

        $queryBuilder->andWhere('r.requestType = :type')
            ->setParameter('type', $reportType);

        $queryBuilder->andWhere('r.status != :status1', 'r.status != :status2')
            ->setParameter('status1', 'needs_approval')
            ->setParameter('status2', 'complete');

        return $queryBuilder;
    }

    /**
     * @param User        $user
     * @param string|null $reportType
     *
     * @return Report[]|array|object[]
     */
    public
    function queueForApprovedTest(User $user, string $reportType = null)
    {
        $queryBuilder = $this->forRole($user);

        $queryBuilder->andWhere('r.requestType = :type')
            ->setParameter('type', $reportType);

        $queryBuilder->andWhere('r.status != :status1', 'r.status != :status2')
            ->setParameter('status1', 'needs_approval')
            ->setParameter('status2', 'complete');

        return $queryBuilder;
    }

    /**
     * @param User        $user
     * @param string|null $reportType
     *
     * @return Report[]|array|object[]
     */
    public
    function queueForNormal(User $user, string $reportType = null)
    {
        $queryBuilder = $this->forRole($user);

        if ($reportType) {
            $queryBuilder->andWhere('r.requestType = :type')
                ->setParameter('type', $reportType);
        }

        $queryBuilder->andWhere('r.status != :status')
            ->setParameter('status', 'completed')
            ->andWhere('r.status != :status1')
            ->setParameter('status1', 'abandoned');

        return $queryBuilder;
    }

    /**
     * @param User        $user
     * @param string|null $reportType
     *
     * @return Report[]|array|object[]
     */
    public function queueForALL(User $user, string $reportType = null)
    {
        $queryBuilder = $this->forRole($user);
        return $queryBuilder;
    }

    /**
     * @param User $user
     *
     * @return mixed
     */
    public function unassigned(User $user)
    {
        $queryBuilder = $this->forRole($user);
        $queryBuilder->andWhere('r.assignedTo is NULL')
            ->andWhere('r.status != :status')
            ->setParameter('status', 'completed')
            ->andWhere('r.status != :status1')
            ->setParameter('status1', 'abandoned');

        return $queryBuilder;
    }

    /**
     * @param User        $user
     * @param string|null $reportType
     *
     * @return QueryBuilder
     */
    public
    function queueGetNewRequest(User $user, string $reportType = null)
    {
        $queryBuilder = $this->forRole($user);

        $queryBuilder->andWhere('r.status = :status')
            ->setParameter('status', 'new_request')
            ->orWhere('r.status = :status1')
            ->setParameter('status1', 'report_type_approved');

        return $queryBuilder;
    }

    /**
     * @param string $id
     *
     * @return Report|object|null
     */
    public
    function getClosedReport(string $id)
    {
        return $this->repository->findOneBy([
            'id' => $id,
            'open' => false
        ]);
    }

    /**
     * @param Report $report
     */
    public
    function openReport(Report $report)
    {
        $report->setOpen(true);

        $this->save($report);
    }

    /**
     * @param string $subjectId
     *
     * @return Report[]|array|object[]
     */
    public function getBySubjectIdentification(string $subjectId)
    {
        $queryBuilder = $this->repository->createQueryBuilder('r');
        $queryBuilder->innerJoin('r.subject', 's')
            ->andWhere('s.identification = :id')
            ->setParameter('id', $subjectId);

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @param Report $report
     *
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Runtime
     * @throws Twig_Error_Syntax
     */
    public function enable(Report $report)
    {
        $report->setEnabled(true);

        $this->save($report);
    }

    /**
     * @param Report $report
     *
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Runtime
     * @throws Twig_Error_Syntax
     */
    public function disable(Report $report)
    {
        $report->setEnabled(false);

        $this->save($report);
    }

    /**
     * @param Report $report
     */
    public function saveOverride(Report $report)
    {
        $this->entityManager->persist($report);
        $this->entityManager->flush();
    }

    /**
     * @param Report $report
     */
    function toggleGeneralComments(Report $report)
    {
        $report->setHideGeneralComments(!$report->getHideGeneralComments());
        $this->save($report);
    }

    /**
     * @param Report $report
     */
    function toggleReportScore(Report $report)
    {
        $report->setHideReportScore(!$report->getHideReportScore());
        $this->save($report);
    }

    /**
     * @param User $user
     *
     * @return array
     * @throws Exception
     */
    public
    function monthlyRequests(User $user)
    {
        $thisMonth = date("m");

        $label = [];
        $dataSets = [];
        $months = [
            "1" => "January",
            "2" => "February",
            "3" => "March",
            "4" => "April",
            "5" => "May",
            "6" => "June",
            "7" => "July",
            "8" => "August",
            "9" => "September",
            "10" => "October",
            "11" => "November",
            "12" => "December",
        ];

        for ($i = 1; $i <= $thisMonth; $i++) {
            array_push($label, $months[$i]);
            array_push($dataSets, $this->queueForNormalData($user, $i));
        }

        $monthlyRequests = [
            "labels" => $label,
            "datasets" => $dataSets,
            "label" => "Requests Completed"

        ];

        return $monthlyRequests;
    }

    /**
     * @param User $user
     * @param      $month
     *
     * @return mixed
     * @throws Exception
     */
    public
    function queueForNormalData(User $user, $month)
    {
        $thisMonth = date("Y") . '-' . $month . '-01';

        $date = new DateTime($thisMonth);
        $date->modify('last day of this month');

        $thisMonthEnd = $date->format('Y-m-d');

        $queryBuilder = $this->forRole($user);

        $countCompleted = $queryBuilder->andWhere('r.status = :status')
            ->andWhere('r.completedDate >= :date1')
            ->andWhere('r.completedDate <= :date2')
            ->setParameter('status', 'completed')
            ->setParameter('date1', $thisMonth)
            ->setParameter('date2', $thisMonthEnd)
            ->select('count(r.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return $countCompleted;
    }

    /**
     * @param Subject $subject
     */
    public function getPDF(Subject $subject)
    {
        $qb = $this->repository->createQueryBuilder('p')
            ->andWhere('p.subject = :subject')
            ->setParameter('subject', $subject)
            ->getQuery();

        $fileName = $qb->execute()[0]->getId() . '/' . $qb->execute()[0]->getPdfFilename();

        if ($this->filesystemPdf->has($fileName)) {
            return $this->filesystemPdf->get($fileName)->read();
        }
    }

    //TODO remove if not needed
    //    /**
    //     * @param Report $report
    //     *
    //     * @return bool|false|string
    //     */
    //    public
    //    function getPDF(Report $report)
    //    {
    //        if ($report->getStatus() === 'completed' && $report->getBlobUrl()) {
    //            $id = $report->getSubject()->getBlobFolder();
    //            $name = $id . '/pdf/' . $report->getSubject()->getId() . '-' . $report->getCompletedDate()->format('Ymd') . '-' . $report->getSubject()->getReportType() . '.pdf';
    //
    //            if ($this->filesystem->has($name)) {
    //                return $this->filesystem->get($name)->read();
    //            }
    //            return false;
    //        }
    //        return false;
    //    }

    /**
     * @param $file
     *
     */
    public function savePDF($report, $file)
    {
        //trigger exception in a "try" block
        try {
            if ($file) {
                $fileName = $report->getId() . '/' . $report->getCompletedDate()->format('Ymd') . '-' . $report->getSubject()->getReportType() . '.pdf';

                $this->deletePdf($report);

                $this->filesystemPdf->write($fileName, $file);

                $report->setBlobUrl('pdf/' . $report->getId());
                $report->setPdfFilename($report->getCompletedDate()->format('Ymd') . '-' . $report->getSubject()->getReportType() . '.pdf');
                $this->save($report);
            }
        } //catch exception
        catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }

    /**
     * @param $company
     *
     * @throws FileNotFoundException
     */
    public function deletePdf($report)
    {
        $fileName = $report->getId() . '/' . $report->getCompletedDate()->format('Ymd') . '-' . $report->getSubject()->getReportType() . '.pdf';

        if ($this->filesystemPdf->has($fileName)) {
            $id = $report->getId();

            $report = $this->entityManager->getRepository(Report::class)->find($id);
            $this->filesystemPdf->Delete($fileName);

            $report->setPdfFilename('');
            $this->entityManager->flush();
        }

        $path = 'pdf/' . $report->getId() . '/' . $report->getPdfFilename();

        if ($this->filesystemPdf->has($path)) {
            $id = $report->getId();

            $report = $this->entityManager->getRepository(Report::class)->find($id);
            $this->filesystemPdf->Delete($path);

            $report->setPdfFilename('');
            $this->entityManager->flush();
        }
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function getOverWriteReportScores($id)
    {
        $getReport = $this->repository->find($id);

        if ($getReport->getReportScoresUpdated() == []) {
            $getReport->setReportScoresUpdated($getReport->getReportScores());
            $this->entityManager->persist($getReport);

            $this->entityManager->flush();

            return $getReport->getReportScores();
        } else {
            $getResponse = $getReport->getReportScoresUpdated();
            $overWrite = ['over_write_report_scores' => $getReport->isOverWriteReportScores()];
            $response = array_merge($getResponse, $overWrite);

            return $response;
        }
    }

    //    /**
    //     * @param Subject $subject
    //     * @param         $file
    //     * @param Report  $report
    //     *
    //     * @return bool
    //     * @throws FileExistsException
    //     */
    //    public
    //    function savePDF(Subject $subject, $file, Report $report)
    //    {
    //        $id = $subject->getBlobFolder();
    //        $name = $id . '/pdf/' . $subject->getId() . '-' . $subject->getCurrentReport()->getCompletedDate()->format('Ymd') . '-' . $subject->getReportType() . '.pdf';
    //
    //        if ($file) {
    //            $this->filesystem->write($name, $file);
    //            $report->setBlobUrl($name);
    //            $this->save($report);
    //
    //            return true;
    //        }
    //
    //        return false;
    //    }

    /**
     * @param string $subjectId
     *
     * @return array
     */
    public
    function getEmploymentbyId(string $subjectId)
    {
        $qb = $this->repositoryEmployment->createQueryBuilder('p')
            ->where('p.subject = :id')
            ->setParameter('id', $subjectId)
            ->orderBy('p.startDate', 'ASC')
            ->getQuery();

        foreach ($qb->execute() as $employment) {
            $response[] = [
                "employer" => $employment->getEmployer(),
                "job_title" => $employment->getJobTitle(),
                "start_date" => $employment->getStartDateFormatted(),
                "end_date" => $employment->getEndDateFormatted(),
                "current" => $employment->isCurrentlyEmployed()
            ];
        }
        if (count($qb->execute()) >= 1) {
            return $response;
        } else {
            return [];
        }
    }

    /**
     * @param User $user
     *
     * @return QueryBuilder
     */
    private function forRoleQueue(User $user, $reportStatus)
    {
        $queryBuilder = $this->repository->createQueryBuilder('r');
        switch ($user->getRoles()[0]) {
            case 'ROLE_SUPER_ADMIN':
                $queryBuilder->innerJoin('r.subject', 's')
                    ->innerJoin('s.company', 'c');
                break;
            case 'ROLE_TEAM_LEAD': // get reports based on team lead assigned to company
                $queryBuilder->innerJoin('r.subject', 's')
                    ->innerJoin('s.company', 'c')
                    ->innerJoin('c.team', 't')
                    ->andWhere('t.teamLeader = :id')
                    ->setParameter('id', $user->getId());
                break;
            case 'ROLE_ANALYST': // get reports based on analyst assigned to report
                $queryBuilder->innerJoin('r.subject', 's')
                    ->innerJoin('s.company', 'c')
                    ->innerJoin('r.assignedTo', 'a')
                    ->andWhere('a.id = :id')
                    ->setParameter('id', $user->getId());
                break;
            case 'ROLE_ADMIN_USER': // get reports based on company
            case 'ROLE_USER_MANAGER':
                $queryBuilder->innerJoin('r.subject', 's')
                    ->innerJoin('s.company', 'c')
                    ->andWhere('c.id = :id')
                    ->setParameter('id', $user->getCompany()->getId());
                break;
            default: // get reports based on company (standard) user requested reports

                $queryBuilder->innerJoin('r.subject', 's')
                    ->innerJoin('s.company', 'c')
                    ->andWhere('c.id = :cid')
                    ->setParameter('cid', $user->getCompany()->getId())
                    ->andWhere('r.user = :id')
                    ->setParameter('id', $user->getId())
                    ->andWhere('r.status = :status')
                    ->setParameter('status', $reportStatus);
                break;
        }
        return $queryBuilder;
    }
}
