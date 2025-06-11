<?php

namespace App\Tests\Util;

use App\Entity\Accounts;
use App\Entity\Company;
use App\Entity\CompanyProduct;
use App\Entity\Subject;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class AccountEntityTest extends TestCase
{
    public function testAccountsGetterAndSetter()
    {

        echo "\nAccountEntityTest:  Account Entity \n";

        $accounts = new Accounts();
        $company = new Company();
        $companyProduct = new CompanyProduct();
        $subject = new Subject();
        $user = new User();

        $accounts->setCompany($company);
        $this->assertEquals($accounts->getCompany(), $company);

        $accounts->setCompanyProduct($companyProduct);
        $this->assertEquals($accounts->getCompanyProduct(), $companyProduct);

        $accounts->setMonthlyUnits('100');
        $this->assertEquals('100',$accounts->getMonthlyUnits());

        $accounts->setAddUnit('100');
        $this->assertEquals('100',$accounts->getAddUnit());

        $accounts->setRejectUnit('1');
        $this->assertEquals('1',$accounts->getRejectUnit());

        $accounts->setTotalUnitUsed('100');
        $this->assertEquals('100',$accounts->getTotalUnitUsed());

        $accounts->setTotalUnitUsed('100');
        $this->assertEquals('100',$accounts->getTotalUnitUsed());

        $accounts->setMonthlyReset(true);
        $this->assertEquals(true,$accounts->isMonthlyReset());

        $accounts->setSubject($subject);
        $this->assertEquals($accounts->getSubject(), $subject);

        $accounts->setRequestType('pre_paid');
        $this->assertEquals('pre_paid',$accounts->getRequestType());

        $accounts->setMonthlyRecurring(true);
        $this->assertEquals(true,$accounts->isMonthlyRecurring());

        $accounts->setUnitUsed('100');
        $this->assertEquals('100',$accounts->getUnitUsed());

        $accounts->setUnitUsed('20-05-2020');
        $this->assertEquals('20-05-2020',$accounts->getUnitUsed());

        $accounts->setTotalUnitAdd('100');
        $this->assertEquals('100',$accounts->getTotalUnitAdd());

        $accounts->setCreatedBy($user);
        $this->assertEquals($accounts->getCreatedBy(), $user);

        $accounts->setResetMonthlyAmounts(true);
        $this->assertEquals(true,$accounts->isResetMonthlyAmounts());

    }
}
