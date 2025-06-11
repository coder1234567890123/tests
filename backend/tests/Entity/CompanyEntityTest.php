<?php

namespace App\Tests\Util;

use App\Entity\Team;
use App\Exception\InvalidBrandingTypeException;
use PHPUnit\Framework\TestCase;
use App\Entity\Company;
use App\Entity\User;
use App\Entity\Country;

class CompanyEntityTest extends TestCase
{
    /**
     * @throws \App\Exception\InvalidBrandingTypeException
     */
    public function testPhaseGetterAndSetter()
    {

        echo "\nCompanyEntityTest:  Company Entity \n";

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

        $company->setImageFooterLogo('2.jpg');
        $this->assertEquals('2.jpg', $company->getImageFooterLogo());

        $company->setPasswordSet(true);
        $this->assertEquals(true, $company->isPasswordSet());

        $teamStub = new Team();
        $teamStub->setTeamLeader($user);
        $company->setTeam($teamStub);
        $this->assertEquals($user, $company->getTeam()->getTeamLeader());
        
        $company->setBrandingType('default');
        $this->assertEquals('default', $company->getBrandingType());

        $this->expectException(InvalidBrandingTypeException::class);
        $company->setBrandingType('def');

        $company->setRegistrationNumber('123456');
        $this->assertEquals('123456', $company->getRegistrationNumber());

        $company->setVatNumber('1234');
        $this->assertEquals('1234', $company->getVatNumber());

        $company->setNote('note');
        $this->assertEquals('note', $company->getNote());

        $company->setMobileNumber('123456');
        $this->assertEquals('123456', $company->getMobileNumber());

        $company->setWebsite('website');
        $this->assertEquals('website', $company->getWebsite());

        $company->setEmail('email');
        $this->assertEquals('email', $company->getEmail());

        $company->setSuburb('sub');
        $this->assertEquals('sub', $company->getSuburb());

        $company->setPostalCode('1233');
        $this->assertEquals('1233', $company->getPostalCode());

        $company->setContactFirstName('name');
        $this->assertEquals('name', $company->getContactFirstName());

        $company->setContactLastName('last');
        $this->assertEquals('last', $company->getContactLastName());

        $company->setContactTelephone('123456');
        $this->assertEquals('123456', $company->getContactTelephone());

        $company->setContactEmail('conctemail');
        $this->assertEquals('conctemail', $company->getContactEmail());
        $this->assertEquals(null, $company->getImage());

        $company->setImageFooterLogo('footLogo');
        $this->assertEquals('footLogo', $company->getImageFooterLogo());

        $company->setImageFrontPage('front');
        $this->assertEquals('front', $company->getImageFrontPage());

        $company->setAccountHolderFirstName('account');
        $this->assertEquals('account', $company->getAccountHolderFirstName());

        $company->setAccountHolderLastName('lastAccount');
        $this->assertEquals('lastAccount', $company->getAccountHolderLastName());

        $company->setAccountHolderEmail('accountEmail');
        $this->assertEquals('accountEmail', $company->getAccountHolderEmail());

        $company->setAccountHolderPhone('12345');
        $this->assertEquals('12345', $company->getAccountHolderPhone());

        $company->setPdfPassword('password');
        $this->assertEquals('password', $company->getPdfPassword());

        $company->setThemeColor('red');
        $this->assertEquals('red', $company->getThemeColor());

        $company->setFooterLink('link');
        $this->assertEquals('link', $company->getFooterLink());

        $company->setDisclaimer('this is some text');
        $this->assertEquals('this is some text', $company->getDisclaimer());

        $company->setUseDisclaimer(true);
        $this->assertEquals(true, $company->isUseDisclaimer());

        $company->setCoverLogo('1.jpg');
        $this->assertEquals('1.jpg', $company->getCoverLogo());

        $company->setAllowTrait(true);
        $this->assertEquals(true, $company->isAllowTrait());

    }
}
