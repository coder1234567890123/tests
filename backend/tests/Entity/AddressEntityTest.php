<?php

namespace App\Tests\Util;

use App\Entity\Address;
use PHPUnit\Framework\TestCase;

class AddressEntityTest extends TestCase
{
    public function testPhaseGetterAndSetter()
    {
        echo "\nAddressEntityTest:  Address Entity \n";

        $address = new Address();

        $address->setStreet('long street');
        $this->assertEquals('long street', $address->getStreet());

        $address->setSuburb('123456');
        $this->assertEquals('123456', $address->getSuburb());

        $address->setPostalCode('123456');
        $this->assertEquals('123456', $address->getPostalCode());

        $address->setCity('abc street 123');
        $this->assertEquals('abc street 123', $address->getCity());

    }
}
