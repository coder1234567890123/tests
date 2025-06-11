<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\Answer;
use App\Entity\Profile;
use App\Entity\Proof;
use App\Entity\Comment;
use App\Entity\ProofStorage;
use App\Entity\Report;
use App\Entity\Subject;
use App\Entity\User;
use App\Exception\InvalidTrackingActionException;
use App\Repository\ReportRepository;
use App\Repository\SubjectRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Twig_Error_Loader;
use Twig_Error_Runtime;
use Twig_Error_Syntax;

/**
 * Class ReportSubjectDuplicatorService
 *
 * @package App\Service
 */
class ReportSubjectDuplicatorService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ReportRepository
     */
    private $reportRepository;

    /**
     * @var SubjectRepository
     */
    private $subjectRepository;

    /**
     * @var WorkflowService
     */
    private $workflowService;

    /**
     * ReportScoreCalculator constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param WorkflowService        $workflowService
     * @param ReportRepository       $reportRepository
     * @param SubjectRepository      $subjectRepository
     */
    public function __construct(EntityManagerInterface $entityManager, WorkflowService $workflowService, ReportRepository $reportRepository, SubjectRepository $subjectRepository)
    {
        $this->entityManager = $entityManager;
        $this->workflowService = $workflowService;
        $this->reportRepository = $reportRepository;
        $this->subjectRepository = $subjectRepository;
    }

    /**
     * @param Subject $subject
     * @param User    $loggedIn
     * @param Report  $selectedReport
     */
    public function duplicate(Subject $subject, User $loggedIn, Report $selectedReport)
    {
        $report = $subject->getCurrentReport();
        $report->setRiskScore($selectedReport->getRiskScore());
        $report->setReportScores($selectedReport->getReportScores());

        //updated scores duplicate
        $report->setReportScoresUpdated($selectedReport->getReportScoresUpdated());
        $report->setOverWriteReportScores($selectedReport->isOverWriteReportScores());

        $report->setRiskComment($selectedReport->getRiskComment());
        $report->setStatus('investigation_completed');
        $subject->setStatus($report->getStatus());
        $subject->setBlobFolder($selectedReport->getSubject()->getBlobFolder());
        $this->entityManager->persist($report);
        $this->entityManager->persist($subject);
        $comments = $selectedReport->getComments();

        // duplicate comments
        $this->duplicateComments($comments, $report, $loggedIn);

        $answers = $selectedReport->getAnswers();

        // duplicate answers, proof and storage
        $this->duplicateAnswers($answers, $report, $subject, $loggedIn);
        $this->duplicateSubjectDetails($subject, $selectedReport->getSubject(), $loggedIn);
        $this->entityManager->flush();
    }

    /**
     * @param Subject             $subject
     * @param User                $loggedIn
     * @param Report              $selectedReport
     * @param Request             $request
     * @param SerializerInterface $serializer
     *
     * @throws InvalidTrackingActionException
     * @throws SearchPhrase\Exception\InvalidTokenException
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Runtime
     * @throws Twig_Error_Syntax
     */
    public function duplicateWithNewSearch(
        Subject $subject,
        User $loggedIn,
        Report $selectedReport,
        Request $request,
        SerializerInterface $serializer
    )
    {
        $report = $subject->getCurrentReport();
        $report->setRiskScore($selectedReport->getRiskScore());
        $report->setReportScores($selectedReport->getReportScores());
        $report->setRiskComment($selectedReport->getRiskComment());
        $this->entityManager->persist($report);
        $this->duplicateSubjectDetails(
            $subject,
            $selectedReport->getSubject(),
            $loggedIn,
            false);

        $this->workflowService->changeStatus(
            $serializer,
            'search_started',
            $loggedIn,
            $request,
            $subject
        );
    }

    /**
     * @param Subject $subject
     *
     * @return array
     */
    public function getSubjectDuplicationInfo(Subject $subject)
    {
        $reports = $this->reportRepository->getBySubjectIdentification($subject->getIdentification());
        
        $subjects = $this->subjectRepository->getSubjectByIdentification($subject->getIdentification());

        $result = [];
        if (count($reports) > 0 || count($subjects) > 0) {
            foreach ($subjects as $sub) {
                if ($sub->getId() !== $subject->getId()) {
                    $result['subjects'][] = [
                        'id' => $sub->getId(),
                        'identification' => $sub->getIdentification(),
                        'subject_id' => $subject->getId(),
                        'name' => $sub->getFirstName() . ' ' . $sub->getLastName(),
                        'company' => $sub->getCompanyName(),
                        'status' => $sub->getStatus()
                    ];
                }
            }

            foreach ($reports as $report) {
                if ($report->getStatus() === 'completed') {
                    $result['reports'][] = [
                        'id' => $report->getId(),
                        'subject_id' => $report->getSubject()->getId(),
                        'sequence' => $report->getSequence(),
                        'completed_date' => $report->getCompletedDate() ? $report->getCompletedDate() : null,
                        'status' => $report->getStatus()
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * @param Collection $comments
     * @param Report     $report
     * @param User       $user
     */
    private function duplicateComments(Collection $comments, Report $report, User $user)
    {
        /** @var Comment $comment */
        foreach ($comments as $comment) {
            /** @var Comment $newClone */
            $newClone = clone $comment;
            $newClone->setReport($report);
            $newClone->setCommentBy($user);
            $this->entityManager->persist($newClone);
        }
    }

    /**
     * @param Collection $answers
     * @param Report     $report
     * @param Subject    $subject
     * @param User       $user
     */
    private function duplicateAnswers(Collection $answers, Report $report, Subject $subject, User $user)
    {
        /** @var Answer $answer */
        foreach ($answers as $answer) {
            $proofs = $answer->getProofs();

            /** @var Answer $newAnswerClone */
            $newAnswerClone = clone $answer;
            $newAnswerClone->setReport($report);
            $newAnswerClone->setSubject($subject);
            $newAnswerClone->setUser($user);
            $this->entityManager->persist($newAnswerClone);

            /** @var Proof $proof */
            foreach ($proofs as $proof) {
                /** @var ProofStorage $newProofStorageClone */
                $newProofStorageClone = clone $proof->getProofStorage();
                $newProofStorageClone->setSubject($subject);
                $newProofStorageClone->setCreatedBy($user);
                $this->entityManager->persist($newProofStorageClone);

                /** @var Proof $newProofClone */
                $newProofClone = clone $proof;
                $newProofClone->setAnswer($newAnswerClone);
                $newProofClone->setProofStorage($newProofStorageClone);
                $this->entityManager->persist($newProofClone);
            }
        }
    }

    /**
     * @param Subject $subject
     * @param Subject $selected
     * @param User    $user
     * @param bool    $validate
     */
    private function duplicateSubjectDetails(
        Subject $subject,
        Subject $selected,
        User $user,
        bool $validate = true)
    {
        /** @var Profile $profile */
        foreach ($selected->getProfiles() as $profile) {
            /** @var Profile $newProfileClone */
            $newProfileClone = clone $profile;
            $newProfileClone->setSubject($subject);
            if (!$validate) {
                $newProfileClone->setValid(false);
            }
            $this->entityManager->persist($newProfileClone);
        }
    }
}