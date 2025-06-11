<?php declare(strict_types=1);

namespace App\Service;

use App\Contracts\AnswerRepositoryInterface;
use App\Contracts\GlobalWeightsRepositoryInterface;
use App\Contracts\SystemConfigRepositoryInterface;
use App\Entity\Answer;
use App\Entity\Profile;
use App\Entity\Proof;
use App\Entity\Question;
use App\Entity\Subject;
use App\Repository\AnswerRepository;
use App\Repository\GlobalWeightsRepository;
use App\Repository\SystemConfigRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ReportScoreCalculator
 *
 * @package App\Service
 */
class ReportScoreCalculator
{
    /**
     * @var GlobalWeightsRepository
     */
    private $globalWeightRepository;

    /**
     * @var SystemConfigRepository
     */
    private $systemConfigRepository;

    /**
     * ReportScoreCalculator constructor.
     *
     * @param EntityManagerInterface           $entityManager
     * @param GlobalWeightsRepositoryInterface $globalWeightRepository
     * @param SystemConfigRepositoryInterface  $systemConfigRepository
     * @param AnswerRepositoryInterface        $answerRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        GlobalWeightsRepositoryInterface $globalWeightRepository,
        SystemConfigRepositoryInterface $systemConfigRepository,
        AnswerRepositoryInterface $answerRepository
    )
    {
        $this->entityManager = $entityManager;

        $this->globalWeightRepository = $globalWeightRepository;
        $this->systemConfigRepository = $systemConfigRepository;
        $this->answerRepository = $answerRepository;

        $this->repositoryAnswers = $entityManager->getRepository(Answer::class);
    }

    /**
     * @param         $questions
     * @param Subject $subject
     *
     * @return bool|array
     */
    public function calculateReportScore($questions, Subject $subject)
    {
        $result = array();

        $sumWeightedPlatformScores = 0.0;
        $behaviorScores = array();

        foreach ($questions['platforms'] as $key => $platformSection) {
            if (count($platformSection) > 0) {
                $result['platforms'][$key]['unweighted_platform_score'] = round($this->platformUnweightedScore($key, $platformSection), 6);
                $result['platforms'][$key]['weighted_platform_score'] = round($this->weightedPlatformScore($key, $result['platforms'][$key]['unweighted_platform_score'], $subject), 6);

                $weightedResult = round($this->weightedPlatformScore($key, $result['platforms'][$key]['unweighted_platform_score'], $subject), 6);
                $unWeightedResult = round($this->platformUnweightedScore($key, $platformSection), 6);

                $result['platforms'][$key]['unweighted_platform_score_rounded'] = round($unWeightedResult, 2);
                $result['platforms'][$key]['weighted_platform_score_rounded'] = round($weightedResult, 2);
                $sumWeightedPlatformScores += $result['platforms'][$key]['weighted_platform_score'];
                $result['platforms'][$key]['comments'] = [];
                if ($subject->getCompany()->isAllowTrait()) {
                    $result['platforms'][$key]['behavior_scores'] = $this->behaviorScores($platformSection, $key);
                    $behaviorScores['behavior_scores'][$key] = $result['platforms'][$key]['behavior_scores'];
                }
            }
        }

        if ($subject->getCompany()->isAllowTrait()) {
            $result['overall_behavior_scores'] = $this->overallBehaviorScores($behaviorScores['behavior_scores']);
        }
        
        $result['weighted_social_media_score'] = round($this->weightedSocialMediaScore($sumWeightedPlatformScores), 6);

        $mediaRoundOff = round($this->weightedSocialMediaScore($sumWeightedPlatformScores), 6);
        $result['weighted_social_media_score_round'] = round($mediaRoundOff, 2);

        $result['risk_score_pre_round'] = round($this->riskScore($result['weighted_social_media_score']), 6);
        $roundOff = round($this->riskScore($result['weighted_social_media_score']), 6);

        $result['risk_score'] = round($roundOff, 2);

        return $result;
    }

    /**
     * @param         $questions
     * @param Subject $subject
     *
     * @return bool|array
     */
    public function calculateSocialMediaReportScore($questions, Subject $subject)
    {
        $response = [];
        $answers = $this->answerRepository->getSubjectAnswers($subject);

        $platforms = [
            'pinterest',
            'twitter',
            'instagram',
            'linkedin',
            'youtube',
            'flickr',
            'facebook',
            'web'
        ];

        foreach ($platforms as $platform) {
            $response[$platform] = [
                'has_account' => $this->getAnswerScore($answers, $platform, $platform . '_has_account'),
                'multiple_account' => $this->getAnswerScore($answers, $platform, $platform . '_multiple_account'),
                'active_account' => $this->getAnswerScore($answers, $platform, $platform . '_active_account'),
                'negative_content' => $this->getAnswerScore($answers, $platform, $platform . '_negative_content'),
                'positive_content' => $this->getAnswerScore($answers, $platform, $platform . '_positive_content'),
                'privacy_settings' => $this->getAnswerScore($answers, $platform, $platform . '_privacy_settings'),
                'information_disclosed' => $this->getAnswerScore($answers, $platform, $platform . '_information_disclosed'),
                'connections' => $this->getAnswerScore($answers, $platform, $platform . '_connections')
            ];
        }

        return $response;
    }

    /**
     * @param $answers
     * @param $platform
     * @param $questionType
     *
     * @return string
     */
    private function getAnswerScore($answers, $platform, $questionType)
    {
        foreach ($answers as $getData) {
            if ($getData->getDefaultName() === $questionType) {
                if (!empty($getData->getScore())) {
                    return $getData->getScore();
                } else {
                    return "0";
                }
            }
        }
    }

    /**
     * @param string $platform
     * @param        $questions
     *
     * @return float
     */
    private function platformUnweightedScore(string $platform, $questions)
    {
        $sum = 0.0;

        /** @var Question $question */
        foreach ($questions as $question) {
            /** @var Answer $ans */
            $ans = $question->getAnswers()[0];

            $scores = array_map('floatval', $question->getAnswerScore());
            $questionScore = 0.0;
            if ($question->getAnswerType() === 'yes_no') {
                if ($ans->getAnswer() === 'yes' || $ans->getAnswer() == 'no') {
                    $questionScore = $ans->getAnswer() === 'yes' ? $scores[1] : $scores[0];
                }
            } else {
                if (trim($ans->getAnswer()) !== '') {
                    $options = $question->getAnswerOptions();
                    for ($i = 0, $iMax = count($options); $i < $iMax; $i++) {
                        if ($options[$i] === $ans->getAnswer()) { // get score from selected answer
                            $questionScore = $scores[$i];
                            break;
                        }
                    }
                }

                //keep for slider in the future
//                if (trim($ans->getAnswer()) != '') {
//                    $options = $question->getAnswerOptions();
//                    for ($i = 0; $i < count($options); $i++) {
//                        if ($options[$i] === $ans->getAnswer()) { // get score from selected answer
//                            $questionScore = $scores[$i];
//                            break;
//                        }
//                    }
//                }

            }

//            if ($question->isSlider()) {
//                $avg = 2.5;
//                $cap = array_map('floatval', $question->getSliderValues());
//                $capacity = $cap[1];
//                if ($ans->getSliderValue() >= $question->getSliderAverage()) {
//                    $questionScore = $avg + (($ans->getSliderValue() / $capacity) * $avg);
//                    if ($ans->getSliderValue() === $question->getSliderAverage()) {
//                        $questionScore = $avg;
//                    }
//                } else {
//                    $questionScore = $avg - (($ans->getSliderValue() / $capacity) * $avg);
//                    if ($ans->getSliderValue() === 0) {
//                        $questionScore = 0.0;
//                    }
//                }
//            }

            $sum += $questionScore;
        }
        $globalWeights = $this->globalWeightRepository->getByPlatform($platform);
        $preObj = $this->systemConfigRepository->getByName('pre_platform_scoring_metric');
        $postObj = $this->systemConfigRepository->getByName('post_platform_scoring_metric');
        $pre = $preObj ? (float)$preObj->getVal() : 30.0;
        $post = $postObj ? (float)$postObj->getVal() : 5.0;

        return ($sum / $pre) * $post;
    }


    /**
     * @param string  $platform
     * @param float   $platformUnweightedScore
     * @param Subject $subject
     *
     * @return float
     */
    public function weightedPlatformScoreOverride(string $platform, float $platformUnweightedScore, Subject $subject)
    {
        $globalWeightsForPlatform = $this->globalWeightRepository->getByPlatform($platform);

        $sum = 0.0;

        foreach (Profile::PLATFORMS as $plat) {
            $profiles = $subject->getPlatformProfiles($plat);

            /** @var Profile $profile */
            foreach ($profiles as $profile) {
                if ($profile->isValid()) {
                    $globalWeight = $this->globalWeightRepository->getByPlatform($plat);
                    $sum += $globalWeight->getGlobalUsageWeighting();
                    break;
                }
            }
        }

        return ($globalWeightsForPlatform->getGlobalUsageWeighting() / $sum) * $platformUnweightedScore;
    }

    /**
     * @param string  $platform
     * @param float   $platformUnweightedScore
     * @param Subject $subject
     *
     * @return float
     */
    public function weightedPlatformScore(string $platform, float $platformUnweightedScore, Subject $subject)
    {
        $globalWeightsForPlatform = $this->globalWeightRepository->getByPlatform($platform);
        $sum = 0.0;

        foreach (Profile::PLATFORMS as $plat) {
            $profiles = $subject->getPlatformProfiles($plat);

            /** @var Profile $profile */
            foreach ($profiles as $profile) {
                if ($profile->isValid()) {
                    $globalWeight = $this->globalWeightRepository->getByPlatform($plat);
                    $sum += $globalWeight->getGlobalUsageWeighting();
                    break;
                }
            }
        }
        return ($globalWeightsForPlatform->getGlobalUsageWeighting() / $sum) * $platformUnweightedScore;
    }

    /**
     * @param float $sumWeightedPlatformScores
     *
     * @return float
     */
    public function weightedSocialMediaScoreOverride(float $sumWeightedPlatformScores)
    {
        $postObj = $this->systemConfigRepository->getByName('post_platform_scoring_metric');
        $socialObj = $this->systemConfigRepository->getByName('social_media_max_score');

        $social = $socialObj ? (float)$socialObj->getVal() : 900.0;
        $post = $postObj ? (float)$postObj->getVal() : 5.0;

        return ($sumWeightedPlatformScores / $post * $social);
    }

    /**
     * @param float $sumWeightedPlatformScores
     *
     * @return float
     */
    public function weightedSocialMediaScore(float $sumWeightedPlatformScores)
    {
        $postObj = $this->systemConfigRepository->getByName('post_platform_scoring_metric');
        $socialObj = $this->systemConfigRepository->getByName('social_media_max_score');
        $post = $postObj ? (float)$postObj->getVal() : 5.0;
        $social = $socialObj ? (float)$socialObj->getVal() : 900.0;
        return ($sumWeightedPlatformScores / $post) * $social;
    }

    /**
     * @param float $weightedSocialMediaScore
     *
     * @return float
     */
    private function riskScore(float $weightedSocialMediaScore)
    {
        $socialObj = $this->systemConfigRepository->getByName('social_media_max_score');
        $social = $socialObj ? (float)$socialObj->getVal() : 900.0;
        return (1 - ($weightedSocialMediaScore / $social)) * 100;
    }

    /**
     * @param float $weightedSocialMediaScore
     *
     * @return float
     */
    private function riskScoreOverride(float $weightedSocialMediaScore)
    {
        $socialObj = $this->systemConfigRepository->getByName('social_media_max_score');
        $social = $socialObj ? (float)$socialObj->getVal() : 900.0;
        return (1 - ($weightedSocialMediaScore / $social)) * 100;
    }

    /**
     * @param        $questions
     * @param string $platform
     *
     * @return array
     */
    private function behaviorScores($questions, string $platform)
    {
        $systemObj = $this->systemConfigRepository->getByName('behavior_weighting');
        $behavior_constant = $systemObj ? (float)$systemObj->getVal() : 0.08;

        $bScoreArray = [];
        $constantManagement = [];
        /** @var Question $question */
        foreach ($questions as $question) {
            /** @var Answer $answer */
            $answer = $question->getAnswers()[0];
            if ($answer && count($answer->getProofs()) > 0) {
                /** @var Proof $proof */
                foreach ($answer->getProofs() as $proof) {
                    $bArray = $proof->getBehaviourScores();

                    if (count($bArray) > 0) {
                        foreach ($bArray as $key => $value) {
                            if (!key_exists($key, $bScoreArray)) {
                                $bScoreArray[$key] = 0.0;
                            }

                            if ($value) {
                                $bScoreArray[$key] += $value;
                            } else {
                                $bScoreArray[$key] += 0;
                            }

                            //Daniel delete if not needed
//                            if ($value === 0) {
//                                if (in_array($key, $constantManagement)) {
//                                    $bScoreArray[$key] += 0;
//                                } else {
//                                    $bScoreArray[$key] += 2.5;
//                                    $constantManagement [] = $key;
//                                }
//                            } else {
//                                $bScoreArray[$key] += ($value * $behavior_constant) + 2.55;
//                            }

                        }
                    }
                }
            }
        }

        return $bScoreArray;
    }


    /**
     * @param $scores
     *
     * @return array
     */
    private function overallBehaviorScores($scores)
    {
        $systemObj = $this->systemConfigRepository->getByName('behavior_weighting');
        $behavior_constant = $systemObj ? (float)$systemObj->getVal() : 0.08;

        $bScoreArray = [];
        $x = 0;

        foreach ($scores as $platform => $platformScores) {
            if (count($platformScores) > 0) {
                foreach ($platformScores as $key => $score) {
                    if (!array_key_exists($key, $bScoreArray)) {
                        $bScoreArray[$key] = 0.0;
                    }

                    $bScoreArray[$key] += ($score * $behavior_constant);
                }
                $x++;
            }
        }

        foreach ($bScoreArray as $key => $value) {
            $sum = round(($value + 2.5), 2);
            $bScoreArray[$key] = $this->checkScoreValue($sum);
        }

        return $bScoreArray;
    }

    /**
     * @param $value
     *
     * @return float
     */
    public function checkScoreValue($value)
    {
        if ($value >= 5) {
            return "5.00";
        } elseif ($value <= -5) {
            return "-5.00";
        } else {
            return $value;
        }
    }

    /**
     * @param $platform
     * @param $currentReport
     *
     * @return float|int
     */
    public function answerCalAll($platform, $currentReport)
    {
        $qb = $this->repositoryAnswers->createQueryBuilder('p')
            ->andWhere('p.report = :report_id')
            ->andWhere('p.enabled = :enable')
            ->andWhere('p.platform = :platform')
            ->setParameter('enable', true)
            ->setParameter('platform', $platform)
            ->setParameter('report_id', $currentReport)
            ->select('sum(p.score)')
            ->getQuery()
            ->getSingleScalarResult();

        if ($qb) {
            return $qb;
        } else {
            return 0;
        }
    }

    /**
     * @param $sum
     *
     * @return float|int
     */
    public function scoreUnweighted($sum)
    {
        $postObj = $this->systemConfigRepository->getByName('post_platform_scoring_metric');
        $post = $postObj ? (float)$postObj->getVal() : 5.0;

        return $sum * $post;
    }

    /**
     * @param $sum
     *
     * @return float|int
     */
    public function score($sum)
    {
        $preObj = $this->systemConfigRepository->getByName('pre_platform_scoring_metric');
        $pre = $preObj ? (float)$preObj->getVal() : 30.0;

        return $sum / $pre;
    }


    /**
     * @param $data
     * @param $subject
     *
     * @return array
     */
    public function overrideScore($data, $subject, $getBehaviorScores)
    {
        $result = array();

        $sumWeightedPlatformScores = 0.0;
        $behaviorScores = array();

        foreach ($data['platforms'] as $key => $platformSection) {
            if (count($platformSection) > 0) {
                $makeFloatUnweighted = floatval($platformSection['unweighted_platform_score_rounded']);

                $weightedResult = $this->weightedPlatformScoreOverride($key, $makeFloatUnweighted, $subject);

                $result['platforms'][$key]['unweighted_platform_score'] = round($makeFloatUnweighted, 10);
                $result['platforms'][$key]['weighted_platform_score'] = round($weightedResult, 10);

                $result['platforms'][$key]['unweighted_platform_score_rounded'] = $platformSection['unweighted_platform_score_rounded'];
                $result['platforms'][$key]['weighted_platform_score_rounded'] = round($weightedResult, 2);
                $result['platforms'][$key]['comments'] = [];

                $sumWeightedPlatformScores += $weightedResult;
            }
        }

        if ($subject->getCompany()->isAllowTrait()) {
            $result['overall_behavior_scores'] = $getBehaviorScores;
        }

        $result['weighted_social_media_score'] = round($this->weightedSocialMediaScoreOverride($sumWeightedPlatformScores), 6);

        $roundOff = round($this->riskScoreOverride($this->weightedSocialMediaScoreOverride($sumWeightedPlatformScores)), 6);
        $result['risk_score'] = round($roundOff, 2);

        return $result;
    }


}