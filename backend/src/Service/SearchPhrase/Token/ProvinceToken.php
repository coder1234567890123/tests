<?php declare(strict_types=1);

namespace App\Service\SearchPhrase\Token;

use App\Entity\Subject;

/**
 * Class ProvinceToken
 *
 * @package App\Service\SearchPhrase\Token
 */
class ProvinceToken implements TokenInterface
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
        if ($subject->getProvince() === null) {
            return null;
        }

        return $subject->getProvince();
    }

    /**
     * Return the string to replace. e.g. "province"
     *
     * @return string
     */
    public function getToken(): string
    {
        return 'province';
    }
}