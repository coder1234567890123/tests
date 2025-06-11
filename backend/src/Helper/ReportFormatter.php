<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: Kuda Dhliwayo
 * Date: 6/18/19
 * Time: 8:20 AM
 */
namespace App\Helper;

use App\Entity\Answer;
use App\Entity\Comment;
use App\Entity\Question;
use App\Entity\Report;

class ReportFormatter {

    /**
     * @var Report
     * @return object[]|null
     */
    public static function format(Report $report) {

        if ($report->getAnswers()) {
            $result = [];
            $result['generalComment'] = [];
            /** @var Answer $answer */
            foreach ($report->getAnswers() as $answer) {

                //Daniel to check if this effects anything

//                if($answer->isEnabled()) {

                    /** @var Question $question */
                    $question = $answer->getQuestion();


                    if ($question) {
                        $question->clearAnswers();
                        $question->addAnswer($answer);
                        $platform = $question->getPlatform();
                        $result['platforms'][$platform][] = $question;
                    } else {
                        $result['generalComment'][] = $answer;
                    }
//                }
            }
            return $result;
        }

        return null;
    }
}