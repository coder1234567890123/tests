<?php

namespace App\Tests\Util;

use App\Entity\Group;
use PHPUnit\Framework\TestCase;


class GroupsEntityTest extends TestCase
{
    public function testGroupGetterAndSetter()
    {

        $group = new Group();

        $group->setRoles(["test", "array"]);
        $this->assertEquals(["test", "array"], $group->getRoles());

        $group->setEnabled(true);
        $this->assertEquals(true, $group->isEnabled());

    }
}
