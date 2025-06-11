<?php
/**
 * Created by PhpStorm.
 * User: kuda
 * Date: 7/24/19
 * Time: 3:04 PM
 */

namespace App\DataFixtures;

use App\Entity\Address;
use App\Entity\Subject;
//use App\Entity\Address;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class SubjectFixtures extends Fixture implements DependentFixtureInterface
{
    public const SUBJECT_REFERENCE = 'subject';

    /**
     * @param ObjectManager $manager
     * @throws \App\Exception\InvalidReportTypeException
     */
    public function load(ObjectManager $manager)
    {

        $user = new User();
        $address = new Address();

        $subject = new Subject();
        $subject->setBlobFolder('47c7939e-348e-11eb-99ad-fbb022be9235');
        //$subject->setCreatedBy($user);
        $subject->setIdentification('12345677890');
        $subject->setFirstName('Kevin');
        $subject->setMaidenName('Non');
        $subject->setLastName('Mitnick');
        $subject->setGender('Male');
        $subject->setNickname('hacker-plus');
//        $subject->setDateOfBirth('2018-06-18 11:51:22.000000');
        $subject->setPrimaryEmail('kevin@mitnick.hack');
        $subject->setSecondaryEmail('kmitnick@mitnick.hack');
        $subject->setPrimaryMobile('0800000009');
        $subject->setSecondaryMobile('0800000008');
        $subject->setAddress($address->setStreet('Hackers lane'));
        $subject->setAddress($address->setSuburb('Hackers Vil'));
        $subject->setAddress($address->setPostalCode('9023'));
        $subject->setAddress($address->setCity('USA'));

        //$subject->setCountry('123 Hidden Lane');
        //$subject->setEducationInstitutes('123 Hidden Lane');
        //$subject->setProvince('123 Hidden Lane');
        //$subject->setCompany('123 Hidden Lane');
        $subject->isEnabled(true);
        //$subject->setRushReport(true);
        $subject->setAllowTrait(true);
        //$subject->setImageFile(true);
        $subject->setStatus(true);
        $subject->setReportType('full');


        $manager->persist($subject);
        $manager->flush();
        $this->addReference(self::SUBJECT_REFERENCE, $subject);
    }

    public function getDependencies()
    {
        return array(
            UserFixtures::class,
            CountryFixtures::class,
        );
    }
}