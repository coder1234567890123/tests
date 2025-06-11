<?php
/**
 * Created by PhpStorm.
 * User: kuda
 * Date: 8/29/19
 * Time: 12:03 PM
 */

namespace App\Service;

use App\Entity\Accounts;
use App\Entity\Comment;
use App\Entity\Company;
use App\Entity\IdentityConfirm;
use App\Entity\Profile;
use App\Entity\Report;
use App\Service\ApiCompanyProductService;
use App\Repository\ReportRepository;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Proxies\__CG__\App\Entity\Subject;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class DashboardService
 *
 * @package App\Service
 */
class ApiReturnService
{
    /**
     * @var ObjectRepository
     */
    private $repositorySubject;
    /**
     * @var ObjectRepository
     */
    private $repositoryProfile;
    /**
     * @var ObjectRepository
     */
    private $repositoryComment;
    /**
     * @var ObjectRepository
     */
    private $repositoryCompany;
    /**
     * @var TokenInterface|null
     */
    private $userToken;

    /**
     * ApiReturnService constructor.
     *
     * @param EntityManagerInterface   $entityManager
     * @param TokenStorageInterface    $token
     * @param ApiCompanyProductService $apiCompanyProductService
     * @param ParameterBagInterface    $params
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $token,
        ApiCompanyProductService $apiCompanyProductService,
        ParameterBagInterface $params
    )
    {
        $this->repositorySubject = $entityManager->getRepository(\App\Entity\Subject::class);
        $this->repositoryProfile = $entityManager->getRepository(Profile::class);
        $this->repositoryComment = $entityManager->getRepository(Comment::class);
        $this->repositoryCompany = $entityManager->getRepository(Company::class);
        $this->repositoryAccount = $entityManager->getRepository(Accounts::class);
        $this->repositoryIdentityConfirm = $entityManager->getRepository(IdentityConfirm::class);
        $this->repositoryReport = $entityManager->getRepository(Report::class);

        $this->apiCompanyProductService = $apiCompanyProductService;
        $this->userToken = $token->getToken();

        $this->pdfBlobUrl = $params->get('BLOB_URL') . '/pdf';
        $this->imagesBlobUrl = $params->get('BLOB_URL') . '/subject-images';
    }


    /**
     * @param $getData
     *
     * @return array
     */
    public function getSubjectIndex($getData)
    {
        $response = [];

        if ($getData) {
            foreach ($getData as $showData) {
                $response[] = $this->getSubjectInfo($showData);
            }
            return $response;
        } else {
            return [];
        }
    }

    /**
     * @param $showData
     *
     * @return array
     */
    public function getSubjectInfo($showData)
    {
        if ($showData) {
            //troubleshooting subjects not loading
            $company="";
            if(is_null($showData->getCompany())){
                $company = "Edit this subject";
            }
            else {
                $company = $showData->getCompany()->getName();
            }
            return [
                'id' => $showData->getId(),
                'first_name' => $showData->getFirstName(),
                'last_name' => $showData->getLastName(),
                'created_at' => $showData->getCreatedAt(),
                'created_by' => $showData->getCreatedBy()->getFullName(),
                'report_type' => $showData->getReportType(),
                'company' => $company,//$showData->getCompany()->getName(), //$company,
                'status' => $showData->getStatus(),
            ];
        } else {
            return [];
        }
    }

    /**
     * @param $subject
     *
     * @return mixed
     */
    public function getSubject($subject)
    {
        $getData = $this->repositorySubject->findBy([
            'id' => $subject
        ]);

        return $this->subjectRolesByView($getData);
    }


    /**
     * @param $subjectId
     *
     * @return array
     */
    public function getSubjectByIdentification($subjectId)
    {
        $getData = $this->repositorySubject->findBy([
            'identification' => $subjectId
        ]);

        return $this->subjectRolesByView($getData);
    }

    /**
     * @param $getData
     *
     * @return array
     */
    public function subjectRolesByView($getData)
    {
        $responseFirst = [];
        foreach ($getData as $showData) {
            $responseFirst = [
                'id' => $showData->getId(),
                'identification' => $showData->getIdentification(),
                'first_name' => $showData->getFirstName(),
                'middle_name' => $showData->getMiddleName(),
                'last_name' => $showData->getLastName(),
                'maiden_name' => $showData->getMaidenName(),
                'nickname' => $showData->getNickname(),
                'handles' => $showData->getHandles(),
                'date_of_birth' => $showData->getDateOfBirth(),
                'gender' => $showData->getGender(),
                'primary_email' => $showData->getPrimaryEmail(),
                'primary_mobile' => $showData->getPrimaryMobile(),
                'secondary_email' => $showData->getSecondaryEmail(),
                'secondary_mobile' => $showData->getSecondaryMobile(),
                'blob_folder' => $showData->getBlobFolder(),
                'identity_image_url' => $this->getImageUrl($showData),
                'report_pdf' => [
                    'file_name' => $this->getPDF($showData),
                    'url' => ''
                ],
                'address' => [
                    'street' => $showData->getAddress()->getstreet(),
                    'suburb' => $showData->getAddress()->getSuburb(),
                    'postal_code' => $showData->getAddress()->getPostalCode(),
                    'city' => $showData->getAddress()->getCity()
                ],
                'province' => $showData->getProvince(),
                'country' => $showData->getCountry(),
                'current_country' => $this->countryCheck($showData->getCountry()),
                'status' => $showData->getStatus(),
                'report_type' => $showData->getReportType(),
                'allow_trait' => $showData->getCompany()->isAllowTrait(),
                'company' => [
                    'id' => $showData->getCompany()->getId(), // must check to see if must be removed
                    'name' => $showData->getCompany()->getName()
                ],
                'comments' => [],
                'qualifications' => $this->getQualifications($showData->getQualifications()),
                'employments' => $this->getEmployments($showData->getEmployments()),
                'current_report' => [
                    'risk_comment' => ''
                ],
                'current_process' => [
                    'risk_comment' => '',
                    'edit_report' => '',
                ],
                'request_check' => $this->requestCheck($getData),
                'accounts' => $this->apiCompanyProductService->basicAccountDetails('subject', $getData)
            ];

            switch ($this->userToken->getUser()->getRoles()[0]) {
                case 'ROLE_TEAM_LEAD':
                case 'ROLE_ANALYST':
                case 'ROLE_SUPER_ADMIN':

                    $response = $this->subjectAdminResponse($showData);

                    return array_merge($responseFirst, $response);
                    break;

                default:
                    if ($responseFirst) {
                        return $responseFirst;
                    } else {
                        return [];
                    }
            }
        }
        if ($responseFirst) {
            return $responseFirst;
        } else {
            return [];
        }
    }

    /**
     * @param $showData
     *
     * @return array
     */
    private function requestCheck($showData)
    {
        $response = [];

        if ($showData) {
            $qb = $this->repositorySubject->createQueryBuilder('p')
                ->andWhere('p.id = :id')
                ->setParameter('id', $showData[0]->getId())
                ->getQuery();

            foreach ($qb->execute() as $getData) {
                if ($getData->getCountry()) {
                    $country = true;
                } else {
                    $country = false;
                }

                if ($getData->getPrimaryEmail()) {
                    $primaryEmail = true;
                } else {
                    $primaryEmail = false;
                }

                if ($getData->getPrimaryMobile()) {
                    $primaryMobile = true;
                } else {
                    $primaryMobile = false;
                }
            }

            if (
                $getData->getCountry() &&
                $getData->getPrimaryEmail() &&
                $getData->getPrimaryMobile()
            ) {
                $warning = false;
            } else {
                $warning = true;
            }

            $response = [
                'warning' => $warning,
                'check' => [
                    [
                        'name' => 'Primary Mobile',
                        'check' => $primaryMobile

                    ],
                    [
                        'name' => 'Primary Email',
                        'check' => $primaryEmail

                    ],
                    [
                        'name' => 'Country',
                        'check' => $country

                    ]
                ]

            ];

            return $response;
        } else {
            return [];
        }
    }

    /**
     * @param $country
     *
     * @return string
     */
    private function countryCheck($country)
    {
        if ($country) {
            return $country->getName();
        } else {
            return '';
        }
    }

    /**
     * @param $showData
     *
     * @return array
     */
    private function subjectAdminResponse($showData): array
    {
        return [
            'approval_comments' => $this->getApprovalComments($showData->getCurrentReport()),
            'current_process' => [
                'risk_comment' => $this->processRiskComment($showData->getStatus(), $showData->getCurrentReport()),
                'edit_report' => $this->processEditReport($showData->getStatus(), $showData->getCurrentReport()),

            ],
            'comments' => $this->getComments($showData->getCurrentReport()),
            'qualifications' => $this->getQualifications($showData->getQualifications()),
            'employments' => $this->getEmployments($showData->getEmployments()),
            'current_report' => $this->getCurrentReport($showData->getCurrentReport()),
            'facebook_profiles' => $this->subjectProfiles('facebook', $showData->getId()),
            'instagram_profiles' => $this->subjectProfiles('instagram', $showData->getId()),
            'twitter_profiles' => $this->subjectProfiles('twitter', $showData->getId()),
            'linkedin_profiles' => $this->subjectProfiles('linkedin', $showData->getId()),
            'pinterest_profiles' => $this->subjectProfiles('pinterest', $showData->getId()),
            'flickr_profiles' => $this->subjectProfiles('flickr', $showData->getId()),
            'youtube_profiles' => $this->subjectProfiles('youtube', $showData->getId()),
            'web_search_profiles' => $this->subjectProfiles('web', $showData->getId()),
            'web_profiles' => $this->subjectProfiles('web_profiles', $showData->getId()),

        ];
    }

    /**
     * @param $status
     * @param $riskComment
     *
     * @return bool
     */
    private function processRiskComment($status, $riskComment): ?bool
    {
        if ($riskComment) {
            if ($status === 'investigation_completed' && $riskComment->getRiskComment() === 'none') {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param $status
     * @param $riskComment
     *
     * @return bool
     */
    private function processEditReport($status, $riskComment): ?bool
    {
        if ($riskComment) {
            return (
                    $status === 'investigation_completed' ||
                    $status === 'team_lead_approved' ||
                    $status === 'completed'
                )
                && $riskComment->getRiskComment() !== 'none';
        } else {
            return false;
        }
    }

    /**
     * @param $currentReport
     *
     * @return array
     */
    private function getCurrentReport($currentReport): ?array
    {
        if ($currentReport) {
            return $response[] = [
                'id' => $currentReport->getId(),
                'sequence' => $currentReport->getSequence(),
                'request_type' => $currentReport->getRequestType(),
                'risk_comment' => $currentReport->getRiskComment(),
                'status' => $currentReport->getStatus(),
                'comments' => $this->getComments($currentReport)

            ];
        } else {
            return [];
        }
    }

    /**
     * @param $qualification
     *
     * @return array
     */
    public function getQualifications($qualification): ?array
    {
        if ($qualification) {
            if (count($qualification) >= 1) {
                $response = [];

                foreach ($qualification as $getData) {
                    $response[] = [
                        'id' => $getData->getId(),
                        'name' => $getData->getName(),
                        'start_date' => $getData->getStartDateFormatted(),
                        'end_date' => $getData->getEndDateFormatted(),
                        'institute' => $getData->getInstitute()
                    ];
                }
                return $response;
            }
            return [];
        } else {
            return [];
        }
    }

    /**
     * @param $employments
     *
     * @return array
     */
    public function getEmployments($employments): ?array
    {
        $response = [];

        if ($employments) {
            if (count($employments) >= 1) {
                foreach ($employments as $getData) {
                    $response[] = [
                        'id' => $getData->getId(),
                        'employer' => $getData->getEmployer(),
                        'job_title' => $getData->getJobTitle(),
                        'address' => $getData->getAddress(),
                        'province' => $getData->getProvince(),
                        'country' => $getData->getCountry(),
                        'currently_employed' => $getData->isCurrentlyEmployed(),
                        'start_date' => $getData->getStartDateFormatted(),
                        'end_date' => $getData->getEndDateFormatted(),
                    ];
                }
                return $response;
            }
            return [];
        } else {
            return [];
        }
    }

    /**
     * @param $platform
     * @param $subject
     *
     * @return array
     */
    private function subjectProfiles($platform, $subject): ?array
    {
        $qb = $this->repositoryProfile->createQueryBuilder('p')
            ->where('p.platform  = :platform')
            ->setParameter('platform', $platform)
            ->andWhere('p.subject  = :subject')
            ->setParameter('subject', $subject)
            ->getQuery();

        $response = [];

        if (count($qb->execute()) >= 1) {
            foreach ($qb->execute() as $getData) {
                $response[] = [
                    'id' => $getData->getid(),
                    'platform' => $getData->getplatform(),
                    'link' => $getData->getLink(),
                    'valid' => $getData->isValid(),
                    'phrase' => $getData->getPhrase(),
                    'priority' => $getData->getPriority()
                ];
            }
            return $response;
        } else {
            return [];
        }
    }

    /**
     * @param $reportId
     *
     * @return array
     */
    public function getComments($reportId): ?array
    {
        $response = [];

        if ($reportId !== null) {
            $qb = $this->repositoryComment->createQueryBuilder('p')
                ->where('p.report  = :report')
                ->setParameter('report', $reportId->getId())
                ->orderBy('p.createdAt', 'DESC')
                ->getQuery();

            if (count($qb->execute()) >= 1) {
                foreach ($qb->execute() as $getData) {
                    $response[] = [
                        'id' => $getData->getid(),
                        'comment' => $getData->getComment(),
                        'comment_type' => $getData->getCommentType(),
                        'approval' => $getData->getApproval(),
                        'private' => $getData->isPrivate(),
                        'comment_by' => $getData->getCommentBy()->getFullName(),
                        'created_at' => $getData->getCreatedAt()
                    ];
                }
                return $response;
            } else {
                return [];
            }
        } else {
            return [];
        }
    }

    /**
     * @param $reportId
     *
     * @return array
     */
    public function getApprovalComments($reportId): ?array
    {
        $response = [];

        if ($reportId !== null) {
            $qb = $this->repositoryComment->createQueryBuilder('p')
                ->where('p.report  = :report')
                ->setParameter('report', $reportId->getId())
                ->orderBy('p.createdAt', 'DESC')
                ->getQuery();

            if (count($qb->execute()) >= 1) {
                foreach ($qb->execute() as $getData) {
                    if (
                        $getData->getCommentType() === 'normal' ||
                        $getData->getCommentType() === 'approval'
                    ) {
                        $response[] = [
                            'id' => $getData->getid(),
                            'comment' => $getData->getComment(),
                            'comment_type' => $getData->getCommentType(),
                            'approval' => $getData->getApproval(),
                            'private' => $getData->isPrivate(),
                            'comment_by' => $getData->getCommentBy()->getFullName(),
                            'created_at' => $getData->getCreatedAt()
                        ];
                    }
                }
                return $response;
            } else {
                return [];
            }
        } else {
            return [];
        }
    }

    /**
     * @param $value
     *
     * @return string
     */
    private function emptyCheck($value)
    {
        if (empty($value)) {
            return '';
        }
    }

    /**
     * @param      $queues
     * @param null $reportStatus
     *
     * @param null $spreadSheetCheck
     *
     * @return array
     */
    public function queuesReturns($queues, $reportStatus = null, $spreadSheetCheck = null): ?array
    {
        $response = [];
        if ($queues) {
            foreach ($queues as $getData) {
                switch ($reportStatus) {
                    case 'new_request':
                        if (
                            $getData->getStatus() === 'new_request' ||
                            $getData->getStatus() === 'report_type_approved'
                        ) {
                            $response[] = $this->queuesResponse($getData, $spreadSheetCheck);
                        }
                        break;
                    case 'unassigned':
                        if (
                            $getData->getStatus() === 'new_request' ||
                            $getData->getStatus() === 'report_type_approved' ||
                            $getData->getStatus() === 'search_completed' ||
                            $getData->getStatus() === 'validated' ||
                            $getData->getStatus() === 'needs_approval' ||
                            $getData->getStatus() === 'under_investigation'
                        ) {
                            $response[] = $this->queuesResponse($getData, $spreadSheetCheck);
                        }
                        break;
                    default:
                        $response[] = $this->queuesResponse($getData, $spreadSheetCheck);
                }
            }

            return $response;
        } else {
            return [];
        }
    }

    /**
     * @
     * @param      $getData
     *
     * @param null $spreadsheet
     *
     * @return array
     */
    public function queuesResponse($getData, $spreadsheet = null): ?array
    {
        if ($getData) {
            $response = [
                'id' => $this->valueCheck($getData->getId()),
                'user' => $this->valueCheck($getData->getUser()->getFullName()),
                'assigned_to' => $this->valueCheck($getData->getAssignedToName()),
                'company_name' => $this->valueCheck($getData->getCompany()->getName()),
                'sequence' => $this->valueCheck($getData->getSequence()),
                'request_type' => $this->valueCheck($getData->getRequestType()),
                'status' => $this->valueCheck($getData->getStatus()),
                'subject' => [
                    'id' => $this->valueCheck($getData->getSubject()->getId()),
                    'first_name' => $this->valueCheck($getData->getSubject()->getFirstName()),
                    'last_name' => $this->valueCheck($getData->getSubject()->getLastName()),
                    'report_type' => $this->valueCheck(strtoupper(substr($getData->getSubject()->getReportType(), 0, 1))),
                    'background_color' => $this->reportColorMatch(substr($getData->getSubject()->getReportType(), 0, 1))
                ],
                'option_value' => $getData->getOptionValue(),
                'created_at' => $this->valueCheck($getData->getCreatedDate())

            ];

            // Excel Spreadsheet API
            if ($spreadsheet === true) {


                $responseUpdate = [
                    'approved_by' => $this->valueAssignedTo($getData),
                    'identification' => $getData->getSubject()->getIdentification(),
                    'country' => $this->valueCheck($getData->getSubject()->getCountryName()),
                    'gender' => $this->valueCheck($getData->getSubject()->getGender()),
                    'province' => $this->valueCheck($getData->getSubject()->getProvince()),
                    'date_completed' => $getData->getCompletedDate(),
                    'overall_behavior_scores' => $this->overallBehaviorScoresCheck($getData),
                    'risk_score' => $this->riskScoreCheck($getData),
                    'weighted_social_media_score' => $this->weightedSocialMediaScoreCheck($getData),
                    'scores_overwritten' => $this->scoresOverwrittenCheck($getData),
                    'overall_behavior_scores_overwritten' => $this->overallBehaviorScoresOverwriteCheck($getData),
                    'risk_score_overwritten' => $this->riskScoreOverwiteCheck($getData),
                    'weighted_social_media_score_overwritten' => $this->weightedSocialMediaScoreOverwiteCheck($getData),
                    'facebook' => $this->socialMedia('facebook', $getData->getReportScores(), $getData->getSocialMediaScores()),
                    'twitter' => $this->socialMedia('twitter', $getData->getReportScores(), $getData->getSocialMediaScores()),
                    'linkedin' => $this->socialMedia('linkedin', $getData->getReportScores(), $getData->getSocialMediaScores()),
                    'instagram' => $this->socialMedia('instagram', $getData->getReportScores(), $getData->getSocialMediaScores()),
                    'web' => $this->socialMedia('web', $getData->getReportScores(), $getData->getSocialMediaScores()),
                    'pinterest' => $this->socialMedia('pinterest', $getData->getReportScores(), $getData->getSocialMediaScores()),
                    'flickr' => $this->socialMedia('flickr', $getData->getReportScores(), $getData->getSocialMediaScores()),
                    'youtube' => $this->socialMedia('youtube', $getData->getReportScores(), $getData->getSocialMediaScores()),

                ];

                return array_merge($response, $responseUpdate);
            }

            return $response;
        } else {
            return [];
        }
    }

    /**
     * @param $reportType
     *
     * @return string|void
     */
    private function reportColorMatch($reportType)
    {
        switch ($reportType) {
            case 'b':
                return "#ADD8E6";
                break;
            case 'f':
                return "#FFE4B5";
                break;
            case 's':
                return "#98AFC7";
                break;
            case 'h':
                return "#E6E6FA";
                break;

            default:
                return '';
        }
    }

    /**
     * @param $getData
     *
     * @return bool
     */
    private function scoresOverwrittenCheck($getData)
    {
        if ($getData->isOverWriteReportScores() === true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $getData
     *
     * @return array
     */
    public function overallBehaviorScoresOverwriteCheck($getData): ?array
    {
        if (!empty($getData->getReportScoresUpdated()) && $getData->isOverWriteReportScores() === true && !empty($getData->getReportScoresUpdated()['overall_behavior_scores'])) {
            return [
                'creativity' => $this->valueCheck($getData->getReportScoresUpdated()['overall_behavior_scores']['creativity']),
                'creativity_percentage' => $this->overallBehaviorScoresPercentage($getData->getReportScoresUpdated()['overall_behavior_scores']['creativity']),

                'network_reach' => $this->valueCheck($getData->getReportScoresUpdated()['overall_behavior_scores']['network_reach']),
                'network_reach_percentage' => $this->overallBehaviorScoresPercentage($getData->getReportScoresUpdated()['overall_behavior_scores']['network_reach']),

                'network_engagement' => $this->valueCheck($getData->getReportScoresUpdated()['overall_behavior_scores']['network_engagement']),
                'network_engagement_percentage' => $this->overallBehaviorScoresPercentage($getData->getReportScoresUpdated()['overall_behavior_scores']['network_engagement']),

                'professional_image' => $this->valueCheck($getData->getReportScoresUpdated()['overall_behavior_scores']['professional_image']),
                'professional_image_percentage' => $this->overallBehaviorScoresPercentage($getData->getReportScoresUpdated()['overall_behavior_scores']['professional_image']),

                'communication_skills' => $this->valueCheck($getData->getReportScoresUpdated()['overall_behavior_scores']['communication_skills']),
                'communication_skills_percentage' => $this->overallBehaviorScoresPercentage($getData->getReportScoresUpdated()['overall_behavior_scores']['communication_skills']),

                'teamwork_collaboration' => $this->valueCheck($getData->getReportScoresUpdated()['overall_behavior_scores']['teamwork_collaboration']),
                'teamwork_collaboration_percentage' => $this->overallBehaviorScoresPercentage($getData->getReportScoresUpdated()['overall_behavior_scores']['teamwork_collaboration']),

                'professional_engagement' => $this->valueCheck($getData->getReportScoresUpdated()['overall_behavior_scores']['professional_engagement']),
                'professional_engagement_percentage' => $this->overallBehaviorScoresPercentage($getData->getReportScoresUpdated()['overall_behavior_scores']['professional_engagement']),

                'business_writing_ability' => $this->valueCheck($getData->getReportScoresUpdated()['overall_behavior_scores']['business_writing_ability']),
                'business_writing_ability_percentage' => $this->overallBehaviorScoresPercentage($getData->getReportScoresUpdated()['overall_behavior_scores']['business_writing_ability']),
            ];
        } else {
            return [
                'creativity' => 'N/A',
                'creativity_percentage' => 0,

                'network_reach' => 'N/A',
                'network_reach_percentage' => 0,

                'network_engagement' => 'N/A',
                'network_engagement_percentage' => 0,

                'professional_image' => 'N/A',
                'professional_image_percentage' => 0,

                'communication_skills' => 'N/A',
                'communication_skills_percentage' => 0,

                'teamwork_collaboration' => 'N/A',
                'teamwork_collaboration_percentage' => 0,

                'professional_engagement' => 'N/A',
                'professional_engagement_percentage' => 0,

                'business_writing_ability' => 'N/A',
                'business_writing_ability_percentage' => 0
            ];
        }
    }

    /**
     * @param $getData
     *
     * @return array
     */
    public function overallBehaviorScoresCheck($getData): ?array
    {
        if (!empty($getData->getReportScores()) && !empty($getData->getReportScores()['overall_behavior_scores'])) {

            return [
                'creativity' => $this->valueCheck($getData->getReportScores()['overall_behavior_scores']['creativity']),
                'creativity_percentage' => $this->overallBehaviorScoresPercentage($getData->getReportScores()['overall_behavior_scores']['creativity']),

                'network_reach' => $this->valueCheck($getData->getReportScores()['overall_behavior_scores']['network_reach']),
                'network_reach_percentage' => $this->overallBehaviorScoresPercentage($getData->getReportScores()['overall_behavior_scores']['network_reach']),

                'network_engagement' => $this->valueCheck($getData->getReportScores()['overall_behavior_scores']['network_engagement']),
                'network_engagement_percentage' => $this->overallBehaviorScoresPercentage($getData->getReportScores()['overall_behavior_scores']['network_engagement']),

                'professional_image' => $this->valueCheck($getData->getReportScores()['overall_behavior_scores']['professional_image']),
                'professional_image_percentage' => $this->overallBehaviorScoresPercentage($getData->getReportScores()['overall_behavior_scores']['professional_image']),

                'communication_skills' => $this->valueCheck($getData->getReportScores()['overall_behavior_scores']['communication_skills']),
                'communication_skills_percentage' => $this->overallBehaviorScoresPercentage($getData->getReportScores()['overall_behavior_scores']['communication_skills']),

                'teamwork_collaboration' => $this->valueCheck($getData->getReportScores()['overall_behavior_scores']['teamwork_collaboration']),
                'teamwork_collaboration_percentage' => $this->overallBehaviorScoresPercentage($getData->getReportScores()['overall_behavior_scores']['teamwork_collaboration']),

                'professional_engagement' => $this->valueCheck($getData->getReportScores()['overall_behavior_scores']['professional_engagement']),
                'professional_engagement_percentage' => $this->overallBehaviorScoresPercentage($getData->getReportScores()['overall_behavior_scores']['professional_engagement']),

                'business_writing_ability' => $this->valueCheck($getData->getReportScores()['overall_behavior_scores']['business_writing_ability']),
                'business_writing_ability_percentage' => $this->overallBehaviorScoresPercentage($getData->getReportScores()['overall_behavior_scores']['business_writing_ability']),
            ];
        } else {

            return [
                'creativity' => 'N/A',
                'creativity_percentage' => 0,

                'network_reach' => 'N/A',
                'network_reach_percentage' => 0,
                
                'network_engagement' => 'N/A',
                'network_engagement_percentage' =>  0,

                'professional_image' => 'N/A',
                'professional_image_percentage' => 0,

                'communication_skills' => 'N/A',
                'communication_skills_percentage' => 0,

                'teamwork_collaboration' => 'N/A',
                'teamwork_collaboration_percentage' => 0,

                'professional_engagement' => 'N/A',
                'professional_engagement_percentage' => 0,

                'business_writing_ability' => 'N/A',
                'business_writing_ability_percentage' => 0
            ];
        }
    }

    /**
     * @param $value
     *
     * @return int
     */
    private function overallBehaviorScoresPercentage($value){

        if($value){
         return   round($value /  5 * 100,2)."%";
        }else{
            return 0;
        }

    }

    /**
     * @param $getData
     *
     * @return string
     */
    private function riskScoreOverwiteCheck($getData): ?string
    {
        if (!empty($getData->getReportScoresUpdated()) && $getData->isOverWriteReportScores() === true) {
            return $this->valueCheck($getData->getReportScoresUpdated()['risk_score']);
        } else {
            return 'N/A';
        }
    }

    /**
     * @param $getData
     *
     * @return string
     */
    private function weightedSocialMediaScoreOverwiteCheck($getData)
    {
        if (!empty($getData->getReportScoresUpdated()) && $getData->isOverWriteReportScores() === true) {
            return $this->valueCheck($getData->getReportScoresUpdated()['weighted_social_media_score']);
        } else {
            return 'N/A';
        }
    }

    /**
     * @param $getData
     *
     * @return string
     */
    private function riskScoreCheck($getData)
    {
        if (!empty($getData->getReportScores())) {
            return $this->valueCheck($getData->getReportScores()['risk_score']);
        } else {
            return 'N/A';
        }
    }

    /**
     * @param $getData
     *
     * @return string
     */
    private function weightedSocialMediaScoreCheck($getData)
    {
        if (!empty($getData->getReportScores())) {
            return $this->valueCheck($getData->getReportScores()['weighted_social_media_score']);
        } else {
            return 'N/A';
        }
    }

    /**
     * @param $platform
     *
     * @return array
     */
    private function socialMedia($platform, $data, $socialMediaScores): array
    {

        $value = '';

        if (!empty($socialMediaScores)) {

            return [

                'account' => $this->valueCheck($socialMediaScores[$platform]['has_account']),
                'positive_content' => $this->valueCheck($socialMediaScores[$platform]['positive_content']),
                'activity' => $this->valueCheck($socialMediaScores[$platform]['active_account']),
                'negative_content' => $this->valueCheck($socialMediaScores[$platform]['negative_content']),
                'privacy_settings' => $this->valueCheck($socialMediaScores[$platform]['privacy_settings']),
                'multiple_accounts' => $this->valueCheck($socialMediaScores[$platform]['multiple_account']),
                'friends' => $this->valueCheck($socialMediaScores[$platform]['connections']),
                'disclosure' => $this->valueCheck($socialMediaScores[$platform]['information_disclosed']),
                'unweighted_platform_score' => $this->platformUnweightedCheck($data, $platform),
                'weighted_platform_score' => $this->platformWeightedCheck($data, $platform)
            ];

        } else {


            return [
                'account' => $this->valueCheck($value),
                'positive_content' => $this->valueCheck($value),
                'activity' => $this->valueCheck($value),
                'negative_content' => $this->valueCheck($value),
                'privacy_settings' => $this->valueCheck($value),
                'multiple_accounts' => $this->valueCheck($value),
                'friends' => $this->valueCheck($value),
                'disclosure' => $this->valueCheck($value),
                'unweighted_platform_score' => $this->valueCheck($value),
                'weighted_platform_score' => $this->valueCheck($value)
            ];
        }
    }

    /**
     * @param $getData
     *
     * @return string
     */
    private function valueAssignedTo($getData)
    {
        if (!empty($getData->getApprovedBy())) {
            return $getData->getApprovedBy()->getFullName();
        } else {
            return 'N/A';
        }
    }

    /**
     * @param $value
     *
     * @return string
     */
    private function valueCheck($value)
    {
        if (!empty($value)) {
            return $value;
        } else {
            return 'N/A';
        }
    }

    /**
     * Checks to see the there is a platform with value
     *
     * @param $data
     * @param $platform
     *
     * @return mixed|string
     */
    private function platformUnweightedCheck($data, $platform)
    {

        if (isset($data['platforms'][$platform])) {
            return $data['platforms'][$platform]['unweighted_platform_score'];
        } else {
            return 'N/A';
        }
    }

    /**
     * Checks to see the there is a platform with value
     *
     * @param $data
     * @param $platform
     *
     * @return mixed|string
     */
    private function platformWeightedCheck($data,$platform)
    {
        if (isset($data['platforms'][$platform])) {
            return $data['platforms'][$platform]['weighted_platform_score'];
        } else {
            return 'N/A';
        }
    }


    /**
     * @param $queues
     *
     * @return array
     */
    public function queuesReturnsBack($queues): ?array
    {
        $response = [];

        if ($queues) {
            foreach ($queues as $getData) {
                $response[] = [

                    'id' => $getData->getId(),
                    'user' => $getData->getUser()->getFullName(),
                    'assigned_to' => $getData->getAssignedToName(),
                    'company_name' => $getData->getCompany()->getName(),
                    'sequence' => $getData->getSequence(),
                    'request_type' => $getData->getRequestType(),
                    'status' => $getData->getStatus(),
                    'subject' => [
                        'id' => $getData->getSubject()->getId(),
                        'first_name' => $getData->getSubject()->getFirstName(),
                        'last_name' => $getData->getSubject()->getLastName()
                    ],
                    'option_value' => $getData->getOptionValue(),
                    'created_at' => $getData->getCreatedDate()

                ];
            }

            return $response;
        } else {
            return [];
        }
    }

    /**
     * @param $getQueues
     *
     * @return array
     */
    public function getQueuesUserReturns($getQueues): ?array
    {
        $response = [];
        if ($getQueues) {
            foreach ($getQueues as $getData) {
                $response[] = [
                    'id' => $getData->getId(),
                    'first_name' => $getData->getFirstName(),
                    'last_name' => $getData->getLastName(),
                    'email' => $getData->getEmail(),
                    'company' => $this->checkIfNullName($getData->getCompany()),
                    'roles' => $getData->getRoles(),
                    'enabled' => $getData->isEnabled(),
                    'archived' => $getData->isArchived()
                ];
            }
            return $response;
        } else {
            return [];
        }
    }

    /**
     * @param $quickCheck
     *
     * @return array
     */
    private function checkIfNullName($quickCheck)
    {
        if ($quickCheck) {
            return $quickCheck->getName();
        } else {
            return '';
        }
    }

    /**
     * @param $user
     *
     * @return array
     */
    public function myProfile($user): ?array
    {
        if ($user) {
            return [
                'id' => $user->getId(),
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'email' => $user->getEmail(),
                'tel_number' => $user->getTelNumber(),
                'mobile_number' => $user->getMobileNumber(),
                'company' => $this->checkCompany($user->getCompany()),
                'roles' => $user->getRoles(),
                'enabled' => $user->isEnabled(),
                'archived' => $user->isArchived()
            ];
        } else {
            return [];
        }
    }

    /**
     * @param $value
     *
     * @return string
     */
    private function companyNameValue($company)
    {
        if ($company) {
            return $company->getName();
        } else {
            return '';
        }
    }


    /**
     * @param $company
     *
     * @return array
     */
    private function checkCompany($company): ?array
    {
        $response = [];

        if ($company) {
            $response = [
                'id' => $company->getId(),
                'name' => $company->getName()
            ];
            return $response;
        } else {
            return [];
        }
    }

    /**
     * @param $message
     *
     * @return array
     */
    public function getMessages($message)
    {
        if ($message) {
            $response = [];

            foreach ($message as $showMessage) {
                $response[] = [
                    'id' => $showMessage->getId(),
                    'subject' => $showMessage->getSubject()->getId(),
                    'message_type' => $showMessage->getMessageType(),
                    'header' => $showMessage->getMessageHeader(),
                    'message' => $showMessage->getMessage()
                ];
            }
            return $response;
        }
    }

    /**
     * @param $subject
     *
     * @return array
     */
    public function getIdentityConfirm($subject)
    {
        $qb = $this->repositoryIdentityConfirm->createQueryBuilder('p')
            ->andWhere('p.subject = :subject_id')
            ->setParameter('subject_id', $subject)
            ->getQuery();

        return $this->showIdentityConfirm($qb->execute());
    }

    /**
     * @param $getData
     *
     * @return array
     */
    public function showIdentityConfirm($getData)
    {
        $response = [];

        if ($getData) {
            foreach ($getData as $identity) {
                $response[] = [
                    'id' => $identity->getId(),
                    'subject_id' => $identity->getSubject()->getId(),
                    'platform' => $identity->getPlatform(),
                    'identity_name' => $identity->isIdentityName(),
                    'identity_middle_name' => $identity->isIdentityMiddleName(),
                    'identity_initials' => $identity->isIdentityInitials(),
                    'identity_surname' => $identity->isIdentitySurname(),
                    'identity_image' => $identity->isIdentityImage(),
                    'identity_location' => $identity->isIdentityLocation(),
                    'identity_employment_history' => $identity->isIdentityEmploymentHistory(),
                    'identity_academic_history' => $identity->isIdentityAcademicHistory(),
                    'identity_country' => $identity->isIdentityCountry(),
                    'identity_profile_image' => $identity->isIdentityProfileImage(),
                    'identity_id_number' => $identity->isIdentityIdNumber(),
                    'identity_contact_number' => $identity->isIdentityContactNumber(),
                    'identity_email_address' => $identity->isIdentityEmailAddress(),
                    'identity_physical_address' => $identity->isIdentityPhysicalAddress(),
                    'identity_tag' => $identity->isIdentityTag(),
                    'identity_alias' => $identity->isIdentityAlias(),
                    'identity_link' => $identity->isIdentityLink(),
                    'identity_location_history' => $identity->isIdentityLocationHistory(),
                    'identity_handle' => $identity->isIdentityHandle(),
                    'identity_title' => $identity->isIdentityTitle()
                ];
            }
            return $response;
        } else {
            return [];
        }
    }

    /**
     * @param $value
     *
     * @return string
     */
    private function checkValue($value)
    {
        if ($value) {
            return $value;
        } else {
            return '';
        }
    }

    /**
     * @param $subject
     *
     * @return string
     *
     */
    public function getPDF($subject)
    {
        $qb = $this->repositoryReport->createQueryBuilder('p')
            ->andWhere('p.subject = :subject')
            ->setParameter('subject', $subject)
            ->getQuery();

        if ($qb->execute()) {
            if ($qb->execute()[0]->getPdfFilename()) {
                return $qb->execute()[0]->getPdfFilename();
            } else {
                return '';
            }
        } else {
            return '';
        }
    }

    /**
     * @param $subject
     *
     * @return string
     *
     */
    public function getImageUrl($subject)
    {
        if ($subject->getImageFile()) {
            return $this->imagesBlobUrl . '/' . $subject->getId() . '/' . $subject->getImageFile();
        } else {
            return '';
        }
    }
}
