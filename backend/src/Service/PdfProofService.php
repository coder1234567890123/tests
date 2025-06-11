<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\Comment;
use App\Entity\Company;
use App\Entity\Phrase;
use App\Entity\Profile;
use App\Entity\Report;
use App\Entity\Subject;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class PdfProofService
 * @package App\Service
 */
class PdfProofService
{
    /**
     * ProfileRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Phrase::class);
        $this->repositoryReport = $entityManager->getRepository(Report::class);
        $this->repositoryComment = $entityManager->getRepository(Comment::class);
        $this->repositorySubject = $entityManager->getRepository(Subject::class);
    }

    /**
     * @param $report
     *
     * @return array
     */
    public function proofCheck($report)
    {
        foreach ($report['questions']['platforms'] as $key => $proofFind) {
            $response[] = [
                "name" => $key,
                "check_proof" => $this->getPlaform($proofFind)
            ];
        }
        return $response;
    }

    /**
     * @param $subject
     * @param $socialMedia
     *
     * @return array
     */
    public function searchInfo($subject, $socialMedia)
    {
        foreach ($socialMedia as $media) {
            $response[] = [
                'name' => $media,
                'results' => $this->getCounts($media, $subject),
                'search_term' => $this->searchTerm($media)
            ];
        }
        return $response;
    }

    /**
     * @param string  $reportId
     * @param Company $company
     *
     * @return array
     */
    public function reportDetails(string $reportId, Company $company)
    {
        $response = [];
        $qb = $this->repositoryReport->createQueryBuilder('p')
            ->where('p.id = :report_id')
            ->setParameter('report_id', $reportId)
            ->getQuery();

        /** @var Report $report */
        foreach ($qb->execute() as $report) {
            $response = [
                'date_requested' => date_format($report->getCreatedDate(), "d M Y"),
                'date_issued' => date_format($report->getCreatedDate(), "d M Y"),
                'date_valid_at' => $report->getCompletedDate() ? date_format($report->getCompletedDate(), "d M Y") : date("d M Y"),
                'tel_no' => $company->getTelNumber(),
                'email' => $company->getEmail()
            ];
        }
        return $response;
    }

    /**
     * @param $reportId
     * @param $socialMedia
     *
     * @return array
     */
    public function comments($reportId, $socialMedia)
    {
        $response = [];
        foreach ($socialMedia as $social) {
            $response[] = [
                'name' => $social,
                'count' => $this->commentCount($social, $reportId),
                'comments' => $this->findComment($social, $reportId)
            ];
        }

        return $response;
    }

    /**
     * @param $subject
     *
     * @return array
     *
     */
    public function mediaValidated($subject)
    {
        $response = [
            'facebook' => $this->getSocialMediaValidated('facebook', $subject),
            'twitter' => $this->getSocialMediaValidated('twitter', $subject),
            'instagram' => $this->getSocialMediaValidated('instagram', $subject),
            'pinterest' => $this->getSocialMediaValidated('pinterest', $subject),
            'linkedin' => $this->getSocialMediaValidated('linkedin', $subject),
            'youtube' => $this->getSocialMediaValidated('youtube', $subject),
            'flickr' => $this->getSocialMediaValidated('flickr', $subject),
            'web' => $this->getSocialMediaValidated('web', $subject),
        ];

        return $response;
    }

    /**
     * @param $subject
     *
     * @return array
     */
    public function personalInfo($subject)
    {
        $qb = $this->repositorySubject->createQueryBuilder('p')
            ->where('p.id = :subject_id')
            ->setParameter('subject_id', $subject->getId())
            ->getQuery();

        /** @var Report $report */
        foreach ($qb->execute() as $info) {

            $response = [
                'first_name' => $this->trueOrFalse($info->getPrimaryEmail()),
                'maiden_name' => $this->trueOrFalse($info->getMaidenName()),
                'surname' => $this->trueOrFalse($info->getLastName()),
                'email' => $this->trueOrFalse($info->getPrimaryEmail()),
                'non_personal_email' => $this->trueOrFalse($info->getSecondaryEmail()),
                'contact_number' => $this->trueOrFalse($info->getPrimaryMobile()),
                'date_of_birth' => $this->trueOrFalse($info->getDateOfBirth()),
                'location' => $this->trueOrFalse($info->getAddress()),
                'employment_history' => $this->trueOrFalse($info->getEmployments()),
                'academic_history' => $this->trueOrFalse($info->getQualifications()),
                'report_id' => $info->getCurrentReport()->getId(),
            ];
        }
        return $response;
    }

    /**
     * @param $subject
     *
     * @return array
     */
    public function riskComment($id)
    {
        $qb = $this->repositoryReport->createQueryBuilder('p')
            ->where('p.id = :report_id')
            ->setParameter('report_id', $id)
            ->getQuery();

        if ($qb->execute()[0]->getRiskComment() == null) {
            return '';
        } else {
            return $qb->execute()[0]->getRiskComment();
        }
    }

    /**
     * @param $check
     *
     * @return bool
     */
    private function trueOrFalse($check)
    {
        if (!empty($check)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $media
     * @param $reportId
     *
     * @return int
     */
    private function commentCount($media, $reportId)
    {
        $qb = $this->repositoryComment->createQueryBuilder('p')
            ->where('p.report = :report_id')
            ->andWhere('p.commentType = :comment_type')
            ->setParameter('comment_type', $media)
            ->setParameter('report_id', $reportId)
            ->getQuery();

        return count($qb->execute());
    }

    /**
     * @param $media
     * @param $reportId
     *
     * @return array
     */
    private function findComment($media, $reportId)
    {
        $response = [];

        $qb = $this->repositoryComment->createQueryBuilder('p')
            ->where('p.report = :report_id')
            ->andWhere('p.commentType = :comment_type')
            ->setParameter('comment_type', $media)
            ->setParameter('report_id', $reportId)
            ->getQuery();

        foreach ($qb->execute() as $comment) {
            $response[] = [
                'comment' => $comment->getComment(),
            ];
        }

        return $response;
    }

    /**
     * @param $media
     *
     * @return array
     */
    private function searchTerm($media)
    {
        $response = [];
        $qb = $this->repository->createQueryBuilder('p')
            ->where('p.enabled = 1')
            ->andWhere('p.searchType = :search_type')
            ->setParameter('search_type', $media)
            ->getQuery();

        foreach ($qb->execute() as $searchTerm) {
            $response[] = [
                'phrase' => $searchTerm->getPhrase()
            ];
        }
        return $response;
    }


    /**
     * @param $media
     * @param $subject
     *
     * @return array
     */
    private function getSocialMediaValidated($media, $subject)
    {
        switch ($media) {
            case "facebook":
                $validated = count($subject->getFacebookProfiles()->filter(function (Profile $profile) {
                    return $profile->isValid();
                }));

                if ($validated == 1) {
                    $validatedCheck = true;
                } else {
                    $validatedCheck = false;
                }

                return $validatedCheck;

                break;
            case "twitter":
                $validated = count($subject->getTwitterProfiles()->filter(function (Profile $profile) {
                    return $profile->isValid();
                }));

                if ($validated >= 1) {
                    $validatedCheck = true;
                } else {
                    $validatedCheck = false;
                }

                return $validatedCheck;

                break;
            case "instagram":
                $validated = count($subject->getInstagramProfiles()->filter(function (Profile $profile) {
                    return $profile->isValid();
                }));

                if ($validated >= 1) {
                    $validatedCheck = true;
                } else {
                    $validatedCheck = false;
                }

                return $validatedCheck;

                break;
            case "pinterest":
                $validated = count($subject->getPinterestProfiles()->filter(function (Profile $profile) {
                    return $profile->isValid();
                }));

                if ($validated >= 1) {
                    $validatedCheck = true;
                } else {
                    $validatedCheck = false;
                }

                return $validatedCheck;

                break;

            case "linkedin":
                $validated = count($subject->getLinkedinProfiles()->filter(function (Profile $profile) {
                    return $profile->isValid();
                }));

                if ($validated >= 1) {
                    $validatedCheck = true;
                } else {
                    $validatedCheck = false;
                }

                return $validatedCheck;;

                break;

            case "youtube":
                $validated = count($subject->getYoutubeProfiles()->filter(function (Profile $profile) {
                    return $profile->isValid();
                }));

                if ($validated >= 1) {
                    $validatedCheck = true;
                } else {
                    $validatedCheck = false;
                }

                return $validatedCheck;

                break;

            case "web":
                $validated = count($subject->getWebProfiles()->filter(function (Profile $profile) {
                    return $profile->isValid();
                }));

                if ($validated == 1) {
                    $validatedCheck = true;
                } else {
                    $validatedCheck = false;
                }

                return $validatedCheck;

                break;

            default:

                return $response = false;
        }
    }

    /**
     * @param $media
     * @param $subject
     *
     * @return array
     */
    private function getCounts($media, $subject)
    {
        switch ($media) {
            case "facebook":
                $countHits = $subject->getFacebookProfiles()->count();
                $validated = count($subject->getFacebookProfiles()->filter(function (Profile $profile) {
                    return $profile->isValid();
                }));

                $response = [
                    'hit_counts' => $countHits,
                    'validated' => $validated
                ];
                break;
            case "twitter":
                $countHits = $subject->getTwitterProfiles()->count();
                $validated = count($subject->getTwitterProfiles()->filter(function (Profile $profile) {
                    return $profile->isValid();
                }));

                $response = [
                    'hit_counts' => $countHits,
                    'validated' => $validated
                ];
                break;
            case "instagram":
                $countHits = $subject->getInstagramProfiles()->count();
                $validated = count($subject->getInstagramProfiles()->filter(function (Profile $profile) {
                    return $profile->isValid();
                }));

                $response = [
                    'hit_counts' => $countHits,
                    'validated' => $validated
                ];
                break;
            case "pinterest":
                $countHits = $subject->getPinterestProfiles()->count();
                $validated = count($subject->getPinterestProfiles()->filter(function (Profile $profile) {
                    return $profile->isValid();
                }));

                $response = [
                    'hit_counts' => $countHits,
                    'validated' => $validated
                ];
                break;

            case "linkedin":
                $countHits = $subject->getLinkedinProfiles()->count();
                $validated = count($subject->getLinkedinProfiles()->filter(function (Profile $profile) {
                    return $profile->isValid();
                }));

                $response = [
                    'hit_counts' => $countHits,
                    'validated' => $validated
                ];
                break;

            case "youtube":
                $countHits = $subject->getYoutubeProfiles()->count();
                $validated = count($subject->getYoutubeProfiles()->filter(function (Profile $profile) {
                    return $profile->isValid();
                }));

                $response = [
                    'hit_counts' => $countHits,
                    'validated' => $validated
                ];
                break;

            case "web":
                $countHits = $subject->getWebProfiles()->count();
                $validated = count($subject->getWebProfiles()->filter(function (Profile $profile) {
                    return $profile->isValid();
                }));

                $response = [
                    'hit_counts' => $countHits,
                    'validated' => $validated
                ];
                break;

            default:
                $response = [
                    'hit_counts' => '0',
                    'validated' => '0'
                ];
        }

        return $response;
    }

    /**
     * @param $proofFind
     *
     * @return bool
     */
    private function getPlaform($proofFind)
    {
        foreach ($proofFind as $answers) {
            $proofCount = count($answers['answers'][0]['proofs']);

            if ($proofCount >= 1) {
                return true;
            } else {
                return false;
            }
        }
    }
}