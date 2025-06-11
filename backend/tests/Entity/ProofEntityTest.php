<?php

namespace App\Tests\Util;

use App\Entity\ProofStorage;
use PHPUnit\Framework\TestCase;
use App\Entity\Proof;
use App\Entity\Answer;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class ProofEntityTest extends TestCase
{
    public function testProofGetterAndSetter()
    {
        echo "\nProofEntityTest:  Proof Entity\n";

        $proof = new Proof();
        $proofStorage = new ProofStorage();
        $answer = new Answer();

        $proof->setProofStorage($proofStorage);
        $this->assertEquals($proofStorage, $proof->getProofStorage());

        $proof->setAnswer($answer);
        $this->assertEquals($proof->getAnswer(), $answer);

        $proof->setEnabled(true);
        $this->assertEquals(true, $proof->isEnabled());

        $proof->setTrait(true);
        $this->assertEquals(true, $proof->isTrait());
    }
}
