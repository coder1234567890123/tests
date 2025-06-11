<?php

namespace App\Tests\Util;

use App\Entity\Team;
use PHPUnit\Framework\TestCase;
use App\Tests\Util\DateTimeImmutable;
use App\Entity\User;
use App\Entity\Company;
use App\Entity\Group;

class UsersEntityTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testUsersGetterAndSetter()
    {
        echo "\nUsersEntityTest:  Users Entity\n";

        $user    = new User();
        $company = new Company();
        $group   = new Group();
        $team   = new Team();

        $user->setGroup($group);
        $this->assertEquals($user->getGroup(), $group);

        $user->setTeam($team);
        $this->assertEquals($user->getTeam(), $team);

        $user->setEmail('test@email.com');
        $this->assertEquals('test@email.com', $user->getEmail());
        $this->assertEquals('test@email.com', $user->getUsername());

        $user->setFirstName('tom');
        $this->assertEquals('tom', $user->getFirstName());

        $user->setArchived(true);
        $this->assertEquals(true, $user->isArchived());

        $user->addRole('test');
        $this->assertEquals(1,  count($user->getRoles()));
        $this->assertEquals(true,  $user->hasRole('test'));

        $user->resetRoles();
        $this->assertEquals([],  $user->getRoles());

        $date = new \DateTimeImmutable();
        $user->setTokenRequested($date);
        $this->assertEquals($date,  $user->getTokenRequested());

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

        $company->setName('company');
        $this->assertEquals($user->getCompanyName(),'company');

        $user->setEnabled(true);
        $this->assertEquals(true, $user->isEnabled());

        $user->setArchived(true);
        $this->assertEquals(true, $user->isArchived());

        $date_test = new \DateTime('2011-01-01');
        $user->setCreatedAt($date_test);
        $this->assertEquals($date_test, $user->getCreatedAt());

        $user->setUpdatedAt($date_test);
        $this->assertEquals($date_test, $user->getUpdatedAt());

        $user->setImageFile('1.jpg');
        $this->assertEquals('1.jpg', $user->getImageFile());

    }
}
