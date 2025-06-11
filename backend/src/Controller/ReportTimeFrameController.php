<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Report;
use App\Entity\ReportTimeFrame;
use App\Entity\Subject;
use App\Entity\SystemConfig;
use App\Entity\UserTracking;
use App\Helper\ReportFormatter;
use App\Repository\CommentRepository;
use App\Repository\ReportRepository;
use App\Repository\ReportTimeFrameRepository;
use App\Repository\SubjectRepository;
use App\Repository\SystemConfigRepository;
use App\Service\ApiErrorsService;
use App\Service\EventTrackingService;
use App\Service\PdfService;
use App\Service\ReportScoreCalculator;
use App\Service\WorkflowService;
use JMS\Serializer\SerializationContext;
use App\Service\Validator;
use Hateoas\Representation\CollectionRepresentation;
use Hateoas\Representation\PaginatedRepresentation;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Swagger\Annotations as SWG;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Workflow;

/**
 * Class ReportController
 *
 * @package App\Controller
 */
class ReportTimeFrameController extends AbstractController
{
    /**
     * @param ReportTimeFrameRepository $repository
     * @param SerializerInterface       $serializer
     *
     * @return Response
     *
     * @Route("/api/report-time-frame", methods={"GET"}, name="reporttimeframe_get")
     * @IsGranted("ROLE_SUPER_ADMIN")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Gets list of Report Time Frame",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=ReportTimeFrame::class, groups={"read"}))
     *     )
     * )
     * @SWG\Tag(name="report_time_frame")
     */
    public function getAction(ReportTimeFrameRepository $repository, SerializerInterface $serializer)
    {
        $reportTimeFrame = $repository->all();

        return new Response(
            $serializer->serialize($reportTimeFrame, 'json'),
            200,
            ['Content-type' => 'application/json']
        );
    }

    /**
     * @param ReportTimeFrameRepository $repository
     * @param SerializerInterface       $serializer
     * @param Validator                 $validator
     * @param Request                   $request
     *
     * @param ReportTimeFrame           $reporttimeframe
     * @param ApiErrorsService          $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/report-time-frame/{id}", methods={"PATCH"}, name="reporttimeframe_update")
     * @ParamConverter("reporttimeframe", class="App\Entity\ReportTimeFrame")
     * @IsGranted("ROLE_SUPER_ADMIN")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Update  Time Frame entity.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=ReportTimeFrame::class, groups={"write"})
     *     )
     * )
     *
     * @SWG\Tag(name="report_time_frame")
     */
    public function updateAction(
        ReportTimeFrameRepository $repository,
        SerializerInterface $serializer,
        Validator $validator,
        Request $request,
        ReportTimeFrame $reporttimeframe,
        ApiErrorsService $apiErrorsService
    )
    {
        $data = json_decode($request->getContent(), true);
        $data['id'] = $reporttimeframe->getId();

        $timeFramesCheck = [$data['days'], $data['hours']];

        if (!in_array("0", $timeFramesCheck)) {
            return new JsonResponse([
                'message' => "Either hours or days"
            ], 500);
        } elseif (count(array_keys($timeFramesCheck, "0")) > 1) {
            return new JsonResponse([
                'message' => "Needs hours or days"
            ], 500);
        }

        /** @var Report $report */
        $reporttimeframe = $serializer->deserialize(
            json_encode($data),
            ReportTimeFrame::class,
            'json',
            DeserializationContext::create()->setGroups(['write'])
        );

        /** @var JsonResponse $response */
        if (($response = $validator->validate($reporttimeframe)) !== false) {
            return $response;
        }

        // Valid Entity
        try {
            $response = $repository->save($reporttimeframe,$data['days']);

            return new Response(
                $serializer->serialize(
                    $response,
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
}
