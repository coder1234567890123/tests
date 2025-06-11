<?php

namespace App\Tests\Util;

use App\Entity\Subject;
use PHPUnit\Framework\TestCase;
use App\Entity\Qualification;


class QualificationEntityTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testQualificationGetterAndSetter()
    {
        echo "\nQualificationEntityTest:  Qualification Entity \n";

        $qualification = new Qualification();
        $subject = new Subject();
        $dateNow = new \DateTimeImmutable('now');

        $qualification->setSubject($subject);
        $this->assertEquals($subject, $qualification->getSubject());

        $subject->setFirstName('test');
        $this->assertEquals('test', $qualification->getSubjectName());

        $qualification->setStartDate($dateNow);
        $this->assertEquals($dateNow, $qualification->getStartDate());

        $qualification->setEndDate($dateNow);
        $this->assertEquals($dateNow, $qualification->getEndDate());

        $qualification->setName('Tom');
        $this->assertEquals('Tom', $qualification->getName());

        $qualification->setInstitute('High School');
        $this->assertEquals('High School', $qualification->getInstitute());

    }
}
