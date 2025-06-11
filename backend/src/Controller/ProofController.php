<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Proof;
use App\Repository\ProofRepository;
use App\Service\ApiErrorsService;
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

/**
 * Class ProofController
 *
 * @package App\Controller
 */
class ProofController extends AbstractController
{
    /**
     * @param ProofRepository     $repository
     * @param SerializerInterface $serializer
     * @param Request             $request
     *
     * @return Response
     *
     * @Route("/api/proof", methods={"GET"}, name="proof_get")
     * @IsGranted("ROLE_SUPER_ADMIN")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get a paginated list of proofs",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Proof::class, groups={"read"}))
     *     )
     * )
     * @SWG\Tag(name="proof")
     *
     * @Areas({"internal"})
     */
    public function getAction(
        ProofRepository $repository,
        SerializerInterface $serializer,
        Request $request
    )
    {
        // Get Parameters
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $descending = $request->get('descending', false);
        $descending = $descending == 'true' ? true : false;
        $sort = $request->get('sort', 'proof');
        $search = $request->get('search', '');

        // Configure Pagination
        $count = $repository->count();
        $offset = ($page - 1) * $limit;
        $proofs = $repository->paginated($offset, $limit, $sort, $descending, $search);
        $pages = (int)ceil($count / $limit);

        $paginatedCollection = new PaginatedRepresentation(
            new CollectionRepresentation(
                $proofs,
                'proofs',
                'proofs'
            ),
            'proof_get',
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
                SerializationContext::create()->setGroups(['Default', 'proofs' => ['read']])
            ),
            200,
            ['Content-type' => 'application/json']
        );
    }

    /**
     * @param ProofRepository     $repository
     *
     * @param SerializerInterface $serializer
     * @param Validator           $validator
     * @param Request             $request
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     * @Route("/api/proof", methods={"POST"}, name="proof_post")
     * @IsGranted("ROLE_ANALYST")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Posts to proof.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Proof::class, groups={"write"})
     *     )
     * )
     * @SWG\Tag(name="proof")
     *
     * @Areas({"internal"})
     */
    public function postAction(
        ProofRepository $repository,
        SerializerInterface $serializer,
        Validator $validator,
        Request $request,
        ApiErrorsService $apiErrorsService
    )
    {
        /** @var Proof $proof */
        $proof = $serializer->deserialize(
            $request->getContent(),
            Proof::class,
            'json',
            DeserializationContext::create()->setGroups(['write'])
        );

        /** @var JsonResponse $response */

        if (($response = $validator->validate($proof)) !== false) {
            return $response;
        }

        // Valid Entity
        try {
            $repository->save($proof);

            return new Response(
                $serializer->serialize(
                    $proof,
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
     * @param ProofRepository     $repository
     * @param SerializerInterface $serializer
     * @param Validator           $validator
     * @param Request             $request
     * @param Proof               $proof
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/proof/{id}", methods={"PATCH"}, name="proof_update")
     * @ParamConverter("proof", class="App\Entity\Proof")
     * @IsGranted("ROLE_ANALYST")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Update the proof entity.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Proof::class, groups={"write"})
     *     )
     * )
     * @SWG\Tag(name="proof")
     *
     * @Areas({"internal"})
     */
    public function updateAction(
        ProofRepository $repository,
        SerializerInterface $serializer,
        Validator $validator,
        Request $request,
        Proof $proof,
        ApiErrorsService $apiErrorsService
    )
    {
        $data = json_decode($request->getContent(), true);
        $data['id'] = $proof->getId();

        /** @var Proof $proof */
        $proof = $serializer->deserialize(
            json_encode($data),
            Proof::class,
            'json',
            DeserializationContext::create()->setGroups(['write'])
        );

        /** @var JsonResponse $response */

        if (($response = $validator->validate($proof)) !== false) {
            return $response;
        }

        // Valid Entity
        try {
            $repository->save($proof);

            return new Response(
                $serializer->serialize(
                    $proof,
                    'json',
                    SerializationContext::create()->setGroups(["investigate", "proof"])
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
     * @param Proof               $proof
     * @param ProofRepository     $repository
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/proof/{id}", methods={"GET"}, name="proof_get_id")
     * @ParamConverter("proof", class="App\Entity\Proof")
     * @IsGranted("ROLE_ANALYST")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns a specific proof.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Proof::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="proof")
     *
     * @Areas({"internal"})
     */
    public function getIDAction(
        SerializerInterface $serializer,
        Proof $proof,
        ProofRepository $repository,
        ApiErrorsService $apiErrorsService
    )
    {
        try {
            $repository->find($proof->getId());

            return new Response(
                $serializer->serialize(
                    $proof,
                    'json',
                    SerializationContext::create()->setGroups(["proof"])
                ),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (Exception $e) {
           return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param ProofRepository     $repository
     * @param SerializerInterface $serializer
     * @param Proof               $proof
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/proof/{id}", methods={"delete"}, name="proof_delete")
     * @ParamConverter("proof", class="App\Entity\Proof")
     * @IsGranted("ROLE_ANALYST")
     *
     * @SWG\Response(
     *     response="200",
     *     description="detele to proof.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Proof::class, groups={"write"})
     *     )
     * )
     * @SWG\Tag(name="proof")
     *
     * @Areas({"internal"})
     */
    public function deleteAction(
        ProofRepository $repository,
        SerializerInterface $serializer,
        Proof $proof,
        ApiErrorsService $apiErrorsService
    )
    {
        try {
            $repository->disable($proof);

            return new Response(
                $serializer->serialize(
                    $proof,
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
     * @param ProofRepository     $repository
     * @param SerializerInterface $serializer
     * @param Proof               $proof
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/proof/{id}/enable", methods={"PUT"}, name="proof_enable")
     * @ParamConverter("proof", class="App\Entity\Proof")
     * @IsGranted("ROLE_ANALYST")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Enables a proof",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Proof::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="proof")
     *
     * @Areas({"internal"})
     */
    public function enableAction(
        ProofRepository $repository,
        SerializerInterface $serializer,
        Proof $proof,
        ApiErrorsService $apiErrorsService
    )
    {
        try {
            $repository->enable($proof);

            return new Response(
                $serializer->serialize(
                    $proof,
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
     * @param SerializerInterface $serializer
     * @param Answer              $answer
     * @param ProofRepository     $repository ,
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/proof/answer/{id}", methods={"GET"}, name="proof_get_answer_id")
     * @ParamConverter("anwser", class="App\Entity\Answer")
     * @IsGranted("ROLE_ANALYST")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns a specific answer proof.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Proof::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="proof")
     *
     * @Areas({"internal"})
     */
    public function getIDAnswer(
        SerializerInterface $serializer,
        Answer $answer,
        ProofRepository $repository,
        ApiErrorsService $apiErrorsService
    )
    {
        try {
            $proof = $repository->answers($answer);

            return new Response(
                $serializer->serialize(
                    $proof,
                    'json',
                    SerializationContext::create()->setGroups(["investigate", "proof"])
                ),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (Exception $e) {
           return $apiErrorsService->errorFiveHundred($e);
        }
    }
}
