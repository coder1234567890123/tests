<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: kuda
 * Date: 8/05/19
 * Time: 7:30 AM
 */

namespace App\Service;

use App\Entity\Comment;
use App\Entity\Report;
use App\Entity\Subject;
use App\Entity\User;
use App\Entity\UserTracking;
use App\Exception\InvalidTrackingActionException;
use App\Repository\MessageSystemRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Workflow;
use Twig_Error_Loader;
use Twig_Error_Runtime;
use Twig_Error_Syntax;

/**
 * Class WorkflowService
 *
 * @package App\Service
 */
class WorkflowService
{
    /**
     * @var Registry
     */
    private $workflows;

    /**
     * @var EventTrackingService
     */
    private $eventTrackingService;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var EventService
     */
    private $eventService;

    /**
     * WorkflowService constructor.
     *
     * @param Registry                $workflows
     * @param EventTrackingService    $eventTrackingService
     * @param EntityManagerInterface  $entityManager
     * @param EventService            $eventService
     * @param TokenStorageInterface   $token
     * @param MessageSystemRepository $messageSystemRepository
     */
    public function __construct(
        Registry $workflows,
        EventTrackingService $eventTrackingService,
        EntityManagerInterface $entityManager,
        EventService $eventService,
        TokenStorageInterface $token,
        MessageSystemRepository $messageSystemRepository
    )
    {
        $this->workflows = $workflows;
        $this->eventTrackingService = $eventTrackingService;
        $this->entityManager = $entityManager;
        $this->eventService = $eventService;

        $this->userToken = $token->getToken()->getUser();
    }

    /**
     * @param User                    $loggedInUser
     * @param Subject                 $subject
     * @param Request                 $request
     * @param Report                  $report
     * @param SerializerInterface     $serializer
     * @param Validator               $validator
     * @param string                  $approval
     * @param string                  $approvedValue
     *
     * @param MessageSystemRepository $messageSystemRepository
     *
     * @return JsonResponse|Response
     * @throws InvalidTrackingActionException
     */
    public function workflowApprove(
        User $loggedInUser,
        Subject $subject,
        Request $request,
        Report $report,
        SerializerInterface $serializer,
        Validator $validator,
        string $approval,
        string $approvedValue,
        MessageSystemRepository $messageSystemRepository
    )
    {
        $workflow = $this->workflows->get($subject);

        // if comment exists
        if ($request->getContent() !== '') {
            // saves comment
            /** @var Comment $comment */
            $comment = $serializer->deserialize(
                $request->getContent(),
                Comment::class,
                'json',
                DeserializationContext::create()->setGroups(['write'])
            );

            $comment->setCommentType($comment->getCommentType());
            $comment->setCommentBy($this->userToken);
            $comment->setApproval($approvedValue);

            /** @var JsonResponse $response */
            if (($response = $validator->validate($comment)) !== false) {
                return $response;
            }
            $comment->setReport($report);
            $this->entityManager->persist($comment);
            $this->entityManager->flush();
        }

        $userSource = $request->headers->has('user-type') ? $request->headers->get('user-type') : UserTracking::SOURCE_CUSTOM;

        // Update the status on the subject
        if ($workflow->can($subject, $approval)) {
            $workflow->apply($subject, $approval);
            $this->entityManager->persist($subject);
            $report->setStatus($subject->getStatus());

            if ($approval === 'complete') {
                $report->setApprovedBy($loggedInUser);
            }

            $this->entityManager->persist($report);
            $this->entityManager->flush();

            $vars = [
                'report' => $report,
                'user' => $loggedInUser,
                'type' => ''
            ];

            if ($approval === 'type_approval') {
                $action = UserTracking::ACTION_REPORT_TYPE_APPROVAL;
                $vars['type'] = 'Rush Report has been approved';

                //Sends to message
                $messageSystemRepository->messageStatusFilterSave($report);

            } else if ($approval === 'approve_team') {
                $vars['type'] = 'Investigation Completion has been approved';
                $action = UserTracking::ACTION_TEAM_LEAD_APPROVAL;

                //Sends to message
                $messageSystemRepository->messageStatusFilterSave($report);
            } else if ($approval === 'complete') {
                $vars['type'] = 'Report Completion has been approved';
                $action = UserTracking::ACTION_REPORT_COMPLETE;
            } else {
                $action = UserTracking::ACTION_SUBJECT_INVESTIGATION;
            }
            $this->eventTrackingService->track($action, $loggedInUser, $userSource, $subject, $report);
            if ($vars['type'] !== '' && $report->getAssignedTo()) {
                // Daniel To check
                // Used to send to approval_notification might no be needed anymore must check
            }

            return new Response(
                $serializer->serialize(
                    $report,
                    'json',
                    SerializationContext::create()->setGroups(["queued"])
                ), 200,
                ['Content-type' => 'application/json']
            );
        }

        if (($subject->getStatus() === 'new_request' && $approval === 'request') ||
            ($subject->getStatus() === 'under_investigation' && $approval === 'investigate')) {
            $message = "Status already set to '" . $subject->getStatus() . "'";
            $http = 200;
        } else {
            $message = "sorry cannot update transition to '$approval'";
            $http = 400;
        }
        return new JsonResponse([
            'message' => $message
        ], $http);
    }

    /**
     * @param SerializerInterface $serializer
     * @param string              $status
     * @param User                $user
     * @param Request             $request
     * @param Subject|null        $subject
     *
     * @return JsonResponse|Response
     * @throws SearchPhrase\Exception\InvalidTokenException
     * @throws InvalidTrackingActionException
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Runtime
     * @throws Twig_Error_Syntax
     */
    public function changeStatus(
        SerializerInterface $serializer,
        string $status,
        User $user,
        Request $request,
        Subject $subject = null
    )
    {
        $stat = $status;
        $userSource = $request->headers->has('user-type') ? $request->headers->get('user-type') : UserTracking::SOURCE_CUSTOM;

        $action = '';
        switch ($stat) {
            case 'validated':
                $status = 'valid';
                $action = UserTracking::ACTION_SUBJECT_PROFILE_VALID;
                break;
            case 'new_request':
                $status = 'request';
                $action = UserTracking::ACTION_INVESTIGATION_REQUEST;
                break;
            case 'search_started':
                $status = 'search_start';
                // Queue all search events.
                $this->eventService->queue($subject);
                $action = UserTracking::ACTION_INVESTIGATION_SEARCH_START;
                break;
            case 'search_completed':
                $status = 'search_complete';
                $action = UserTracking::ACTION_INVESTIGATION_SEARCH_COMPLETE;
                break;
            case 'under_investigation':
                $status = 'investigate';
                $action = UserTracking::ACTION_SUBJECT_INVESTIGATION;
                break;
            case 'investigation_completed':
                $status = 'investigation_complete';
                $action = UserTracking::ACTION_INVESTIGATION_COMPLETE;
                break;
        }

        if ($subject->getStatus() === $stat) {
            // $this->checkReport($reportRepository, $subject, $status);
            return new JsonResponse([
                'message' => "Subject status is already set to '$status'"
            ], 200);
        }

        $workflow = $this->workflows->get($subject);
        // Update the status on the subject
        if ($workflow->can($subject, $status)) {
            $workflow->apply($subject, $status);
            $this->entityManager->persist($subject);
            if ($subject->getCurrentReport()) {
                /** @var Report $report */
                $report = $subject->getCurrentReport();
                $report->setStatus($subject->getStatus());
                $this->entityManager->persist($report);
                $this->eventTrackingService->track($action, $user, $userSource, $subject, $report);
                if ($subject->getStatus() === 'search_completed' && $report->getAssignedTo()) {
                    $vars = [
                        'report' => $report
                    ];
                }
            }
            $this->entityManager->flush();

            return new Response(
                $serializer->serialize(
                    $subject,
                    'json',
                    SerializationContext::create()->setGroups(["read"])
                ), 200,
                ['Content-type' => 'application/json']
            );
        }

        //check for enabled transition
        $workflowResult = $workflow->getEnabledTransitions($subject);
        if (count($workflowResult) > 0) {
            $transitions = array_map(function ($trans) {
                return $trans->getName();
            }, $workflowResult);
            $message = "Incorrect Transition. Enabled Transitions : " . implode(',', $transitions);
        } else {
            $message = "Please make sure the investigation questions are all answered/or you not trying to return to an old status.
             Current status: " . $subject->getStatus() . ", new status: " . $stat;
        }

        return new JsonResponse([
            'error' => true,
            'message' => $message
        ], 200);
    }
}
