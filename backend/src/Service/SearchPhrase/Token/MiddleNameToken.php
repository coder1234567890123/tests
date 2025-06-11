<?php declare(strict_types=1);

namespace App\Service\SearchPhrase\Token;

use App\Entity\Subject;

/**
 * Class MiddleNameToken
 *
 * @package App\Service\SearchPhrase\Token
 */
class MiddleNameToken implements TokenInterface
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
        if ($subject->getMiddleName() === null || $subject->getMiddleName() === '') {
            return null;
        }

        return $subject->getMiddleName();
    }

    /**
     * Return the string to replace. e.g. "last_name"
     *
     * @return string
     */
    public function getToken(): string
    {
        return 'middle_name';
    }
}