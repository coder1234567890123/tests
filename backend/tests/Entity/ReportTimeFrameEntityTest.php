<?php

namespace App\Tests\Util;

use App\Entity\ReportTimeFrame;
use PHPUnit\Framework\TestCase;

class ReportTimeFrameEntityTest extends TestCase
{
    public function testPhaseGetterAndSetter()
    {
        echo "\nReportTimeFrameEntityTest:  Report Time Frame Entity \n";

        $reportTimeFrame = new ReportTimeFrame();

        $reportTimeFrame->setName('test');
        $this->assertEquals('test', $reportTimeFrame->getName());

        $reportTimeFrame->setHours(3);
        $this->assertEquals(3, $reportTimeFrame->getHours());
    }
}
