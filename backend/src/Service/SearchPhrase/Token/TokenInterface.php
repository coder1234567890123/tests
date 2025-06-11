<?php declare(strict_types=1);

namespace App\Service\SearchPhrase\Token;

use App\Entity\Subject;

/**
 * Interface TokenInterface
 *
 * @package App\Service\SearchPhrase\Token
 */
interface TokenInterface
{
    /**
     * Return the replacement value.
     *
     * @param Subject $subject
     *
     * @return string|null
     */
    public function getReplacement(Subject $subject): ?string;

    /**
     * Return the string to replace. e.g. "first_name"
     *
     * @return string
     */
    public function getToken(): string;
}