<?php

use App\Contracts\AnswerRepositoryInterface;
use App\Contracts\GlobalWeightsRepositoryInterface;
use App\Contracts\SystemConfigRepositoryInterface;
use App\Entity\Answer;
use App\Entity\Company;
use App\Entity\GlobalWeights;
use App\Entity\Profile;
use App\Entity\Question;
use App\Entity\SystemConfig;
use App\EntityQuestion;
use App\Entity\Subject;
use App\Repository\AnswerRepository;
use App\Repository\SystemConfigRepository;
use App\Repository\GlobalWeightsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use App\Service\ReportScoreCalculator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ReportScoreCalculatorTest extends TestCase
{

    private $ReportScoreCalculator;
    private $subjectMock;

    public function setUp()
    {
        //$this->ReportScoreCalculator = $this->createMock(ReportScoreCalculator::class);

        //$this->reportScoreCalculator = Mockery::mock('ReportScoreCalculator');
        //$this->company = Mockery::mock('Company');
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    /**
     *
     */
    public function testPlatformUnweightedScore()
    {
        echo "ReportScoreCalculator:  platform Unweighted Score \n";

        $systemConfigRepositoryMock = Mockery::mock(SystemConfigRepositoryInterface::class);
        $globalWeightsRepositoryMock = Mockery::mock(GlobalWeightsRepositoryInterface::class);
        $answerRepositoryMock = Mockery::mock(AnswerRepositoryInterface::class);

        $prePlatformScoringMock = Mockery::mock(SystemConfig::class);
        $prePlatformScoringMock->shouldReceive('getVal')->andReturn(30);
        $systemConfigRepositoryMock->shouldReceive('getByName')->with('pre_platform_scoring_metric')->andReturn($prePlatformScoringMock);

        $postPlatformScoringMock = Mockery::mock(SystemConfig::class);
        $postPlatformScoringMock->shouldReceive('getVal')->andReturn(5);
        $systemConfigRepositoryMock->shouldReceive('getByName')->with('post_platform_scoring_metric')->andReturn($postPlatformScoringMock);

        $socialMediaScoringMock = Mockery::mock(SystemConfig::class);
        $socialMediaScoringMock->shouldReceive('getVal')->andReturn(900);
        $systemConfigRepositoryMock->shouldReceive('getByName')->with('social_media_max_score')->andReturn($socialMediaScoringMock);

        //dd($systemConfigRepositoryMock);

        $entityManagerMock = $this->createMock(EntityManagerInterface::class);

        $reportScoreCalculator = new ReportScoreCalculator(

            $entityManagerMock,
            $globalWeightsRepositoryMock,
            $systemConfigRepositoryMock,
            $answerRepositoryMock
        );

        $answerMock = Mockery::mock(Answer::class);
        $answerMock->shouldReceive('getAnswer')->andReturn('no');
        $answerMockCollection = new ArrayCollection([$answerMock]);

        //$score = [2.5, 0.63, 1.26, 1.94, 2.13, 2.13, 2.5, 2.78, 3.84, 4.26, 5, 0.22, 1.16, 1.99];
        //$score = [2.5, 0.63, 1.26, 1.94, 2.13, 2.13, 2.5, 2.78, 3.84, 4.26, 5, 0.22, 1.16, 1.99];

        $facebookQuestionMock = Mockery::mock(Question::class);
        $facebookQuestionMock->shouldReceive('getAnswers')->andReturn($answerMockCollection);
        $facebookQuestionMock->shouldReceive('getAnswerScore')->andReturn([5], [2.5], [5], [2.5], [2.5], [0], [0], [5]);
        $facebookQuestionMock->shouldReceive('getAnswerType')->andReturn('yes_no');

        $linkedInQuestionMock = Mockery::mock(Question::class);
        $linkedInQuestionMock->shouldReceive('getAnswers')->andReturn($answerMockCollection);
        $linkedInQuestionMock->shouldReceive('getAnswerScore')->andReturn([5], [4.99], [5], [5], [5], [0], [0], [5]);
        $linkedInQuestionMock->shouldReceive('getAnswerType')->andReturn('yes_no');

        // Global Weights
        $globalFacebookWeightMock = Mockery::mock(GlobalWeights::class);
        $globalFacebookWeightMock->shouldReceive('getGlobalUsageWeighting')->andReturn(12);
        $globalLinkedInWeightMock = Mockery::mock(GlobalWeights::class);
        $globalLinkedInWeightMock->shouldReceive('getGlobalUsageWeighting')->andReturn(5);
        $globalTwitterWeightMock = Mockery::mock(GlobalWeights::class);
        $globalTwitterWeightMock->shouldReceive('getGlobalUsageWeighting')->andReturn(12);
        $globalInstagramWeightMock = Mockery::mock(GlobalWeights::class);
        $globalInstagramWeightMock->shouldReceive('getGlobalUsageWeighting')->andReturn(12);
        $globalPinterestWeightMock = Mockery::mock(GlobalWeights::class);
        $globalPinterestWeightMock->shouldReceive('getGlobalUsageWeighting')->andReturn(5);
        $globalFlickrWeightMock = Mockery::mock(GlobalWeights::class);
        $globalFlickrWeightMock->shouldReceive('getGlobalUsageWeighting')->andReturn(1);
        $globalYoutubeWeightMock = Mockery::mock(GlobalWeights::class);
        $globalYoutubeWeightMock->shouldReceive('getGlobalUsageWeighting')->andReturn(1);
        $globalWebWeightMock = Mockery::mock(GlobalWeights::class);
        $globalWebWeightMock->shouldReceive('getGlobalUsageWeighting')->andReturn(11);
        $globalWeightsRepositoryMock->shouldReceive('getByPlatform')->with('facebook')->andReturn($globalFacebookWeightMock);
        $globalWeightsRepositoryMock->shouldReceive('getByPlatform')->with('linkedin')->andReturn($globalLinkedInWeightMock);
        $globalWeightsRepositoryMock->shouldReceive('getByPlatform')->with('twitter')->andReturn($globalTwitterWeightMock);
        $globalWeightsRepositoryMock->shouldReceive('getByPlatform')->with('instagram')->andReturn($globalInstagramWeightMock);
        $globalWeightsRepositoryMock->shouldReceive('getByPlatform')->with('pinterest')->andReturn($globalPinterestWeightMock);
        $globalWeightsRepositoryMock->shouldReceive('getByPlatform')->with('flickr')->andReturn($globalFlickrWeightMock);
        $globalWeightsRepositoryMock->shouldReceive('getByPlatform')->with('youtube')->andReturn($globalYoutubeWeightMock);
        $globalWeightsRepositoryMock->shouldReceive('getByPlatform')->with('web')->andReturn($globalWebWeightMock);

        $questionsMock = [
            'platforms' => [
                'facebook' => [
                    $facebookQuestionMock,
                    $facebookQuestionMock,
                    $facebookQuestionMock,
                    $facebookQuestionMock,
                    $facebookQuestionMock,
                    $facebookQuestionMock,
                    $facebookQuestionMock,
                    $facebookQuestionMock,
                ],
                'linkedin' => [
                    $linkedInQuestionMock,
                    $linkedInQuestionMock,
                    $linkedInQuestionMock,
                    $linkedInQuestionMock,
                    $linkedInQuestionMock,
                    $linkedInQuestionMock,
                    $linkedInQuestionMock,
                    $linkedInQuestionMock,
                ],
            ]
        ];

        $subjectMock = Mockery::mock(Subject::class);

        $companyMock = Mockery::mock(Company::class);
        $subjectMock->shouldReceive('getCompany')->andReturn($companyMock);
        $companyMock->shouldReceive('isAllowTrait')->andReturn(false);

        // Mock Profiles
        $facebookProfileMock = Mockery::mock(Profile::class);
        $facebookProfileMock->shouldReceive('isValid')->andReturn(true);
        $linkedInProfileMock = Mockery::mock(Profile::class);
        $linkedInProfileMock->shouldReceive('isValid')->andReturn(true);
        $subjectMock->shouldReceive('getPlatformProfiles')->with('facebook')->andReturn([$facebookProfileMock]);
        $subjectMock->shouldReceive('getPlatformProfiles')->with('linkedin')->andReturn([$linkedInProfileMock]);
        $subjectMock->shouldReceive('getPlatformProfiles')->andReturn([]);

        $test = $reportScoreCalculator->calculateReportScore($questionsMock, $subjectMock);

//        $test = [
//            "platforms" => [
//                "facebook" => [
//                    "unweighted_platform_score" => 0.11,
//                    "weighted_platform_score" => 0.01,
//                    "comments" => []
//                ],
//                "linkedin" => [
//                    "unweighted_platform_score" => 0.11,
//                    "weighted_platform_score" => 0.01,
//                    "comments" => []
//                ]
//            ],
//            "weighted_social_media_score" => 3.6,
//            "risk_score" => 99.6
//        ]
//     var_dump($test);
//     die();
        //$test1 = $test['platforms']['facebook']['unweighted_platform_score'];

//        $result = [
//            "platforms" => [
//                "facebook" => [
//                    "unweighted_platform_score" => 3.75,
//                    "weighted_platform_score" => 2.65,
//                    "comments" => [],
//                    "behavior_scores" => [],
//                ],
//                "linkedin" => [
//                    "unweighted_platform_score" => 5.0,
//                    "weighted_platform_score" => 1.47,
//                    "comments" => [],
//                    "behavior_scores" => [],
//                ]
//            ],
//            "overall_behavior_scores" => [],
//            "weighted_social_media_score" => 741.6,
//            "risk_score" => 17.6
//        ];

        $this->assertEquals('3.75', $test['platforms']['facebook']['unweighted_platform_score']);
        $this->assertEquals('2.65', $test['platforms']['facebook']['weighted_platform_score']);

        $this->assertEquals('5.0', $test['platforms']['linkedin']['unweighted_platform_score']);
        $this->assertEquals('1.47', $test['platforms']['linkedin']['weighted_platform_score']);

        $this->assertEquals('741.6', $test['weighted_social_media_score']);
        $this->assertEquals('17.6', $test['risk_score']);

//        $questionsMock = [
//            'platforms' => [
//                'facebook' => [
//                    'question' => 'facebook_active_account',
//                    'answerScore' => 2.5,
//                    'question' => 'facebook_connections',
//                    'answerScore' => 2.5,
//                    'question' => 'facebook_has_account',
//                    'answerScore' => 5,
//                    'question' => 'facebook_information_disclosed',
//                    'answerScore' => 5,
//                    'question' => 'facebook_positive_content',
//                    'answerScore' => 2.5,
//                    'question' => 'facebook_privacy_settings',
//                    'answerScore' => 5,
//                ],
//                'linkedin' => [
//                    'question' => 'linkedin_active_account',
//                    'answerScore' => 4.99,
//                    'question' => 'linkedin_connections',
//                    'answerScore' => 5,
//                    'question' => 'linkedin_has_account',
//                    'answerScore' => 5,
//                    'question' => 'linkedin_information_disclosed',
//                    'answerScore' => 5,
//                    'question' => 'linkedin_positive_content',
//                    'answerScore' => 5,
//                    'question' => 'linkedin_privacy_settings',
//                    'answerScore' => 5,
//                ]
//            ]
//
//        ];

        $result = [
            "platforms" => [
                "facebook" => [
                    "unweighted_platform_score" => 3.75,
                    "weighted_platform_score" => 2.65,
                    "comments" => [],
                    "behavior_scores" => [],
                ],
                "linkedin" => [
                    "unweighted_platform_score" => 5.0,
                    "weighted_platform_score" => 1.47,
                    "comments" => [],
                    "behavior_scores" => [],
                ]
            ],
            "overall_behavior_scores" => [],
            "weighted_social_media_score" => 741.6,
            "risk_score" => 17.6
        ];

        // Zander Code

//        echo "ReportScoreCalculator:  platform Unweighted Score";
//o 
//        // Class Setup
//        // TODO: Find what this returns
//        $globalWeightsRepositoryMock = Mockery::mock(\App\Repository\GlobalWeightsRepository::class);
//        $globalWeightsRepositoryMock->shouldReceive('getByPlatform')->with('facebook')->andReturn('');
//        // TODO: Find what this returns
//        $systemConfigRepositoryMock = Mockery::mock(\App\Repository\SystemConfigRepository::class);
//        $systemConfigRepositoryMock->shouldReceive('getByName')->with('pre_platform_scoring_metric')->andReturn('');
//        $systemConfigRepositoryMock->shouldReceive('getByName')->with('post_platform_scoring_metric')->andReturn('');
//
//
//        $answerRepositoryMock = Mockery::mock(\App\Repository\AnswerRepository::class);
//        $reportScoreCalculator = new ReportScoreCalculator(
//            $globalWeightsRepositoryMock,
//            $systemConfigRepositoryMock,
//            $answerRepositoryMock
//        );
//
//        // Function Setup
//        $answerMock = Mockery::mock(\App\Entity\Answer::class);
//        $answerMock->shouldReceive('getAnswer')->andReturn('yes');
//        $platformQuestionMock = Mockery::mock(\App\Entity\Question::class);
//        $platformQuestionMock->shouldReceive('getAnswers')->andReturn([$answerMock]);
//        $platformQuestionMock->shouldReceive('getQuestionType')->andReturn('yes_no');
//        $platformQuestionMock->shouldReceive('answerScore')->andReturn(5);
//
//        $questions = [];
//        $companyMock = Mockery::mock(Company::class);
//        $companyMock->shouldReceive('isAllowTrait')->andReturn(true);
//        $subjectMock = Mockery::mock(Subject::class);
//        $subjectMock->shouldReceive('getCompany')->andReturn($companyMock);
//
//        $result = $reportScoreCalculator->calculateReportScore();
//        // Todo: Assertions
//
//        $subject = new Subject();
//        $questions =[
//            'platforms' => [
//                'facebook' => [
//                    // Mocked Questions
//                ]
//            ]
//        ];

        // Zander Code end

    }


}


//old code

//       ok back

//$this->assertEquals('', $testResult);

//$company = Mockery::mock('Company');
//$subject = Mockery::mock('Subject');

//$question = new Question();

//$questions = [];

//$reportScoreCalculator->calculateReportScore($questions, $subject);
//$this->ReportScoreCalculator->calculateReportScore($questions, $subject);

//dd($testResult);

//$company->shouldReceive('isAllowTrait')->andReturn(false);

// var_dump($mockResult);

//        $question = [
//        ['platforms'=> [
//            'facebook' => [
//            '0' => 'unweighted_platform_score'
//            ]
//        ]
//        ];

//dd($question);
//$subject = [];

//        $result = [
//            "platforms" => [
//                "facebook" => [
//                    "unweighted_platform_score" => 3.75,
//                    "weighted_platform_score" => 2.65,
//                    "comments" => [],
//                    "behavior_scores" => [],
//                ],
//                "linkedin" => [
//                    "unweighted_platform_score" => 5.0,
//                    "weighted_platform_score" => 1.47,
//                    "comments" => [],
//                    "behavior_scores" => [],
//                ]
//            ],
//            "overall_behavior_scores" => [],
//            "weighted_social_media_score" => 741.6,
//            "risk_score" => 17.6
//
//        ];

//        $result = 'test123';
//
//        $this->assertEquals($result, '10');

// Function Setup
//        $answerMock = Mockery::mock(\App\Entity\Answer::class);
//        $answerMock->shouldReceive('getAnswer')->andReturn('yes');
//        $platformQuestionMock = Mockery::mock(\App\Entity\Question::class);
//        $platformQuestionMock->shouldReceive('getAnswers')->andReturn([$answerMock]);
//        $platformQuestionMock->shouldReceive('getQuestionType')->andReturn('yes_no');
//        $platformQuestionMock->shouldReceive('answerScore')->andReturn(5);

// $questions = [];
//        $companyMock = Mockery::mock(Company::class);
//        $companyMock->shouldReceive('isAllowTrait')->andReturn(true);
//        $subjectMock = Mockery::mock(Subject::class);
//        $subjectMock->shouldReceive('getCompany')->andReturn($companyMock);

//$result = $reportScoreCalculator->calculateReportScore();

//$systemConfigRepositoryMock = Mockery::mock(SystemConfigRepositoryInterface::class);

//        $globalWeightsRepository = new GlobalWeightsRepository();
//        $globalWeightsRepositoryMock = Mockery::mock($globalWeightsRepository);

//        $entityManagerMock = Mockery::mock(EntityManagerInterface::class);
//        $paramsMock = Mockery::mock(ParameterBagInterface::class);
//
//        $systemConfigRepository = new SystemConfigRepository($entityManagerMock,  $paramsMock);
//        $systemConfigRepositoryMock = Mockery::mock($systemConfigRepository);
//
//        $globalWeightsRepository = new GlobalWeightsRepository($entityManagerMock);
//        $globalWeightsRepositoryMock = Mockery::mock($globalWeightsRepository);
//
//        //$globalWeightsRepositoryMock = Mockery::mock(GlobalWeightsRepository::class);
//        $answerRepositoryMock = Mockery::mock(GlobalWeightsRepository::class);

//        $globalWeightsRepository = new GlobalWeightsRepository(
//            $entityManagerMock
//        );

//$entityManagerMock = Mockery::mock(EntityManagerInterface::class);

//$entityManagerMock->shouldReceive('getRepository')->andReturnTrue();

//$entityManagerMock->shouldReceive('getRepository')->with(Mockery::type(SystemConfig::class))->once();

//        $globalWeightsRepository = new GlobalWeightsRepository(
//            $entityManagerMock
//        );

//$entityManagerMock->shouldReceive('getRepository')->with(Mockery::type(SystemConfig::class))->once();

//$paramsMock = Mockery::mock(ParameterBagInterface::class);
//$paramsMock->shouldReceive('getRepository')->with(Mockery::type(SystemConfig::class))->once();
// $paramsMock->with(Mockery::type(SystemConfig::class))->add(BlobRestProxy::createBlobService(
//     $paramsMock->get('BLOB_ENDPOINTS_PROTOCOL')))->once();

// Class Setup
// TODO: Find what this returns
//$globalWeightsRepositoryMock = Mockery::mock(\App\Repository\GlobalWeightsRepository::class);
//$globalWeightsRepositoryMock->shouldReceive('getByPlatform')->with('facebook')->andReturn('');

//$entityManagerMock = Mockery::mock(EntityManagerInterface::class);

//$entityManagerMock->andReturn(true);

// TODO: Find what this returns

//$entityManagerMock->shouldReceive('getRepository')->andReturnTrue();

//$systemConfigRepositoryMock->shouldReceive('getByName')->with('pre_platform_scoring_metric')->andReturn('');
//$systemConfigRepositoryMock->shouldReceive('getByName')->with('post_platform_scoring_metric')->andReturn('');

//        $answerRepositoryMock = Mockery::mock(AnswerRepository::class);
//        //$answerRepositoryMock->andReturn(true);
//
//        $reportScoreCalculator = new ReportScoreCalculator(
//            //$globalWeightsRepositoryMock,
//            //$systemConfigRepositoryMock
//            //$answerRepositoryMock
//        );

// Function Setup
//        $answerMock = Mockery::mock(\App\Entity\Answer::class);
//        $answerMock->shouldReceive('getAnswer')->andReturn('yes');
//        $platformQuestionMock = Mockery::mock(\App\Entity\Question::class);
//        $platformQuestionMock->shouldReceive('getAnswers')->andReturn([$answerMock]);
//        $platformQuestionMock->shouldReceive('getQuestionType')->andReturn('yes_no');
//        $platformQuestionMock->shouldReceive('answerScore')->andReturn(5);

// $questions = [];
//        $companyMock = Mockery::mock(Company::class);
//        $companyMock->shouldReceive('isAllowTrait')->andReturn(true);
//        $subjectMock = Mockery::mock(Subject::class);
//        $subjectMock->shouldReceive('getCompany')->andReturn($companyMock);

//$result = $reportScoreCalculator->calculateReportScore();
// Todo: Assertions

//$subject = new Subject();
//$questions = [
//    'platforms' => [
//        'facebook' => [
//            // Mocked Questions
//        ]
//    ]
//];

//dd($subject);

//$reportScoreCalculator = $this->createMock(ReportScoreCalculator::class);

//$reportScoreCalculator = Mockery::mock(ReportScoreCalculator::class);

//$reportScoreCalculator = new ReportScoreCalculator();

//        $testResult = $reportScoreCalculator->shouldReceive('calculateReportScore')
//            ->withArgs([$questions, $subject]);

//$reportScoreCalculator->shouldReceive('platformUnweightedScore');

// $reportScoreCalculator->calculateReportScore($questions, $subject);