<?php declare(strict_types=1);

namespace App\Service\SearchPhrase\Exception;

/**
 * Class InvalidTokenException
 *
 * @package App\Service\SearchPhrase\Exception
 */
class InvalidTokenException extends \Exception
{
    /**
     * InvalidTokenException constructor.
     *
     * @param string $token
     */
    public function __construct(string $token)
    {
        parent::__construct("Invalid token: $token");
    }
}