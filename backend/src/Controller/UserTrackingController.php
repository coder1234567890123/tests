<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\UserTracking;
use App\Entity\Subject;
use App\Helper\QueuedHelper;
use App\Repository\UserTrackingRepository;
use App\Service\ApiErrorsService;
use App\Service\SpreadSheetService;
use Doctrine\ORM\Query;
use Exception;
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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class UserTrackingController
 * @package App\Controller
 *
 */
class UserTrackingController extends AbstractController
{
    /**
     * @param UserTrackingRepository $repository
     * @param SerializerInterface    $serializer
     * @param Request                $request
     *
     * @return Response
     *
     * @Route("/api/usertracking", methods={"GET"}, name="usertracking_get")
     * @Security("is_granted('ROLE_ADMIN_USER')")
     * @SWG\Response(
     *     response="200",
     *     description="Get a paginated list of companies.",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=UserTracking::class, groups={"read"}))
     * )
     *)
     * @SWG\Tag(name="usertracking")
     *
     * @Areas({"internal"})
     */
    public function getAction(
        UserTrackingRepository $repository,
        SerializerInterface $serializer,
        Request $request
    )
    {
        // Get Parameters
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $descending = $request->get('descending', false);
        $descending = $descending == 'true' ? true : false;
        $sort = $request->get('sort', 'createdAt');
        $search = $request->get('search', '');

        // Configure Pagination
        $offset = ($page - 1) * $limit;
        $usertracking = $repository->paginated($offset, $limit, $sort, $descending, $search, $this->getUser());

        $count = $repository->count();
        $pages = (int)ceil($count / $limit);

        $paginatedCollection = new PaginatedRepresentation(
            new CollectionRepresentation(
                $usertracking,
                'usertracking',
                'usertracking'
            ),
            'usertracking_get',
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
                SerializationContext::create()->setGroups(['Default', 'user_tracker', 'minimalInfo'])
            ),
            200,
            ['Content-type' => 'application/json']
        );
    }

    /**
     * @param UserTrackingRepository $repository
     * @param Request                $request
     * @param SpreadSheetService     $spreadSheet
     *
     * @param ApiErrorsService       $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/usertracking/export", methods={"GET"}, name="usertracking_export")
     * @Security("is_granted('ROLE_ADMIN_USER')")
     * @SWG\Response(
     *     response="200",
     *     description="Get a paginated list of User Tracking.",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=UserTracking::class, groups={"read"}))
     * )
     *)
     * @SWG\Tag(name="usertracking")
     *
     * @Areas({"internal"})
     *
     */
    public function exportAction(
        UserTrackingRepository $repository,
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
        $sort = $request->get('sort', 'createdAt');
        $search = $request->get('search', '');

        // Configure Pagination
        $offset = ($page - 1) * $limit;
        try {
            $usertracking = $repository->paginated($offset, $limit, $sort, $descending, $search, $this->getUser());
            $file = $spreadSheet->exportUserReport(null, $usertracking);

            // Return the excel file as an attachment
            $response = $this->file($file['path'], $file['name'], ResponseHeaderBag::DISPOSITION_INLINE);
            $response->headers->set('Access-Control-Expose-Headers', 'Content-Disposition');

            return $response;
        } catch (Exception $e) {
           return $apiErrorsService->errorFiveHundred($e);
        }
    }
}
