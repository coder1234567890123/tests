<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\MessageSystem;
use App\Entity\UserTracking;
use App\Helper\InvestigationHelper;
use App\Repository\MessageSystemRepository;
use App\Repository\AnswerRepository;
use App\Repository\TeamRepository;
use App\Repository\UserRepository;
use App\Service\ApiErrorsService;
use App\Service\EventTrackingService;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Environment;
use function count;
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
use Throwable;
use function ucfirst;

/**
 * Class MessageSystemController
 * @package App\Controller
 */
class MessageSystemController extends AbstractController
{
    /**
     * @param Request                 $request
     * @param MessageSystemRepository $repository
     *
     * @param MessageSystem           $messageSystem
     * @param ApiErrorsService        $apiErrorsService
     *
     * @return JsonResponse|Response
     * @Route("/api/message_system/viewed/{id}", methods={"POST"}, name="message_system_messagesystem")
     * @ParamConverter("messageSystem", class="App\Entity\MessageSystem")
     * @SWG\Parameter(name="body", in="body",
     *     @SWG\Schema(ref=@Model(type=MessageSystem::class, groups={"write"}))
     * )
     *
     *    * @SWG\Response(
     *     response="200",
     *     description="View Message",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=MessageSystem::class, groups={"write"})
     *     )
     * )
     *
     * @SWG\Tag(name="message_system")
     * @Areas({"internal"})
     */
    public function messageViewedAction(
        Request $request,
        MessageSystemRepository $repository,
        messageSystem $messageSystem,
        ApiErrorsService $apiErrorsService
    )
    {
        $userSource = $request->headers->has('user-type') ? $request->headers->get('user-type') : UserTracking::SOURCE_CUSTOM;

        try {
            $message = $repository->messageViewed($messageSystem->getId());

            return new JsonResponse([
                'message' => $message
            ], 200);
        } catch (\Exception $e) {
           return $apiErrorsService->errorFiveHundred($e);
        }
    }
}