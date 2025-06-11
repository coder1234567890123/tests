<?php

namespace App\Tests\Util;


use App\Entity\RoleGroup;
use PHPUnit\Framework\TestCase;


class RolesGroupsEntityTest extends TestCase
{


    public function testRolesGroupsGetterAndSetter()
    {
        $roleGroups = new RoleGroup();

        $roleGroups->setName('tom jones');
        $this->assertEquals('tom jones', $roleGroups->getName());

        $roleGroups->setRadio(true);
        $this->assertEquals(true, $roleGroups->isRadio());

    }
}
