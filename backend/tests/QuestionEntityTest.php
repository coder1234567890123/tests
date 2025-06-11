<?php

namespace App\Tests\Util;

use PHPUnit\Framework\TestCase;
use App\Entity\Question;
use App\Entity\ReportSection;

class QuestionEntityTest extends TestCase
{
    public function testQuestionGetterAndSetter()
    {
        $question = new Question();
        $reportSection = new ReportSection();

        $question->setReportSection($reportSection);
        $this->assertEquals($question->getReportSection(), $reportSection);

        $question->setQuestion('this is sample question');
        $this->assertEquals('this is sample question', $question->getQuestion());

        $question->setReportLabel('sample question');
        $this->assertEquals('sample question', $question->getReportLabel());

        $question->setPlatforms(['twitter', 'facebook']);
        $this->assertEquals('twitter', $question->getPlatforms()[0]);

        $question->setEnabled(true);
        $this->assertEquals(true, $question->isEnabled());

        $question->setOrderNumber(1);
        $this->assertEquals(1, $question->getOrderNumber());

    }
}
