<?php

namespace App\Tests\Util;

use App\Entity\Role;
use App\Entity\RoleGroup;
use PHPUnit\Framework\TestCase;


class RolesEntityTest extends TestCase
{


    public function testRolesGetterAndSetter()
    {
        echo "\nRolesEntityTest:  Roles Entity \n";

        $role       = new Role();
        $roleGroups = new RoleGroup();

        $role->setRoleGroup($roleGroups);
        $this->assertEquals($role->getRoleGroup(), $roleGroups);

        $role->setName('tom jones');
        $this->assertEquals('tom jones', $role->getName());

        $role->setValue('user');
        $this->assertEquals('user', $role->getValue());

        $date_test = new \DateTime('2011-01-01');

        $role->setCreatedAt($date_test);
        $this->assertEquals($date_test, $role->getCreatedAt());

        $role->setUpdatedAt($date_test);
        $this->assertEquals($date_test, $role->getUpdatedAt());

    }
}
