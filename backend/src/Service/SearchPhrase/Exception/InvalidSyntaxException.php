<?php declare(strict_types=1);

namespace App\Service\SearchPhrase\Exception;

/**
 * Class InvalidSyntaxException
 *
 * @package App\Service\SearchPhrase\Exception
 */
class InvalidSyntaxException extends \Exception
{
    /**
     * InvalidSyntaxException constructor.
     */
    public function __construct()
    {
        parent::__construct("Invalid syntax! Please double check the search phrase.");
    }
}