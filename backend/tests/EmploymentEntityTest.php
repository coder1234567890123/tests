<?php

namespace App\Tests\Util;

use App\Entity\Address;
use App\Entity\Employment;
use App\Entity\Country;
use PHPUnit\Framework\TestCase;

class EmploymentEntityTest extends TestCase
{
    public function testEmploymentGetterAndSetter()
    {
        $employment = new Employment();
        $address    = new Address();
        $country    = new Country();

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
    }
}
