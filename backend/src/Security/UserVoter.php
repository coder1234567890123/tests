<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Class UserVoter
 * 
 * @package App\Security
 */
class UserVoter extends Voter
{

    // Roles
    const COMPANY_ADMIN = 'ROLE_ADMIN_USER';
    const SUPER = 'ROLE_SUPER_ADMIN';
    /**
     * @var Security
     */
    private $security;

    /**
     * UserVoter constructor.
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
        return $subject instanceof \App\Entity\User; // only vote on User objects inside this voter
    }

    /**
     * @param string $attribute
     * @param object $subject
     * @param TokenInterface $token
     *
     * @return boolean
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
//        dd($attribute);
        $loggedInUser = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$loggedInUser instanceof UserInterface) {
            return false;
        }

        // you know $subject is a User object, thanks to supports
        /** @var User $user */
        $user = $subject;

        // if the user is super admin, allow access to all ROLEs
        // ROLE_SUPER_ADMIN can do anything! The power!
        if ($this->security->isGranted(self::SUPER)) {
            return true;
        }

        // COMPANY_ADMIN admin can create/update company users
        if ($this->security->isGranted(self::COMPANY_ADMIN)) {
            return $this->isSameCompany($loggedInUser, $user);
        }

        if ($this->security->isGranted('ROLE_USER_MANAGER')) {
            return $this->isSameCompany($loggedInUser, $user) && $user->hasRole('ROLE_USER_STANDARD');
        }

        if ($this->security->isGranted('ROLE_TEAM_LEAD')) {
            return $user->getTeam() && $loggedInUser->getId() === $user->getTeam()->getTeamLeader()->getId();
        }

        if ($loggedInUser->getId() === $user->getId()){
            return true;
        }

        return false;
    }

    /**
     * @param User $user // user being requested
     * @param User $loggedInUser //logged in user performing action
     *
     * @return boolean
     */
    private function isSameCompany(User $loggedInUser, User $user)
    {
        //user not super admin then check if users belong to same company
        return $loggedInUser->getCompanyId() === $user->getCompanyId();
    }
}