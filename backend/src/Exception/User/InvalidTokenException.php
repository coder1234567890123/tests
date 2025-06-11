<?php declare(strict_types=1);

namespace App\Exception\User;

/**
 * Class InvalidTokenException
 *
 * @package App\Exception\User
 */
class InvalidTokenException extends \Exception
{
    /**
     * InvalidTokenException constructor.
     */
    public function __construct()
    {
        parent::__construct("This token is invalid!");
    }
}