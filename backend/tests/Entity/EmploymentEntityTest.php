<?php

namespace App\Tests\Util;

use App\Entity\Address;
use App\Entity\Employment;
use App\Entity\Country;
use App\Entity\Subject;
use PHPUnit\Framework\TestCase;

class EmploymentEntityTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testEmploymentGetterAndSetter()
    {

        echo "\nEmploymentEntityTest:  Employment Entity \n";

        $employment = new Employment();
        $address    = new Address();
        $country    = new Country();
        $subject    = new Subject();

        $employment->setEmployer('Bank');
        $this->assertEquals('Bank', $employment->getEmployer());

        $date_test = new \DateTimeImmutable('2011-01-01');

        $employment->setStartDate($date_test);
        $this->assertEquals($date_test, $employment->getStartDate());

        $employment->setEndDate($date_test);
        $this->assertEquals($date_test, $employment->getEndDate());

        $employment->setAddress($address);
        $this->assertEquals($employment->getAddress(), $address);

        $employment->setProvince('Eastern Cape');
        $this->assertEquals('Eastern Cape', $employment->getProvince());

        $employment->setCountry($country);
        $this->assertEquals($employment->getCountry(), $country);

        $country->setName('SA');
        $this->assertEquals($employment->getCountryName(), 'SA');

        $employment->setSubject($subject);
        $this->assertEquals($employment->getSubject(), $subject);

        $subject->setFirstName('test');
        $this->assertEquals($employment->getSubjectName(), 'test');

        $employment->setJobTitle('title');
        $this->assertEquals('title', $employment->getJobTitle());
    }
}
