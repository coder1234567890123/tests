<?php declare(strict_types=1);

namespace App\Service\SearchPhrase\Token;

use App\Entity\Subject;

/**
 * Class LastNameToken
 *
 * @package App\Service\SearchPhrase\Token
 */
class LastNameToken implements TokenInterface
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
        if ($subject->getLastName() === null) {
            return null;
        }

        return $subject->getLastName();
    }

    /**
     * Return the string to replace. e.g. "last_name"
     *
     * @return string
     */
    public function getToken(): string
    {
        return 'last_name';
    }
}