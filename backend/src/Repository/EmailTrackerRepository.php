<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Company;
use App\Entity\EmailTracker;
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


/**
 * Class MessageSystemRepository
 * @package App\Repository
 */
final class EmailTrackerRepository
{

    /**
     * EmailTrackerRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        EntityManagerInterface $entityManager
    )
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(EmailTracker::class);
    }

    /**
     * @param $report
     */
    public function addEmail($report)
    {
        $emailTracker = new EmailTracker();

        $emailTracker->setCompany($report->getCompany());
        $emailTracker->setSubject($report->getSubject());
        $emailTracker->setReport($report);
        $emailTracker->setMessage($this->messages($report, $report->getStatus()));
        $emailTracker->setMessageHeader($this->messagesHeader($report, $report->getStatus()));
        $emailTracker->setMessageType($report->getRequestType());
        $emailTracker->setMessageRead(false);
        $emailTracker->setStatus($report->getStatus());
        $emailTracker->setUser($report->getUser());

        $assignedToCheck = $this->assignedToCheck($report);

        if ($assignedToCheck === false) {
            $emailTracker->setAssignedTo($report->getAssignedTo());
        }

        if ($report->getCompany()->getTeam()) {
            $emailTracker->setTeamLead($report->getCompany()->getTeam()->getTeamLeader());
        }

        $emailTracker->setUser($report->getUser());

        $this->entityManager->persist($emailTracker);
        $this->entityManager->flush();
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
     * @return int
     */
    public function emailDuplicationCheck($report)
    {
        $qb = $this->repository->createQueryBuilder('p')
            ->andWhere('p.report = :report')
            ->setParameter('report', $report)
            ->andWhere('p.status = :status')
            ->setParameter('status', $report->getStatus())
            ->getQuery();

        return count($qb->execute());
    }
}