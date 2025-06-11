<?php declare(strict_types=1);

namespace App\Service\SearchPhrase\Token;

use App\Entity\Qualification;
use App\Entity\Subject;

/**
 * Class EducationInstituteToken
 *
 * @package App\Service\SearchPhrase\Token
 */
class EducationInstituteToken implements TokenInterface
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
        if ($subject->getQualifications() === null || $subject->getQualifications()->isEmpty()) {
            return null;
        }

        $data         = [];
        $results      = $subject->getQualifications()->toArray();

        /** @var Qualification $value */
        foreach ($results as $value) {
            $data[] = $value->getName();
        }

        return '(' . implode(' || ', $data) . ')';
    }

    /**
     * Return the string to replace. e.g. "education_institute"
     *
     * @return string
     */
    public function getToken(): string
    {
        return 'education_institute';
    }
}