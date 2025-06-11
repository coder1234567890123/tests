<?php

namespace App\Security;

use App\Entity\Subject;
use App\Entity\Profile;
use App\Entity\User;
use http\Env\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Class SubjectVoter
 *
 * @package App\Security
 */
class SubjectVoter extends Voter
{

    /**
     * @var Security
     */
    private $security;

    /**
     * SubjectVoter constructor.
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
        // only vote on Subject or Profile objects
        if ($subject instanceof Subject || $subject instanceof Profile) {
            return true;
        }

        return false;
    }

    /**
     * @param string         $attribute
     * @param mixed          $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $loggedInUser = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$loggedInUser instanceof UserInterface) {
            return false;
        }

        // ROLE_SUPER_ADMIN can do anything! The power!
        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }

        // ... all the normal voter logic
        // check if its Subject or Profile
        if ($subject instanceof Subject) {
            /** @var Subject $subject */
            $subject = $subject;

            if ($this->security->isGranted('ROLE_ADMIN_USER') || $this->security->isGranted('ROLE_USER_MANAGER')) {
                if ($this->isSameCompany( $loggedInUser, $subject) ){
                    return true;
                }
            }

            if ($this->security->isGranted('ROLE_TEAM_LEAD') || $this->security->isGranted('ROLE_ANALYST')) {
                if ($loggedInUser->getCompany()) {
                    return false;
                }

                if ($this->security->isGranted('ROLE_TEAM_LEAD')) {

                    if($subject->getCompany()){
                        return $subject->getCompany()->getTeam()->getTeamLeader()->getId() === $loggedInUser->getId();
                    }
                }

                if ($this->security->isGranted('ROLE_ANALYST')) {
                    {
                        if($subject->getCompany()){
                            return $subject->getCompany()->getTeam()->getId() === $loggedInUser->getTeam()->getId();
                        }

                    }

                }
                return false;
            }

            if ($this->security->isGranted('ROLE_USER_STANDARD')) {
                // standard user should only manipulate subjects they created 
                return $this->isSameCompany($loggedInUser, $subject) && $loggedInUser->getId() === $subject->getCreatedBy()->getId();
            }

            return $this->isSameCompany($loggedInUser, $subject);
        } elseif ($subject instanceof Profile) {
            /** @var Profile $profile */
            $profile = $subject;

            return $this->isSameCompany($loggedInUser, $profile->getSubject());
        }
    }

    /**
     * @param Subject $subject      // subject being requested
     * @param User    $loggedInUser //logged in user performing action
     *
     * @return boolean
     */
    private function isSameCompany(User $loggedInUser, Subject $subject)
    {
        //user not super admin then check if subject belongs to same company
        return $loggedInUser->getCompany() === $subject->getCompany();
    }
}