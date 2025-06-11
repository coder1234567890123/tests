<?php declare(strict_types=1);

namespace App\Service\SearchPhrase\Token;

use App\Entity\Subject;

/**
 * Class NicknameToken
 *
 * @package App\Service\SearchPhrase\Token
 */
class NicknameToken implements TokenInterface
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
        if ($subject->getNickname() === null || $subject->getNickname() === '') {
            return null;
        }

        return $subject->getNickname();
    }

    /**
     * Return the string to replace. e.g. "last_name"
     *
     * @return string
     */
    public function getToken(): string
    {
        return 'nickname';
    }
}