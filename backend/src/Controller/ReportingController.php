<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Company;
use App\Entity\Report;
use App\Entity\User;
use App\Entity\Subject;
use App\Repository\ReportingRepository;
use App\Repository\ReportRepository;
use App\Repository\TeamRepository;
use App\Repository\UserRepository;
use App\Service\ApiErrorsService;
use App\Service\DashboardService;
use App\Service\SpreadSheetService;
use App\Service\PdfService;
use App\Repository\UserTrackingRepository;
use Doctrine\ORM\Query;
use Exception;
use JMS\Serializer\SerializationContext;
use App\Service\Validator;
use Hateoas\Representation\CollectionRepresentation;
use Hateoas\Representation\PaginatedRepresentation;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Areas;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class ReportingController Controller
 *
 * @package App\Controller
 */
class ReportingController extends AbstractController
{
    /**
     * @param SerializerInterface $serializer
     * @param DashboardService    $dashboard
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/reporting/dashboard", methods={"GET"}, name="reporting_get_dashboard")
     * @Security("is_granted('ROLE_USER_STANDARD') or is_granted('ROLE_ANALYST')")
     *
     * @SWG\Response(
     *     response="200",
     *     description="User Specific Dashboard"
     * )
     *
     *
     * @SWG\Tag(name="reporting")
     *
     * @Areas({"internal","default"})
     */
    public function dashboardAction(
        SerializerInterface $serializer,
        DashboardService $dashboard,
        ApiErrorsService $apiErrorsService
    )
    {

        //* @Security("is_granted('ROLE_ANALYST') or is_granted('ROLE_USER_STANDARD')")
        try {
            /** @var User $user */
            $user = $this->getUser();
            $result = $dashboard->getRoleDashBoard($user);

            return new Response(
                $serializer->serialize(
                    $result,
                    'json',
                    SerializationContext::create()->setGroups(["queue"])
                ),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (Exception $e) {
           return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param ReportRepository   $repository
     * @param Request            $request
     * @param SpreadSheetService $spreadSheet
     *
     * @param ApiErrorsService   $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/reporting/export", methods={"GET"}, name="reporting_export")
     * @Security("is_granted('ROLE_ANALYST') or is_granted('ROLE_USER_STANDARD', subject)")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get a paginated list of report queues.",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Report::class, groups={"read"}))
     * )
     *)
     * @SWG\Tag(name="report")
     *
     * @Areas({"internal"})
     */
    public function exportAction(
        ReportRepository $repository,
        Request $request,
        SpreadSheetService $spreadSheet,
        ApiErrorsService $apiErrorsService
    )
    {
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
        try {
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
                $status,
                true);

            $file = $spreadSheet->exportFilterReportQueues($reportQueues, $this->getUser()->getRoles()[0]);
            $count = $repository->count();

            // Return the excel file as an attachment
            $response = $this->file($file['path'], $file['name'], ResponseHeaderBag::DISPOSITION_INLINE);
            $response->headers->set('Access-Control-Expose-Headers', 'Content-Disposition');

            return $response;
        } catch (Exception $e) {
           return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param SerializerInterface $serializer
     * @param User                $user_
     *
     * @param ReportingRepository $repository
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/reporting/{id}/user-monthly", methods={"GET"}, name="reporting_usertracking_monthly")
     * @ParamConverter("user_", class="App\Entity\User")
     * @Security("is_granted('ROLE_ANALYST', user_) or is_granted('ROLE_USER_STANDARD', user_)")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns a specific User tracking.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=User::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="reporting")
     *
     * @Areas({"internal"})
     */
    public function userTrackerMonthly(
        SerializerInterface $serializer,
        User $user_,
        ReportingRepository $repository,
        ApiErrorsService $apiErrorsService
    )
    {
        try {
            $report = $repository->userTrackerMonthly($user_);

            return new Response(
                $serializer->serialize(
                    $report,
                    'json',
                    SerializationContext::create()->setGroups(["user_tracker", "minimalInfo"])
                ),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (Exception $e) {
           return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param SerializerInterface $serializer
     * @param Company             $company
     *
     *
     * @param ReportingRepository $repository
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/reporting/{id}/company-monthly", methods={"GET"}, name="reporting_companytracking_monthly")
     * @ParamConverter("company", class="App\Entity\Company")
     * @Security("is_granted('ROLE_ANALYST', company) or is_granted('ROLE_USER_STANDARD', company)")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns a specific Company tracking.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Company::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="reporting")
     *
     * @Areas({"internal"})
     */
    public function companyTrackerMonthly(
        SerializerInterface $serializer,
        Company $company,
        ReportingRepository $repository,
        ApiErrorsService $apiErrorsService
    )
    {
        try {
            $report = $repository->companyTrackerMonthly($company);

            return new Response(
                $serializer->serialize(
                    $report,
                    'json',
                    SerializationContext::create()->setGroups(["user_tracker", "minimalInfo"])
                ),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (Exception $e) {
           return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param SerializerInterface $serializer
     * @param Company             $company
     * @param ReportingRepository $repository
     * @param Request             $request
     * @param Validator           $validator
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/reporting/{id}/company-daterange", methods={"GET"}, name="reporting_companytracking_daterange")
     * @ParamConverter("company", class="App\Entity\Company")
     * @Security("is_granted('ROLE_TEAM_LEAD', company) or is_granted('ROLE_ADMIN_USER', company)")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns a specific Company tracking.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Company::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="reporting")
     *
     * @Areas({"internal"})
     */
    public function companyTrackerRange(
        SerializerInterface $serializer,
        Company $company,
        ReportingRepository $repository,
        Request $request,
        Validator $validator,
        ApiErrorsService $apiErrorsService
    )
    {
        $data = json_decode($request->getContent(), true);
        $data['id'] = $company->getId();

        try {
            $report = $repository->companyTrackerDateRange($company, $data);

            return new Response(
                $serializer->serialize(
                    $report,
                    'json',
                    SerializationContext::create()->setGroups(["user_tracker", "minimalInfo"])
                ),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (Exception $e) {
           return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param SerializerInterface $serializer
     * @param User                $user_
     * @param ReportingRepository $repository
     * @param Request             $request
     * @param Validator           $validator
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/reporting/{id}/user-daterange", methods={"GET"}, name="reporting_usertracking_daterange")
     * @ParamConverter("user_", class="App\Entity\User")
     * @Security("is_granted('ROLE_TEAM_LEAD', user_) or is_granted('ROLE_ADMIN_USER', user_)")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns a specific User tracking.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Company::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="reporting")
     *
     * @Areas({"internal"})
     */
    public function userTrackerRange(
        SerializerInterface $serializer,
        User $user_,
        ReportingRepository $repository,
        Request $request,
        Validator $validator,
        ApiErrorsService $apiErrorsService
    )
    {
        $data = json_decode($request->getContent(), true);
        $data['id'] = $user_->getId();

        try {
            $report = $repository->userTrackerDateRange($user_, $data);

            return new Response(
                $serializer->serialize(
                    $report,
                    'json',
                    SerializationContext::create()->setGroups(["user_tracker", "minimalInfo"])
                ),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (Exception $e) {
           return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param SpreadSheetService  $spreadSheet
     * @param User                $user
     * @param ReportingRepository $repository
     * @param Request             $request
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/reporting/{id}/exportUserDateRange", methods={"GET"}, name="reporting_export_usertracking_daterange")
     * @ParamConverter("user", class="App\Entity\User")
     * @Security("is_granted('ROLE_TEAM_LEAD') or is_granted('ROLE_MANAGER_USER')")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns a specific User tracking.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Company::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="reporting")
     *
     * @Areas({"internal"})
     */
    public function exportUserTrackerRange(
        SpreadSheetService $spreadSheet,
        User $user,
        ReportingRepository $repository,
        Request $request,
        ApiErrorsService $apiErrorsService
    )
    {
        $data = json_decode($request->getContent(), true);
        $data['id'] = $user->getId();

        try {
            $reports = $repository->userTrackerDateRange($user, $data);

            $file = $spreadSheet->exportUserReport($user, $reports['results']);
            // Return the excel file as an attachment
            return $this->file($file['path'], $file['name'], ResponseHeaderBag::DISPOSITION_INLINE);
        } catch (Exception $e) {
           return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param SpreadSheetService  $spreadSheet
     * @param ReportingRepository $repository
     * @param User                $user
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/reporting/{id}/exportUserMonthly", methods={"GET"}, name="reporting_export_monthly")
     * @ParamConverter("user", class="App\Entity\User")
     * @Security("is_granted('ROLE_TEAM_LEAD') or is_granted('ROLE_USER_MANAGER', subject)")
     * @SWG\Response(
     *     response="200",
     *     description="testing.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Company::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="reporting")
     *
     * @Areas({"internal"})
     */
    public function exportUserMonthly(
        SpreadSheetService $spreadSheet,
        ReportingRepository $repository,
        User $user,
        ApiErrorsService $apiErrorsService
    )
    {
        try {
            $reports = $repository->userTrackerMonthly($user);
            $file = $spreadSheet->exportUserReport($user, $reports);
            // Return the excel file as an attachment
            return $this->file($file['path'], $file['name'], ResponseHeaderBag::DISPOSITION_INLINE);
        } catch (Exception $e) {
           return $apiErrorsService->errorFiveHundred($e);
        }
    }
}
