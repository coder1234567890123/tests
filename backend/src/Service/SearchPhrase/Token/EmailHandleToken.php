<?php declare(strict_types=1);

namespace App\Service\SearchPhrase\Token;

use App\Entity\Subject;

/**
 * Class EmailHandleToken
 *
 * @package App\Service\SearchPhrase\Token
 */
class EmailHandleToken implements TokenInterface
{
    /**
     * Return the replacement value.
     *
     * @param Subject $subject
     *
     * @return string|null
     */
    public function getReplacement(Subject $subject): ?string
    {
        $secondaryEmail = trim($subject->getSecondaryEmail());

        if ($subject->getPrimaryEmail() === null) {
            return null;
        }

        if ($secondaryEmail === '') {
            return $this->getEmailHandle($subject->getPrimaryEmail());
        }

        $joinEmails = [
            $this->getEmailHandle($subject->getPrimaryEmail()),
            $this->getEmailHandle($subject->getSecondaryEmail())
        ];
        return '(' . implode(' || ', $joinEmails) . ')';
    }

    /**
     * @param string $email
     *
     * @return string
     */
    private function getEmailHandle(string $email): string
    {
        $parts = explode('@', $email);

        return $parts[0];
    }

    /**
     * Return the string to replace. e.g. "city"
     *
     * @return string
     */
    public function getToken(): string
    {
        return 'email_handle';
    }
}