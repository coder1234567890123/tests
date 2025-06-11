<?php
/**
 * Created by PhpStorm.
 * User: kuda
 * Date: 7/24/19
 * Time: 7:13 AM
 */

namespace App\DataFixtures;

use App\Entity\Team;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

// use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class UserFixtures extends Fixture
{
    public const SUPER_USER_REFERENCE = 'super-user';
    public const TEAM_LEAD_REFERENCE = 'team-lead-user';
    public const ANALYST_REFERENCE = 'analyst-user';
    public const ADMIN_USER_REFERENCE = 'admin-user';
    public const USER_MANAGER_REFERENCE = 'user-manager';
    public const USER_STANDARD_REFERENCE = 'user-standard';

    public function load(ObjectManager $manager)
    {
        $roles = [
            'ROLE_SUPER_ADMIN' => [
                'first_name' => 'admin',
                'last_name' => 'admin',
                'email' => 'admin@testing.test',
                'mobile' => '0114568039',
                'password' => 'Password123'
            ],

            'ROLE_TEAM_LEAD' => [
                'first_name' => 'team',
                'last_name' => 'lead',
                'email' => 'teamlead@testing.test',
                'mobile' => '0114568039',
                'password' => 'Password123'
            ],
            'ROLE_ANALYST' => [
                'first_name' => 'analyst',
                'last_name' => 'analyst',
                'email' => 'analyst@testing.test',
                'mobile' => '0114568039',
                'password' => 'Password123'
            ],
            'ROLE_ADMIN_USER' => [
                'first_name' => 'admin',
                'last_name' => 'user',
                'email' => 'adminuser@testing.test',
                'mobile' => '0114568039',
                'password' => 'Password123'
            ],
            'ROLE_USER_MANAGER' => [
                'first_name' => 'manager',
                'last_name' => 'user',
                'email' => 'manageruser@testing.test',
                'mobile' => '0114568039',
                'password' => 'Password123'
            ],
            'ROLE_USER_STANDARD' => [
                'first_name' => 'standard',
                'last_name' => 'user',
                'email' => 'standarduser@testing.test',
                'mobile' => '0114568039',
                'password' => 'Password123'
            ]
        ];


        $teamLead = null;
        foreach ($roles as $index => $addUser) {

            $user = new User();
            $user->setFirstName($addUser['first_name']);
            $user->setLastName($addUser['last_name']);
            $user->setEmail($addUser['email']);
            $user->setMobileNumber($addUser['mobile']);
            $user->setPassword($addUser['password']);
            $user->addRole($index);
            if ($index === 'ROLE_TEAM_LEAD') {
                $teamLead = new Team();
                $teamLead->setTeamLeader($user);
                $manager->persist($teamLead);
            }
            if ($index === 'ROLE_ANALYST') {
                $user->setTeam($teamLead);
            }
            $manager->persist($user);
            $this->addRef($index, $user);
        }


        $manager->flush();
    }

    /**
     * @param string $role
     * @param User $user
     */
    private function addRef(string $role, User $user)
    {
        switch ($role) {
            case 'ROLE_SUPER_ADMIN':
                $this->addReference(self::SUPER_USER_REFERENCE, $user);
                break;
            case 'ROLE_TEAM_LEAD':
                $this->addReference(self::TEAM_LEAD_REFERENCE, $user);
                break;
            case 'ANALYST_REFERENCE':
                $this->addReference(self::ANALYST_REFERENCE, $user);
                break;
            case 'ROLE_ADMIN_USER':
                $this->addReference(self::ADMIN_USER_REFERENCE, $user);
                break;
            case 'ROLE_USER_MANAGER':
                $this->addReference(self::USER_MANAGER_REFERENCE, $user);
                break;
            case 'ROLE_USER_STANDARD':
                $this->addReference(self::USER_STANDARD_REFERENCE, $user);
                break;
        }
    }
}
