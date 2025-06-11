<?php declare(strict_types=1);

namespace App\Exception;

use App\Entity\Question;

/**
 * Class InvalidAnswerTypeException
 *
 * @package App\Exception
 */
class InvalidAnswerTypeException extends \Exception
{
    /**
     * InvalidAnswerTypeException constructor.
     */
    public function __construct()
    {
        $validAnswerTypes = implode(', ', Question::ANSWER_TYPES);
        parent::__construct("Invalid answer type specified. Valid answer types: '$validAnswerTypes'");
    }
}