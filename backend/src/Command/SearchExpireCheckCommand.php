<?php

namespace App\Command;

use App\Entity\EmailTracker;
use App\Entity\MessageQueue;
use App\Entity\MessageSystem;
use App\Entity\Question;
use App\Entity\Report;
use App\Entity\Subject;
use App\Repository\EmailTrackerRepository;
use App\Service\EventTrackingService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use SendGrid\Mail\Mail;
use SendGrid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class SearchExpireCheckCommand extends Command
{
    protected static $defaultName = 'cron:search-expire-check';
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * ResetForTestingCommand constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param Environment            $twig
     * @param Swift_Mailer           $mailer
     * @param ParameterBagInterface  $parameterBag
     * @param EmailTrackerRepository $emailTrackerRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        Environment $twig,
        Swift_Mailer $mailer,
        ParameterBagInterface $parameterBag,
        EmailTrackerRepository $emailTrackerRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(MessageQueue::class);
        $this->repositoryEmailTracker = $entityManager->getRepository(EmailTracker::class);
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->parameterBag = $parameterBag;
        $this->emailTrackerRepository = $emailTrackerRepository;

        parent::__construct();
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $expireMessages = [];
        echo "\n";
        $output->writeln("Checking for expired searches");

        $qb = $this->repository->createQueryBuilder('p')
            ->andWhere('p.messageReceived = :messageReceived')
            ->andWhere('p.overWritten = :overWritten')
            ->andWhere('p.systemOverWrite = :systemOverWrite')
            ->setParameter('messageReceived', false)
            ->setParameter('overWritten', false)
            ->setParameter('systemOverWrite', false)
            ->getQuery();

        foreach ($qb->execute() as $getMessages) {
            echo $getMessages->getPhrase();
            echo "\n";
            echo $getMessages->getCreatedAt()->format('Y-m-d h:i:s');
            echo "\n";
            echo $checkTime = $this->checkTimeDiff($getMessages->getCreatedAt()->format('Y-m-d h:i:s'));
            echo "\n";

            if ($checkTime >= 5) {
                echo "over 5 mins";
                echo "\n";

                array_push($expireMessages, $getMessages->getSubject()->getId());
            } else {
                echo "under 5 mins";
                echo "\n";
            }
            echo "\n";
        }

        foreach (array_unique($expireMessages) as $getOverWrite) {
            $this->messageOverwrite($getOverWrite);
        }
    }

    /**
     * @param $startDate
     *
     * @return string
     */
    private function checkTimeDiff($startDate)
    {
        $dateTime = date(" Y-m-d h:i:s");
        $toTime = strtotime($startDate);
        $currentDateTime = strtotime($dateTime);

        return round(abs($toTime - $currentDateTime) / 60, 2);
    }

    /**
     * @param $subject_id
     */
    public function messageOverwrite($subject_id)
    {
        $subject = $this->entityManager->getRepository(Subject::class)->find($subject_id);

        if ($subject) {
            $qb = $this->repository->createQueryBuilder('p')
                ->andWhere('p.subject = :subject_id')
                ->setParameter('subject_id', $subject)
                ->getQuery();

            if ($qb->execute()) {
                foreach ($qb->execute() as $getData) {
                    $messageQueue = $this->repository->find($getData->getId());
                    $messageQueue->setSystemOverWrite(true);
                    $this->save($messageQueue);
                }

                $this->updateStatus($subject, 'search_completed');

                $this->searchComplete($subject->getCurrentReport());

                $report = $subject->getCurrentReport();

                if ($report->getAssignedTo()) {
                    $this->sendMessage($report, $report->getAssignedTo());
                    $this->sendMessage($report, $report->getCompany()->getTeam()->getTeamLeader());
                } else {
                    $this->sendMessage($report, $report->getCompany()->getTeam()->getTeamLeader());
                }

                echo "\n Sending Messages \n";
            }
        }
    }

    /**
     * @param $messageQueue
     */
    public function save($messageQueue)
    {
        $this->entityManager->persist($messageQueue);
        $this->entityManager->flush();
    }

    /**
     * @param $subject
     * @param $status
     */
    public function updateStatus($subject, $status)
    {
        if ($subject && $status) {
            $this->updateReportStatus($subject, $status);
            $this->updateSubjectStatus($subject, $status);
        }
    }

    /**
     * @param $subject
     * @param $status
     */
    public function updateReportStatus($subject, $status)
    {
        $this->entityManager->persist($subject);
        if ($subject->getCurrentReport()) {
            /** @var Report $report */
            $report = $subject->getCurrentReport();
            $report->setStatus($status);
            $this->entityManager->persist($report);
        }
        $this->entityManager->flush();
    }

    /**
     * @param $subject
     * @param $status
     */
    public function updateSubjectStatus($subject, $status)
    {
        $subject = $this->entityManager->getRepository(Subject::class)->find($subject);
        $subject->setStatus($status);
        $this->entityManager->flush();
    }

    /**
     * @param $report
     *
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Runtime
     * @throws Twig_Error_Syntax
     */
    public function searchComplete($report)
    {
        if ($report) {
            if ($report->getAssignedTo()) {
                $this->notificationSend($report, $report->getAssignedTo()->getEmail(), 'Search Completed');
            }

            if ($report->getCompany()->getTeam()) {
                $this->notificationSend($report, $report->getCompany()->getTeam()->getTeamLeader()->getEmail(), 'Search Completed');
            }
        }
    }

    /**
     * @param $report
     * @param $email
     * @param $notification
     *
     * @return string
     * @throws SendGrid\Mail\TypeException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function notificationSend($report, $email, $notification)
    {
        if ($this->parameterBag->get('DEV_MAIL') === "true") {
            $message = (new Swift_Message('Farosian Notification: ' . $notification))
                ->setFrom('support@farosian.com')
                ->setTo($email)
                ->setBody(
                    $this->twig->render(
                        'emails/notification_email.html.twig',
                        [
                            'subjectName'  => $this->nameCheck($report->getSubjectName()),
                            'notification' => $this->nameCheck($notification),
                            'request_type' => $this->cleanUpName($report->getRequestType()),
                            'report_type'  => $this->cleanUpName($report->getSubject()->getReportType()),
                            'company'      => $this->nameCheck($report->getCompany()->getName())
                        ]
                    ),
                    'text/html'
                );

            $emailCheck = $this->emailDuplicationCheck($report);
            if ($emailCheck === 0) {
                $this->emailTrackerRepository->addEmail($report);
                $this->mailer->send($message);
            }

        } else {
            $sendgridEmail = new Mail();
            $sendgridEmail->setFrom("support@farosian.com", "Farosian Support");
            $sendgridEmail->setSubject('Farosian Notification: ' . $notification);
            $email->addTo($email, "Farosian Support");
            $sendgridEmail->addContent('text/html',
                $this->twig->render(
                    'emails/notification_email.html.twig',
                    [
                        'subjectName'  => $this->nameCheck($report->getSubjectName()),
                        'notification' => $this->nameCheck($notification),
                        'request_type' => $this->cleanUpName($report->getRequestType()),
                        'report_type'  => $this->cleanUpName($report->getSubject()->getReportType()),
                        'company'      => $this->nameCheck($report->getCompany()->getName())
                    ]
                )
            );

            $sendgrid = new SendGrid(getenv('SENDGRID_API_KEY'));

            try {

                $emailCheck = $this->emailDuplicationCheck($report);
                if ($emailCheck === 0) {
                    $this->emailTrackerRepository->addEmail($report);
                    $sendgrid->send($sendgridEmail);
                }

            } catch (Exception $e) {
                return '';
            }
        }
    }

    /**
     * @param $name
     *
     * @return string
     */
    public function nameCheck($name)
    {
        if ($name) {
            return trim($name);
        } else {
            return 'N/A';
        }
    }

    /**
     * @param $name
     *
     * @return string
     */
    public function cleanUpName($name)
    {
        if ($name) {
            $name = str_replace('_', ' ', $name);
            $name = ucwords($name);

            return trim($name);
        } else {
            return 'N/A';
        }
    }

    /**
     * @param $report
     *
     * @return int
     */
    public function emailDuplicationCheck($report)
    {
        $qb = $this->repositoryEmailTracker->createQueryBuilder('p')
            ->andWhere('p.report = :report')
            ->setParameter('report', $report->getId())
            ->andWhere('p.user = :user')
            ->setParameter('user', $report->getUser()->getId())
            ->andWhere('p.status = :status')
            ->setParameter('status', $report->getStatus())
            ->getQuery();

        return count($qb->execute());
    }

    /**
     * @param $report
     * @param $sendto
     */
    private function sendMessage($report, $sendto)
    {
        $messageSystem = new MessageSystem();

        $messageSystem->setCompany($report->getCompany());
        $messageSystem->setSubject($report->getSubject());
        $messageSystem->setMessage($this->messages($report, $report->getStatus()));
        $messageSystem->setMessageHeader($this->messagesHeader($report, $report->getStatus()));
        $messageSystem->setMessageType($report->getRequestType());
        $messageSystem->setMessageRead(false);
        $messageSystem->setStatus($report->getStatus());
        $messageSystem->setUser($report->getUser());
        $messageSystem->setMessageFor($sendto);

        if ($report->getCompany()->getTeam()) {
            $messageSystem->setTeamLead($report->getCompany()->getTeam()->getTeamLeader());
        }

        $messageSystem->setUser($report->getUser());

        $this->entityManager->persist($messageSystem);
        $this->entityManager->flush();
    }

    /**
     * @param $report
     *
     * @param $status
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


}