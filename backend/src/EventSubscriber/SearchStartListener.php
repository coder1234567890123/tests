<?php
/**
 * Created by PhpStorm.
 * User: kuda
 * Date: 8/12/19
 * Time: 2:36 PM
 */

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

class SearchStartListener implements EventSubscriberInterface
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
    public function guardSearchStart(GuardEvent $event)
    {
        /** @var \App\Entity\Subject $subject */
        $subject = $event->getSubject();
        /** @var Report $report */
        $report = $subject->getCurrentReport();


        if ($subject->getStatus() !== 'report_type_approved' && $report && $report->getRequestType() === 'rush') {
            $event->setBlocked('true');
        }

    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.report_status.guard.search_start' => ['guardSearchStart'],
        ];
    }
}