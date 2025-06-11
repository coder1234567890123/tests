<?php declare(strict_types=1);

namespace App\Service\SearchPhrase\Token;

use App\Entity\Subject;

/**
 * Class FirstNameToken
 *
 * @package App\Service\SearchPhrase\Token
 */
class FirstNameToken implements TokenInterface
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
        if ($subject->getFirstName() === null) {
            return null;
        }

        return $subject->getFirstName();
    }

    /**
     * Return the string to replace. e.g. "first_name"
     *
     * @return string
     */
    public function getToken(): string
    {
        return 'first_name';
    }
}