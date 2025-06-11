<?php

namespace App\Tests\Util;

use PHPUnit\Framework\TestCase;
use App\Tests\Util\DateTimeImmutable;
use App\Entity\User;
use App\Entity\Company;
use App\Entity\Group;

class UsersEntityTest extends TestCase
{
    public function testUsersGetterAndSetter()
    {

        $user    = new User();
        $company = new Company();
        $group   = new Group();

        $user->setGroup($group);
        $this->assertEquals($user->getGroup(), $group);

        $user->setEmail('test@email.com');
        $this->assertEquals('test@email.com', $user->getEmail());

        $user->setFirstName('tom');
        $this->assertEquals('tom', $user->getFirstName());

        $user->setLastName('jones');
        $this->assertEquals('jones', $user->getLastName());

        $user->setTelNumber('12346579');
        $this->assertEquals('12346579', $user->getTelNumber());

        $user->setFaxNumber('12346579');
        $this->assertEquals('12346579', $user->getFaxNumber());

        $user->setMobileNumber('12346579');
        $this->assertEquals('12346579', $user->getMobileNumber());

        $user->setWebsite('www.example.com');
        $this->assertEquals('www.example.com', $user->getWebsite());

        $user->setEnabled(true);
        $this->assertEquals(true, $user->isEnabled());

        $user->setToken('43243trtre');
        $this->assertEquals('43243trtre', $user->getToken());

        $user->setCompany($company);
        $this->assertEquals($user->getCompany(),$company);

        $company->setEnabled(true);
        $this->assertEquals(true, $company->isEnabled());

        $company->setArchived(true);
        $this->assertEquals(true, $company->isArchived());

        $date_test = new \DateTime('2011-01-01');
        $user->setCreatedAt($date_test);
        $this->assertEquals($date_test, $user->getCreatedAt());

        $user->setUpdatedAt($date_test);
        $this->assertEquals($date_test, $user->getUpdatedAt());

        $user->setImageFile('1.jpg');
        $this->assertEquals('1.jpg', $user->getImageFile());

    }
}
