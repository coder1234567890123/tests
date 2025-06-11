<?php declare(strict_types=1);

namespace App\Service\SearchPhrase\Token;

use App\Entity\Subject;

/**
 * Class CityToken
 *
 * @package App\Service\SearchPhrase\Token
 */
class CityToken implements TokenInterface
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
        if (
            $subject->getAddress() === null ||
            ($subject->getAddress() && $subject->getAddress()->getCity() == '')
        )
        {
            return null;
        }

        return $subject->getAddress()->getCity();
    }

    /**
     * Return the string to replace. e.g. "city"
     *
     * @return string
     */
    public function getToken(): string
    {
        return 'city';
    }
}