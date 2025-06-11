<?php

namespace App\Security;

use App\Entity\Report;
use App\Entity\Subject;
use App\Entity\Team;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Class InvestigationVoter
 * 
 * @package App\Security
 */
class InvestigationVoter extends Voter
{
    // Roles
    const SUPER = 'ROLE_SUPER_ADMIN';
    
    /**
     * @var Security
     */
    private $security;

    /**
     * InvestigationVoter constructor.
     *
     * @param Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @param string $attribute
     * @param object $subject
     *
     * @return boolean
     */
    protected function supports($attribute, $subject)
    {
        // only vote on Report objects inside this voter
        return $subject instanceof \App\Entity\Report;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {

        //dd($attribute);
        /** @var User $loggedInUser */
        $loggedInUser = $token->getUser();
        /** @var Report $report */
        $report = $subject;

        // if the user is anonymous, do not grant access
        if (!$loggedInUser instanceof UserInterface) {
            return false;
        }

        // ROLE_SUPER_ADMIN can do anything! The power!
        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }

        if ($this->security->isGranted('ROLE_TEAM_LEAD') || $this->security->isGranted('ROLE_ANALYST')) {
            /** @var Team $team */
            $team = $report->getSubject()->getCompany()->getTeam();
            if($loggedInUser->hasRole('ROLE_TEAM_LEAD') && $team->getTeamLeader()->getId() === $loggedInUser->getId()){
                return true; // see all your reports
            } elseif($loggedInUser->hasRole('ROLE_ANALYST') && $report->getAssignedTo()->getId() === $loggedInUser->getId()){
                return true; // see reports assigned to you
            }

            return false;
        }

        if (($this->security->isGranted('ROLE_USER_MANAGER') 
            || (($this->security->isGranted('ROLE_ADMIN_USER'))
                && $this->isSameCompany($loggedInUser, $report->getSubject())))) {
            return true; //see reports/queues of your company
        }

        if ($this->security->isGranted('ROLE_USER_STANDARD' && $this->isSameCompany($loggedInUser, $report->getSubject()))) {
            return $report->getUser()->getId() === $loggedInUser->getId(); //see reports/queues you initiated
        }

        // ... all the normal voter logic
        return false;
    }

    /**
     * @param Subject $subject // subject being requested
     * @param User $loggedInUser //logged in user performing action
     *
     * @return boolean
     */
    private function isSameCompany(User $loggedInUser, Subject $subject)
    {
        //user not super admin then check if subject belongs to same company
        return $loggedInUser->getCompany() === $subject->getCompany();
    }
}