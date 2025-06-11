<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\BundleUsed;
use App\Entity\Comment;
use App\Entity\Company;
use App\Entity\MessageSystem;
use App\Entity\Profile;
use App\Entity\Question;
use App\Entity\Report;
use App\Entity\Subject;
use App\Entity\SystemConfig;
use App\Entity\UserTracking;
use App\Helper\ReportFormatter;
use App\Repository\CommentRepository;
use App\Repository\DefaultBrandingRepository;
use App\Repository\BundleUsedRepository;
use App\Repository\CompanyProductRepository;
use App\Repository\MessageSystemRepository;
use App\Repository\ReportRepository;
use App\Repository\SubjectRepository;
use App\Repository\SystemConfigRepository;
use App\Service\ApiCompanyProductService;
use App\Service\ApiErrorsService;
use App\Service\EventTrackingService;
use App\Service\PdfService;
use App\Service\ReportScoreCalculator;
use App\Service\ReportSubjectDuplicatorService;
use App\Service\WorkflowService;
use App\Service\ReportTimeFrames\TimeFramesService;
use App\Service\PdfProofService;
use App\Service\ApiReportsService;
use App\Service\AccountsService;
use Symfony\Component\HttpFoundation\RequestStack;
use DateTime;
use JMS\Serializer\SerializationContext;
use App\Service\Validator;
use Hateoas\Representation\CollectionRepresentation;
use Hateoas\Representation\PaginatedRepresentation;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Areas;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Workflow;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class ReportController
 *
 * @package App\Controller
 */
class ReportController extends AbstractController
{
    /**
     * @param ReportRepository    $repository
     * @param SerializerInterface $serializer
     * @param Request             $request
     *
     * @return Response
     *
     * @Route("/api/report", methods={"GET"}, name="report_get")
     * @Security("is_granted('ROLE_ANALYST') or is_granted('ROLE_USER_STANDARD')")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get a paginated list of report queues.",
     *)
     * @SWG\Tag(name="report")
     *
     * @Areas({"internal","default"})
     */
    public function getAction(
        ReportRepository $repository,
        SerializerInterface $serializer,
        Request $request
    ) {
        // Get Parameters
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $descending = $request->get('descending', false);
        $descending = $descending == 'true' ? true : false;
        $sort = $request->get('sort', 'created_at');
        $search = $request->get('search', '');
        $date_from = $request->get('date_from', null);
        $date_to = $request->get('date_to', null);
        $company = $request->get('company', null);
        $type = $request->get('type', null);
        $status = $request->get('status', null);
        $filter = $request->get('filter_request');

        // Configure Pagination
        $offset = ($page - 1) * $limit;
        $reportQueues = $repository->paginatedBySubject(
            $filter,
            $company,
            $date_from,
            $date_to,
            $offset,
            $limit,
            $sort,
            $descending,
            $search,
            $this->getUser(),
            $type,
            $status
        );

        $count = $repository->count();
        $pages = (int)ceil($count / $limit);

        $paginatedCollection = new PaginatedRepresentation(
            new CollectionRepresentation(
                $reportQueues,
                'report_queues',
                'report_queues'
            ),
            'report_get',
            [],
            $page,
            $limit,
            $pages,
            'page',
            'limit',
            false,
            $count
        );

        return new Response(
            $serializer->serialize(
                $paginatedCollection,
                'json',
                SerializationContext::create()->setGroups(['Default', 'queued'])
            ),
            200,
            ['Content-type' => 'application/json']
        );
    }

    /**
     * @param ReportRepository        $repository
     * @param SerializerInterface     $serializer
     * @param Validator               $validator
     * @param Request                 $request
     * @param Report                  $report
     *
     * @param MessageSystemRepository $messageSystemRepository
     *
     * @return Response
     *
     * @Route("/api/report/{id}", methods={"PATCH"}, name="report_update")
     * @ParamConverter("report", class="App\Entity\Report")
     * @IsGranted("ROLE_ANALYST", subject="report")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Update the report entity.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Report::class, groups={"write"})
     *     )
     * )
     * @SWG\Tag(name="report")
     *
     * @Areas({"internal"})
     */
    public function updateAction(
        ReportRepository $repository,
        SerializerInterface $serializer,
        Validator $validator,
        Request $request,
        Report $report,
        MessageSystemRepository $messageSystemRepository,
        ApiErrorsService $apiErrorsService
    ) {
        $data = json_decode($request->getContent(), true);
        $data['id'] = $report->getId();

        /** @var Report $report */
        $report = $serializer->deserialize(
            json_encode($data),
            Report::class,
            'json',
            DeserializationContext::create()->setGroups(['write'])
        );

        /** @var JsonResponse $response */
        if (($response = $validator->validate($report)) !== false) {
            return $response;
        }

        // Valid Entity
        try {
            $repository->save($report);

            return new Response(
                $serializer->serialize(
                    $report,
                    'json',
                    SerializationContext::create()->setGroups(["read"])
                ),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param SerializerInterface $serializer
     * @param Report              $report
     *
     * @return Response
     *
     * @Route("/api/report/{id}", methods={"GET"}, name="report_get_id")
     * @ParamConverter("report", class="App\Entity\Report")
     * @Security("is_granted('ROLE_ANALYST') or is_granted('ROLE_TEAM_LEAD')")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns a specific report.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Report::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="report")
     *
     * @Areas({"internal"})
     */
    public function getIDAction(SerializerInterface $serializer, Report $report)
    {
        return new Response(
            $serializer->serialize(
                $report,
                'json',
                SerializationContext::create()->setGroups(['read'])
            ),
            200,
            [
                'Content-Type' => 'application/json'
            ]
        );
    }

    /**
     * @param ReportRepository    $repository
     * @param SerializerInterface $serializer
     * @param Report              $report
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/report/{id}", methods={"delete"}, name="report_delete")
     * @ParamConverter("report", class="App\Entity\Report")
     * @IsGranted("ROLE_TEAM_LEAD", subject="report")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Delete a report.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Report::class, groups={"write"})
     *     )
     * )
     * @SWG\Tag(name="report")
     *
     * @Areas({"internal"})
     */
    public function deleteAction(
        ReportRepository $repository,
        SerializerInterface $serializer,
        Report $report,
        ApiErrorsService $apiErrorsService
    ) {
        try {
            $repository->disable($report);

            return new Response(
                $serializer->serialize(
                    $report,
                    'json',
                    SerializationContext::create()->setGroups(["read"])
                ),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param SerializerInterface            $serializer
     * @param Subject                        $subject
     * @param ReportSubjectDuplicatorService $duplicateService
     *
     * @return Response
     *
     * @Route("/api/report/{id}/information", methods={"GET"}, name="report_get_id_info")
     * @ParamConverter("subject", class="App\Entity\Subject")
     * @IsGranted("ROLE_ANALYST", subject="subject")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns a specific information for a subject and reports.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Report::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="report")
     *
     * @Areas({"internal"})
     */
    public function getSubjectInfoAction(
        SerializerInterface $serializer,
        Subject $subject,
        ReportSubjectDuplicatorService $duplicateService,
        ApiErrorsService $apiErrorsService
    ) {
        try {
            $result = $duplicateService->getSubjectDuplicationInfo($subject);

            return new Response(
                $serializer->serialize(
                    $result,
                    'json',
                    SerializationContext::create()->setGroups(["read"])
                ),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param Subject                        $subject
     * @param Report                         $report
     * @param ReportSubjectDuplicatorService $duplicateService
     *
     * @param ApiErrorsService               $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/report/{subject}/duplicate/{report}", methods={"GET"}, name="report_get_id_duplicate")
     * @ParamConverter("subject", class="App\Entity\Subject", options={"id" = "subject"})
     * @ParamConverter("report", class="App\Entity\Report", options={"id" = "report"})
     * @IsGranted("ROLE_ANALYST", subject="subject")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Duplicate a specific report for subject",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Report::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="report")
     *
     * @Areas({"internal"})
     */
    public function getDuplicateAction(
        Subject $subject,
        Report $report,
        ReportSubjectDuplicatorService $duplicateService,
        ApiErrorsService $apiErrorsService
    ) {
        try {
            $result = $duplicateService->duplicate($subject, $this->getUser(), $report);

            return new JsonResponse([
                'message' => 'Duplication Successful'
            ], 200);
        } catch (\Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param SerializerInterface            $serializer
     * @param Subject                        $subject
     * @param Report                         $report
     * @param ReportSubjectDuplicatorService $duplicateService
     * @param Request                        $request
     *
     * @param ApiErrorsService               $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/report/{subject}/duplicate_search/{report}", methods={"GET"}, name="report_get_id_duplicate_search")
     * @ParamConverter("subject", class="App\Entity\Subject", options={"id" = "subject"})
     * @ParamConverter("report", class="App\Entity\Report", options={"id" = "report"})
     * @IsGranted("ROLE_ANALYST", subject="subject")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Duplicate a specific report for subject and perform a new search",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Report::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="report")
     *
     * @Areas({"internal"})
     */
    public function getDuplicateWithNewSearchAction(
        SerializerInterface $serializer,
        Subject $subject,
        Report $report,
        ReportSubjectDuplicatorService $duplicateService,
        Request $request,
        ApiErrorsService $apiErrorsService
    ) {
        try {
            $result = $duplicateService->duplicateWithNewSearch(
                $subject,
                $this->getUser(),
                $report,
                $request,
                $serializer
            );

            return new JsonResponse([
                'message' => 'Duplication Successful'
            ], 200);
        } catch (\Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param ReportRepository    $repository
     * @param SerializerInterface $serializer
     * @param Report              $report
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/report/{id}/enable", methods={"PUT"}, name="report_enable")
     * @ParamConverter("report", class="App\Entity\Report")
     * @IsGranted("ROLE_TEAM_LEAD", subject="report")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Enables a report",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Report::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="report")
     *
     * @Areas({"internal"})
     */
    public function enableAction(
        ReportRepository $repository,
        SerializerInterface $serializer,
        Report $report,
        ApiErrorsService $apiErrorsService
    ) {
        try {
            $repository->enable($report);

            return new Response(
                $serializer->serialize(
                    $report,
                    'json',
                    SerializationContext::create()->setGroups(["read"])
                ),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param ReportRepository    $repository
     * @param SerializerInterface $serializer
     * @param Report              $report
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/report/{id}/close", methods={"PUT"}, name="report_close")
     * @ParamConverter("report", class="App\Entity\Report")
     * @IsGranted("ROLE_TEAM_LEAD", subject="report")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Closes a report",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Report::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="report")
     *
     * @Areas({"internal"})
     */
    public function closeAction(
        ReportRepository $repository,
        SerializerInterface $serializer,
        Report $report,
        ApiErrorsService $apiErrorsService
    ) {
        try {
            $repository->closeReport($report);

            return new Response(
                $serializer->serialize(
                    $report,
                    'json',
                    SerializationContext::create()->setGroups(["read"])
                ),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param ReportRepository    $repository
     * @param SerializerInterface $serializer
     * @param Request             $request
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/report/open", methods={"PUT"}, name="report_open")
     *
     * @SWG\Response(
     *     response="200",
     *     description="opens a report",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Report::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="report")
     *
     * @Areas({"internal"})
     */
    public function openAction(
        ReportRepository $repository,
        SerializerInterface $serializer,
        Request $request,
        ApiErrorsService $apiErrorsService
    ) {
        try {
            $id = $request->get('id', null);
            $report = $repository->getClosedReport($id);
            // only team lead allowed and above (SUPER) to open closed reports
            $this->denyAccessUnlessGranted('ROLE_TEAM_LEAD', $report);
            $repository->closeOpenReport($report->getSubject()->getId());
            $repository->openReport($report);

            return new Response(
                $serializer->serialize(
                    $report,
                    'json',
                    SerializationContext::create()->setGroups(["read"])
                ),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param ReportRepository    $repository
     * @param SerializerInterface $serializer
     * @param Request             $request
     *
     * @return Response
     *
     * @Route("/api/report/subject/reports", methods={"GET"}, name="report_subject_reports")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get a paginated list of subject reports",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Report::class, groups={"read"}))
     *     )
     * )
     * @SWG\Tag(name="report")
     *
     *
     * @Areas({"internal"})
     */
    public function getBySubjectAction(
        ReportRepository $repository,
        SerializerInterface $serializer,
        Request $request
    ) {
        // Get Parameters
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $descending = $request->get('descending', false);
        $descending = $descending == 'true' ? true : false;
        $sort = $request->get('sort', 'created_at');
        $search = $request->get('search', '');
        $date_from = $request->get('date_from', null);
        $date_to = $request->get('date_to', null);
        $company = $request->get('company', null);
        $type = $request->get('type', null);
        $status = $request->get('status', null);
        $filter = $request->get('filter_request');

        // Configure Pagination
        $offset = ($page - 1) * $limit;
        $reportQueues = $repository->paginatedBySubject(
            $filter,
            $company,
            $date_from,
            $date_to,
            $offset,
            $limit,
            $sort,
            $descending,
            $search,
            $this->getUser(),
            $type,
            $status
        );

        $count = $repository->count();
        $pages = (int)ceil($count / $limit);

        $paginatedCollection = new PaginatedRepresentation(
            new CollectionRepresentation(
                $reportQueues,
                'reports',
                'reports'
            ),
            'report_subject_reports',
            [],
            $page,
            $limit,
            $pages,
            'page',
            'limit',
            false,
            $count
        );
        return new Response(
            $serializer->serialize(
                $paginatedCollection,
                'json',
                SerializationContext::create()->setGroups(['Default', 'queued'])
            ),
            200,
            ['Content-type' => 'application/json']
        );
    }

    /**
     * @param ReportRepository     $repository
     * @param SubjectRepository    $subjectRepository
     * @param SerializerInterface  $serializer
     * @param Report               $report
     * @param Request              $request
     * @param Registry             $workflows
     * @param EventTrackingService $eventTrackingService
     *
     * @param ApiErrorsService     $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/report/{id}/status", methods={"GET"}, name="report_update_status")
     * @ParamConverter("report", class="App\Entity\report")
     * @IsGranted("ROLE_TEAM_LEAD", subject="report")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Change status on report.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Report::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="report")
     *
     * @Areas({"internal"})
     */
    public function updateReportStatusAction(
        ReportRepository $repository,
        SubjectRepository $subjectRepository,
        SerializerInterface $serializer,
        Report $report,
        Request $request,
        Registry $workflows,
        EventTrackingService $eventTrackingService,
        ApiErrorsService $apiErrorsService
    ) {
        try {
            // Get Parameters
            $toStatus = $request->get('status', 'completed');
            $userSource = $request->headers->has('user-type') ? $request->headers->get('user-type') : UserTracking::SOURCE_CUSTOM;

            /** @var Subject $subject */
            $subject = $report->getSubject();
            $workflow = $workflows->get($subject);
            $status = 'complete';

            switch ($toStatus) {
                case 'abandoned_request':
                    $status = 'abandon_request';
                    break;
                default:
                    $status = 'complete';
                    break;
            }
            $workflowResult = $workflow->getEnabledTransitions($subject);
            $transitions = array_map(function ($trans) {
                return $trans->getName();
            }, $workflowResult);

            if ($toStatus === 'abandoned_request') {
                $report->setStatus($toStatus);
                $repository->save($report);

                $eventTrackingService->track(UserTracking::ACTION_ABANDON_REQUEST, $this->getUser(), $userSource, $subject, $report);

                return new Response(
                    $serializer->serialize(
                        $report,
                        'json',
                        SerializationContext::create()->setGroups(["read"])
                    ),
                    200,
                    ['Content-Type' => 'application/json']
                );
            } elseif (in_array($status, $transitions) && $workflow->can($subject, $status)) {
                $workflow->apply($subject, $status);
                $subjectRepository->save($subject);
                $report->setStatus($subject->getStatus());

                $eventTrackingService->track(UserTracking::ACTION_REPORT_COMPLETE, $this->getUser(), $userSource, $subject, $report);

                return new Response(
                    $serializer->serialize(
                        $report,
                        'json',
                        SerializationContext::create()->setGroups(["read"])
                    ),
                    200,
                    ['Content-Type' => 'application/json']
                );
            } else {
                return new JsonResponse([
                    'message' => 'Incorrect workflow transition: ' . $status . ' - available: ' . implode(',', $transitions)
                ], 400);
            }
        } catch (\Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param ReportRepository     $repository
     * @param SubjectRepository    $subjectRepository
     * @param SerializerInterface  $serializer
     * @param Report               $report
     * @param Request              $request
     * @param Registry             $workflows
     * @param EventTrackingService $eventTrackingService
     *
     * @param ApiErrorsService     $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/report/{id}/abandoned", methods={"GET"}, name="report_request_abandoned")
     * @ParamConverter("report", class="App\Entity\Report")
     * @IsGranted("ROLE_SUPER_ADMIN", subject="report")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Change status on report.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Report::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="report")
     *
     * @Areas({"internal"})
     */
    public function abandonedAction(
        ReportRepository $repository,
        SubjectRepository $subjectRepository,
        SerializerInterface $serializer,
        Report $report,
        Request $request,
        Registry $workflows,
        EventTrackingService $eventTrackingService,
        ApiErrorsService $apiErrorsService
    ) {
        try {
            // Get Parameters

            $userSource = $request->headers->has('user-type') ? $request->headers->get('user-type') : UserTracking::SOURCE_CUSTOM;

            $subjectRepository->abandonedStatus($report->getSubject());
            $report->setStatus('abandoned');
            $repository->save($report);

            $eventTrackingService->track(UserTracking::ACTION_SUPER_ABANDON, $this->getUser(), $userSource, $report->getSubject(), $report);

            return new Response(
                $serializer->serialize(
                    $report,
                    'json',
                    SerializationContext::create()->setGroups(["read"])
                ),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param ReportRepository     $repository
     * @param SubjectRepository    $subjectRepository
     * @param SerializerInterface  $serializer
     * @param Report               $report
     * @param Request              $request
     * @param Registry             $workflows
     * @param EventTrackingService $eventTrackingService
     *
     * @param ApiErrorsService     $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/report/{id}/cancel-abandoned", methods={"GET"}, name="report_request_cancel_abandoned")
     * @ParamConverter("report", class="App\Entity\Report")
     * @IsGranted("ROLE_SUPER_ADMIN", subject="report")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Change status on report.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Report::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="report")
     *
     * @Areas({"internal"})
     */
    public function cancelAbandonedAction(
        ReportRepository $repository,
        SubjectRepository $subjectRepository,
        SerializerInterface $serializer,
        Report $report,
        Request $request,
        Registry $workflows,
        EventTrackingService $eventTrackingService,
        ApiErrorsService $apiErrorsService
    ) {
        try {
            // Get Parameters

            $userSource = $request->headers->has('user-type') ? $request->headers->get('user-type') : UserTracking::SOURCE_CUSTOM;

            $report->setStatus($report->getSubject()->getStatus());
            $repository->save($report);

            $eventTrackingService->track(UserTracking::ACTION_SUPER_ABANDON_REJECT, $this->getUser(), $userSource, $report->getSubject(), $report);

            return new Response(
                $serializer->serialize(
                    $report,
                    'json',
                    SerializationContext::create()->setGroups(["read"])
                ),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }


    /**
     * @param ReportRepository         $repository
     * @param SubjectRepository        $subjectRepository
     * @param MessageSystemRepository  $messageSystemRepository
     * @param SerializerInterface      $serializer
     * @param Registry                 $workflows
     * @param Subject                  $subject
     * @param Request                  $request
     * @param Validator                $validator
     * @param EventTrackingService     $eventTrackingService
     * @param TimeFramesService        $reportTimeFrames
     * @param CompanyProductRepository $companyProductRepository
     * @param AccountsService          $accountsService
     * @param ApiCompanyProductService $apiCompanyProductService
     *
     * @param ApiErrorsService         $apiErrorsService
     *
     * @return Response
     *
     * @throws \Exception
     * @Route("/api/report/subject/{id}/request", methods={"POST"}, name="subject_request_investigation")
     * @ParamConverter("subject", class="App\Entity\Subject")
     *
     * @IsGranted("ROLE_USER_STANDARD", subject="subject", statusCode=404,  message="No access is Granted")
     * @IsGranted("ROLE_TEAM_LEAD", subject="subject" , statusCode=404,  message="No access is Granted")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Request investigation on subject.",
     * )
     *
     * @SWG\Tag(name="report")
     *
     * @Areas({"internal","default"})
     */
    public function requestInvestigationAction(
        ReportRepository $repository,
        SubjectRepository $subjectRepository,
        MessageSystemRepository $messageSystemRepository,
        SerializerInterface $serializer,
        Registry $workflows,
        Subject $subject,
        Request $request,
        Validator $validator,
        EventTrackingService $eventTrackingService,
        TimeFramesService $reportTimeFrames,
        CompanyProductRepository $companyProductRepository,
        AccountsService $accountsService,
        ApiCompanyProductService $apiCompanyProductService,
        ApiErrorsService $apiErrorsService
    ) {
        $userSource = $request->headers->has('user-type') ? $request->headers->get('user-type') : UserTracking::SOURCE_CUSTOM;

        //Check if accounts is open
        $accountCheck = $apiCompanyProductService->basicAccountDetails('request', $subject->getCompany());

        $account_status = $accountCheck['account_status'];

        if ($account_status !== "suspended") {
            $normalReportAllowed = $accountCheck['normal_report_allowed'];
            $rushedReportAllowed = $accountCheck['rushed_report_allowed'];
            $testReportAllowed = $accountCheck['test_report_allowed'];

            $content = json_decode($request->getContent(), true);
            $requestType = $content['request_type'];

            //Checks each report type and if you there is enough
            switch ($requestType) {
                case 'normal':
                    if ($normalReportAllowed === false) {
                        return new JsonResponse([
                            'message' => $accountCheck
                        ], 400);
                        die();
                    }
                    break;
                case
                'rush':
                    if ($rushedReportAllowed === false) {
                        return new JsonResponse([
                            'message' => $accountCheck
                        ], 400);
                        die();
                    }
                    break;
                case 'test':
                    if ($testReportAllowed === false) {
                        return new JsonResponse([
                            'message' => $accountCheck
                        ], 400);
                        die();
                    }
                    break;
                default:
                    return new JsonResponse([
                        'message' => $accountCheck
                    ], 400);
                    die();
                    break;
            }
        } else {
            return new JsonResponse([
                'message' => $accountCheck
            ], 400);
            die();
        }

        /** @var Report $report */
        $report = $serializer->deserialize(
            $request->getContent(),
            Report::class,
            'json',
            DeserializationContext::create()->setGroups(['queued_write'])
        );

        $getTimeframe = $reportTimeFrames->getTimeFrame($report->getRequestType());

        $date = new DateTime($getTimeframe);

        // set sequence value
        $repository->closeOpenReport($subject->getId());
        $count = $repository->count() + 1;

        $subjectReports = count($subjectRepository->duplicateIdCheck($subject->getIdentification()));

        $report->setSequence('RPT' . $count);

        /** @var JsonResponse $response */
        if (($response = $validator->validate($report)) !== false) {
            return $response;
        }

        try {
            $workflow = $workflows->get($subject);

            if (
                $report->getrequestType() === 'rush' ||
                $report->getrequestType() === 'test'
            ) {
                $newStatus = 'needs_approval';
            } else {
                $newStatus = 'new_request';
            }

            // Update the status on the subject
            if ($workflow->can($subject, 'request')) {
                // checks product type
                $accountsService->getProductType($subject->getCompany());

                $report->setDueDate($date);
                $report->setUser($this->getUser());
                $report->setCompany($subject->getCompany());
                $report->setSubject($subject);
                $report->setStatus($newStatus);
                if ($subjectReports > 1) {
                    $report->setOptionValue(1);
                }
                $report->setRiskComment('none');
                $repository->save($report);
                $workflow->apply($subject, 'request');
                $subjectRepository->save($subject);
                $eventTrackingService->track(UserTracking::ACTION_INVESTIGATION_REQUEST, $this->getUser(), $userSource, $subject, $report);

                //Adds bundle for user as part of the accounting system
                $accountsService->addUnit($subject, $report);

                $eventTrackingService->track(UserTracking::ACTION_INVESTIGATION_REQUEST, $this->getUser(), $userSource, $subject, $report);

                return new Response(
                    $serializer->serialize(
                        $report,
                        'json',
                        SerializationContext::create()->setGroups(["queued"])
                    ),
                    200,
                    ['Content-type' => 'application/json']
                );
            }

            //check for enabled transition
            $workflowResult = $workflow->getEnabledTransitions($subject);
            if (count($workflowResult) > 0) {
                $enabled = $workflowResult[0]->getName();
                $message = "Incorrect Transition. Enabled Transition : $enabled";
            } else {
                $message = "Please make sure subject is validated";
            }

            return new JsonResponse([
                'message' => $message
            ], 400);
        } catch (\Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }


    /**
     * @param ReportRepository     $repository
     * @param SubjectRepository    $subjectRepository
     * @param SerializerInterface  $serializer
     * @param Registry             $workflows
     * @param Subject              $subject
     * @param Request              $request
     * @param Validator            $validator
     * @param EventTrackingService $eventTrackingService
     * @param TimeFramesService    $reportTimeFrames
     *
     * @param ApiErrorsService     $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/report/subject/{id}/new_invest", methods={"POST"}, name="subject_request_new_investigation")
     * @ParamConverter("subject", class="App\Entity\Subject")
     * @Security("is_granted('ROLE_USER_STANDARD', subject) or is_granted('ROLE_TEAM_LEAD', subject)")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Request more investigations on exisisting subject.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Report::class, groups={"queued_write"})
     *     )
     * )
     * @SWG\Tag(name="report")
     *
     * @Areas({"internal"})
     */
    public function requestNewInvestigationAction(
        ReportRepository $repository,
        SubjectRepository $subjectRepository,
        SerializerInterface $serializer,
        Registry $workflows,
        Subject $subject,
        Request $request,
        Validator $validator,
        EventTrackingService $eventTrackingService,
        TimeFramesService $reportTimeFrames,
        ApiErrorsService $apiErrorsService
    ) {
        try {
            // only start a new investigation if the current one is completed
            // this way even if subject details are changed they won't affect previous reports
            if ($subject->getStatus() === 'completed') {
                $subject->setStatus('new_subject');
                $subjectRepository->save($subject);
                return $this->requestInvestigationAction($repository, $subjectRepository, $serializer, $workflows, $subject, $request, $validator, $eventTrackingService, $reportTimeFrames);
            } else {
                return new JsonResponse([
                    'error' => true,
                    'message' => 'complete current report'
                ], 200);
            }
        } catch (\Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param SerializerInterface     $serializer
     * @param Registry                $workflows
     * @param Report                  $report
     * @param Request                 $request
     * @param Validator               $validator
     * @param WorkflowService         $workflowService
     * @param ReportRepository        $repository
     * @param ReportScoreCalculator   $calculator
     *
     * @param MessageSystemRepository $messageSystemRepository
     *
     * @param ApiErrorsService        $apiErrorsService
     *
     * @return JsonResponse|Response
     *
     * @Route("/api/report/queue/{id}/approve", methods={"POST"}, name="report_queue_approval")
     * @ParamConverter("report", class="App\Entity\Report")
     * @IsGranted("ROLE_TEAM_LEAD", subject="report")
     * @IsGranted("ROLE_SUPER_ADMIN", subject="report")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Approve report queues",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Report::class, groups={"queued_write"})
     *     )
     * )
     * @SWG\Tag(name="report")
     *
     * @Areas({"internal"})
     */
    public function approvalAction(
        SerializerInterface $serializer,
        Registry $workflows,
        Report $report,
        Request $request,
        Validator $validator,
        WorkflowService $workflowService,
        ReportRepository $repository,
        ReportScoreCalculator $calculator,
        MessageSystemRepository $messageSystemRepository,
        ApiErrorsService $apiErrorsService
    ) {
        $approvedValue = $request->get('approved', 'yes');

        try {
            $userSource = $request->headers->has('user-type') ? $request->headers->get('user-type') : UserTracking::SOURCE_CUSTOM;
            /** @var Subject $subject */
            $subject = $report->getSubject();
            $workflow = $workflows->get($subject);

            $workflowResult = $workflow->getEnabledTransitions($subject);
            $transitions = array_map(function ($trans) {
                return $trans->getName();
            }, $workflowResult);
            $enabled = count($transitions) > 0 ? $transitions[0] : 'investigate';
            $message = '';

            if (count($workflowResult) > 0) {
                // check which roles allowed to approve and if in correct transition to approve
                if (($this->getUser()->hasRole('ROLE_TEAM_LEAD') || $this->getUser()->hasRole('ROLE_SUPER_ADMIN'))
                    && in_array('approve_team', $transitions)
                ) {
                    $enabled = $approvedValue === 'yes' ? 'approve_team' : 'investigate';
                    $report->setReportScores([]);
                    return $workflowService->workflowApprove(
                        $this->getUser(),
                        $subject,
                        $request,
                        $report,
                        $serializer,
                        $validator,
                        $enabled,
                        $approvedValue,
                        $messageSystemRepository
                    );
                } elseif (
                    $this->getUser()->hasRole('ROLE_SUPER_ADMIN')
                    && (in_array('type_approval', $transitions)
                        || in_array('complete', $transitions))
                ) {
                    $enabled = in_array('type_approval', $transitions) ? 'type_approval' : 'complete';
                    if (
                        ($enabled === 'type_approval'
                            && $report->getRequestType() === 'rush' && $approvedValue === 'no')
                        ||
                        ($enabled === 'type_approval'
                            && $report->getRequestType() === 'test' && $approvedValue === 'no')
                    ) {
                        $report->setRequestType('normal');
                        $enabled = 'request';
                    } elseif ($enabled === 'complete' && $approvedValue === 'no') {
                        $enabled = 'investigate';
                    } elseif ($enabled === 'complete') {
                        $date = new DateTime();
                        $report->setCompletedDate($date);
                    }

                    $result = $workflowService->workflowApprove(
                        $this->getUser(),
                        $subject,
                        $request,
                        $report,
                        $serializer,
                        $validator,
                        $enabled,
                        $approvedValue,
                        $messageSystemRepository
                    );

                    if ($enabled === 'complete') {
                        $req = new Request();
                        $req->headers->set('user-type', $userSource);
                        $this->reportAction($repository, $serializer, $report->getSubject(), $req, $calculator, $apiErrorsService);
                    }

                    return $result;
                } else {
                    $message = "Incorrect role/Transition. Enabled Transition : $enabled";
                }
            } else {
                $message = "An error occured: no workflow enabled. Please check subject has correct status for this approval";
            }

            return new JsonResponse([
                'message' => $message
            ], 400);
        } catch (\Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param SerializerInterface     $serializer
     * @param Subject                 $subject
     * @param Request                 $request
     * @param WorkflowService         $workflowService
     *
     *
     * @param MessageSystemRepository $messageSystemRepository
     *
     * @param ApiErrorsService        $apiErrorsService
     *
     * @return JsonResponse|Response
     *
     * @Route("/api/report/subject/{id}/status", methods={"GET"}, name="subject_update_status")
     * @ParamConverter("subject", class="App\Entity\Subject")
     * @IsGranted("ROLE_ANALYST")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Change status on subject.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Subject::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="report")
     *
     * @Areas({"internal"})
     */
    public function updateSubjectStatusAction(
        SerializerInterface $serializer,
        Subject $subject,
        Request $request,
        WorkflowService $workflowService,
        MessageSystemRepository $messageSystemRepository,
        ApiErrorsService $apiErrorsService
    ) {
        // Get Parameters
        $status = $request->get('status', 'new_request');

        try {
            return $workflowService->changeStatus($serializer, $status, $this->getUser(), $request, $subject);
        } catch (\Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param SystemConfig[] $configs
     * @param Company        $company
     *
     * @return mixed
     */
    private function buildConfigRebuild($configs, Company $company)
    {
        $configVals = [];
        $farosianVals = [];
        $companyVals = [];

        foreach ($configs as $config) {
            $farosianVals[$config->getOpt()] = $config->getVal();
        }
        if ($company) {
            $companyVals['pdf_password'] = $company->getPdfPassword();
            $companyVals['pdf_password_set'] = $company->isPasswordSet();
        }

        $configVals['default'] = $farosianVals;
        $configVals['company'] = $companyVals;

        return $configVals;
    }

    /**
     * @param SystemConfig[] $configs
     * @param Company        $company
     *
     * @return mixed
     */
    private function buildConfig($configs, Company $company)
    {
        $configVals = [];
        $farosianVals = [];
        $companyVals = [];

        foreach ($configs as $config) {
            $farosianVals[$config->getOpt()] = $config->getVal();
        }
        if ($company) {
            $companyVals['image_front_page'] = $company->getImageFrontPage();
            $companyVals['image_footer_logo'] = $company->getImageFooterLogo();
            $companyVals['theme_color'] = $company->getThemeColor();
            $companyVals['theme_color_second'] = $company->getThemeColorSecond();
            $companyVals['footer_link'] = $company->getFooterLink();
            $companyVals['pdf_password'] = $company->getPdfPassword();
            $companyVals['pdf_password_set'] = $company->isPasswordSet();
            $companyVals['disclaimer'] = $company->getDisclaimer();
        }

        $configVals['default'] = $farosianVals;
        $configVals['company'] = $companyVals;

        return $configVals;
    }

    /**
     * @param ReportRepository      $repository
     * @param SerializerInterface   $serializer
     * @param Subject               $subject
     * @param Request               $request
     * @param ReportScoreCalculator $calculator
     *
     * @param ApiErrorsService      $apiErrorsService
     *
     *
     * @return Response
     *
     * @Route("/api/report/subject/{id}",  methods={"GET"}, name="report_subject_get_report")
     * @ParamConverter("subject", class="App\Entity\Subject", options={"id" = "id"})
     *
     * @IsGranted("ROLE_ANALYST", subject="subject", statusCode=404,  message="No access is Granted")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get list of questions and answers for report",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Question::class, groups={"read"}))
     *     )
     * )
     * @SWG\Tag(name="report")
     *
     * @Areas({"internal"})
     */
    public function reportAction(
        ReportRepository $repository,
        SerializerInterface $serializer,
        Subject $subject,
        Request $request,
        ReportScoreCalculator $calculator,
        ApiErrorsService $apiErrorsService
    ) {
        try {
            $id = $request->get('report', '');
            $report = $id !== '' ? $repository->find($id) : $subject->getCurrentReport();

            /** @var Report $theReport */
            $theReport = $report === null ? $theReport = $subject->getCurrentReport() : $theReport = $report;

            $reportAnswers = ReportFormatter::format($theReport);

            $reportQuestions = array();
            $platforms = Profile::PLATFORMS;

            foreach ($platforms as $platform) { // include blank sections
                if (!key_exists($platform, (array)$reportAnswers['platforms'])) {
                    $reportAnswers['platforms'][$platform] = [];
                }
            }
            $reportQuestions['questions']['platforms'] = $reportAnswers['platforms'];

            if (count($reportAnswers['generalComment']) > 0) {
                $reportQuestions['generalComment'] = $reportAnswers['generalComment'];
            }

            //start calculation
            if ($theReport->getReportScores() && $theReport->getStatus() === 'investigation_completed') { //calculation already done
                $reportScore = $theReport->getReportScores();
                $socialMediaReportScore = $theReport->getSocialMediaScores();
            } else {
                //creates Calculate jsons for scores and social media scores
                $reportScore = $calculator->calculateReportScore($reportQuestions['questions'], $theReport->getSubject());

                $socialMediaReportScore = $calculator->calculateSocialMediaReportScore($reportAnswers, $theReport->getSubject());

                if ($reportScore) {
                    $theReport->setRiskScore($reportScore['risk_score']);
                    $theReport->setReportScores($reportScore);
                    $theReport->setSocialMediaScores($socialMediaReportScore);
                    $repository->save($theReport);
                }
            }

            $reportQuestions['reportScore'] = $reportScore;
            $reportQuestions['social_media_scores'] = $socialMediaReportScore;

            $reportQuestions['details'] = [
                'id' => $theReport->getId(),
                'sequence' => $theReport->getSequence(),
                'status' => $theReport->getStatus(),
                'enabled' => $theReport->isEnabled(),
                'open' => $theReport->isOpen()
            ];

            $reportQuestions['override_scores'] = [
                'override_report_scores' => $theReport->isOverWriteReportScores(),
                'report_scores_updated' => $theReport->getReportScoresUpdated()
            ];

            return new Response(
                $serializer->serialize(
                    $reportQuestions,
                    'json',
                    SerializationContext::create()->setGroups(["report"])
                ),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param ReportRepository          $repository
     * @param SystemConfigRepository    $configRepository
     * @param SerializerInterface       $serializer
     * @param Subject                   $subject
     * @param Request                   $request
     * @param ReportScoreCalculator     $calculator
     * @param PdfService                $pdfService
     * @param PdfProofService           $pdfProofService
     * @param ParameterBagInterface     $params
     * @param DefaultBrandingRepository $defaultBrandingRepository
     * @param RequestStack              $requestStack
     * @param ApiReportsService         $apiReportsService
     * @param EventTrackingService      $eventTrackingService
     *
     * @return Response
     *
     * @Route("/api/report/subject/{id}/pdf-standard", methods={"GET"}, name="report_subject_get_report_pdf_standard")
     * @ParamConverter("subject", class="App\Entity\Subject")
     *
     * @IsGranted("ROLE_ANALYST", subject="subject")
     * @IsGranted("ROLE_USER_STANDARD", subject="subject")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get subject report .pdf file",
     * )
     * @SWG\Tag(name="report")
     *
     * @Areas({"internal","default"})
     */
    public function reportPdfActionStandard(
        ReportRepository $repository,
        SystemConfigRepository $configRepository,
        SerializerInterface $serializer,
        Subject $subject,
        Request $request,
        ReportScoreCalculator $calculator,
        PdfService $pdfService,
        PdfProofService $pdfProofService,
        ParameterBagInterface $params,
        DefaultBrandingRepository $defaultBrandingRepository,
        RequestStack $requestStack,
        ApiReportsService $apiReportsService,
        EventTrackingService $eventTrackingService
    ) {
        $id = $request->get('report', '');
        $report = $id !== '' ? $repository->find($id) : $subject->getCurrentReport();

        $userSource = $request->headers->has('user-type') ? $request->headers->get('user-type') : UserTracking::SOURCE_CUSTOM;

        if ($report === null || ($report->getStatus() !== 'completed' && $report->getStatus() !== 'investigation_completed')) {
            $message = ['Report has not been completed yet'];

            return new JsonResponse([
                'message' => $message
            ], 500);

            die();
        }

        if (empty($report->getPdfFilename()) || $report->getPdfFilename() === null) {
            $reportInfo = $apiReportsService->pdfReport($report, $subject);

            $baseUrl = $this->baseUrl = $requestStack->getCurrentRequest()->getSchemeAndHttpHost();

            /** @var Company $company */
            $company = $subject->getCompany();
            $configs = $configRepository->all();
            $configVals = $this->buildConfigRebuild($configs, $company);

            //Password
            $password = $request->get('p') ? $request->get('p') : '';

            $header = null;
            $company = null;
            $footer = null;

            $coverTemplete = $this->renderView('pdf/standardPdfCover.html.twig', array(
                'base_url' => $baseUrl,
                'cover_page' => $reportInfo['cover_page']['branding'],
                'report_types' => str_replace("_", " ", $reportInfo['reports']['report_type']),
                'report_details' => $reportInfo['reports']['report_details'],
                'branding' => $reportInfo['footer']['branding'],
                'theme_colour' => $reportInfo['cover_page']['branding']['theme_color']

            ));

            $reportTemplete = $this->renderView('pdf/standardPdfReport.html.twig', array(
                'base_url' => $baseUrl,
                'report_types' => $reportInfo['reports']['report_type'],
                'candidate' => $reportInfo['reports']['candidate'],
                'report_details' => $reportInfo['reports']['report_details'],
                'report_summary' => $reportInfo['reports']['report_summary'],
                'findings' => $reportInfo['reports']['report_summary']['findings'],
                'overall_behavior_scores' => $reportInfo['reports']['report_summary']['overall_behavior_scores'],
                'detailed_summary' => $reportInfo['reports']['report_summary']['detailed_summary'],
                'final_section' => $reportInfo['reports']['final_section'],
                'theme_colour' => $reportInfo['cover_page']['branding']['theme_color'],
                'circle_graph_inner_color' => $reportInfo['cover_page']['branding']['theme_color_second'],
                'circle_graph_out_color' => $reportInfo['cover_page']['branding']['theme_color'],
                'main_risk_gauge_circle_background' => $reportInfo['cover_page']['branding']['theme_color_second'],

            ));

            $footerTemplete = $this->renderView('pdf/standardPdfFooter.html.twig', array(
                'base_url' => $baseUrl,
                'branding' => $reportInfo['footer']['branding'],
                'theme_colour' => $reportInfo['cover_page']['branding']['theme_color'],
                'branding_type' => $reportInfo['cover_page']['branding']['branding_type']
            ));

            $result = $pdfService->generatePdf($coverTemplete, $reportTemplete, $subject->getCompany(), $header, $footerTemplete, $password);

            $repository->savePDF($report, $result);

            try {
                $eventTrackingService->track(UserTracking::ACTION_GET_STANDARD_REPORT_PDF, $this->getUser(), $userSource, $subject);
                return new PdfResponse(
                    $result,
                    'file.pdf'
                );
            } catch (\Exception $e) {
                return new JsonResponse([
                    'message' => $e->getMessage()
                ], 500);
            }
        } else {
            try {
                $eventTrackingService->track(UserTracking::ACTION_GET_STANDARD_REPORT_PDF, $this->getUser(), $userSource, $subject);
                $result = $repository->getPDF($subject);

                return new PdfResponse(
                    $result,
                    'file.pdf'
                );
            } catch (\Exception $e) {
                return new JsonResponse([
                    'message' => $e->getMessage()
                ], 500);
            }
        }
    }

    /**
     * @param ReportRepository          $repository
     * @param SystemConfigRepository    $configRepository
     * @param SerializerInterface       $serializer
     * @param Subject                   $subject
     * @param Request                   $request
     * @param ReportScoreCalculator     $calculator
     * @param PdfService                $pdfService
     * @param PdfProofService           $pdfProofService
     * @param ParameterBagInterface     $params
     * @param DefaultBrandingRepository $defaultBrandingRepository
     * @param RequestStack              $requestStack
     * @param ApiReportsService         $apiReportsService
     *
     * @param ApiErrorsService          $apiErrorsService
     * @param EventTrackingService      $eventTrackingService
     *
     * @return Response
     *
     * @Route("/api/report/subject/{id}/pdf-rebuild", methods={"GET"}, name="report_subject_get_report_pdf_rebuild")
     * @ParamConverter("subject", class="App\Entity\Subject")
     *
     * @IsGranted("ROLE_TEAM_LEAD", subject="subject")
     * @IsGranted("ROLE_SUPER_ADMIN", subject="subject")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get list of questions and answers for report rebuild",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Subject::class, groups={"read"}))
     *     )
     * )
     * @SWG\Tag(name="report")
     *
     * @Areas({"internal"})
     */
    public function reportPdfActionRebuild(
        ReportRepository $repository,
        SystemConfigRepository $configRepository,
        SerializerInterface $serializer,
        Subject $subject,
        Request $request,
        ReportScoreCalculator $calculator,
        PdfService $pdfService,
        PdfProofService $pdfProofService,
        ParameterBagInterface $params,
        DefaultBrandingRepository $defaultBrandingRepository,
        RequestStack $requestStack,
        ApiReportsService $apiReportsService,
        ApiErrorsService $apiErrorsService,
        EventTrackingService $eventTrackingService
    ) {
        $id = $request->get('report', '');
        $report = $id !== '' ? $repository->find($id) : $subject->getCurrentReport();

        $userSource = $request->headers->has('user-type') ? $request->headers->get('user-type') : UserTracking::SOURCE_CUSTOM;

        if ($report === null || ($report->getStatus() !== 'completed' && $report->getStatus() !== 'investigation_completed')) {
            $message = ['Report has not been completed yet'];

            return new JsonResponse([
                'message' => $message
            ], 500);

            die();
        }

        $reportInfo = $apiReportsService->pdfReport($report, $subject);

        $baseUrl = $this->baseUrl = $requestStack->getCurrentRequest()->getSchemeAndHttpHost();

        /** @var Company $company */
        $company = $subject->getCompany();
        $configs = $configRepository->all();
        $configVals = $this->buildConfigRebuild($configs, $company);

        //Password
        //$password = $request->get('p') ? $request->get('p') : '';

        $password = '';

        $header = null;
        $company = null;
        $footer = null;

        $coverTemplete = $this->renderView('pdf/standardPdfCover.html.twig', array(
            'base_url' => $baseUrl,
            'cover_page' => $reportInfo['cover_page']['branding'],
            'report_types' => str_replace("_", " ", $reportInfo['reports']['report_type']),
            'report_details' => $reportInfo['reports']['report_details'],
            'branding' => $reportInfo['footer']['branding'],
            'theme_colour' => $reportInfo['cover_page']['branding']['theme_color']

        ));

        $reportTemplete = $this->renderView('pdf/standardPdfReport.html.twig', array(
            'base_url' => $baseUrl,
            'report_types' => $reportInfo['reports']['report_type'],
            'candidate' => $reportInfo['reports']['candidate'],
            'report_details' => $reportInfo['reports']['report_details'],
            'report_summary' => $reportInfo['reports']['report_summary'],
            'findings' => $reportInfo['reports']['report_summary']['findings'],
            'overall_behavior_scores' => $reportInfo['reports']['report_summary']['overall_behavior_scores'],
            'detailed_summary' => $reportInfo['reports']['report_summary']['detailed_summary'],
            'final_section' => $reportInfo['reports']['final_section'],
            'theme_colour' => $reportInfo['cover_page']['branding']['theme_color'],
            'circle_graph_inner_color' => $reportInfo['cover_page']['branding']['theme_color_second'],
            'circle_graph_out_color' => $reportInfo['cover_page']['branding']['theme_color'],
            'main_risk_gauge_circle_background' => $reportInfo['cover_page']['branding']['theme_color_second'],

        ));

        $footerTemplete = $this->renderView('pdf/standardPdfFooter.html.twig', array(
            'base_url' => $baseUrl,
            'branding' => $reportInfo['footer']['branding'],
            'theme_colour' => $reportInfo['cover_page']['branding']['theme_color'],
            'branding_type' => $reportInfo['cover_page']['branding']['branding_type']
        ));

        $result = $pdfService->generatePdf($coverTemplete, $reportTemplete, $subject->getCompany(), $header, $footerTemplete, $password);

        $repository->savePDF($report, $result);

        try {
            $eventTrackingService->track(UserTracking::ACTION_REBUILD_STANDARD_REPORT_PDF, $this->getUser(), $userSource, $subject);
            return new PdfResponse(
                $result,
                'file.pdf'
            );
        } catch (\Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param ReportRepository       $repository
     * @param SystemConfigRepository $configRepository
     * @param SerializerInterface    $serializer
     * @param Subject                $subject
     * @param Request                $request
     * @param ReportScoreCalculator  $calculator
     * @param PdfService             $pdfService
     * @param PdfProofService        $pdfProofService
     * @param ParameterBagInterface  $params
     *
     * @param ApiErrorsService       $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/report/subject/{id}/pdf", methods={"GET"}, name="report_get_report_pdf")
     * @ParamConverter("subject", class="App\Entity\Subject")
     * @Security("is_granted('ROLE_ANALYST') or is_granted('ROLE_USER_STANDARD', subject)")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get list of questions and answers for report",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Report::class, groups={"read"}))
     *     )
     * )
     * @SWG\Tag(name="report")
     *
     * @Areas({"internal"})
     */
    public function reportIdPdfAction(
        ReportRepository $repository,
        SystemConfigRepository $configRepository,
        SerializerInterface $serializer,
        Subject $subject,
        Request $request,
        ReportScoreCalculator $calculator,
        PdfService $pdfService,
        PdfProofService $pdfProofService,
        ParameterBagInterface $params,
        ApiErrorsService $apiErrorsService
    ) {
        try {
            $result = $repository->getPDF($subject);

            return new PdfResponse(
                $result,
                'file.pdf'
            );
        } catch (\Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param ReportRepository    $repository
     * @param SerializerInterface $serializer
     * @param Report              $report
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/report/{id}/toggleGeneralComments", methods={"PATCH"}, name="report_toggle_general_comments")
     * @Security("is_granted('ROLE_ANALYST', report)")
     *
     * @SWG\Response(
     *     response="200",
     *     description="opens a report",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Report::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="report")
     *
     * @Areas({"internal"})
     */
    public function toggleGeneralCommentsAction(
        ReportRepository $repository,
        SerializerInterface $serializer,
        Report $report,
        ApiErrorsService $apiErrorsService
    ) {
        try {
            $repository->toggleGeneralComments($report);

            return new Response(
                $serializer->serialize(
                    $report,
                    'json',
                    SerializationContext::create()->setGroups(["read"])
                ),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param ReportRepository    $repository
     * @param SerializerInterface $serializer
     * @param Report              $report
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/report/{id}/toggleReportScore", methods={"PATCH"}, name="report_toggle_report_score")
     * @Security("is_granted('ROLE_TEAM_LEAD', report)")
     *
     * @SWG\Response(
     *     response="200",
     *     description="opens a report",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Report::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="report")
     *
     * @Areas({"internal"})
     */
    public function toggleReportScoreAction(
        ReportRepository $repository,
        SerializerInterface $serializer,
        Report $report,
        ApiErrorsService $apiErrorsService
    ) {
        try {
            $repository->toggleReportScore($report);

            return new Response(
                $serializer->serialize(
                    $report,
                    'json',
                    SerializationContext::create()->setGroups(["read"])
                ),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param SerializerInterface $serializer
     * @param Subject             $subject
     * @param PdfProofService     $pdfProofService
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/report/{id}/risk-comments", methods={"GET"}, name="report_risk_comment")
     *
     * @ParamConverter("subject", class="App\Entity\Subject")
     *
     * @Security("is_granted('ROLE_ANALYST', subject)")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get list of questions and answers for report",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Question::class, groups={"read"}))
     *     )
     * )
     * @SWG\Tag(name="report")
     *
     * @Areas({"internal"})
     */
    public function riskComments(
        SerializerInterface $serializer,
        Subject $subject,
        PdfProofService $pdfProofService,
        ApiErrorsService $apiErrorsService
    ) {
        $respnose = [
            'media_validated' => $pdfProofService->mediaValidated($subject),
            'subject_info' => $pdfProofService->personalInfo($subject),
            'risk_comment' => $pdfProofService->riskComment($subject->getCurrentReport()->getId())
        ];

        try {
            return new Response(
                $serializer->serialize(
                    $respnose,
                    'json',
                    SerializationContext::create()->setGroups(["read"])
                ),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param ReportRepository       $repository
     * @param SystemConfigRepository $configRepository
     * @param SerializerInterface    $serializer
     * @param Subject                $subject
     * @param Request                $request
     * @param ReportScoreCalculator  $calculator
     * @param PdfService             $pdfService
     * @param PdfProofService        $pdfProofService
     * @param ParameterBagInterface  $params
     *
     * @param ApiErrorsService       $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/report/subject/{id}/web", methods={"GET"}, name="report_subject_get_report_web")
     * @ParamConverter("Report", class="App\Entity\Subject")
     * @Security("is_granted('ROLE_ANALYST', subject)")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get list of questions and answers for report",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Question::class, groups={"read"}))
     *     )
     * )
     * @SWG\Tag(name="report")
     *
     * @Areas({"internal"})
     */
    public function reportWebAction(
        ReportRepository $repository,
        SystemConfigRepository $configRepository,
        SerializerInterface $serializer,
        Subject $subject,
        Request $request,
        ReportScoreCalculator $calculator,
        PdfService $pdfService,
        PdfProofService $pdfProofService,
        ParameterBagInterface $params,
        ApiErrorsService $apiErrorsService
    ) {
        $vals = $this->reportAction($repository, $serializer, $subject, $request, $calculator, $apiErrorsService);
        $report = json_decode($vals->getContent(), true);

        $socialMediaIcon = [
            'facebook' => $params->get('BLOB_URL') . '/icons/facebook-icon-01.png',
            'twitter' => $params->get('BLOB_URL') . '/icons/twitter-icon-01.png',
            'instagram' => $params->get('BLOB_URL') . '/icons/instagram-icon-01.png',
            'pinterest' => $params->get('BLOB_URL') . '/icons/pinterest-icon-01.png',
            'linkedin' => $params->get('BLOB_URL') . '/icons/linkedin-icon-01.png',
            'youtube' => $params->get('BLOB_URL') . '/icons/youtube-icon-01.png',
            'flickr' => $params->get('BLOB_URL') . '/icons/flickr-icon-01.png',
            'web' => $params->get('BLOB_URL') . '/icons/google-plus-icon-01.png',
        ];

        if (isset($report['reportScore']['overall_behavior_scores'])) {
            $overallBehaviorScores = [
                "creativity" => $report['reportScore']['overall_behavior_scores'] && $report['reportScore']['overall_behavior_scores']['creativity'] ? $report['reportScore']['overall_behavior_scores']['creativity'] : 0,
                "network_reach" => $report['reportScore']['overall_behavior_scores'] && $report['reportScore']['overall_behavior_scores']['network_reach'] ? $report['reportScore']['overall_behavior_scores']['network_reach'] : 0,
                "network_engagement" => $report['reportScore']['overall_behavior_scores'] && $report['reportScore']['overall_behavior_scores']['network_engagement'] ? $report['reportScore']['overall_behavior_scores']['network_engagement'] : 0,
                "professional_image" => $report['reportScore']['overall_behavior_scores'] && $report['reportScore']['overall_behavior_scores']['professional_image'] ? $report['reportScore']['overall_behavior_scores']['professional_image'] : 0,
                "communication_skills" => $report['reportScore']['overall_behavior_scores'] && $report['reportScore']['overall_behavior_scores']['communication_skills'] ? $report['reportScore']['overall_behavior_scores']['communication_skills'] : 0,
                "teamwork_collaboration" => $report['reportScore']['overall_behavior_scores'] && $report['reportScore']['overall_behavior_scores']['teamwork_collaboration'] ? $report['reportScore']['overall_behavior_scores']['teamwork_collaboration'] : 0,
                "professional_engagement" => $report['reportScore']['overall_behavior_scores'] && $report['reportScore']['overall_behavior_scores']['professional_engagement'] ? $report['reportScore']['overall_behavior_scores']['professional_engagement'] : 0,
                "business_writing_ability" => $report['reportScore']['overall_behavior_scores'] && $report['reportScore']['overall_behavior_scores']['business_writing_ability'] ? $report['reportScore']['overall_behavior_scores']['business_writing_ability'] : 0
            ];
            $overallBehaviorScoresCheck = true;
        } else {
            $overallBehaviorScoresCheck = false;
            $overallBehaviorScores = [];
        }

        $socialMedia = ['facebook', 'twitter', 'instagram', 'pinterest', 'linkedin', 'youtube', 'flickr', 'web'];

        $company = $subject->getCompany();
        $report = [
            'socialMediaIcon' => $socialMediaIcon,
            'comment' => $pdfProofService->comments($report['details']['id'], $socialMedia),
            'reportDetails' => $pdfProofService->reportDetails($report['details']['id'], $company),
            'report_type' => $subject->getReportType(),
            'report' => $report,
            'subject' => $subject,
            'company' => $company,
            'proof' => $pdfProofService->proofCheck($report),
            'socialMediaIcon' => $socialMediaIcon,
            'overallBehaviorScores' => $overallBehaviorScores,
            'overallBehaviorScoresCheck' => $overallBehaviorScoresCheck,
            'searchTerms' => $pdfProofService->searchInfo($subject, $socialMedia),
            'risk_comment' => $pdfProofService->riskComment($report['details']['id']),
            'platform_scores' => $report['reportScore']['platforms']
        ];

        // Valid Entity
        try {
            return new JsonResponse([
                $report
            ], 200);
        } catch (Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param ReportRepository       $repository
     * @param SystemConfigRepository $configRepository
     * @param SerializerInterface    $serializer
     * @param Request                $request
     * @param ReportScoreCalculator  $calculator
     * @param PdfService             $pdfService
     * @param PdfProofService        $pdfProofService
     * @param ParameterBagInterface  $params
     *
     * @param Subject                $subject
     * @param ApiErrorsService       $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/report/subject/{id}/get-scores", methods={"GET"}, name="report_subject_get_report_scores")
     * @ParamConverter("Report", class="App\Entity\Subject")
     * @Security("is_granted('ROLE_ANALYST') or is_granted('ROLE_USER_STANDARD', subject)")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get list of questions and answers for report",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Question::class, groups={"read"}))
     *     )
     * )
     * @SWG\Tag(name="report")
     *
     * @Areas({"internal"})
     */
    public function getScores(
        ReportRepository $repository,
        SystemConfigRepository $configRepository,
        SerializerInterface $serializer,
        Request $request,
        ReportScoreCalculator $calculator,
        PdfService $pdfService,
        PdfProofService $pdfProofService,
        ParameterBagInterface $params,
        Subject $subject,
        ApiErrorsService $apiErrorsService
    ) {
        /** @var Report $theReport */

        $theReport = $theReport = $subject->getCurrentReport();

        $reportAnswers = ReportFormatter::format($theReport);

        $reportQuestions = array();
        $platforms = Profile::PLATFORMS;

        foreach ($platforms as $platform) { // include blank sections
            if (!key_exists($platform, (array)$reportAnswers['platforms'])) {
                $reportAnswers['platforms'][$platform] = [];
            }
        }
        $reportQuestions['questions']['platforms'] = $reportAnswers['platforms'];

        $reportScore = $calculator->calculateReportScore($reportQuestions['questions'], $theReport->getSubject());

        // Valid Entity
        try {
            return new JsonResponse([
                $reportScore
            ], 200);
        } catch (Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param ReportRepository       $repository
     * @param SystemConfigRepository $configRepository
     * @param SerializerInterface    $serializer
     * @param Request                $request
     * @param ReportScoreCalculator  $calculator
     * @param PdfService             $pdfService
     * @param PdfProofService        $pdfProofService
     * @param ParameterBagInterface  $params
     *
     * @param Subject                $subject
     * @param ApiErrorsService       $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/report/subject/{id}/get-edit-report", methods={"GET"}, name="report_subject_get_report_edit")
     * @ParamConverter("Report", class="App\Entity\Subject")
     * @Security("is_granted('ROLE_ANALYST') or is_granted('ROLE_USER_STANDARD', subject)")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get list of questions and answers for report",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Question::class, groups={"read"}))
     *     )
     * )
     * @SWG\Tag(name="report")
     *
     * @Areas({"internal"})
     */
    public function getEditReport(
        ReportRepository $repository,
        SystemConfigRepository $configRepository,
        SerializerInterface $serializer,
        Request $request,
        ReportScoreCalculator $calculator,
        PdfService $pdfService,
        PdfProofService $pdfProofService,
        ParameterBagInterface $params,
        Subject $subject,
        ApiErrorsService $apiErrorsService
    ) {
        /** @var Report $theReport */

        $theReport = $theReport = $subject->getCurrentReport();

        $reportAnswers = ReportFormatter::format($theReport);

        $reportQuestions = array();
        $platforms = Profile::PLATFORMS;

        foreach ($platforms as $platform) { // include blank sections
            if (!key_exists($platform, (array)$reportAnswers['platforms'])) {
                $reportAnswers['platforms'][$platform] = [];
            }
        }
        $reportQuestions['questions']['platforms'] = $reportAnswers['platforms'];

        $reportScore = $calculator->calculateReportScore($reportQuestions['questions'], $theReport->getSubject());

        $report = ['scores' => $reportScore];

        // Valid Entity
        try {
            return new JsonResponse([
                $report
            ], 200);
        } catch (Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param ReportRepository       $repository
     * @param SystemConfigRepository $configRepository
     * @param SerializerInterface    $serializer
     * @param Request                $request
     * @param ReportScoreCalculator  $calculator
     * @param PdfService             $pdfService
     * @param PdfProofService        $pdfProofService
     * @param ParameterBagInterface  $params
     *
     * @param Subject                $subject
     * @param ApiErrorsService       $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/report/subject/{id}/get-edit-report-scores", methods={"GET"}, name="report_subject_get_report_scores_edit")
     * @ParamConverter("Report", class="App\Entity\Subject")
     * @Security("is_granted('ROLE_ANALYST') or is_granted('ROLE_USER_STANDARD', subject)")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get list of questions and answers for report",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Question::class, groups={"read"}))
     *     )
     * )
     * @SWG\Tag(name="report")
     *
     * @Areas({"internal"})
     */
    public
    function getEditReportScores(
        ReportRepository $repository,
        SystemConfigRepository $configRepository,
        SerializerInterface $serializer,
        Request $request,
        ReportScoreCalculator $calculator,
        PdfService $pdfService,
        PdfProofService $pdfProofService,
        ParameterBagInterface $params,
        Subject $subject,
        ApiErrorsService $apiErrorsService
    ) {
        try {
            $response = $repository->getOverWriteReportScores($subject->getCurrentReport());
            return new Response(
                $serializer->serialize(
                    $response,
                    'json',
                    SerializationContext::create()->setGroups(["read"])
                ),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param ReportRepository       $repository
     * @param SystemConfigRepository $configRepository
     * @param SerializerInterface    $serializer
     * @param Request                $request
     * @param ReportScoreCalculator  $calculator
     * @param PdfService             $pdfService
     * @param PdfProofService        $pdfProofService
     * @param ParameterBagInterface  $params
     * @param Subject                $subject
     * @param Validator              $validator
     *
     * @param ApiErrorsService       $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/report/subject/{id}/update-edit-report-scores ", methods={"PATCH"}, name="report_subject_update_report_scores_edit")
     * @ParamConverter("Report", class="App\Entity\Subject")
     * @Security("is_granted('ROLE_ANALYST') or is_granted('ROLE_USER_STANDARD', subject)")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get list of questions and answers for report",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Question::class, groups={"read"}))
     *     )
     * )
     * @SWG\Tag(name="report")
     *
     * @Areas({"internal"})
     */
    public function updateEditReportScores(
        ReportRepository $repository,
        SystemConfigRepository $configRepository,
        SerializerInterface $serializer,
        Request $request,
        ReportScoreCalculator $calculator,
        PdfService $pdfService,
        PdfProofService $pdfProofService,
        ParameterBagInterface $params,
        Subject $subject,
        Validator $validator,
        ApiErrorsService $apiErrorsService
    ) {
        $data = json_decode($request->getContent(), true);
        $data['id'] = $subject->getCurrentReport()->getId();

        /** @var Report $report */
        $report = $serializer->deserialize(
            json_encode($data),
            Report::class,
            'json',
            DeserializationContext::create()->setGroups(['write'])
        );

        /** @var JsonResponse $response */
        if (($response = $validator->validate($report)) !== false) {
            return $response;
        }

        // Valid Entity
        try {
            $repository->saveOverride($report);

            return new Response(
                $serializer->serialize(
                    $report,
                    'json',
                    SerializationContext::create()->setGroups(["read"])
                ),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param ReportRepository      $repository
     * @param SerializerInterface   $serializer
     * @param Subject               $subject
     * @param Request               $request
     * @param ReportScoreCalculator $calculator
     *
     * @return Response
     *
     * @Route("/api/report/subject/{id}/build-math",  methods={"GET"}, name="report_subject_rebuild_math")
     * @ParamConverter("subject", class="App\Entity\Subject", options={"id" = "id"})
     * @Security("is_granted('ROLE_TEAM_LEAD', subject)")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Gets Maths for reports ahead of time",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Question::class, groups={"read"}))
     *     )
     * )
     * @SWG\Tag(name="report")
     *
     * @Areas({"internal"})
     */
    public function buildMaths(
        ReportRepository $repository,
        SerializerInterface $serializer,
        Subject $subject,
        Request $request,
        ReportScoreCalculator $calculator,
        ApiErrorsService $apiErrorsService,
        MessageSystemRepository $messageSystemRepository
    ) {
        try {
            $start = microtime(true);
            $id = $request->get('report', '');
            error_log('[buildMaths] Incoming report ID: ' . $id);

            $report = $id !== '' ? $repository->find($id) : $subject->getCurrentReport();
            error_log('[buildMaths] Time to fetch report: ' . (microtime(true) - $start) . ' seconds');

            // Retry if report is not yet ready
            if (!$report) {
                error_log('[buildMaths] Report not found, retrying after short delay...');
                usleep(300000); // 300ms
                $report = $id !== '' ? $repository->find($id) : $subject->getCurrentReport();
            }

            if (!$report) {
                error_log('[buildMaths] Report still not available.');
                return new JsonResponse(['error' => 'Report not ready. Try again shortly.'], 503);
            }

            $theReport = $report;

            $start = microtime(true);
            $reportAnswers = ReportFormatter::format($theReport);
            error_log('[buildMaths] Time to format report: ' . (microtime(true) - $start) . ' seconds');

            $reportQuestions = [];
            $platforms = Profile::PLATFORMS;

            // Ensure each platform is present
            foreach ($platforms as $platform) {
                if (!array_key_exists($platform, (array) $reportAnswers['platforms'])) {
                    $reportAnswers['platforms'][$platform] = [];
                }
            }

            // Handle general comments if present
            if (!empty($reportAnswers['generalComment'])) {
                $reportQuestions['generalComment'] = $reportAnswers['generalComment'];
            }

            $reportQuestions['questions']['platforms'] = $reportAnswers['platforms'];

            $start = microtime(true);
            $reportScore = $calculator->calculateReportScore($reportQuestions['questions'], $theReport->getSubject());
            error_log('[buildMaths] Time to calculate report score: ' . (microtime(true) - $start) . ' seconds');

            $start = microtime(true);
            $socialMediaReportScore = $calculator->calculateSocialMediaReportScore($reportAnswers, $theReport->getSubject());
            error_log('[buildMaths] Time to calculate social media score: ' . (microtime(true) - $start) . ' seconds');

            // Save scores if available
            if ($reportScore) {
                $theReport->setRiskScore($reportScore['risk_score']);
                $theReport->setReportScores($reportScore);
                $theReport->setSocialMediaScores($socialMediaReportScore);

                $start = microtime(true);
                $repository->save($theReport);
                error_log('[buildMaths] Time to save report: ' . (microtime(true) - $start) . ' seconds');
            }

            gc_collect_cycles();

            return new JsonResponse([
                'message' => 'Scores updated'
            ], 200);
        } catch (\Throwable $e) {
            error_log('[buildMaths] Exception: ' . $e->getMessage());
            return $apiErrorsService->errorFiveHundred($e);
        }
    }
//    public function buildMaths(
//        ReportRepository $repository,
//        SerializerInterface $serializer,
//        Subject $subject,
//        Request $request,
//        ReportScoreCalculator $calculator,
//        ApiErrorsService $apiErrorsService,
//        MessageSystemRepository $messageSystemRepository
//    ) {
//        try {
//            $start = microtime(true);
//            $id = $request->get('report', '');
//            $report = $id !== '' ? $repository->find($id) : $subject->getCurrentReport();
//            error_log('Time to fetch report: ' . (microtime(true) - $start) . ' seconds');
//
//            $start = microtime(true);
//            $theReport = $report === null ? $subject->getCurrentReport() : $report;
//            error_log('Time to determine report: ' . (microtime(true) - $start) . ' seconds');
//
//            $start = microtime(true);
//            $reportAnswers = ReportFormatter::format($theReport);
//            error_log('Time to format report: ' . (microtime(true) - $start) . ' seconds');
//
//            // // Ensure platforms key is set
//            // if (!isset($reportAnswers['platforms'])) {
//            //     $reportAnswers['platforms'] = [];
//            // }
//
//            // error_log(print_r($reportAnswers, true));
//
//            $reportQuestions = array();
//            $platforms = Profile::PLATFORMS;
//
//            foreach ($platforms as $platform) { // include blank sections
//                if (!array_key_exists($platform, (array)$reportAnswers['platforms'])) {
//                    $reportAnswers['platforms'][$platform] = [];
//                }
//            }
//
//            if (count($reportAnswers['generalComment']) > 0) {
//                $reportQuestions['generalComment'] = $reportAnswers['generalComment'];
//            }
//            $reportQuestions['questions']['platforms'] = $reportAnswers['platforms'];
//
//            $start = microtime(true);
//            $reportScore = $calculator->calculateReportScore($reportQuestions['questions'], $theReport->getSubject());
//            // $reportScore = $calculator->calculateReportScore($reportQuestions['questions'], $theReport->getSubject());
//            error_log('Time to calculate report score: ' . (microtime(true) - $start) . ' seconds');
//
//            $start = microtime(true);
//            $socialMediaReportScore = $calculator->calculateSocialMediaReportScore($reportAnswers, $theReport->getSubject());
//            error_log('Time to calculate social media report score: ' . (microtime(true) - $start) . ' seconds');
//
//            if ($reportScore) {
//                $theReport->setRiskScore($reportScore['risk_score']);
//                $theReport->setReportScores($reportScore);
//                $theReport->setSocialMediaScores($socialMediaReportScore);
//
//                $start = microtime(true);
//                // $repository->saveWithExponentialBackoff($theReport);
//                $repository->save($theReport);
//                error_log('Time to save report with exponential backoff: ' . (microtime(true) - $start) . ' seconds');
//
//                // $start = microtime(true);
//                // $messageSystemRepository->messageStatusFilterSave($theReport);
//                // error_log('Time to save message status: ' . (microtime(true) - $start) . ' seconds');
//            }
//
//            gc_collect_cycles();
//
//            return new JsonResponse([
//                'message' => "scores Updated"
//            ], 200);
//        } catch (Exception $e) {
//            return $apiErrorsService->errorFiveHundred($e);
//        }
//    }



    /**
     *
     * @param ReportRepository      $repository
     * @param SerializerInterface   $serializer
     * @param Subject               $subject
     * @param Request               $request
     * @param ReportScoreCalculator $calculator
     *
     * @return Response
     *
     * @Route("/api/report/subject/{id}/change-math",  methods={"POST"}, name="report_subject_change_math")
     * @ParamConverter("subject", class="App\Entity\Subject", options={"id" = "id"})
     * @Security("is_granted('ROLE_TEAM_LEAD', subject)")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Gets Maths for reports ahead of time",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Question::class, groups={"read"}))
     *     )
     * )
     * @SWG\Tag(name="report")
     *
     * @Areas({"internal"})
     */
    public function changeMaths(
        ReportRepository $repository,
        SerializerInterface $serializer,
        Subject $subject,
        Request $request,
        ReportScoreCalculator $calculator,
        ApiErrorsService $apiErrorsService
    ) {
        try {
            $data = json_decode($request->getContent(), true);

            $id = $request->get('report', '');
            $report = $id !== '' ? $repository->find($id) : $subject->getCurrentReport();

            /** @var Report $theReport */
            $theReport = $report === null ? $theReport = $subject->getCurrentReport() : $theReport = $report;

            $reportScore = $calculator->overrideScore(
                $data['report_scores_updated'],
                $theReport->getSubject(),
                $data['report_scores_updated']['overall_behavior_scores']
            );

            $reportAnswers = ReportFormatter::format($theReport);

            $reportQuestions = array();
            $platforms = Profile::PLATFORMS;

            foreach ($platforms as $platform) { // include blank sections

                if (!array_key_exists($platform, (array)$reportAnswers['platforms'])) {
                    $reportAnswers['platforms'][$platform] = [];
                }
            }

            $reportQuestions['questions']['platforms'] = $reportAnswers['platforms'];

            if (count($reportAnswers['generalComment']) > 0) {
                $reportQuestions['generalComment'] = $reportAnswers['generalComment'];
            }
            $reportQuestions['questions']['platforms'] = $reportAnswers['platforms'];

            $socialMediaReportScore = $calculator->calculateSocialMediaReportScore($reportAnswers, $theReport->getSubject());

            gc_collect_cycles();

            return new JsonResponse(
                $reportScore,
                200
            );
        } catch (Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }
}
