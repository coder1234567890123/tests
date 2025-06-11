<?php declare(strict_types=1);

namespace App\Exception;

use App\Entity\Question;

/**
 * Class AnswerOptionException
 *
 * @package App\Exception
 */
class AnswerOptionException extends \Exception
{
    /**
     * AnswerOptionException constructor.
     */
    public function __construct()
    {
        parent::__construct("Empty Answer options. You have selected 'multiple choice', please add the multiple choice options to choose from");
    }
}