<?php

namespace App\Tests\Util;

use PHPUnit\Framework\TestCase;
use App\Entity\Proof;
use App\Entity\Answer;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class ProofEntityTest extends TestCase
{
    public function testReportSectionGetterAndSetter()
    {
        $proof = new Proof();
        $answer = new Answer();

        $proof->setUrl('http:/string');
        $this->assertEquals('http:/string', $proof->getUrl());

        $proof->setAnswer($answer);
        $this->assertEquals($proof->getAnswer(), $answer);

        $proof->setEnabled(true);
        $this->assertEquals(true, $proof->isEnabled());

    }
}
