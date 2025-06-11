<?php

namespace App\Tests\Util;

use App\Entity\Company;
use App\Entity\Country;
use PHPUnit\Framework\TestCase;
use App\Service\SearchPhrase\Token\CityToken;
use App\Service\SearchPhrase\Token\CompanyToken;
use App\Service\SearchPhrase\Token\CountryToken;
use App\Service\SearchPhrase\Token\EducationInstituteToken;
use App\Service\SearchPhrase\Token\EmailToken;
use App\Service\SearchPhrase\Token\FirstNameToken;
use App\Service\SearchPhrase\Token\LastNameToken;
use App\Service\SearchPhrase\Token\HandlesToken;
use App\Service\SearchPhrase\Token\PhoneToken;
use App\Service\SearchPhrase\Token\ProvinceToken;
use App\Entity\Subject;
use App\Entity\Address;

class TokenTest extends TestCase
{
    public function testGetterAndSetter()
    {
        $subject = new Subject();
        $company = new Company();
        $country = new Country();
        $address = new Address();

        $subject->setAddress($address);

        $cityToken = new CityToken();

        $this->assertEquals('city', $cityToken->getToken());

        $companyToken = new CompanyToken();
        $subject->setCompany($company);
        $this->assertEquals('company', $companyToken->getToken());

        $countryToken = new CountryToken();
        $subject->setCountry($country);
        $this->assertEquals('country', $countryToken->getToken());

        $educationInstituteToken = new EducationInstituteToken();
        $subject->setEducationInstitutes(["school", "matrix"]);
        $this->assertEquals('education_institute', $educationInstituteToken->getToken());

        $emailToken = new EmailToken();
        $subject->setPrimaryEmail('mail1@example.com');
        $this->assertEquals('email', $emailToken->getToken());

        $emailToken = new EmailToken();
        $subject->setSecondaryEmail('mail2@example.com');
        $this->assertEquals('email', $emailToken->getToken());

        $firstNameToken = new FirstNameToken();
        $subject->setFirstName('Tom');
        $this->assertEquals('first_name', $firstNameToken->getToken());

        $lastNameToken = new LastNameToken();
        $subject->setLastName('Jones');
        $this->assertEquals('last_name', $lastNameToken->getToken());

        $handlesToken = new HandlesToken();
        $subject->setHandles(["test", "array"]);
        $this->assertEquals('handles', $handlesToken->getToken());

        $phoneToken = new PhoneToken();
        $subject->setPrimaryMobile('083123456');
        $this->assertEquals('phone', $phoneToken->getToken());

        $phoneToken = new PhoneToken();
        $subject->setSecondaryMobile('083123456');
        $this->assertEquals('phone', $phoneToken->getToken());

        $provinceToken = new ProvinceToken();
        $subject->setProvince('Eastern Cape');
        $this->assertEquals('province', $provinceToken->getToken());

        $cityToken = new CityToken();
        $subject->getAddress()->setCity('East London');
        $this->assertEquals('city', $cityToken->getToken());
    }
}
