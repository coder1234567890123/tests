<?php declare(strict_types=1);

namespace App\Service\SearchPhrase\Token;

use App\Entity\Subject;

/**
 * Class EmailToken
 *
 * @package App\Service\SearchPhrase\Token
 */
class EmailToken implements TokenInterface
{
    /**
     * Return the replacement value.
     *
     * @param Subject $subject
     *
     * @return string | null
     */
    public function getReplacement(Subject $subject): ?string
    {
        $secondaryEmail = trim($subject->getSecondaryEmail());

        if ($subject->getPrimaryEmail() === null) {
            return null;
        }

        if ($secondaryEmail === '') {
            return $subject->getPrimaryEmail();
        }

        $joinEmails = [$subject->getPrimaryEmail(), $subject->getSecondaryEmail()];
        return '(' . implode(' || ', $joinEmails) . ')';
    }

    /**
     * Return the string to replace. e.g. "email"
     *
     * @return string
     */
    public function getToken(): string
    {
        return 'email';
    }
}