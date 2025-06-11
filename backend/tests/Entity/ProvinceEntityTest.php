<?php
/**
 * Created by PhpStorm.
 * User: kuda
 * Date: 8/1/19
 * Time: 12:39 PM
 */

namespace App\Tests\Util;

use App\Entity\Province;
use PHPUnit\Framework\TestCase;

class ProvinceEntityTest extends TestCase
{
    public function testProvinceGetterAndSetter()
    {

        echo "\nProvinceEntityTest:  Province Entity \n";

        $province        = new Province();

        $province->setName('Prov');
        $this->assertEquals('Prov', $province->getName());

        $province->setCode('PV');
        $this->assertEquals('PV', $province->getCode());
    }
}