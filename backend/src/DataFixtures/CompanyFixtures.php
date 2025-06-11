<?php
/**
 * Created by PhpStorm.
 * User: kuda
 * Date: 7/24/19
 * Time: 3:04 PM
 */

namespace App\DataFixtures;

use App\Entity\Company;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class CompanyFixtures extends Fixture implements DependentFixtureInterface
{
    public const COMPANY_REFERENCE = 'company';

    public function load(ObjectManager $manager)
    {
        $company = new Company();
        $company->setCreatedBy($this->getReference('super-user'));
        $company->setCountry($this->getReference('country-za'));
        $company->setName('Company Test');
        $company->setMobileNumber('0117654363');
        $company->setEmail('info@company.com');
        $company->setBrandingType('default');
        $company->setFooterLink('www.company.com');
        $company->setCity('Jozi');
        $company->setFaxNumber('0117654363');
        $company->setNote('Note');
        $company->setPdfPassword('1234');
        $company->setWebsite('www.company.com');
        $company->setProvince('Gauteng');
        $company->setVatNumber('123456789');
        $company->setRegistrationNumber('5764382397');
        $company->setStreet1('street1');
        $company->setStreet2('street2');
        $company->setTelNumber('0117654363');
        $company->setSuburb('Suburb');
        $company->setPostalCode('1234');
        $company->setContactFirstName( 'Name');
        $company->setContactLastName('Lastname');
        $company->setContactEmail('nanme@contact.com');
        $company->setContactTelephone('0116752633');
        $company->setAccountHolderFirstName('Name');
        $company->setAccountHolderLastName('Lastname');
        $company->setAccountHolderEmail('nanme@contact.com');
        $company->setAccountHolderPhone('0116752633');
        $company->setCompanyTypes('internal');

        $manager->persist($company);
        $manager->flush();
        $this->addReference(self::COMPANY_REFERENCE, $company);
    }

    public function getDependencies()
    {
        return array(
            UserFixtures::class,
            CountryFixtures::class,
        );
    }
}