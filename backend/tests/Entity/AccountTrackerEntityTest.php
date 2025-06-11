<?php

namespace App\Tests\Util;

use App\Entity\Company;
use App\Entity\CompanyProduct;
use App\Entity\AccountsTracker;
use PHPUnit\Framework\TestCase;

class AccountTrackerEntityTest extends TestCase
{
    public function testAccountsGetterAndSetter()
    {

        echo "\nAccountTrackerEntityTest:  Account Tracker Entity \n";

        $accountsTracker = new AccountsTracker();
        $companyProduct = new CompanyProduct();
        $company = new Company();

        $accountsTracker->setCompanyProduct($companyProduct);
        $this->assertEquals($accountsTracker->getCompanyProduct(), $companyProduct);

        $accountsTracker->setMonthlyUnits('100');
        $this->assertEquals('100', $accountsTracker->getMonthlyUnits());

        $accountsTracker->setRejectUnit('100');
        $this->assertEquals('100', $accountsTracker->getRejectUnit());

        $accountsTracker->setMonthlyReset('1');
        $this->assertEquals('1', $accountsTracker->getMonthlyReset());

        $accountsTracker->setCompany($company);
        $this->assertEquals($accountsTracker->getCompany(), $company);

        $accountsTracker->setTotalUnit('100');
        $this->assertEquals('100', $accountsTracker->getTotalUnit());
    }
}