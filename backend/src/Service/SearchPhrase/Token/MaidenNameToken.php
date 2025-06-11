<?php declare(strict_types=1);

namespace App\Service\SearchPhrase\Token;

use App\Entity\Subject;

/**
 * Class MaidenNameToken
 *
 * @package App\Service\SearchPhrase\Token
 */
class MaidenNameToken implements TokenInterface
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
        if ($subject->getMaidenName() === null || $subject->getMaidenName() === '') {
            return null;
        }

        return $subject->getMaidenName();
    }

    /**
     * Return the string to replace. e.g. "last_name"
     *
     * @return string
     */
    public function getToken(): string
    {
        return 'maiden_name';
    }
}