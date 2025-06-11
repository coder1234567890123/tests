<?php

namespace App\DataFixtures;

use App\Entity\Role;
use App\Entity\RoleGroup;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class RoleFixtures extends Fixture
{

    public function load(ObjectManager $manager)
    {
        $roles = [
            'ROLE_SUPER_ADMIN', 'ROLE_TEAM_LEAD', 'ROLE_ANALYST'
        ];

        $group = new RoleGroup();
        $group->setName('UserGroup');

        $manager->persist($group);
        $manager->flush();

        foreach ($roles as $r) {
            $role = new Role();
            $role->setName($r);
            $role->setValue($r);
            $role->setRoleGroup($group);
            $manager->persist($role);
        }

        $manager->flush();
    }
}
