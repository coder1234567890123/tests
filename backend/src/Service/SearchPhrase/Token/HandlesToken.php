<?php declare(strict_types=1);

namespace App\Service\SearchPhrase\Token;

use App\Entity\Subject;

/**
 * Class HandlesToken
 *
 * @package App\Service\SearchPhrase\Token
 */
class HandlesToken implements TokenInterface
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
        if ($subject->getHandles() === null || empty($subject->getHandles())) {
            return null;
        }

        return '(' . implode(' || ', $subject->getHandles()) . ')';
    }

    /**
     * Return the string to replace. e.g. "handles"
     *
     * @return string
     */
    public function getToken(): string
    {
        return 'handles';
    }
}