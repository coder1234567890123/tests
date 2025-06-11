<?php

namespace App\Tests\Util;

use App\Entity\Answer;
use App\Exception\AnswerOptionException;
use App\Exception\InvalidAnswerTypeException;
use App\Exception\InvalidPlatformException;
use App\Exception\InvalidReportTypeException;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use App\Entity\Question;

class QuestionEntityTest extends TestCase
{
    /**
     * @throws \App\Exception\InvalidPlatformException
     * @throws InvalidReportTypeException
     * @throws \App\Exception\InvalidAnswerTypeException
     * @throws AnswerOptionException
     */
    public function testQuestionGetterAndSetter()
    {
        echo "\nQuestionEntityTest:  Question Entity \n";

        $question = new Question();
        $answer = new Answer();
        $answers = new ArrayCollection();
        $answers->add($answer);

        $question->setQuestion('this is sample question');
        $this->assertEquals('this is sample question', $question->getQuestion());

        $question->setReportLabel('sample question');
        $this->assertEquals('sample question', $question->getReportLabel());

        $question->addAnswer($answer);
        $this->assertEquals($answers, $question->getAnswers());

        $question->clearAnswers();
        $this->assertEquals(new ArrayCollection(), $question->getAnswers());

        $question->setPlatform('facebook');
        $this->assertEquals('facebook', $question->getPlatform());

        $question->setReportTypes(['full', 'basic']);
        $this->assertEquals('full', $question->getReportTypes()[0]);

        $question->setAnswerType('text');
        $this->assertEquals('text', $question->getAnswerType());


        $question->setEnabled(true);
        $this->assertEquals(true, $question->isEnabled());

        $question->setOrderNumber(1);
        $this->assertEquals(1, $question->getOrderNumber());

        $question->setAnswerType(Question::ANSWER_TYPE_MULTIPLE);
        $question->setAnswerOptions(['res']);
        $this->assertEquals(['res'], $question->getAnswerOptions());

        $question->setAnswerScore(['23']);
        $this->assertEquals(['23'], $question->getAnswerScore());

        $this->assertEquals(5, count($question->getAllReportTypes()));

        $question->setEnabled(false);
        $this->assertEquals(false, $question->isEnabled());

        $question->setOrderNumber(3);
        $this->assertEquals(3, $question->getOrderNumber());

    }
}
