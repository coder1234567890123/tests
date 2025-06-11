<?php

namespace App\Tests\Util;

use PHPUnit\Framework\TestCase;
use App\Entity\Country;
use App\Entity\User;
use App\Entity\Subject;
use App\Entity\Company;

class SubjectEntityTest extends TestCase
{
    public function testSubjectGetterAndSetter()
    {
        $subject = new Subject();
        $user = new User();
        $company = new Company();
        $country = new Country();

        $subject->setCreatedBy($user);
        $this->assertEquals($subject->getCreatedBy(), $user);

        $subject->setCountry($country);
        $this->assertEquals($subject->getCountry(), $country);

        $subject->setCompany($company);
        $this->assertEquals($subject->getCompany(), $company);

        $subject->setIdentification('123456789');
        $this->assertEquals('123456789', $subject->getIdentification());

        $subject->setFirstName('bob');
        $this->assertEquals('bob', $subject->getFirstName());

        $subject->setMiddleName('bob');
        $this->assertEquals('bob', $subject->getMiddleName());

        $subject->setLastName('bob');
        $this->assertEquals('bob', $subject->getLastName());

        $subject->setMaidenName('bob');
        $this->assertEquals('bob', $subject->getMaidenName());

        $subject->setNickname('bob');
        $this->assertEquals('bob', $subject->getNickname());

        $subject->setHandles(["test","array"]);
        $this->assertEquals(["test","array"], $subject->getHandles());

        $subject->setPrimaryEmail('test@example.com');
        $this->assertEquals('test@example.com', $subject->getPrimaryEmail());

        $subject->setSecondaryEmail('test@example.com');
        $this->assertEquals('test@example.com', $subject->getSecondaryEmail());

        $subject->setEducationInstitutes(["test","array"]);
        $this->assertEquals(["test","array"], $subject->getEducationInstitutes());

        $subject->setProvince('Eastern Cape');
        $this->assertEquals('Eastern Cape', $subject->getProvince());

        $subject->setEnabled(true);
        $this->assertEquals(true, $subject->isEnabled());

        $subject->setImageFile('1.jpg');
        $this->assertEquals('1.jpg', $subject->getImageFile());

    }
}
