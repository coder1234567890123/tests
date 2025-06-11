<?php declare(strict_types=1);

namespace App\Service\SearchPhrase\Token;

use App\Entity\Employment;
use App\Entity\Subject;

/**
 * Class CompanyToken
 *
 * @package App\Service\SearchPhrase\Token
 */
class CompanyToken implements TokenInterface
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
        if ($subject->getEmployments() === null || $subject->getEmployments()->isEmpty()) {
            return null;
        }

        $data    = [];
        $results = $subject->getEmployments()->toArray();

        /** @var Employment $value */
        foreach ($results as $value) {
            $data[] = $value->getEmployer();
        }

        return '(' . implode(' || ', $data) . ')';
    }

    /**
     * Return the string to replace. e.g. "company"
     *
     * @return string
     */
    public function getToken(): string
    {
        return 'company';
    }
}