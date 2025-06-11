<?php
/**
 * Created by PhpStorm.
 * User: kuda
 * Date: 7/23/19
 * Time: 8:14 AM
 */

use App\Entity\Team;
use App\Entity\Company;
use PHPUnit\Framework\TestCase;
use App\Entity\User;

class TeamEntityTest extends TestCase
{
    public function testUsersGetterAndSetter()
    {
        echo "\nTeamEntityTest:  Team Entity\n";

        $user    = new User();
        $team   = new Team();
        $company = new Company();

        $team->setTeamLeader($user);
        $this->assertEquals($team->getTeamLeader(), $user);

        $user->setFirstName('Test');
        $user->setLastName('Test');
        $this->assertEquals($team->getTeamName(), 'Test Test');

        $team->addCompany($company);
        $this->assertEquals($team->getCompanies()[0], $company);

        $teamMember = new User();
        $team->addUser($teamMember);
        $this->assertEquals($team->getUsers()[0], $teamMember);
    }
}