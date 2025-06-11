<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\DefaultBranding;
use App\Entity\Question;
use App\Entity\Comment;
use App\Entity\Proof;
use App\Entity\Subject;
use App\Repository\AnswerRepository;
use App\Repository\CommentRepository;
use App\Repository\DefaultBrandingRepository;
use App\Repository\ProofRepository;
use App\Repository\ProofStorageRepository;
use App\Service\ApiErrorsService;
use App\Service\DashboardService;
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
 * Class DefaultBrandingController
 *
 * @package App\Controller
 */
class DefaultBrandingController extends AbstractController
{
    /**
     * @param DefaultBrandingRepository $repository
     * @param SerializerInterface       $serializer
     *
     * @return Response
     *
     * @Route("/api/default-branding", methods={"GET"}, name="default_branding_get")
     * @IsGranted("ROLE_SUPER_ADMIN")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get a list of all the default branding",
     * )
     * @SWG\Tag(name="default_branding")
     *
     *
     * @Areas({"internal"})
     */
    public function getAction(DefaultBrandingRepository $repository, SerializerInterface $serializer)
    {
        $defaultBranding = $repository->all();

        return new Response(
            $serializer->serialize($defaultBranding, 'json'),
            200,
            ['Content-type' => 'application/json']
        );
    }

    /**
     * @param DefaultBrandingRepository $repository
     * @param SerializerInterface       $serializer
     * @param Validator                 $validator
     * @param Request                   $request
     * @param DefaultBranding           $defaultBranding
     *
     * @param ApiErrorsService          $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/default-branding/{id}", methods={"PATCH"}, name="default_branding_update")
     * @ParamConverter("DefaultBranding", class="App\Entity\DefaultBranding")
     *
     * @IsGranted("ROLE_SUPER_ADMIN")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Update default branding.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=DefaultBranding::class, groups={"write"})
     *     )
     * )
     * @SWG\Tag(name="default_branding")
     *
     * @Areas({"internal"})
     */
    public function updateAction(
        DefaultBrandingRepository $repository,
        SerializerInterface $serializer,
        Validator $validator,
        Request $request,
        DefaultBranding $defaultBranding,
        ApiErrorsService $apiErrorsService
    )
    {
        $data = json_decode($request->getContent(), true);
        $data['id'] = $defaultBranding->getId();

        /** @var Proof $proof */
        $defaultBranding = $serializer->deserialize(
            json_encode($data),
            DefaultBranding::class,
            'json',
            DeserializationContext::create()->setGroups(['write'])
        );

        /** @var JsonResponse $response */

        if (($response = $validator->validate($defaultBranding)) !== false) {
            return $response;
        }

        // Valid Entity
        try {
            $repository->save($defaultBranding);

            return new Response(
                $serializer->serialize(
                    $repository->all(),
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
     * @param DefaultBrandingRepository $repository
     * @param SerializerInterface       $serializer
     * @param Request                   $request
     *
     * @param ApiErrorsService          $apiErrorsService
     *
     * @return Response
     *
     * @throws \League\Flysystem\FileExistsException
     * @throws \League\Flysystem\FileNotFoundException
     * @Route("/api/default-branding/images", methods={"POST"}, name="default_branding_image")
     *
     * @IsGranted("ROLE_SUPER_ADMIN")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Update default branding images.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=DefaultBranding::class, groups={"write"})
     *     )
     * )
     * @SWG\Tag(name="default_branding")
     *
     * @Areas({"internal"})
     */
    public function updateSystemAssetsAction(
        DefaultBrandingRepository $repository,
        SerializerInterface $serializer,
        Request $request,
        ApiErrorsService $apiErrorsService
    )
    {
        try {
            $response = $repository->systemAssets($request->files->get('file'), $request->get('placement'));

            return new Response(
                $serializer->serialize(
                    $repository->all(),
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