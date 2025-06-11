<?php declare(strict_types=1);

namespace App\Service\SearchPhrase\Token;

use App\Entity\Subject;

/**
 * Class CountryToken
 *
 * @package App\Service\SearchPhrase\Token
 */
class CountryToken implements TokenInterface
{
    /**
     * Return the replacement value.
     *
     * @param Subject $subject
     *
     * @return string|null
     */
    public function getReplacement(Subject $subject): ?string
    {
        if ($subject->getCountry() === null) {
            return null;
        }

        return $subject->getCountryName();
    }

    /**
     * Return the string to replace. e.g. "country"
     *
     * @return string
     */
    public function getToken(): string
    {
        return 'country';
    }
}