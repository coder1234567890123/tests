<?php

namespace App\Tests\Util;

use PHPUnit\Framework\TestCase;
use App\Entity\Country;

class CountryEntityTest extends TestCase
{
    public function testPhaseGetterAndSetter()
    {
        $country = new Country();

        $country->setCode('SA');
        $this->assertEquals('SA', $country->getCode());

        $country->setName('South Africa');
        $this->assertEquals('South Africa', $country->getName());

    }
}
