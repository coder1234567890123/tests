<?php

namespace App\Tests\Util;

use PHPUnit\Framework\TestCase;
use App\Entity\ReportSection;
use App\Entity\Question;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class ReportSectionEntityTest extends TestCase
{
    public function testReportSectionGetterAndSetter()
    {
        $reportSection = new ReportSection();
        $question = new Question();
        $questions = new ArrayCollection();
        $questions->add($question);

        $reportSection->setName('Sample Section');
        $this->assertEquals('Sample Section', $reportSection->getName());

        $reportSection->addQuestion($question);
        $this->assertEquals($reportSection->getQuestions(), $questions);

        $reportSection->setEnabled(true);
        $this->assertEquals(true, $reportSection->isEnabled());

    }
}
