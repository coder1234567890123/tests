<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Company;
use App\Entity\MessageSystem;
use App\Entity\Report;
use App\Entity\Team;
use App\Entity\User;
use App\Service\EventTrackingService;
use App\Service\MessageService;
use App\Service\ApiReturnService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig_Error_Loader;
use Twig_Error_Runtime;
use Twig_Error_Syntax;

/**
 * Class MessageSystemRepository
 * @package App\Repository
 */
final class MessageSystemRepository
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
     * @var ApiReturnService
     */
    private $apiReturnService;

    /**
     * @var MessageService
     */
    private $messageService;

    private $repositoryReport;

    private $repositoryCompany;

    /**
     * MessageSystemRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param TokenStorageInterface $token
     * @param ParameterBagInterface $params
     * @param ApiReturnService $apiReturnService
     * @param MessageService $messageService
     * @param EventTrackingService $eventTrackingService
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $token,
        ParameterBagInterface $params,
        ApiReturnService $apiReturnService,
        MessageService $messageService,
        EventTrackingService $eventTrackingService
    )
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(MessageSystem::class);
        $this->repositoryReport = $entityManager->getRepository(Report::class);
        $this->repositoryCompany = $entityManager->getRepository(Company::class);
        $this->repositoryTeam = $entityManager->getRepository(Team::class);
        $this->repositoryUser = $entityManager->getRepository(User::class);
        $this->apiReturnService = $apiReturnService;
        $this->messageService = $messageService;
        $this->eventTrackingService = $eventTrackingService;
    }

    /**
     * @param $user
     *
     * @return mixed
     */
    public function getByUnread($user)
    {
        if ($user) {
            $qb = $this->repository->createQueryBuilder('p');
            $qb->andWhere('p.messageFor = :messageFor')
                ->andWhere('p.messageRead = :messageRead')
                ->setParameter('messageFor', $user)
                ->setParameter('messageRead', false);

            if ($qb->getQuery()->execute()) {
                return $this->apiReturnService->getMessages($qb->getQuery()->execute());
            } else {
                return [];
            }
        } else {
            return [];
        }
    }

    /**
     * @param $id
     *
     * @return array
     */
    public function messageViewed($id)
    {
        try {
            $message = $this->repository->find($id);

            $message->setMessageRead(true);
            $this->entityManager->flush();

            return [
                'message' => 'viewed'
            ];
        } //catch exception
        catch (Exception $e) {
            //echo 'Message: ' . $e->getMessage();
        }
    }

    /**
     * @param $report
     * @param $status
     */
    public function messagesAssignedTo($report, $status)
    {
        $this->messageStatusFilter($report, $status);
    }

    /**
     * @param      $subject
     * @param null $status
     *
     * @return null
     */
    public function messagesStatus($subject, $status = null)
    {
        if ($subject) {
            $qb = $this->repositoryReport->createQueryBuilder('p')
                ->andWhere('p.subject = :subject')
                ->setParameter('subject', $subject)
                ->setMaxResults(1)
                ->getQuery();

            $this->messageStatusFilter($qb->execute()[0], $status);
        } else {
            return null;
        }
    }

    /**
     * @param $report
     *
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Runtime
     * @throws Twig_Error_Syntax
     */
    public function messageStatusFilterSave($report)
    {
        $this->messageStatusFilterSendTo($report);
        $this->emailMessages($report);
    }

    /**
     * @param $report
     */
    public function messageStatusFilterSendTo($report)
    {
        $getStatus = $report->getStatus();

        switch ($getStatus) {
            case 'new_subject':
                //just in case
                break;
            case 'new_request':

                if ($report->getAssignedTo()) {
                    $this->addMessage($report, $report->getAssignedTo());
                } else {
                    $this->addMessage($report, $report->getCompany()->getTeam()->getTeamLeader());
                }

                break;
            case 'needs_approval':

                $messageFor = $report->getCompany()->getTeam()->getTeamLeader();
                $this->addMessage($report, $messageFor);

                $qb = $this->repositoryUser->createQueryBuilder('p')
                    ->andWhere('p.roles = :roles')
                    ->setParameter('roles', '["ROLE_SUPER_ADMIN"]')
                    ->getQuery();

                foreach ($qb->execute() as $getAdmin) {
                    $this->addMessage($report, $getAdmin);
                }

                break;

            case 'report_type_approved':

                if ($report->getAssignedTo()) {
                    $this->addMessage($report, $report->getAssignedTo());
                    $this->addMessage($report, $report->getCompany()->getTeam()->getTeamLeader());
                } else {
                    $this->addMessage($report, $report->getCompany()->getTeam()->getTeamLeader());
                }

                break;
            case 'search_started':
                //just incase
                break;
            case 'search_completed':

                if ($report->getAssignedTo()) {
                    $this->addMessage($report, $report->getAssignedTo());
                    $this->addMessage($report, $report->getCompany()->getTeam()->getTeamLeader());
                } else {
                    $this->addMessage($report, $report->getCompany()->getTeam()->getTeamLeader());
                }

                break;
            case 'validated':
                //$this->teamLeader($subject, $report, 'validated');
                break;
            case 'team_lead_approved':

                $qb = $this->repositoryUser->createQueryBuilder('p')
                    ->andWhere('p.roles = :roles')
                    ->setParameter('roles', '["ROLE_SUPER_ADMIN"]')
                    ->getQuery();

                foreach ($qb->execute() as $getAdmin) {
                    $this->addMessage($report, $getAdmin);
                }

                break;
            case 'abandoned_request':

                $qb = $this->repositoryUser->createQueryBuilder('p')
                    ->andWhere('p.roles = :roles')
                    ->setParameter('roles', '["ROLE_SUPER_ADMIN"]')
                    ->getQuery();

                foreach ($qb->execute() as $getAdmin) {
                    $this->addMessage($report, $getAdmin);
                }

                break;
            case 'report_type_approved':
                //$this->teamLeader($subject, $report, 'report_type_approved');
                break;
            case 'under_investigation':
                // $this->teamLeader($subject, $report, 'under_investigation');
                break;
            case 'investigation_completed':
                $this->addMessage($report, $report->getCompany()->getTeam()->getTeamLeader());

                break;
            case 'completed':

                if ($report->getAssignedTo()) {
                    $this->addMessage($report, $report->getAssignedTo());
                    $this->addMessage($report, $report->getCompany()->getTeam()->getTeamLeader());
                    $this->addMessage($report, $report->getUser());
                } else {
                    $this->addMessage($report, $report->getCompany()->getTeam()->getTeamLeader());
                    $this->addMessage($report, $report->getUser());
                }

                break;
        }
    }

    /**
     *
     * Saves message and check for duplicate message
     *
     * @param      $report
     * @param null $messageFor
     */
    public function addMessage($report, $messageFor = null)
    {

        //Checks for duplicate messages
        if ($this->messageDuplicationCheck($report) === 0) {

            $messageSystem = new MessageSystem();

            $messageSystem->setCompany($report->getCompany());
            $messageSystem->setSubject($report->getSubject());
            $messageSystem->setMessage($this->messages($report, $report->getStatus()));
            $messageSystem->setMessageHeader($this->messagesHeader($report, $report->getStatus()));
            $messageSystem->setMessageType($report->getRequestType());
            $messageSystem->setMessageRead(false);
            $messageSystem->setStatus($report->getStatus());
            $messageSystem->setUser($report->getUser());

            $assignedToCheck = $this->assignedToCheck($report);

            if ($assignedToCheck === false) {
                $messageSystem->setAssignedTo($report->getAssignedTo());
            }

            if ($report->getCompany()->getTeam()) {
                $messageSystem->setTeamLead($report->getCompany()->getTeam()->getTeamLeader());
            }

            $messageSystem->setUser($report->getUser());

            $messageSystem->setMessageFor($messageFor);

            $this->entityManager->persist($messageSystem);
            $this->entityManager->flush();
        }
    }

    /**
     * @param $report
     *
     * @return int
     */
    public function messageDuplicationCheck($report)
    {
        $qb = $this->repository->createQueryBuilder('p')
            ->andWhere('p.messageFor = :messageFor')
            ->setParameter('messageFor', $report->getUser())
            ->andWhere('p.status = :status')
            ->setParameter('status', $report->getStatus())
            ->getQuery();

        return count($qb->execute());
    }

    /**
     * @param $report
     *
     * @return string
     */
    public function messages($report)
    {
        if ($report) {
            $reportType = str_replace('_', ' ', $report->getSubject()->getReportType());
            return $report->getSubject()->getFirstName() . ' ' . $report->getSubject()->getLastName() . ' - ' . ucfirst($reportType);
        } else {
            return 'Message Error';
        }
    }

    /**
     * @param $report
     *
     * @return string
     */
    public function messagesHeader($report)
    {
        $statusHeader = str_replace('_', ' ', $report->getStatus());

        if ($report) {
            return ucfirst($statusHeader) . ' - ' . ucfirst($report->getRequestType());
        } else {
            return 'Message Error';
        }
    }

    /**
     * @param $report
     *
     * @return bool
     */
    public function assignedToCheck($report)
    {
        $qb = $this->repository->createQueryBuilder('p')
            ->andWhere('p.assignedTo = :assignedTo')
            ->setParameter('assignedTo', $report->getAssignedTo())
            ->andWhere('p.subject = :subject')
            ->setParameter('subject', $report->getSubject())
            ->getQuery();

        if ($qb->execute()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $report
     *
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Runtime
     * @throws Twig_Error_Syntax
     */
    public function emailMessages($report)
    {
        $this->eventTrackingService->sendMailMessages($report);
    }

    /**
     * @param null $report
     * @param null $status
     */
    public function save($report = null, $status = null, $user = null, $sendTo)
    {
        if ($this->messageDuplicationCheck($report) === 0) {
            $messageSystem = new MessageSystem();

            $messageSystem->setCompany($report->getCompany());
            $messageSystem->setSubject($report->getSubject());
            $messageSystem->setMessage($this->messages($report, $status));
            $messageSystem->setMessageHeader($this->messagesHeader($report, $status));
            $messageSystem->setMessageType($report->getRequestType());
            $messageSystem->setMessageRead(false);
            $messageSystem->setUser($report->getUser());
            $messageSystem->setStatus($status);
            $messageSystem->setAssignedTo($report->getAssignedTo());

            $this->entityManager->persist($messageSystem);
            $this->entityManager->flush();
        }
    }

}
