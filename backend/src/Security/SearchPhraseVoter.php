<?php

namespace App\Security;

use App\Entity\User;
use App\Entity\Phrase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Class SearchPhraseVoter
 * 
 * @package App\Security
 */
class SearchPhraseVoter extends Voter
{
    // Roles
    const SUPER = 'ROLE_SUPER_ADMIN';
    
    /**
     * @var Security
     */
    private $security;

    /**
     * SearchPhraseVoter constructor.
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
        return $subject instanceof \App\Entity\Phrase; // only vote on Phrase objects inside this voter
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

        // you know $subject is a Phrase object, thanks to supports
        /** @var Phrase $phrase */
        $phrase = $subject;

        // if the user is super admin, allow access to all ROLEs
        // ROLE_SUPER_ADMIN can do anything! The power!
        if ($this->security->isGranted(self::SUPER)) {
            return true;
        }

        return $this->isSameCompany($loggedInUser, $phrase->getCreatedBy());
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