<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Profile;
use App\Entity\ProofStorage;
use App\Entity\Subject;
use App\Repository\ProofStorageRepository;
use App\Service\ApiErrorsService;
use App\Service\EventService;
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

//use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class ProofStorageController
 *
 * @package App\Controller
 */
class ProofStorageController extends AbstractController
{
    /**
     * @param SerializerInterface    $serializer
     * @param Request                $request
     * @param ProofStorageRepository $repository
     * @param Subject                $subject
     *
     * @param ApiErrorsService       $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/proofstorage/{id}/image", methods={"POST"}, name="proofstorage_add_image")
     * @ParamConverter("subject", class="App\Entity\Subject")
     * @IsGranted("ROLE_ANALYST")
     * @SWG\Parameter(
     *         description="Upload file with form-data, use the subject Id in Param",
     *         in="formData",
     *         name="form-data",
     *         type = "file",
     *  )
     *
     * @SWG\Response(
     *     response="200",
     *     description="Add's image.",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="message", type="string"),
     *     )
     * )
     * @SWG\Tag(name="proofstorage")
     *
     * @Areas({"internal"})
     */
    public function addImageAction(
        SerializerInterface $serializer,
        Request $request,
        ProofStorageRepository $repository,
        Subject $subject,
        ApiErrorsService $apiErrorsService
    )
    {
        // Valid Entity
        try {
            $response = $repository->saveImage($subject, $request->files->get('file'));

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
     * @param ProofStorageRepository $repository
     * @param ProofStorage           $proofstorage
     *
     * @param ApiErrorsService       $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/proofstorage/{id}/image", methods={"DELETE"}, name="proofstorage_delete_image")
     * @ParamConverter("proofstorage", class="App\Entity\ProofStorage")
     * @IsGranted("ROLE_ANALYST")
     * @SWG\Response(
     *     response="200",
     *     description="Delete image. use Proof Storage Id in Param",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="message", type="string"),
     *     )
     * )
     * @SWG\Tag(name="proofstorage")
     *
     * @Areas({"internal"})
     */
    public function deleteImageAction(
        ProofStorageRepository $repository,
        ProofStorage $proofstorage,
        ApiErrorsService $apiErrorsService
    )
    {

        // Valid Entity
        try {
            $repository->deleteImage($proofstorage);

            return new JsonResponse([
                'message' => "File Deleted"
            ], 200);
        } catch (Exception $e) {
           return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param SerializerInterface    $serializer
     * @param Subject                $subject
     *
     * @param ProofStorageRepository $repository
     * @param ApiErrorsService       $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/proofstorage/image/{id}/list", methods={"GET"}, name="proofstorage_image_list")
     * @ParamConverter("subject", class="App\Entity\Subject")
     * @IsGranted("ROLE_ANALYST")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get a list of all the files in the folder. Use Subject Id in Param",
     *     @SWG\Schema(
     *         type="object",
     *          @SWG\Property(property="path", type="string"),
     *          @SWG\Property(property="timestamp", type="string"),
     *          @SWG\Property(property="dirname", type="string"),
     *          @SWG\Property(property="mimetype", type="string"),
     *          @SWG\Property(property="size", type="string"),
     *          @SWG\Property(property="type", type="string"),
     *          @SWG\Property(property="basename", type="string"),
     *          @SWG\Property(property="extension", type="string"),
     *          @SWG\Property(property="filename", type="string"),
     *     )
     *)
     * @SWG\Tag(name="proofstorage")
     *
     * @Areas({"internal"})
     */
    public function listImageAction(
        SerializerInterface $serializer,
        Subject $subject,
        ProofStorageRepository $repository,
        ApiErrorsService $apiErrorsService
    )
    {
        // Valid Entity
        try {
            $repository->listImage($subject);

            return new Response(
                $serializer->serialize(
                    $repository->listImage($subject),
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
