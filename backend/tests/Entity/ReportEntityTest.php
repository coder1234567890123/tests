<?php

namespace App\Tests\Util;

use PHPUnit\Framework\TestCase;
use App\Entity\Report;
use App\Entity\Answer;
use App\Entity\Comment;
use App\Entity\Subject;
use App\Entity\User;

class ReportEntityTest extends TestCase
{
    /**
     *
     */
    public function testReportGetterAndSetter()
    {
        echo "\nReportEntityTest:  Report Entity \n";

        $report = new Report();
        $answer = new Answer();
        $comment = new Comment();
        $subject = new Subject();
        $user = new User();

        $report->addAnswer($answer);
        $this->assertEquals($report->getAnswers()[0], $answer);

        $report->addComment($comment);
        $this->assertEquals($report->getComments()[0], $comment);

        $report->setSequence('RPT7');
        $this->assertEquals('RPT7', $report->getSequence());

        $report->setRequestType('rush');
        $this->assertEquals('rush', $report->getRequestType());

        $report->setStatus('new_request');
        $this->assertEquals('new_request', $report->getStatus());

        $report->setRisk('risk');
        $this->assertEquals('risk', $report->getRisk());

        $report->setRiskScore(23.0);
        $this->assertEquals(23.0, $report->getRiskScore());

        $date = new \DateTime();
        $report->setCompletedDate($date);
        $this->assertEquals($date, $report->getCompletedDate());

        $report->setDueDate($date);
        $this->assertEquals($date, $report->getDueDate());

        $report->setSubject($subject);
        $this->assertEquals($report->getSubject(), $subject);

        $subject->setFirstName('Test');
        $subject->setLastName('Name');
        $this->assertEquals($report->getSubjectName(), 'Test Name');

        $report->setUser($user);
        $this->assertEquals($report->getUser(), $user);

        $user->setFirstName('Name');
        $user->setLastName('Test');
        $this->assertEquals($report->getUserName(), 'Name Test');

        $report->setAssignedTo($user);
        $this->assertEquals($report->getAssignedTo(), $user);
        $this->assertEquals($report->getAssignedToName(), 'Name Test');


        $report->setReportScores(['test']);
        $this->assertEquals('test', $report->getReportScores()[0]);

        $report->setEnabled(true);
        $this->assertEquals(true, $report->isEnabled());

        $report->setOpen(true);
        $this->assertEquals(true, $report->isOpen());

        $report->setHideReportScore(true);
        $this->assertEquals(true, $report->getHideReportScore());

        $report->setHideGeneralComments(true);
        $this->assertEquals(true, $report->getHideGeneralComments());
    }
}
