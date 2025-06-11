<?php

namespace App\Tests\Util;

use PHPUnit\Framework\TestCase;
use App\Entity\Phrase;
use App\Entity\User;

class PhraseEntityTest extends TestCase
{
    public function testPhaseGetterAndSetter()
    {
        $phrase = new Phrase();

        $user = new User();
        $phrase->setCreatedBy($user);

        $this->assertEquals($phrase->getCreatedBy(), $user);

        $phrase->setPhrase('TestPhrase');
        $this->assertEquals('TestPhrase', $phrase->getPhrase());

        $phrase->setSearchType('TestSearch');
        $this->assertEquals('TestSearch', $phrase->getSearchType());

        $phrase->setEnable(true);
        $this->assertEquals(true, $phrase->isEnabled());

        $phrase->setArchived(true);
        $this->assertEquals(true, $phrase->isArchived());

        $phrase->setPriority(1);
        $this->assertEquals(1, $phrase->getPriority());
    }
}
