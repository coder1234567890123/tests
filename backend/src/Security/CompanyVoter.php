<?php

namespace App\Security;

use App\Entity\Company;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Class CompanyVoter
 * 
 * @package App\Security
 */
class CompanyVoter extends Voter
{

    // Roles
    const COMPANY_ADMIN = 'ROLE_ADMIN_USER';
    const SUPER = 'ROLE_SUPER_ADMIN';

    /**
     * @var Security
     */
    private $security;

    /**
     * CompanyVoter constructor.
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
        
        return $subject instanceof \App\Entity\Company; // only vote on Company objects inside this voter
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
        $loggedInUser = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$loggedInUser instanceof UserInterface) {
            return false;
        }

        // you know $subject is a User object, thanks to supports
        /** @var Company $company */
        $company = $subject;

        // if the user is super admin, allow access to all ROLEs
        // ROLE_SUPER_ADMIN can do anything! The power!
        if ($this->security->isGranted(self::SUPER)) {
            return true;
        }

        // COMPANY_ADMIN admin can update company details
        if ($this->security->isGranted(self::COMPANY_ADMIN)) {
            $this->isSameCompany($loggedInUser, $company);
        }

        if ($this->security->isGranted('ROLE_TEAM_LEAD')) {
            return $company->getTeam() && $company->getTeam()->getTeamLeader()->getId() === $loggedInUser->getId();
        }

        if ($this->security->isGranted('ROLE_ANALYST')) {
            return $company->getTeam() && $company->getTeam()->getId() === $loggedInUser->getTeam()->getId();
        }

        return false;
    }

    /**
     * @param Company $company // user being requested
     * @param User $loggedInUser //logged in user performing action
     *
     * @return boolean
     */
    private function isSameCompany(User $loggedInUser, Company $company)
    {
        //user not super admin then check if users belong to same company
        return $loggedInUser->getCompanyId() === $company->getId();
    }
}