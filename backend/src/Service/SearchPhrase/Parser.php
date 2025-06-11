<?php declare(strict_types=1);

namespace App\Service\SearchPhrase;

use App\Entity\Subject;
use App\Service\SearchPhrase\Exception\InvalidSyntaxException;
use App\Service\SearchPhrase\Exception\InvalidTokenException;
use App\Service\SearchPhrase\Token\TokenInterface;

/**
 * Class Parser
 *
 * @package App\Service\SearchPhrase
 */
class Parser
{
    /**
     * @var iterable
     */
    private $tokens;

    /**
     * Parser constructor.
     *
     * @param iterable $tokens
     */
    public function __construct(iterable $tokens)
    {
        $this->tokens = $tokens;
    }

    /**
     * @param Subject $subject
     * @param string  $searchPhrase
     *
     * @return mixed|string
     * @throws InvalidTokenException
     */
    public function replace(Subject $subject, string $searchPhrase)
    {
        // Run Replacements
        /** @var TokenInterface $token */
        foreach ($this->tokens as $token) {
            if (strstr($searchPhrase, "[{$token->getToken()}]") !== false) {
                if ($token->getReplacement($subject) === null) return null;
            } else {
                continue;
            }

            $searchPhrase = str_replace(
                "[{$token->getToken()}]",
                $token->getReplacement($subject),
                $searchPhrase
            );
        }

        // Check if all tokens have been replaced.
        $matches = [];
        preg_match_all('/\\[(?<!\\])(.*)\\]/uiU', $searchPhrase, $matches);
        if (!empty($matches[1])) {
            throw new InvalidTokenException(implode(",", $matches[1]));
        }

        return $searchPhrase;
    }

    /**
     * @param Subject $subject
     * @param string  $searchPhase
     *
     * @return bool
     * @throws InvalidSyntaxException
     * @throws InvalidTokenException
     */
    public function test(Subject $subject, string $searchPhase): bool
    {
        $phrase = $this->replace($subject, $searchPhase);

        // Test Brackets
        if (
            substr_count($phrase, "[") !== substr_count($phrase, "]") ||
            substr_count($phrase, "(") !== substr_count($phrase, ")")
        ) {
            throw new InvalidSyntaxException();
        }

        return true;
    }
}

