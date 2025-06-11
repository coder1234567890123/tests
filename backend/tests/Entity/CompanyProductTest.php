<?php

namespace App\Tests\Util;

use App\Entity\BundleUsed;
use App\Entity\Company;
use App\Entity\User;
use App\Entity\CompanyProduct;
use App\Entity\Product;
use PHPUnit\Framework\TestCase;

class CompanyProductTest extends TestCase
{
    public function testCompanyProductGetterAndSetter()
    {

        echo "\nCompanyProductTest:  Company Product \n";

        $companyProduct = new CompanyProduct();
        $company = new Company();
        $product = new Product();
        $user = new User();

        $companyProduct->setCompany($company);
        $this->assertEquals($companyProduct->getCompany(), $company);

        $companyProduct->setProductType($product);
        $this->assertEquals($companyProduct->getProductType(), $product);

        $companyProduct->setCreatedBy($user);
        $this->assertEquals($companyProduct->getCreatedBy(), $user);

        $companyProduct->setUpdatedBy($user);
        $this->assertEquals($companyProduct->getUpdatedBy(), $user);

        $companyProduct->setBundleAmount('10');
        $this->assertEquals('10', $companyProduct->getBundleAmount());

        $companyProduct->setAmountCompleted('10');
        $this->assertEquals('10', $companyProduct->getAmountCompleted());

        $companyProduct->setAdditionalRequested('10');
        $this->assertEquals('10', $companyProduct->getAdditionalRequested());

        $companyProduct->setSuspended(true);
        $this->assertEquals(true, $companyProduct->getSuspended());
    }
}
