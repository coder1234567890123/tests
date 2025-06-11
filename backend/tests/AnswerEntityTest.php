<?php

namespace App\Tests\Util;

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
    public function testReportSectionGetterAndSetter()
    {
        $proof = new Proof();
        $question = new Question();
        $subject = new Subject();
        $user = new User();
        $answer = new Answer();
        $proofs = new ArrayCollection();
        $proofs->add($proof);

        $answer->setAnswer('answer');
        $this->assertEquals('answer', $answer->getAnswer());

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

        $answer->setSkipped(true);
        $this->assertEquals(true, $answer->isSkipped());

        $answer->setNotApplicable(true);
        $this->assertEquals(true, $answer->isNotApplicable());

    }
}
