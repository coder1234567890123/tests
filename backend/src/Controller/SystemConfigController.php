<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Subject;
use App\Entity\SystemConfig;
use App\Repository\SystemConfigRsystemconfigepository;
use App\Repository\SystemConfigRepository;
use App\Service\ApiErrorsService;
use App\Service\EventService;
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
 * Class SystemConfigController
 *
 * @package App\Controller
 */
class SystemConfigController extends AbstractController
{
    /**
     * @param SystemConfigRepository $repository
     * @param SerializerInterface    $serializer
     *
     * @return Response
     *
     * @Route("/api/systemconfig", methods={"GET"}, name="system_config_get")
     * @IsGranted("ROLE_SUPER_ADMIN")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get a paginated list of systemconfigs",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Subject::class, groups={"read"}))
     *     )
     * )
     * @SWG\Tag(name="systemconfig")
     *
     * @Areas({"internal"})
     */
    public function getAction(SystemConfigRepository $repository, SerializerInterface $serializer)
    {
        $systemConfig = $repository->all();

        return new Response(
            $serializer->serialize($systemConfig, 'json'),
            200,
            ['Content-type' => 'application/json']
        );
    }

    /**
     * @param SystemConfigRepository $repository
     * @param SerializerInterface    $serializer
     * @param Validator              $validator
     * @param Request                $request
     * @param SystemConfig           $systemConfig
     * @param ApiErrorsService       $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/systemconfig/{id}", methods={"PATCH"}, name="system_config_update")
     * @ParamConverter("SystemConfig", class="App\Entity\SystemConfig")
     * @IsGranted("ROLE_SUPER_ADMIN")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Update the SystemConfig entity.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=SystemConfig::class, groups={"write"})
     *     )
     * )
     * @SWG\Tag(name="systemconfig")
     *
     * @Areas({"internal"})
     */
    public function updateAction(
        SystemConfigRepository $repository,
        SerializerInterface $serializer,
        Validator $validator,
        Request $request,
        SystemConfig $systemConfig,
        ApiErrorsService $apiErrorsService
    )
    {
        $data = json_decode($request->getContent(), true);
        $data['id'] = $systemConfig->getId();

        /** @var SystemConfig $systemConfig */
        $systemConfig = $serializer->deserialize(
            json_encode($data),
            SystemConfig::class,
            'json',
            DeserializationContext::create()->setGroups(['write'])
        );

        /** @var JsonResponse $response */

        if (($response = $validator->validate($systemConfig)) !== false) {
            return $response;
        }

        // Valid Entity
        try {
            $repository->update($systemConfig);

            return new Response(
                $serializer->serialize(
                    $systemConfig,
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
     * @param SystemConfigRepository $repository
     * @param SerializerInterface    $serializer
     * @param Request                $request
     * @param SystemConfig           $systemConfig
     *
     * @param ApiErrorsService       $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/systemconfig/systemassets/{id}", methods={"POST"}, name="system_config_update_system_assets")
     * @ParamConverter("SystemConfig", class="App\Entity\SystemConfig")
     * @IsGranted("ROLE_SUPER_ADMIN")
     * @SWG\Parameter(
     *         description="Upload file with form-data, use the system assets Id in Param",
     *         in="formData",
     *         name="form-data",
     *         type = "file",
     *  )
     *
     * @SWG\Response(
     *     response="200",
     *     description="Add's files to system access.",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="message", type="string"),
     *     )
     * )
     * @SWG\Tag(name="systemconfig")
     *
     * @Areas({"internal"})
     */
    public function updateSystemAssetsAction(
        SystemConfigRepository $repository,
        SerializerInterface $serializer,
        Request $request,
        SystemConfig $systemConfig,
        ApiErrorsService $apiErrorsService
    )
    {
        // Valid Entity
        try {
            $response = $repository->systemAssets($systemConfig, $request->files->get('file'));

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
     * @param SystemConfigRepository $repository
     * @param SerializerInterface    $serializer
     *
     * @param ApiErrorsService       $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/systemconfig/systemassets/list", methods={"GET"}, name="system_config_image_list")
     * @IsGranted("ROLE_SUPER_ADMIN")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get a list of all the files in the folder. Use SystemConfig Id in Param",
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
     * @SWG\Tag(name="systemconfig")
     *
     * @Areas({"internal"})
     */
    public function listSystemAssetsAction(
        SystemConfigRepository $repository,
        SerializerInterface $serializer,
        ApiErrorsService $apiErrorsService
    )
    {
        // Valid Entity
        try {
            $repository->systemAssetsList();

            return new Response(
                $serializer->serialize(
                    $repository->systemAssetsList(),
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
