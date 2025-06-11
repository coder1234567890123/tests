<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Subject;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\TransitionBlocker;

class SubjectValidateProfileListener implements EventSubscriberInterface
{
    /** @var GuardEvent $event */
    public function guardValidate(GuardEvent $event)
    {
        /** @var \App\Entity\Subject $subject */
        $subject = $event->getSubject();

        $validated = 0;

        // check if there is at least 1 validated profile
        if (\count($subject->getProfiles()) > 0) {
            foreach ($subject->getProfiles() as $profile) {
                if ($profile->isValid()) {
                    $validated++;
                }
            }
        }

        if ($validated < 1) {
            $event->setBlocked('true');
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.report_status.guard.valid' => ['guardValidate'],
        ];
    }
}