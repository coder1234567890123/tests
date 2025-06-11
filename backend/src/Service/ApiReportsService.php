<?php

namespace App\Service;

use App\Entity\Answer;
use App\Entity\Comment;
use App\Entity\DefaultBranding;
use App\Entity\IdentityConfirm;
use App\Entity\Proof;
use App\Entity\ProofStorage;
use App\Entity\Question;
use App\Entity\Profile;
use App\Entity\Report;
use App\Repository\DefaultBrandingRepository;
use App\Service\PdfProofService;
use App\Service\ApiReturnService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Exception;
use phpDocumentor\Reflection\Types\Context;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class ApiReportsService
 *
 * @package App\Service
 */
class ApiReportsService
{
    /**
     * @var \App\Service\ApiReturnService
     */
    private $apiReturnService;

    /**
     * ProfileRepository constructor.
     *
     * @param EntityManagerInterface        $entityManager
     * @param \App\Service\PdfProofService  $pdfProofService
     * @param \App\Service\ApiReturnService $apiReturnService
     * @param ParameterBagInterface         $params
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        PdfProofService $pdfProofService,
        ApiReturnService $apiReturnService,
        ParameterBagInterface $params,
        DefaultBrandingRepository $defaultBrandingRepository
    )
    {
        $this->entityManager             = $entityManager;
        $this->pdfProofService           = $pdfProofService;
        $this->params                    = $params;
        $this->defaultBrandingRepository = $defaultBrandingRepository;

        $this->socialMedia = ['facebook', 'twitter', 'instagram', 'pinterest', 'linkedin', 'youtube', 'flickr', 'web'];

        $this->apiReturnService = $apiReturnService;

        $this->repositoryComments        = $entityManager->getRepository(Comment::class);
        $this->repositoryQuestions       = $entityManager->getRepository(Question::class);
        $this->repositoryAnswer          = $entityManager->getRepository(Answer::class);
        $this->repositoryProof           = $entityManager->getRepository(Proof::class);
        $this->repositoryProofStorage    = $entityManager->getRepository(ProofStorage::class);
        $this->repositoryDefaultBranding = $entityManager->getRepository(DefaultBranding::class);
        $this->repositoryIdentityConfirm = $entityManager->getRepository(IdentityConfirm::class);
        $this->repositoryProfile         = $entityManager->getRepository(Profile::class);
    }

    /**
     * @param $report
     * @param $subject
     * builds report
     *
     * @return array
     */
    public function pdfReport($report, $subject)
    {
        $response = [
            'cover_page' => [
                'branding' => $this->getBranding($report),

            ],
            'reports'    => [
                'report_type'    => $report->getSubject()->getReportType(),
                'candidate'      => [
                    'name'          => $subject->getFirstName() . ' ' . $subject->getLastName(),
                    'qualification' => $this->apiReturnService->getQualifications($subject->getQualifications()),
                    'employment'    => $this->apiReturnService->getEmployments($subject->getEmployments())
                ],
                'report_details' => [
                    'date_requested' => $report->getCreatedDate(),
                    'date_issued'    => $report->getUpdatedAt(),
                    'date_validated' => $report->getCreatedDate(),
                    'tel_no'         => $this->checkTel($subject->getCompany()->getTelNumber()),
                    'email'          => $this->checkEmail($subject->getCompany()->getEmail())
                ],
                'report_summary' => [
                    'details'                 => [
                        'date_requested' => $report->getCreatedDate(),
                        'date_issued'    => $report->getCreatedDate(),
                        'date_validated' => $report->getCreatedDate(),
                        'tel_no'         => $report->getUser()->getTelNumber(),
                        'email'          => $report->getUser()->getEmail()
                    ],
                    'overall_behavior_scores' => $this->isOverWriteReportScores($report),
                    'findings'                => $this->findings($report),
                    'detailed_summary'        => $this->socialMediaFindings($report),
                ],
                'final_section'  => [
                    'risks_score'    => $this->getRiskScore($report),
                    'weighted_score' => $this->weighedScore($report),
                    'risk_comment'   => $report->getRiskComment(),
                    'disclaimer'     => $this->getDisclaimer($report)
                ],

            ],
            'footer'     => [
                'branding' => $this->getBranding($report)
            ]

        ];

        return $response;
    }

    /**
     * @param $report
     *
     * @return array
     * front cover for the PDF
     */
    private function getBranding($report)
    {
        switch ($report->getCompany()->getBrandingType()) {
            case 'white_label':
                return [
                    'cover_logo'              => $this->params->get('BLOB_URL') . '/company-images/' . $report->getCompany()->getId() . '/' . $report->getCompany()->getCoverLogo(),
                    'logo'                    => $this->params->get('BLOB_URL') . '/company-images/' . $report->getCompany()->getId() . '/' . $report->getCompany()->getCoverLogo(),
                    'footer_logo'             => $this->params->get('BLOB_URL') . '/company-images/' . $report->getCompany()->getId() . '/' . $report->getCompany()->getImageFooterLogo(),
                    'front_page'              => $this->params->get('BLOB_URL') . '/company-images/' . $report->getCompany()->getId() . '/' . $report->getCompany()->getImageFrontPage(),
                    'theme_color'             => $report->getCompany()->getThemeColor(),
                    'theme_color_second'      => $this->checkValue($report->getCompany()->getThemeColorSecond()),
                    'theme_color_overlay_rgb' => $this->createRga($report->getCompany()->getThemeColor()),
                    'footer_link'             => $report->getCompany()->getFooterLink(),
                    'company_logo'            => '',
                    'company_footer_logo'     => '',
                    'company_front_page'      => '',
                    'company_theme_color'     => '',
                    'company_footer_link'     => '',
                    'branding_type'           => $report->getCompany()->getBrandingType()
                ];
                break;
            case 'co_branded':
                return [
                    'cover_logo'              => $this->params->get('BLOB_URL') . '/system-assets/' . $this->defaultBrandingRepository->all()['cover_logo'],
                    'logo'                    => $this->params->get('BLOB_URL') . '/system-assets/' . $this->defaultBrandingRepository->all()['logo'],
                    'footer_logo'             => $this->params->get('BLOB_URL') . '/system-assets/' . $this->defaultBrandingRepository->all()['logo'],
                    'front_page'              => $this->params->get('BLOB_URL') . '/system-assets/' . $this->defaultBrandingRepository->all()['front_page'],
                    'theme_color'             => $this->checkValue($report->getCompany()->getThemeColor()),
                    'theme_color_second'      => $this->checkValue($report->getCompany()->getThemeColorSecond()),
                    'footer_link'             => $this->defaultBrandingRepository->all()['footer_link'],
                    'company_logo'            => $this->params->get('BLOB_URL') . '/company-images/' . $report->getCompany()->getId() . '/' . $report->getCompany()->getCoverLogo(),
                    'company_footer_logo'     => $this->params->get('BLOB_URL') . '/company-images/' . $report->getCompany()->getId() . '/' . $report->getCompany()->getImageFooterLogo(),
                    'company_front_page'      => $this->params->get('BLOB_URL') . '/system-assets/' . $this->defaultBrandingRepository->all()['front_page'],
                    'company_theme_color'     => $report->getCompany()->getThemeColor(),
                    'company_theme_color_2'   => $report->getCompany()->getThemeColorSecond(),
                    'theme_color_overlay_rgb' => $this->createRga($report->getCompany()->getThemeColor()),
                    'company_footer_link'     => $report->getCompany()->getFooterLink(),
                    'branding_type'           => $report->getCompany()->getBrandingType()
                ];
                break;

            default:
                return [
                    'cover_logo'              => $this->params->get('BLOB_URL') . '/system-assets/' . $this->defaultBrandingRepository->all()['cover_logo'],
                    'logo'                    => $this->params->get('BLOB_URL') . '/system-assets/' . $this->defaultBrandingRepository->all()['logo'],
                    'footer_logo'             => $this->params->get('BLOB_URL') . '/system-assets/' . $this->defaultBrandingRepository->all()['logo'],
                    'front_page'              => $this->params->get('BLOB_URL') . '/system-assets/' . $this->defaultBrandingRepository->all()['front_page'],
                    'theme_color'             => $this->defaultBrandingRepository->all()['theme_color'],
                    'theme_color_second'      => $this->defaultBrandingRepository->all()['theme_color_second'],
                    'theme_color_overlay_rgb' => $this->createRga($this->defaultBrandingRepository->all()['theme_color']),
                    'footer_link'             => $this->defaultBrandingRepository->all()['footer_link'],
                    'company_logo'            => '',
                    'company_footer_logo'     => '',
                    'company_front_page'      => '',
                    'company_theme_color'     => '',
                    'company_footer_link'     => '',
                    'branding_type'           => $report->getCompany()->getBrandingType()
                ];
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
     * @param $hex
     *
     * @return string
     */
    private function createRga($hex)
    {
        if ($hex) {
            list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");
            return "rgba($r, $g, $b, 0.5)";
        } else {
            return '';
        }
    }

    /**
     * @param $value
     *
     * @return string
     */
    private function checkTel($value)
    {
        if ($value) {
            return $value;
        } else {
            return 'Not provided';
        }
    }

    /**
     * @param $value
     *
     * @return string
     */
    private function checkEmail($value)
    {
        if ($value) {
            return $value;
        } else {
            return 'Not provided';
        }
    }

    /**
     * @param $report
     *
     * @return array|null
     */
    private function isOverWriteReportScores($report)
    {
        if ($report->isOverWriteReportScores() === false) {
            return $this->apiReturnService->overallBehaviorScoresCheck($report);
        } else {
            //Daniel
            return $this->apiReturnService->overallBehaviorScoresOverwriteCheck($report);
        }
    }

    /**
     * @return array
     */
    private function findings($report)
    {
        $response = [];

        foreach ($this->socialMedia as $socialMedia) {
            if ($this->answerCheck($socialMedia, $report)) {
                $response[] = [
                    'platform'         => $socialMedia,
                    'icon'             => $this->socialMediaIcons($socialMedia),
                    'score'            => $this->socialMediaScore($socialMedia, $report),
                    'score_percentage' => $this->scoresPercentage($this->socialMediaScore($socialMedia, $report)),
                    'comments'         => $this->findingComments($report, $socialMedia)
                ];
            }
        }
        return $response;
    }

    /**
     * @param $socialMedia
     *
     * @return bool
     */
    private function answerCheck($socialMedia, $report)
    {
        $qb = $this->repositoryAnswer->createQueryBuilder('p')
                                     ->andWhere('p.platform  = :platform')
                                     ->setParameter('platform', $socialMedia)
                                     ->andWhere('p.report  = :report_id')
                                     ->setParameter('report_id', $report)
                                     ->getQuery();

        if (count($qb->execute()) >= 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $socialMedia
     *
     * @return string
     */
    public function socialMediaIcons($socialMedia)
    {
        switch ($socialMedia) {
            case 'facebook':
                return $this->params->get('BLOB_URL') . '/icons/facebook-icon-01.png';
                break;
            case 'twitter':
                return $this->params->get('BLOB_URL') . '/icons/twitter-icon-01.png';
                break;
            case 'instagram' :
                return $this->params->get('BLOB_URL') . '/icons/instagram-icon-01.png';
                break;
            case 'pinterest' :
                return $this->params->get('BLOB_URL') . '/icons/pinterest-icon-01.png';
                break;
            case 'linkedin' :
                return $this->params->get('BLOB_URL') . '/icons/linkedin-icon-01.png';
                break;
            case 'youtube' :
                return $this->params->get('BLOB_URL') . '/icons/youtube-icon-01.png';
                break;
            case 'flickr' :
                return $this->params->get('BLOB_URL') . '/icons/flickr-icon-01.png';
                break;
            case 'web' :
                return $this->params->get('BLOB_URL') . '/icons/google-plus-icon-01.png';
                break;

            default:
                return '';
        }
    }

    /**
     * @param $socialMedia
     * @param $report
     *
     * @return int|mixed
     */
    private function socialMediaScore($socialMedia, $report)
    {
        if ($report->isOverWriteReportScores() === false) {
            foreach ($report->getReportScores() as $getReportScores) {
                if ($getReportScores[$socialMedia]['unweighted_platform_score_rounded']) {
                    return $getReportScores[$socialMedia]['unweighted_platform_score_rounded'];
                } else {
                    return 0;
                }
            }
        } else {
            foreach ($report->getReportScoresUpdated() as $getReportScores) {
                if ($getReportScores[$socialMedia]['unweighted_platform_score_rounded']) {
                    return $getReportScores[$socialMedia]['unweighted_platform_score_rounded'];
                } else {
                    return 0;
                }
            }
        }
    }

    /**
     * @param $value
     *
     * @return int
     */
    private function scoresPercentage($value)
    {
        if ($value) {
            return round(($value / 5 * 100) - 20) . ",100";
        } else {
            return 0;
        }
    }

    /**
     * @param $report
     * @param $socialMedia
     *
     * @return array
     */
    private function findingComments($report, $socialMedia)
    {
        $qb = $this->repositoryComments->createQueryBuilder('p')
                                       ->andWhere('p.report = :id')
                                       ->setParameter('id', $report)
                                       ->andWhere('p.commentType  = :socialMedia')
                                       ->setParameter('socialMedia', $socialMedia)
                                       ->getQuery();

        $repsonse = [];

        if (count($qb->execute()) >= 1) {
            if ($qb->execute()) {
                foreach ($qb->execute() as $comment) {
                    $repsonse[] = $comment->getComment();
                }
                return $repsonse;
            } else {
                return [];
            }
            return $qb->execute();
        }
    }

    /**
     * @return array
     */
    private function socialMediaFindings($report)
    {
        $response = [];

        foreach ($this->socialMedia as $socialMedia) {
            if ($this->answerCheck($socialMedia, $report)) {
                $response[] = [
                    'report_summary' => [
                        'social_media'             => $socialMedia,
                        'confirmation_of_identity' => $this->getConfirmationOfIdentity($socialMedia, $report),
                        'profile_hits'             => $this->profileHits($report, $socialMedia),
                        'true_hits'                => $this->profileTrueHits($report, $socialMedia),
                        'score'                    => $this->socialMediaScore($socialMedia, $report),
                        'score_percentage'         => $this->scoresPercentage($this->socialMediaScore($socialMedia, $report)),
                        'noteworthy_findings'      => $this->findingsProof($report, $socialMedia)
                    ]
                ];
            }
        }
        return $response;
    }

    /**
     * @param $socialMedia
     * @param $report
     *
     * @return string
     */
    private function getConfirmationOfIdentity($socialMedia, $report)
    {
        $response = [];
        $qb       = $this->repositoryIdentityConfirm->createQueryBuilder('p')
                                                    ->andWhere('p.subject = :subject_id')
                                                    ->setParameter('subject_id', $report->getSubject())
                                                    ->andWhere('p.platform = :platform')
                                                    ->setParameter('platform', $socialMedia)
                                                    ->getQuery();

        if ($qb->execute()) {
            foreach ($qb->execute() as $identity) {
                $response[] = $this->checkConfirmationOfIdentity('Name', $identity->isIdentityName());
                $response[] = $this->checkConfirmationOfIdentity('Middle name', $identity->isIdentityMiddleName());
                $response[] = $this->checkConfirmationOfIdentity('Initials', $identity->isIdentityInitials());
                $response[] = $this->checkConfirmationOfIdentity('Surname', $identity->isIdentitySurname());
                $response[] = $this->checkConfirmationOfIdentity('Image', $identity->isIdentityImage());
                $response[] = $this->checkConfirmationOfIdentity('Location', $identity->isIdentityLocation());
                $response[] = $this->checkConfirmationOfIdentity('Employment history', $identity->isIdentityEmploymentHistory());
                $response[] = $this->checkConfirmationOfIdentity('Academic history', $identity->isIdentityAcademicHistory());
                $response[] = $this->checkConfirmationOfIdentity('Country', $identity->isIdentityCountry());
                $response[] = $this->checkConfirmationOfIdentity('Profile Image', $identity->isIdentityProfileImage());
                $response[] = $this->checkConfirmationOfIdentity('Id Number', $identity->isIdentityIdNumber());
                $response[] = $this->checkConfirmationOfIdentity('Contact Number', $identity->isIdentityContactNumber());
                $response[] = $this->checkConfirmationOfIdentity('Email Address', $identity->isIdentityEmailAddress());
                $response[] = $this->checkConfirmationOfIdentity('Physical Address', $identity->isIdentityPhysicalAddress());
                $response[] = $this->checkConfirmationOfIdentity('Tag', $identity->isIdentityTag());
                $response[] = $this->checkConfirmationOfIdentity('Alias', $identity->isIdentityAlias());
                $response[] = $this->checkConfirmationOfIdentity('Link', $identity->isIdentityLink());
                $response[] = $this->checkConfirmationOfIdentity('Location History', $identity->isIdentityLocationHistory());
                $response[] = $this->checkConfirmationOfIdentity('Handle', $identity->isIdentityHandle());
                $response[] = $this->checkConfirmationOfIdentity('Title', $identity->isIdentityTitle());
            }

            return $this->confirmationOfIdentityListReturn($response);
        } else {
            return '';
        }
    }

    /**
     * @param $identity
     * @param $check
     *
     * @return null
     */
    private function checkConfirmationOfIdentity($identity, $check)
    {
        if ($identity && $check) {
            if ($check) {
                return $identity;
            }
        } else {
            return null;
        }
    }

    /**
     * @param $list
     *
     * @return string
     */
    private function confirmationOfIdentityListReturn($list)
    {
        if ($list) {
            $makeList = [];

            foreach ($list as $getData) {
                if ($getData !== null) {
                    $makeList[] = $getData;
                }
            }

            $updatedList = implode(', ', $makeList);
            $updatedList = substr_replace($updatedList, ' and', strrpos($updatedList, ','), 1);
            return $updatedList . ".";
        } else {
            return '';
        }
    }

    /**
     * @param $report
     *
     * @return int
     */
    private function profileHits($report, $socialMedia)
    {
        $qb = $this->repositoryProfile->createQueryBuilder('p')
                                      ->andWhere('p.subject = :subject')
                                      ->setParameter('subject', $report->getSubject())
                                      ->andWhere('p.platform = :platform')
                                      ->setParameter('platform', $socialMedia)
                                      ->getQuery();

        if ($qb->execute()) {
            return count($qb->execute());
        } else {
            return 0;
        }
    }

    /**
     * @param $report
     *
     * @return int
     */
    private function profileTrueHits($report, $socialMedia)
    {
        $qb = $this->repositoryProfile->createQueryBuilder('p')
                                      ->andWhere('p.subject = :subject')
                                      ->setParameter('subject', $report->getSubject())
                                      ->andWhere('p.valid = :valid')
                                      ->setParameter('valid', true)
                                      ->andWhere('p.platform = :platform')
                                      ->setParameter('platform', $socialMedia)
                                      ->getQuery();

        if ($qb->execute()) {
            return count($qb->execute());
        } else {
            return 0;
        }
    }

    /**
     * @param $report
     * @param $socialMedia
     *
     * @return array
     */
    private function findingsProof($report, $socialMedia)
    {
        $response = [];

        $qb = $this->repositoryAnswer->createQueryBuilder('p')
                                     ->andWhere('p.platform  = :platform')
                                     ->setParameter('platform', $socialMedia)
                                     ->andWhere('p.report  = :report_id')
                                     ->setParameter('report_id', $report->getId())
                                     ->getQuery();

        if (count($qb->execute()) >= 1) {
            if ($qb->execute()) {
                foreach ($qb->execute() as $answers) {
                    $response[] = $this->getProof($answers->getId(), $report);
                }
                return $response;
            } else {
                return [];
            }
        }
    }

    /**
     * @param $id
     *
     * @return array
     */
    private function getProof($id, $report)
    {
        $response = [];

        $qb = $this->repositoryProof->createQueryBuilder('p')
                                    ->andWhere('p.answer  = :id')
                                    ->setParameter('id', $id)
                                    ->getQuery();

        if ($qb->execute()) {
            foreach ($qb->execute() as $proof) {
                if ($proof->getComment()) {
                    $response[] = [
                        'id'          => $proof->getId(),
                        'comment'     => $proof->getComment(),
                        'proof_image' => $this->getProofStage($proof->getProofStorage(), $report)
                    ];
                }
            }
            return $response;
        } else {
            return null;
        }
    }

    /**
     * @param $id
     *
     * @return array
     */
    private function getProofStage($id, $report)
    {
        $response = [];

        $qb = $this->repositoryProofStorage->createQueryBuilder('p')
                                           ->andWhere('p.id  = :id')
                                           ->setParameter('id', $id);

        if (count($qb->getQuery()->execute()) >= 1) {
            if ($qb->getQuery()->execute()) {
                foreach ($qb->getQuery()->execute() as $proofStorage) {
                    $response = $this->params->get('BLOB_URL') . '/profile-images/' . $report->getSubject()->getBlobFolder() . '/' . $proofStorage->getImageFile();
                }

                return $response;
            } else {
                return [];
            }
        }
    }

    /**
     * @param $report
     *
     * @return mixed
     */
    private function getRiskScore($report)
    {
        if ($report->isOverWriteReportScores() === false) {
            return $this->lessThanGreaterThanCheck($report->getReportScores()['risk_score']);
        } else {
            return $this->lessThanGreaterThanCheck($report->getReportScoresUpdated()['risk_score']);
        }
    }

    /**
     * @param $score
     *
     * @return int
     */
    private function lessThanGreaterThanCheck($score)
    {
        if ($score >= 100) {
            return 100;
        } elseif ($score <= 0) {
            return 0;
        } else {
            return $score;
        }
    }

    /**
     * @param $report
     *
     * @return mixed
     */
    private function weighedScore($report)
    {
        if ($report->isOverWriteReportScores() === false) {
            return $report->getReportScores()['weighted_social_media_score_round'];
        } else {
            return $report->getReportScoresUpdated()['weighted_social_media_score'];
        }
    }

    /**
     * @param $report
     *
     * @return mixed
     */
    private function getDisclaimer($report)
    {
        if ($report->getCompany()->isUseDisclaimer()) {
            return $report->getCompany()->getDisclaimer();
        } else {
            return $this->defaultBrandingRepository->all()['disclaimer'];
        }
    }
}
