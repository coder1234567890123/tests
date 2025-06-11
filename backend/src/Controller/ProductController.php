<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Profile;
use App\Entity\Product;
use App\Repository\ProfileRepository;
use App\Repository\ProductRepository;
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
 * Class ProductController
 *
 * @package App\Controller
 */
class ProductController extends AbstractController
{
    /**
     * @param ProductRepository   $repository
     * @param SerializerInterface $serializer
     *
     * @return Response
     *
     * @Route("/api/product", methods={"GET"}, name="product_get")
     * @Security("is_granted('ROLE_TEAM_LEAD') or is_granted('ROLE_USER_STANDARD')")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get a list of all the products",
     * )
     * @SWG\Tag(name="product")
     *
     * @Areas({"internal"})
     */
    public function getAction(
        ProductRepository $repository,
        SerializerInterface $serializer
    )
    {
        $product = $repository->all();

        return new Response(
            $serializer->serialize($product, 'json'),
            200,
            ['Content-type' => 'application/json']
        );
    }

    /**
     * @param ProductRepository   $repository
     *
     * @param SerializerInterface $serializer
     * @param Validator           $validator
     * @param Request             $request
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     * @Route("/api/product", methods={"post"}, name="product_post")
     * @Security("is_granted('ROLE_TEAM_LEAD') or is_granted('ROLE_USER_STANDARD')")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Posts to Product.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Product::class, groups={"write"})
     *     )
     * )
     * @SWG\Tag(name="product")
     *
     * @Areas({"internal"})
     */
    public function postAction(
        ProductRepository $repository,
        SerializerInterface $serializer,
        Validator $validator,
        Request $request,
        ApiErrorsService $apiErrorsService
    )
    {
        /** @var Product $product */
        $product = $serializer->deserialize(
            $request->getContent(),
            Product::class,
            'json',
            DeserializationContext::create()->setGroups(['write'])
        );

        /** @var JsonResponse $response */
        if (($response = $validator->validate($product)) !== false) {
            return $response;
        }

        // Valid Entity
        try {
            $response = $repository->save($product);

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
     * @param ProductRepository   $repository
     *
     * @param SerializerInterface $serializer
     * @param Validator           $validator
     * @param Request             $request
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     * @Route("/api/product/{id}", methods={"PATCH"}, name="product_edit")
     * @ParamConverter("product", class="App\Entity\Product")
     * @Security("is_granted('ROLE_TEAM_LEAD') or is_granted('ROLE_USER_STANDARD')")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Patch to Product.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Product::class, groups={"write"})
     *     )
     * )
     * @SWG\Tag(name="product")
     *
     * @Areas({"internal"})
     */
    public function editAction(
        ProductRepository $repository,
        SerializerInterface $serializer,
        Validator $validator,
        Request $request,
        ApiErrorsService $apiErrorsService
    )
    {
        /** @var Product $product */
        $product = $serializer->deserialize(
            $request->getContent(),
            Product::class,
            'json',
            DeserializationContext::create()->setGroups(['write'])
        );

        /** @var JsonResponse $response */
        if (($response = $validator->validate($product)) !== false) {
            return $response;
        }

        // Valid Entity
        try {
            $response = $repository->save($product);

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
     * @param ProductRepository   $repository
     * @param SerializerInterface $serializer
     * @param Product             $product
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/product/{id}/enable", methods={"PATCH"}, name="product_enable")
     * @ParamConverter("product", class="App\Entity\Product")
     * @IsGranted("ROLE_SUPER_ADMIN")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Enables a product",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Product::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="product")
     *
     * @Areas({"internal"})
     */
    public function enableAction(
        ProductRepository $repository,
        SerializerInterface $serializer,
        Product $product,
        ApiErrorsService $apiErrorsService
    )
    {
        try {
            $repository->enable($product);

            return new Response(
                $serializer->serialize(
                    $product,
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
     * @param ProductRepository   $repository
     * @param SerializerInterface $serializer
     * @param Product             $product
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/product/{id}/disable", methods={"PUT"}, name="product_disable")
     * @ParamConverter("product", class="App\Entity\Product")
     * @IsGranted("ROLE_SUPER_ADMIN")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Enables a question",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Product::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="product")
     *
     * @Areas({"internal"})
     */
    public function disableAction(
        ProductRepository $repository,
        SerializerInterface $serializer,
        Product $product,
        ApiErrorsService $apiErrorsService
    )
    {
        try {
            $repository->disable($product);

            return new Response(
                $serializer->serialize(
                    $product,
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
