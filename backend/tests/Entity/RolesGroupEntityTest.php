<?php

namespace App\Tests\Util;


use App\Entity\Role;
use App\Entity\RoleGroup;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;


class RolesGroupsEntityTest extends TestCase
{
    public function testRolesGroupsGetterAndSetter()
    {

        echo "\nRolesGroupsEntityTest:  Roles Groups Entity \n";

        $roleGroups = new RoleGroup();
        $role = new Role();
        $roles = new ArrayCollection();
        $roles->add($role);

        $roleGroups->setName('tom jones');
        $this->assertEquals('tom jones', $roleGroups->getName());

        $roleGroups->addRole($role);
        $this->assertEquals($roles, $roleGroups->getRoles());

        $roleGroups->setRadio(true);
        $this->assertEquals(true, $roleGroups->isRadio());

    }
}
