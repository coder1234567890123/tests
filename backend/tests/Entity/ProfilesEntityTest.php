<?php

namespace App\Tests\Util;

use PHPUnit\Framework\TestCase;
use App\Entity\Profile;
use App\Entity\Subject;


class ProfilesEntityTest extends TestCase
{
    public function testProfilesGetterAndSetter()
    {

        echo "\nProfilesEntityTest:  Profiles Entity\n";

        $profile = new Profile();
        $subject = new Subject();

        $profile->setSubject($subject);
        $this->assertEquals($profile->getSubject(), $subject);

        $profile->setPlatform('twitter');
        $this->assertEquals('twitter', $profile->getPlatform());

        $profile->setFirstName('Tom');
        $this->assertEquals('Tom', $profile->getFirstName());

        $profile->setLastName('Jones');
        $this->assertEquals('Jones', $profile->getLastName());

        $profile->setEmailAddress('test@example.com');
        $this->assertEquals('test@example.com', $profile->getEmailAddress());

        $profile->setPhone('08312345678');
        $this->assertEquals('08312345678', $profile->getPhone());

        $profile->setLink('http:\\example.com');
        $this->assertEquals('http:\\example.com', $profile->getLink());

        $profile->setPhrase('phrase');
        $this->assertEquals('phrase', $profile->getPhrase());

        $profile->setPriority(1);
        $this->assertEquals(1, $profile->getPriority());

        $profile->setValid(true);
        $this->assertEquals(true, $profile->isValid());
    }
}
