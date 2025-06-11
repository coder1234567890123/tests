<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Answer;
use App\Entity\GlobalWeights;
use App\Entity\SystemConfig;
use App\Entity\Proof;
use App\Entity\Report;
use App\Service\ReportScoreCalculator;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class AnswerRepository
 *
 * @package App\Repository
 */
final class CalculationRepository
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @var TokenStorageInterface
     */
    private $userToken;
    /**
     * @var ObjectRepository
     */
    private $repositoryAnswers;
    /**
     * @var ObjectRepository
     */
    private $repositoryGlobalWeights;
    /**
     * @var ObjectRepository
     */
    private $repositorySystemConfig;

    /**
     * AnswerRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param TokenStorageInterface  $token
     * @param SystemConfigRepository $systemConfigRepository
     * @param ReportScoreCalculator  $reportScoreCalculator
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $token,
        SystemConfigRepository $systemConfigRepository,
        ReportScoreCalculator $reportScoreCalculator,
        GlobalWeightsRepository $globalWeightRepository

    )
    {
        $this->entityManager = $entityManager;
        $this->repositoryAnswers = $entityManager->getRepository(Answer::class);
        $this->repositoryProof = $entityManager->getRepository(Proof::class);
        $this->repositoryReport = $entityManager->getRepository(Report::class);
        $this->repositoryGlobalWeights = $entityManager->getRepository(GlobalWeights::class);
        $this->repositorySystemConfig = $entityManager->getRepository(SystemConfig::class);
        $this->userToken = $token->getToken()->getUser();

        $this->reportScoreCalculator = $reportScoreCalculator;
        $this->systemConfigRepository = $systemConfigRepository;
        $this->globalWeightRepository = $globalWeightRepository;
    }


    /**
     * @param $subject
     *
     * @return array
     */
    public function findById($subject)
    {
        $platformList = [
            'pinterest',
            'twitter',
            'instagram',
            'linkedin',
            'youtube',
            'flickr',
            'facebook',
            'web'
        ];

        $finalWeights['final_weights'] = $this->finalWeights();
        $scoringConfig['scoring_config'] = $this->scoringConfig();

        $reportScores['report_scores'] = $this->reportScores($subject);
        $reportScoresOverwrite['report_scores_overwrite'] = $this->reportScoresOverRight($subject);

        $calBreakDown['cal'] = $this->calBreakDown($subject, $platformList);
        $answers['answers'] = $this->getAllAnswers($subject, $platformList);
        $answers['behavior_scores_overview'] = $this->getAllBehaviorScores($subject, $platformList);

        return array_merge(
            $finalWeights,
            $scoringConfig,
            $reportScores,
            $reportScoresOverwrite,
            $calBreakDown,
            $answers
        );
    }

    /**
     * @return array
     */
    public function finalWeights()
    {
        $response = [];
        $qb = $this->repositoryGlobalWeights->createQueryBuilder('p')
            ->getQuery();

        foreach ($qb->execute() as $getData) {
            $response[] = [
                'social_platform' => $getData->getSocialPlatform(),
                'global_usage_weighting' => $getData->getGlobalUsageWeighting(),
            ];
        }

        return $response;
    }

    /**
     * @return array
     */
    public function scoringConfig()
    {
        $response = [];
        $qb = $this->repositorySystemConfig->createQueryBuilder('p')
            ->getQuery();

        foreach ($qb->execute() as $getData) {
            $response[] = [
                'score_type' => $getData->getOpt(),
                'val' => $getData->getVal(),
            ];
        }

        return $response;
    }

    /**
     * @param $subject
     *
     * @return mixed
     */
    public function reportScores($subject)
    {
        $currentReport = $subject->getCurrentReport();

        return $currentReport->getReportScores();
    }

    /**
     * @param $subject
     *
     * @return mixed
     */
    public function reportScoresOverRight($subject)
    {
        $currentReport = $subject->getCurrentReport();

        return $currentReport->getReportScoresUpdated();
    }

    /**
     * @param $subject
     *
     * @param $platformList
     *
     * @return array
     */
    public function calBreakDown($subject, $platformList)
    {
        $currentReport = $subject->getCurrentReport();

        $response = [];

        foreach ($platformList as $platform) {
            $answerCalAll = $this->reportScoreCalculator->answerCalAll($platform, $currentReport);
            $score = $this->reportScoreCalculator->score($answerCalAll);
            $finalScoreUnweighted = $this->reportScoreCalculator->scoreUnweighted($score);
            $weightedPreScoring = $this->reportScoreCalculator->weightedPlatformScore($platform, $finalScoreUnweighted, $subject);
            $preObj = $this->systemConfigRepository->getByName('pre_platform_scoring_metric');
            $postObj = $this->systemConfigRepository->getByName('post_platform_scoring_metric');
            $globalWeight = $this->globalWeightRepository->getByPlatform($platform)->getGlobalUsageWeighting();

            if ($score !== 0.0) {
                $response[] =
                    [
                        'platform' => $platform,
                        'answer_cal_all' => round($answerCalAll, 2),
                        'score' => round($score, 4),
                        'pre_platform_scoring_metric' => $preObj->getVal(),
                        'post_platform_scoring_metric' => $postObj->getVal(),
                        'global_weight' => $globalWeight,
                        'final_score_unweighted' => round($finalScoreUnweighted, 2),
                        'weighted_pre_scoring' => round($weightedPreScoring, 6),
                        'weighted' => round($weightedPreScoring, 2)
                    ];
            }
        }

        return $response;
    }

    /**
     * @param $subject
     * @param $platformList
     *
     * @return array
     */
    public function getAllAnswers($subject, $platformList)
    {
        $response = [];
        foreach ($platformList as $platform) {
            $response[$platform] = $this->getAnswers($subject, $platform);
        }

        return $response;
    }

    /**
     * @param $subject
     *
     * @return mixed
     */
    public function getAnswers($subject, $platform)
    {
        $currentReport = $subject->getCurrentReport();

        $response = [];

        $qb = $this->repositoryAnswers->createQueryBuilder('p')
            ->andWhere('p.report = :report_id')
            ->andWhere('p.enabled = :enable')
            ->andWhere('p.platform = :platform')
            ->setParameter('enable', true)
            ->setParameter('platform', $platform)
            ->setParameter('report_id', $currentReport)
            ->orderBy('p.platform', 'ASC')
            ->getQuery();

        if ($qb->execute()) {
            foreach ($qb->execute() as $getData) {
                $response[] = [
                    'question' => $getData->getQuestion()->getQuestion(),
                    'platform' => $getData->getPlatform(),
                    'answer_score' => $getData->getScore()
                ];
            }
        } else {
            return [];
        }

        return $response;
    }

    /**
     * @param $subject
     * @param $platformList
     *
     * @return array
     */
    public function getAllBehaviorScores($subject)
    {
        $qb = $this->repositoryAnswers->createQueryBuilder('p')
            ->where('p.report = :report_id')
            ->setParameter('report_id', $subject->getCurrentReport()->getId())
            ->orderBy('p.platform', 'ASC')
            ->getQuery();

        $response = [];

        foreach ($qb->execute() as $getData) {
            if ($this->getProof($getData->getId())) {
                $response[$getData->getPlatform()][] = [
                    "id" => $getData->getId(),
                    "question" => $this->getQuestions($getData->getQuestion()),
                    "count" => $this->getProofCount($getData->getId()),
                    "behaviour_score" => $this->behaviorScoresBreakdown($this->getProof($getData->getId()), $subject)

                ];
            }
        }

        return $response;
    }

    /**
     * @param $id
     *
     * @return arrays
     */
    public function getProof($id)
    {
        $qb = $this->repositoryProof->createQueryBuilder('p')
            ->where('p.answer = :answer_id')
            ->setParameter('answer_id', $id)
            ->getQuery();

        $response = [];

        foreach ($qb->execute() as $getData) {
            $response[] = [
                'id' => $getData->getId(),
                'comment' => $getData->getComment(),
                'behaviour_scores' => $getData->getBehaviourScores()
            ];
        }

        return $response;
    }

    /**
     * @param $quetion
     *
     * @return array
     */
    private function getQuestions($quetion)
    {
        if ($quetion) {
            return [
                "id" => $quetion->getId(),
                "question" => $quetion->getQuestion(),
            ];
        } else {
            return [];
        }
    }

    /**
     * @param $id
     *
     * @return arrays
     */
    public function getProofCount($id)
    {
        if ($id) {
            $qb = $this->repositoryProof->createQueryBuilder('p')
                ->where('p.answer = :answer_id')
                ->setParameter('answer_id', $id)
                ->getQuery();

            return count($qb->execute());
        } else {
            return 0;
        }
    }

    /**
     * @param $behaviorScores
     *
     * @return array
     */
    private function behaviorScoresBreakdown($behaviorScores, $subject)
    {
        $response = [];

        if ($behaviorScores) {
            foreach ($behaviorScores as $getData) {
                $response[] = [

                    'comment' => $getData['comment'],
                    'behaviour_scores' => [
                        "creativity" => [
                            "score" => $getData['behaviour_scores']['creativity'],
                            "cal" => $this->behaviorScoresCal($getData['behaviour_scores']['creativity'])
                        ],
                        "network_reach" => [
                            "score" => $getData['behaviour_scores']['network_reach'],
                            "cal" => $this->behaviorScoresCal($getData['behaviour_scores']['network_reach'])
                        ],
                        "network_engagement" => [
                            "score" => $getData['behaviour_scores']['network_engagement'],
                            "cal" => $this->behaviorScoresCal($getData['behaviour_scores']['network_engagement'])
                        ],
                        "professional_image" => [
                            "score" => $getData['behaviour_scores']['professional_image'],
                            "cal" => $this->behaviorScoresCal($getData['behaviour_scores']['professional_image'])
                        ],
                        "communication_skills" => [
                            "score" => $getData['behaviour_scores']['communication_skills'],
                            "cal" => $this->behaviorScoresCal($getData['behaviour_scores']['communication_skills'])
                        ],
                        "teamwork_collaboration" => [
                            "score" => $getData['behaviour_scores']['teamwork_collaboration'],
                            "cal" => $this->behaviorScoresCal($getData['behaviour_scores']['teamwork_collaboration'])
                        ],
                        "professional_engagement" => [
                            "score" => $getData['behaviour_scores']['professional_engagement'],
                            "cal" => $this->behaviorScoresCal($getData['behaviour_scores']['professional_engagement'])
                        ],
                        "business_writing_ability" => [
                            "score" => $getData['behaviour_scores']['business_writing_ability'],
                            "cal" => $this->behaviorScoresCal($getData['behaviour_scores']['business_writing_ability'])
                        ]

                    ]
                ];
            }

            return $response;
        } else {
            return [];
        }
    }

    private function behaviorScoresCal($score)
    {
        return ($score * 0.08) + 2.5;
    }

    /**
     * @param $proof
     *
     * @return bool
     */
    public function proofCheck($proof)
    {
        if ($proof) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $subject
     *
     * @return mixed
     */
    public
    function getAnswers2($subject, $platform)
    {
        $platformList = [
            'pinterest',
            'twitter',
            'instagram',
            'linkedin',
            'youtube',
            'flickr',
            'facebook',
            'web'
        ];

        $currentReport = $subject->getCurrentReport();

        $response = [];

        $qb = $this->repositoryAnswers->createQueryBuilder('p')
            ->andWhere('p.report = :report_id')
            ->andWhere('p.enabled = :enable')
            ->andWhere('p.platform = :platform')
            ->setParameter('enable', true)
            ->setParameter('platform', $platform)
            ->setParameter('report_id', $currentReport)
            ->orderBy('p.platform', 'ASC')
            ->getQuery();

        if ($qb->execute()) {
            foreach ($qb->execute() as $getData) {
                $response[] = [
                    'question' => $getData->getQuestion()->getQuestion(),
                    'platform' => $getData->getPlatform(),
                    'answer_score' => $getData->getScore()
                ];
            }
        } else {
            return [];
        }

        return $response;
    }


}
