<?php
/**
 * Created by PhpStorm.
 * User: kuda
 * Date: 8/29/19
 * Time: 12:03 PM
 */

namespace App\Service;

use App\Entity\Report;
use App\Entity\User;
use App\Repository\Company;
use App\Service\ApiCompanyProductService;
use App\Repository\MessageSystemRepository;
use App\Repository\ReportRepository;
use App\Repository\TeamRepository;
use App\Repository\UserTrackingRepository;
use Exception;

/**
 * Class DashboardService
 *
 * @package App\Service
 */
class DashboardService
{
    /**
     * @var ReportRepository
     */
    private $reportRepository;

    /**
     * @var TeamRepository
     */
    private $teamRepository;

    /**
     * @var UserTrackingRepository
     */
    private $userTrackingRepository;
    /**
     * @var MessageSystemRepository
     */
    private $messageSystemRepository;

    /**
     * DashboardService constructor.
     *
     * @param ReportRepository        $reportRepository
     * @param TeamRepository          $teamRepository
     * @param UserTrackingRepository  $userTrackingRepository
     * @param MessageSystemRepository $messageSystemRepository
     */
    public function __construct(
        ReportRepository $reportRepository,
        TeamRepository $teamRepository,
        UserTrackingRepository $userTrackingRepository,
        MessageSystemRepository $messageSystemRepository,
        ApiCompanyProductService $apiCompanyProductService
    )
    {
        $this->reportRepository = $reportRepository;
        $this->teamRepository = $teamRepository;
        $this->userTrackingRepository = $userTrackingRepository;
        $this->messageSystemRepository = $messageSystemRepository;
        $this->apiCompanyProductService = $apiCompanyProductService;
    }

    /**
     * @param User $user
     *
     * @return array
     * @throws Exception
     */
    public function getRoleDashBoard(User $user)
    {
        switch ($user->getRoles()[0]) {
            case "ROLE_SUPER_ADMIN":
                $result = $this->superDashboard($user);
                unset($result['values']);
                return $result;
                break;
            case "ROLE_TEAM_LEAD":
                $result = $this->teamDashboard($user);

                unset($result['values']);
                return $result;
                break;
            case "ROLE_ANALYST":
                $result = $this->getQueue($user);
                array_splice($result['queues'], $result['values']['investigation_completed'], 1);
                array_splice($result['queues'], $result['values']['validated'], 1);
                array_splice($result['queues'], $result['values']['new_request'], 1);
                unset($result['values']);
                return $result;
                break;
            case "ROLE_USER_STANDARD":

                $result = $this->getQueue($user);
                array_splice($result['queues'], $result['values']['investigation_completed'], 1);
                array_splice($result['queues'], $result['values']['validated'], 1);
                array_splice($result['queues'], $result['values']['new_request'], 1);
                unset($result['values']);
                return $result;
                break;
            default:
                $result = $this->getQueue($user);

                unset($result['values']);
                return $result;
                break;
        }
    }

    /**
     * @param User $user
     *
     * @return array
     * @throws Exception
     */
    private function superDashboard(User $user)
    {
        $result = $this->getQueue($user);
        $tracks = $this->userTrackingRepository->getLatest(5);
        $audit = [];
        foreach ($tracks as $track) {
            $p['user'] = $track->getUser()->getFullName();
            $p['action'] = $track->getAction();
            $p['date'] = $track->getCreatedAt();
            $p['source'] = $track->getSource();

            $audit [] = $p;
        }

        $teamVal = count($result['queues']);
        $result['queues'][$teamVal]['status'] = 'teams';
        $result['queues'][$teamVal]['count'] = $this->teamRepository->count();

        $result['audit_log'] = $audit;
        return $result;
    }

    /**
     * @param User $user
     *
     * @return array
     * @throws Exception
     */
    private function teamDashboard(User $user)
    {
        $result = $this->getQueue($user);

        $k = count($result['queues']);
        $team = $this->teamRepository->findByTeamLead($user->getId());
        $result['queues'][$k]['status'] = 'team_members';
        $result['queues'][$k]['count'] = $team->getUsers()->count();
        return $result;
    }

    /**
     * @param User $user
     *
     * @return array
     * @throws Exception
     */
    private function getQueue(User $user)
    {
        $queueValues = [];
        $values = [];
        $i = 0;

        foreach (Report::REPORT_STATUSES as $status) {
            $status_value = $this->reportRepository->queueFor($user, null, $status);

            switch ($status) {
                case 'new_request':
                    $returnValue = $this->reportRepository->queueGetNewRequest($user, $status);

                    $returnValue = count($returnValue->getQuery()->execute());
                    $queueValues [$i]['status'] = 'new_request';
                    $queueValues [$i]['count'] = $returnValue;
                    $values['new_request'] = $i;
                    $i++;
                    break;
                case 'search_started':
                    $queueValues [$i]['status'] = $status;
                    $queueValues [$i]['count'] = count($status_value->getQuery()->execute());
                    $values[$status] = $i;
                    $i++;
                    break;
                case 'unassigned':

                    if ($user->getRoles()[0] == 'ROLE_SUPER_ADMIN' || $user->getRoles()[0] == 'ROLE_TEAM_LEAD') {
                        $returnValue = $this->reportRepository->unassigned($user);

                        $returnValue = count($returnValue->getQuery()->execute());

                        $queueValues [$i]['status'] = 'unassigned';
                        $queueValues [$i]['count'] = $returnValue;
                        $values['unassigned'] = $i;
                        $i++;
                    }

                    break;
                case 'search_completed':
                    $queueValues [$i]['status'] = $status;
                    $queueValues [$i]['count'] = count($status_value->getQuery()->execute());
                    $values[$status] = $i;
                    $i++;
                    break;
                case 'validated':
                    $queueValues [$i]['status'] = $status;
                    $queueValues [$i]['count'] = count($status_value->getQuery()->execute());
                    $values[$status] = $i;
                    $i++;
                    break;
                case 'report_type_approved':
                    $queueValues [$i]['status'] = $status;
                    $queueValues [$i]['count'] = count($status_value->getQuery()->execute());
                    $values[$status] = $i;
                    $i++;
                    break;
                case 'under_investigation':
                    $queueValues [$i]['status'] = $status;
                    $queueValues [$i]['count'] = count($status_value->getQuery()->execute());
                    $values[$status] = $i;
                    $i++;
                    break;
                case 'investigation_completed':
                    $queueValues [$i]['status'] = $status;
                    $queueValues [$i]['count'] = count($status_value->getQuery()->execute());
                    $values[$status] = $i;
                    $i++;
                    break;
                case 'team_lead_approved':
                    $queueValues [$i]['status'] = $status;
                    $queueValues [$i]['count'] = count($status_value->getQuery()->execute());
                    $values[$status] = $i;
                    $i++;
                    break;
                case 'completed':
                    $queueValues [$i]['status'] = $status;
                    $queueValues [$i]['count'] = count($status_value->getQuery()->execute());
                    $values[$status] = $i;
                    $i++;
                    break;
                case 'abandoned':
                    $queueValues [$i]['status'] = $status;
                    $queueValues [$i]['count'] = count($status_value->getQuery()->execute());
                    $values[$status] = $i;
                    $i++;
                    break;
            }
        }

        $monthly = $this->reportRepository->monthlyRequests($user);

        $message = $this->messageSystemRepository->getByUnread($user);

        return [
            'accounts' => $this->accounts($user),
            'queues' => $queueValues,
            'reports' => $this->reportTypeReturned($user),
            'monthly_request' => $monthly,
            'values' => $values,

            'messages' => [
                'count' => count($message),
                'messages' => $message
            ]
        ];
    }

    /**
     * @param $type
     * @param $user
     *
     * @return int
     */
    private function getReportAmounts($type, $user)
    {
        switch ($type) {
            case 'new_rush':

                $returnValue = $this->reportRepository->queueFor($user, 'rush', 'needs_approval');
                $returnValue = count($returnValue->getQuery()->execute());

                return $returnValue;
                break;
            case 'rush_approved':

                $returnValue = $type_value = $this->reportRepository->queueForApprovedRush($user, 'rush', 'report_type_approved');
                $returnValue = count($returnValue->getQuery()->execute());

                return $returnValue;
                break;

            case 'new_test':

                $returnValue = $this->reportRepository->queueFor($user, 'test', 'needs_approval');
                $returnValue = count($returnValue->getQuery()->execute());

                return $returnValue;
                break;

            case 'test_approved':

                $returnValue = $this->reportRepository->queueForApprovedTest($user, 'test', 'report_type_approved');
                $returnValue = count($returnValue->getQuery()->execute());

                return $returnValue;
                break;

            case 'normal':

                $returnValue = $this->reportRepository->queueForNormal($user, 'normal', 'new_request');
                $returnValue = count($returnValue->getQuery()->execute());

                return $returnValue;
                break;
            default:
                return 0;
        }
    }

    /**
     * @return array
     */
    private function reportTypeReturned($user)
    {
        $otherView = [];

        switch ($user->getRoles()[0]) {
            case 'ROLE_SUPER_ADMIN':
            case 'ROLE_TEAM_LEAD':

                $otherView = [
                    [
                        'type' => 'new_rush',
                        'count' => $this->getReportAmounts('new_rush', $user)
                    ],
                    [
                        'type' => 'rush_approved',
                        'count' => $this->getReportAmounts('rush_approved', $user)
                    ],
                    [
                        'type' => 'new_test',
                        'count' => $this->getReportAmounts('new_test', $user)
                    ],
                    [
                        'type' => 'test_approved',
                        'count' => $this->getReportAmounts('test_approved', $user)
                    ]
                ];

                break;

            case 'ROLE_ANALYST':
            case 'ROLE_USER_STANDARD':
            case 'ROLE_ADMIN_USER':
            case 'ROLE_USER_MANAGER':

                $otherView = [
                    [
                        'type' => 'rush_approved',
                        'count' => $this->getReportAmounts('rush_approved', $user)
                    ],
                    [
                        'type' => 'test_approved',
                        'count' => $this->getReportAmounts('test_approved', $user)
                    ]
                ];

                break;
        }

        $normalView = [
            [
                'type' => 'normal',
                'count' => $this->getReportAmounts('normal', $user)
            ]
        ];

        return array_merge($otherView, $normalView);
    }

    /**
     * @param User $user
     *
     * @return array|string[]
     */
    private function accounts(User $user)
    {
        $suspendedResponse = [
            'product_type' => 'suspended',
            'bundle_remaining' => 0,
            'account_status' => 'suspended',
            'rushed_report_allowed' => false,
            'test_report_allowed' => false
        ];

        switch ($user->getRoles()[0]) {
            case 'ROLE_TEAM_LEAD':
            case 'ROLE_ANALYST':
            case 'ROLE_SUPER_ADMIN':
                return $suspendedResponse;

                break;

            case 'ROLE_USER_STANDARD':
            case 'ROLE_ADMIN_USER':
            case 'ROLE_USER_MANAGER':
                return $this->apiCompanyProductService->basicAccountDetails('dashboard', $user->getCompany());
                break;

            default:

                return $suspendedResponse;
        }
    }
}