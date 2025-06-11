<?php

namespace App\Tests\Util;

use PHPUnit\Framework\TestCase;
use App\Entity\Company;
use App\Entity\User;
use App\Entity\Country;

class CompanyEnityTest extends TestCase
{
    public function testPhaseGetterAndSetter()
    {
        $company = new Company();

        $user = new User();
        $company->setCreatedBy($user);

        $this->assertEquals($company->getCreatedBy(), $user);

        $company->setName('CompanyName');
        $this->assertEquals('CompanyName', $company->getName());

        $company->setTelNumber('123456');
        $this->assertEquals('123456', $company->getTelNumber());

        $company->setFaxNumber('123456');
        $this->assertEquals('123456', $company->getFaxNumber());

        $company->setStreet1('abc street 123');
        $this->assertEquals('abc street 123', $company->getStreet1());

        $company->setStreet2('abc street 123');
        $this->assertEquals('abc street 123', $company->getStreet2());

        $company->setCity('Pretoria');
        $this->assertEquals('Pretoria', $company->getCity());

        $countryStub = new Country();
        $countryStub->setName('South Africa');
        $company->setCountry($countryStub);
        $this->assertEquals('South Africa', $company->getCountry()->getName());

        $company->setProvince('Eastern Cape');
        $this->assertEquals('Eastern Cape', $company->getProvince());

        $company->setEnabled(true);
        $this->assertEquals(true, $company->isEnabled());

        $company->setArchived(true);
        $this->assertEquals(true, $company->isArchived());

        $company->setImageFile('1.jpg');
        $this->assertEquals('1.jpg', $company->getImageFile());
    }
}
