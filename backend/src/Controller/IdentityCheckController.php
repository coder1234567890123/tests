<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\IdentityConfirm;
use App\Entity\Subject;
use App\Repository\IdentityCheckRepository;
use App\Service\ApiErrorsService;
use App\Service\Validator;
use App\Service\ApiReturnService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Areas;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Qualification;
use App\Repository\QualificationRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class IdentityConfirmController
 *
 * @package App\Controller
 */
class IdentityCheckController
{

    /**
     * @param ApiReturnService $apiReturnService
     */
    public function __construct(ApiReturnService $apiReturnService)
    {
        $this->apiReturnService = $apiReturnService;
    }

    /**
     * @param IdentityCheckRepository $repository
     * @param SerializerInterface     $serializer
     *
     * @return Response
     *
     * @Route("/api/identity-confirmation/{id}", methods={"GET"}, name="identity-confirmation_get")
     * @ParamConverter("subject", class="App\Entity\Subject")
     * @IsGranted("ROLE_ANALYST")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get a list of all the identity confirmation",
     * )
     * @SWG\Tag(name="identity-confirmation")
     *
     *  @Areas({"internal"})
     */
    public function getAction(
        IdentityCheckRepository $repository, SerializerInterface $serializer, Subject $subject)
    {
        return new Response(
            $serializer->serialize($this->apiReturnService->getIdentityConfirm($subject),
                'json'),
            200,
            ['Content-type' => 'application/json']
        );
    }

    /**
     * @param IdentityCheckRepository $repository
     * @param SerializerInterface     $serializer
     * @param Validator               $validator
     * @param Request                 $request
     * @param Subject                 $subject
     * @param ApiReturnService        $apiReturnService
     *
     * @param ApiErrorsService        $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/identity-confirmation/{id}", methods={"POST"}, name="identity-confirmation_post")
     * @ParamConverter("subject", class="App\Entity\Subject")
     * @IsGranted("ROLE_ANALYST")
     *
     *
     * @SWG\Response(
     *     response="200",
     *     description="Create a identity confirmation object.",
     * )
     *
     ** @SWG\Parameter(name="body", in="body",
     *     @SWG\Schema(ref=@Model(type=IdentityConfirm::class, groups={"write"}))
     * )
     *
     * @SWG\Tag(name="identity-confirmation")
     *
     * @Areas({"internal"})
     */
    public function postAction(
        IdentityCheckRepository $repository,
        SerializerInterface $serializer,
        Validator $validator,
        Request $request,
        Subject $subject,
        ApiReturnService $apiReturnService,
        ApiErrorsService $apiErrorsService
    )
    {
        $data = json_decode($request->getContent(), true);
        unset($data['id']);

        /** @var IdentityConfirm $IdentityConfirm */
        $identityConfirm = $serializer->deserialize(
            json_encode($data),
            IdentityConfirm::class,
            'json',
            DeserializationContext::create()->setGroups(['write'])
        );

        $identityConfirm->setSubject($subject);

        /** @var JsonResponse $response */
        if (($response = $validator->validate($identityConfirm)) !== false) {
            return $response;
        }

        // Valid Entity
        try {
            $repository->checkId($identityConfirm, $subject);

            return new Response(
                $serializer->serialize(
                    $this->apiReturnService->getIdentityConfirm($subject),
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
     * @param IdentityCheckRepository $repository
     * @param SerializerInterface     $serializer
     * @param Validator               $validator
     * @param Subject                 $subject
     * @param Request                 $request
     *
     * @return Response
     *
     * @Route("/api/identity-confirmation/platform/{id}", methods={"POST"}, name="identity-confirmation-platfrom_get")
     * @ParamConverter("subject", class="App\Entity\Subject")
     * @IsGranted("ROLE_ANALYST")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get a list of all the identity confirmation",
     * )
     * @SWG\Tag(name="identity-confirmation")
     *
     * @Areas({"internal"})
     */
    public function getByPlatform(
        IdentityCheckRepository $repository,
        SerializerInterface $serializer,
        Subject $subject,
        Validator $validator,
        Request $request
    )
    {
        $data = json_decode($request->getContent(), true);
        unset($data['id']);

        /** @var IdentityConfirm $IdentityConfirm */
        $identityConfirm = $serializer->deserialize(
            json_encode($data),
            IdentityConfirm::class,
            'json',
            DeserializationContext::create()->setGroups(['write'])
        );

        $identityConfirm->setSubject($subject);

        /** @var JsonResponse $response */
        if (($response = $validator->validate($identityConfirm)) !== false) {
            return $response;
        }

        return new Response(
            $serializer->serialize($repository->getPlatform($identityConfirm, $subject),
                'json'),
            200,
            ['Content-type' => 'application/json']
        );
    }
}