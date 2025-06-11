<?php

namespace App\Tests\Util;

use App\Entity\Comment;
use App\Entity\Report;
use PHPUnit\Framework\TestCase;
use App\Entity\Proof;
use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\User;
use App\Entity\Subject;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class AnswerEntityTest extends TestCase
{
    public function testAnswerGetterAndSetter()
    {

        echo "\nAnswerEntityTest:  Answer Entity \n";

        $report = new Report();
        $proof = new Proof();
        $question = new Question();
        $subject = new Subject();
        $user = new User();
        $answer = new Answer();
        $proofs = new ArrayCollection();
        $proofs->add($proof);

        $answer->setReport($report);
        $this->assertEquals($report, $answer->getReport());

        $answer->setAnswer('answer');
        $this->assertEquals('answer', $answer->getAnswer());

        $answer->setScore('12');
        $this->assertEquals('12', $answer->getScore());

        $answer->setDefaultName('bob');
        $this->assertEquals('bob', $answer->getDefaultName());

        $answer->addProof($proof);
        $this->assertEquals($answer->getProofs(), $proofs);

        $answer->setQuestion($question);
        $this->assertEquals($answer->getQuestion(), $question);

        $answer->setSubject($subject);
        $this->assertEquals($answer->getSubject(), $subject);

        $answer->setUser($user);
        $this->assertEquals($answer->getUser(), $user);

        $answer->setEnabled(true);
        $this->assertEquals(true, $answer->isEnabled());

        $answer->setNotApplicable(true);
        $this->assertEquals(true, $answer->isNotApplicable());

        $answer->setLabelAnswer('bob');
        $this->assertEquals('bob', $answer->getLabelAnswer());

        $answer->setSliderValue('12');
        $this->assertEquals('12', $answer->getSliderValue());

        $answer->setPlatform('12');
        $this->assertEquals('12', $answer->getPlatform());
    }
}
