<?php
/**
 * Created by PhpStorm.
 * User: kuda
 * Date: 7/25/19
 * Time: 11:05 AM
 */

namespace App\DataFixtures;

use App\Entity\Country;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class CountryFixtures extends Fixture
{
    public const COUNTRY_ZA_REFERENCE = 'country-za';

    public function load(ObjectManager $manager)
    {
        $country = new Country();
        $country->setName('South Africa');
        $country->setCode('ZA');
        $manager->persist($country);
        $manager->flush();
        $this->addReference(self::COUNTRY_ZA_REFERENCE, $country);
    }
}