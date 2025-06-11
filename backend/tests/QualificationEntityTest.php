<?php

namespace App\Tests\Util;

use PHPUnit\Framework\TestCase;
use App\Entity\Qualification;


class QualificationEntityTest extends TestCase
{
    public function testQualificationGetterAndSetter()
    {
        $qualification = new Qualification();

        $qualification->setName('Tom');
        $this->assertEquals('Tom', $qualification->getName());

        $qualification->setInstitute('High School');
        $this->assertEquals('High School', $qualification->getInstitute());

    }
}
