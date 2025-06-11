<?php declare(strict_types=1);

namespace App\Service\SearchPhrase\Token;

use App\Entity\Subject;

/**
 * Class DOBToken
 *
 * @package App\Service\SearchPhrase\Token
 */
class DOBToken implements TokenInterface
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
        if ($subject->getDateOfBirth() === null) {
            return null;
        }

        return $subject->getDateOfBirth()->format('Y-m-d');
    }

    /**
     * Return the string to replace. e.g. "last_name"
     *
     * @return string
     */
    public function getToken(): string
    {
        return 'date_of_birth';
    }
}