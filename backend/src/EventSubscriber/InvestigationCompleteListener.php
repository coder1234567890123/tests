<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Report;
use App\Entity\Subject;
use App\Repository\QuestionRepository;
use App\Repository\AnswerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\TransitionBlocker;

class InvestigationCompleteListener implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var TokenStorageInterface
     */
    private $userToken;

    /**
     * InvestigationCompleteListener constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param TokenStorageInterface $userToken
     */
    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $userToken)
    {
        $this->entityManager = $entityManager;
        $this->userToken = $userToken;
    }

    /** @var GuardEvent $event */
    public function guardComplete(GuardEvent $event)
    {
        /** @var \App\Entity\Subject $subject */
        $subject = $event->getSubject();

        if ($subject->getStatus() === 'under_investigation' || $subject->getStatus() === 'team_lead_approved'
            || $subject->getStatus() === 'investigation_completed') {    
                
            $questionRepository = new QuestionRepository($this->entityManager, $this->userToken);
            $answerRepository = new AnswerRepository($this->entityManager, $this->userToken);

            $questions = $questionRepository->getByReportType($subject->getReportType());
            /** @var Report $report */
            $report = $subject->getCurrentReport();

            // check if all investigation questions have been answered
            if (count($questions) > 0 && $report) {
                foreach ($questions as $question) {
                    $answer = $answerRepository->findBySubject($subject->getId(), $question->getId(), $report->getId());
                    if (count($answer) === 0) {
                        $event->setBlocked('true');
                        break;
                    }
                }
            }
        } else {
            $event->setBlocked('true');
        }

    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.report_status.guard.investigation_complete' => ['guardComplete'],
            'workflow.report_status.guard.approve_team' => ['guardComplete'],
            'workflow.report_status.guard.complete' => ['guardComplete'],
        ];
    }
}