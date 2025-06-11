<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\GlobalWeights;
use App\Entity\SystemConfig;
use App\Entity\UserTracking;
use App\Repository\SystemConfigRsystemconfigepository;
use App\Repository\SystemConfigRepository;
use App\Repository\GlobalWeightsRepository;
use App\Service\ApiErrorsService;
use App\Service\EventService;
use App\Service\EventTrackingService;
use Doctrine\ORM\EntityManagerInterface;
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
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Workflow\Exception\TransitionException;
use Symfony\Component\Workflow\Registry;

/**
 * Class GlobalWeightsController
 *
 * @package App\Controller
 */
class GlobalWeightsController extends AbstractController
{
    /**
     * @param GlobalWeightsRepository $repository
     * @param SerializerInterface     $serializer
     *
     * @return Response
     *
     * @Route("/api/global-weights", methods={"GET"}, name="global_weights_get")
     * @IsGranted( "ROLE_ANALYST")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get a paginated list of globalweights",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=GlobalWeights::class, groups={"read"}))
     *     )
     * )
     * @SWG\Tag(name="globalweights")
     *
     * @Areas({"internal"})
     *
     */
    public function getAction(
        GlobalWeightsRepository $repository,
        SerializerInterface $serializer
    )
    {
        $globalWeights = $repository->all();

        return new Response(
            $serializer->serialize($globalWeights, 'json'),
            200,
            ['Content-type' => 'application/json']
        );
    }

    /**
     * @param GlobalWeightsRepository $repository
     * @param SerializerInterface     $serializer
     * @param Validator               $validator
     * @param Request                 $request
     * @param GlobalWeights           $globalWeights
     * @param EventTrackingService    $eventTrackingService
     *
     * @param ApiErrorsService        $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/global-weights/{id}", methods={"PATCH"}, name="global_weights_update")
     * @ParamConverter("globalweights", class="App\Entity\GlobalWeights")
     * @IsGranted("ROLE_SUPER_ADMIN")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Update the globalweights entity.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=GlobalWeights::class, groups={"write"})
     *     )
     * )
     * @SWG\Tag(name="globalweights")
     *
     * @Areas({"internal"})
     */
    public function updateAction(
        GlobalWeightsRepository $repository,
        SerializerInterface $serializer,
        Validator $validator,
        Request $request,
        GlobalWeights $globalWeights,
        EventTrackingService $eventTrackingService,
        ApiErrorsService $apiErrorsService
    )
    {
        $data = json_decode($request->getContent(), true);
        $data['id'] = $globalWeights->getId();
        $userSource = $request->headers->has('user-type') ? $request->headers->get('user-type'): UserTracking::SOURCE_CUSTOM;

        /** @var SystemConfig $systemConfig */
        $globalWeights = $serializer->deserialize(
            json_encode($data),
            GlobalWeights::class,
            'json',
            DeserializationContext::create()->setGroups(['write'])
        );

        /** @var JsonResponse $response */

        if (($response = $validator->validate($globalWeights)) !== false) {
            return $response;
        }

        try {
            $repository->save($globalWeights);

            $eventTrackingService->track(UserTracking::ACTION_UPDATED_GLOBAL_WEIGHTS, $this->getUser(), $userSource);

            return new Response(
                $serializer->serialize(
                    $globalWeights,
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
}
