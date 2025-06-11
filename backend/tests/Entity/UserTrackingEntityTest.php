<?php

namespace App\Tests\Util;

use App\Entity\Company;

use App\Entity\Report;
use App\Entity\User;
use App\Entity\UserTracking;
use App\Exception\InvalidTrackingActionException;
use PHPUnit\Framework\TestCase;
use App\Entity\Subject;

class UserTrackingEntityTest extends TestCase
{

    /**
     * @throws InvalidTrackingActionException
     *
     */
    public function testUserTrackingGetterAndSetter()
    {
        echo "\nUserTrackingEntityTest:  User Tracking Entity\n";

        $userTracking = new UserTracking();
        $report = new Report();
        $subject = new Subject();
        $user = new User();
        $company = new Company();

        $userTracking->setUser($user);
        $this->assertEquals($userTracking->getUser(), $user);

        $userTracking->setCompany($company);
        $this->assertEquals($userTracking->getCompany(), $company);

        $userTracking->setReport($report);
        $this->assertEquals($userTracking->getReport(), $report);

        $userTracking->setSubject($subject);
        $this->assertEquals($userTracking->getSubject(), $subject);

        $userTracking->setAction(UserTracking::ACTION_SUBJECT_PROFILE_VALID);
        $this->assertEquals(UserTracking::ACTION_SUBJECT_PROFILE_VALID, $userTracking->getAction());

        $this->expectException(InvalidTrackingActionException::class);
        $userTracking->setAction('test');

        $userTracking->setReportStatus('new');
        $this->assertEquals('new', $userTracking->getReportStatus());

    }
}
