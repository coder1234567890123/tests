<?php declare(strict_types=1);

namespace App\Helper;

use App\Entity\Answer;
use App\Entity\Profile;
use App\Entity\Question;
use App\Entity\Report;
use App\Entity\Subject;
use App\Entity\User;
use App\Repository\AnswerRepository;
use App\Repository\GlobalWeightsRepository;
use Doctrine\Common\Collections\Collection;

class InvestigationHelper
{

    public static function prepareQuestions(GlobalWeightsRepository $globalWeightsRepository, array $questions, Subject $subject, AnswerRepository $answerRepository, User $user)
    {
        $unasnswered = array();
        /** @var Report $report */
        $report = $subject->getCurrentReport();

        foreach ($questions as $question) {
            // check if subject has minimum required profiles
            $hasProfile = InvestigationHelper::checkPlatformsProfiles($question, $subject);

            // Fix all answers.
            $question->clearAnswers();

            // check if the question was answered previously for the subject and for the correct report
            $answers = $answerRepository->findBySubject($subject->getId(), $question->getId(), $report->getId());

            $hasAnswer = count($answers) > 0;
            if ($hasAnswer) {// if answer available attach to question
                $question->addAnswer($answers[0]);
            }

            //Saves the answers before new investigation is started, Saves to answers
            if (!$hasProfile && !$hasAnswer) {
                $answer = new Answer();
                $answer
                    ->setUser($user)
                    ->setSubject($subject)
                    ->setQuestion($question)
                    ->setReport($report)
                    ->setAnswer('not_applicable')
                    ->setEnabled(false)
                    ->setDefaultName($question->getDefaultName())
                    ->setNotApplicable(true);

                $answerRepository->save($answer);
                $question->addAnswer($answer);
            }

            if ($hasProfile) { // only include questions for which subject has a profile for
                $unasnswered[] = $question;
            }
        }

        $unasnswered = InvestigationHelper::orderQuestions($globalWeightsRepository, $unasnswered);
        return $unasnswered;
    }

    /**
     * @param Question $question
     * @param Subject  $subject
     *
     * @return bool
     *
     */
    private static function checkPlatformsProfiles(Question $question, Subject $subject)
    {
        $profile = false;
        $platform = $question->getPlatform() === Profile::PLATFORM_ALL ? "" : ucfirst($question->getPlatform());
        $profileFunction = 'get' . $platform . 'Profiles';
        /** @var Collection $profiles */
        $profiles = $subject->$profileFunction();
        $validProfiles = $profiles->filter(function (Profile $profile) {
            return $profile->isValid();
        });
        if (count($validProfiles) > 0) {
            $profile = true;
        }
        return $profile;
    }

    /**
     * @param GlobalWeightsRepository $globalWeightsRepository
     * @param array                   $questions
     *
     * @return array
     */
    private static function orderQuestions(GlobalWeightsRepository $globalWeightsRepository, array $questions)
    {
        $newOrder = [];

        /** @var Question $question */
        foreach ($questions as $question) {
            $newOrder[$question->getPlatform()]['questions'][] = $question;
            if (!key_exists('order', $newOrder[$question->getPlatform()])) {
                $ordering = $globalWeightsRepository->getByPlatform($question->getPlatform());
                $newOrder[$question->getPlatform()]['order'] = $ordering->getOrdering();
            }
        }
        if (count($newOrder) > 0) {
            usort($newOrder, function ($a, $b) {
                return $a['order'] <=> $b['order'];
            });

            foreach ($newOrder as $platform) {
                usort($platform['questions'], function (Question $a, Question $b) {
                    return $a->getOrderNumber() <=> $b->getOrderNumber();
                });
            }

            $x = [];

            foreach ($newOrder as $items) {
                foreach ($items['questions'] as $item) {
                    $x[] = $item;
                }
            }

            $newOrder = $x;
        }
        return $newOrder;
    }
}