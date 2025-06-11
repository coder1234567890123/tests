<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\Company;
use App\Entity\Report;
use App\Entity\Subject;
use App\Entity\User;
use App\Entity\UserTracking;
use App\Entity\EmailTracker;
use App\Exception\InvalidTrackingActionException;
use App\Repository\EmailTrackerRepository;
use App\Repository\UserTrackingRepository;
use App\Repository\UserRepository;
use App\Service\User\UserManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use SendGrid\Mail\Mail;
use SendGrid;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig_Error_Loader;
use Twig_Error_Runtime;
use Twig_Error_Syntax;

/**
 * Class EventTrackingService
 *
 * @package App\Service
 */
class EventTrackingService
{
    /**
     * @var Swift_Mailer
     */
    private $mailer;

    /**
     * @var UserTrackingRepository
     */
    private $repository;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;
    /**
     * @var ObjectRepository
     */
    private $repositoryUsers;
    /**
     * @var TokenInterface|null
     */
    private $userToken;

    /**
     * EventTrackingService constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param UserTrackingRepository $repository
     * @param Swift_Mailer           $mailer
     * @param Environment            $twig
     * @param ParameterBagInterface  $parameterBag
     * @param UserManager            $manager
     * @param TokenStorageInterface  $token
     * @param EmailTrackerRepository $emailTrackerRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        UserTrackingRepository $repository,
        Swift_Mailer $mailer,
        Environment $twig,
        ParameterBagInterface $parameterBag,
        UserManager $manager,
        TokenStorageInterface $token,
        EmailTrackerRepository $emailTrackerRepository
    )
    {
        $this->repository = $repository;
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->parameterBag = $parameterBag;
        $this->manager = $manager;
        $this->emailTrackerRepository = $emailTrackerRepository;

        $this->repositoryUsers = $entityManager->getRepository(User::class);
        $this->repositoryEmailTracker = $entityManager->getRepository(EmailTracker::class);
        $this->userToken = $token->getToken()->getUser();
    }

    /**
     * @param User $user
     *
     * @return JsonResponse
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Runtime
     * @throws Twig_Error_Syntax
     */
    public function sendMail(User $user)
    {
        if (!$this->manager->generateToken($user)) {
            return new JsonResponse(['message' => 'An unknown error occurred!'], 500);
        }

        if ($this->parameterBag->get('DEV_MAIL') === "true") {
            $message = (new Swift_Message('New User Registration'))
                ->setFrom('support@farosian.com')
                ->setTo($user->getEmail())
                ->setBody(
                    $this->twig->render(
                        'emails/registration_email.html.twig',
                        [
                            'resetUrl' => $this->parameterBag->get('RESET_URL'),
                            'user'     => $user
                        ]
                    ),
                    'text/html'
                );

            $this->mailer->send($message);
        } else {
            $email = new Mail();
            $email->setFrom("support@farosian.com", 'Farosian Support');
            $email->setSubject("Reset Password Email");
            $email->addTo($user->getEmail(), "Farosian Support");
            $email->addContent('text/html',
                $this->twig->render(
                    'emails/registration_email.html.twig',
                    [
                        'resetUrl' => $this->parameterBag->get('RESET_URL'),
                        'user'     => $user
                    ]
                )
            );
            $sendgrid = new SendGrid(getenv('SENDGRID_API_KEY'));
            try {
                $sendgrid->send($email);
            } catch (Exception $e) {
                return '';
            }
        }
    }

    /**
     * @param $report
     *
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Runtime
     * @throws Twig_Error_Syntax
     */
    public function sendMailMessages($report)
    {
        switch ($report->getStatus()) {
            case 'needs_approval':
                $this->needsApproval($report);
                break;
            case 'new_request':
                $this->newRequest($report);
                break;
            case 'search_completed':
                $this->searchComplete($report);
                break;
            case 'investigation_completed':
                $this->notificationSend($report, $report->getCompany()->getTeam()->getTeamLeader()->getEmail(), 'Investigation Completed');
                break;
            case 'completed':
                $this->notificationSend($report, $report->getUser()->getEmail(), 'Completed');
                break;
            case 'team_lead_approved':
                $this->teamLeadApproved($report);
                break;
        }
    }

    /**
     * @param $report
     *
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Runtime
     * @throws Twig_Error_Syntax
     */
    public function needsApproval($report)
    {
        if ($report) {
            $qb = $this->repositoryUsers->createQueryBuilder('p')
                ->andWhere('p.roles = :roles')
                ->setParameter('roles', '["ROLE_SUPER_ADMIN"]')
                ->getQuery();

            foreach ($qb->execute() as $getData) {
                $this->notificationSend($report, $getData->getEmail(), 'Needs Approval');
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
            $sendGridEmail = new Mail();
            $sendGridEmail->setFrom("support@farosian.com", 'Farosian Support');
            $sendGridEmail->setSubject('Farosian Notification: ' . $notification);
            $sendGridEmail->addTo($email, "Farosian Support");
            $sendGridEmail->addContent('text/html',
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

                    $sendgrid->send($sendGridEmail);
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
     *
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Runtime
     * @throws Twig_Error_Syntax
     */
    public function newRequest($report)
    {
        if ($report) {
            if ($report->getAssignedTo()) {
                $this->notificationSend($report, $report->getAssignedTo()->getEmail(), 'New Request');
            } else {
                $this->notificationSend($report, $report->getCompany()->getTeam()->getTeamLeader()->getEmail(), 'New Request');
            }
        }
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
     *
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Runtime
     * @throws Twig_Error_Syntax
     */
    public function teamLeadApproved($report)
    {
        if ($report) {
            $qb = $this->repositoryUsers->createQueryBuilder('p')
                ->andWhere('p.roles = :roles')
                ->setParameter('roles', '["ROLE_SUPER_ADMIN"]')
                ->getQuery();

            foreach ($qb->execute() as $getData) {
                $this->notificationSend($report, $getData->getEmail(), 'Team Lead Approved');
            }
        }
    }

    /**
     * FUNCTION TO TRACK USER ACTIONS
     *
     * @param string       $action
     * @param User         $user
     * @param string       $source
     * @param Subject|null $subject
     * @param Report|null  $report
     * @param Company|null $company
     *
     * @throws InvalidTrackingActionException
     * @throws Exception
     */
    public function track(string $action, User $user, string $source, Subject $subject = null, Report $report = null, Company $company = null)
    {
        $tracking = new UserTracking();
        $tracking->setUser($user);
        $tracking->setAction($action);
        $tracking->setSource($source);
        $company = $company ? $company : $this->getCompany($user, $subject);
        if ($subject) {
            $tracking->setSubject($subject);
        }
        if ($company) {
            $tracking->setCompany($company);
        }
        if ($report) {
            $tracking->setReportStatus($report->getStatus());
            $tracking->setReport($report);
        }

        $this->repository->save($tracking);
    }

    /**
     * @param User|null    $user
     * @param Subject|null $subject
     *
     * @return Company|null
     */
    private function getCompany(User $user = null, Subject $subject = null)
    {
        $company = null;
        if ($user && $user->getCompany()) {
            $company = $user->getCompany();
        }

        if ($subject && $subject->getCompany()) {
            $company = $subject->getCompany();
        }

        return $company;
    }
}