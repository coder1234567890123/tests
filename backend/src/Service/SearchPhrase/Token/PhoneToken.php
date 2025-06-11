<?php declare(strict_types=1);

namespace App\Service\SearchPhrase\Token;

use App\Entity\Subject;

/**
 * Class PhoneToken
 *
 * @package App\Service\SearchPhrase\Token
 */
class PhoneToken implements TokenInterface
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
        $secondaryMobile = trim($subject->getSecondaryMobile());

        if ($subject->getPrimaryMobile() === null) {
            return null;
        }

        if ($secondaryMobile === '') {
            return $subject->getPrimaryMobile();
        }

        $joinMobileNumbers = [$subject->getPrimaryMobile(), $subject->getSecondaryMobile()];
        return '(' . implode(' || ', $joinMobileNumbers) . ')';
    }

    /**
     * Return the string to replace. e.g. "phone"
     *
     * @return string
     */
    public function getToken(): string
    {
        return 'phone';
    }
}