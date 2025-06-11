<?php

namespace App\Tests\Util;

use App\Entity\Address;
use App\Entity\Product;
use PHPUnit\Framework\TestCase;

class ProductEntityTest extends TestCase
{
    public function testProductGetterAndSetter()
    {

        echo "\nProductEntityTest:  Product Entity\n";

        $products = new Product();

        $products->setName('bundle package');
        $this->assertEquals('bundle package', $products->getName());

        $products->setType('123456');
        $this->assertEquals('123456', $products->getType());

        $products->setBundle('10');
        $this->assertEquals('10', $products->getBundle());

        $products->setBundle(true);
        $products->setEnable(true, $products->getEnable());

    }
}
