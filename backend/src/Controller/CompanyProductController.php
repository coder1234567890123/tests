<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\CompanyProduct;
use App\Entity\Company;
use App\Entity\Product;
use App\Repository\CompanyProductRepository;
use App\Repository\CompanyRepository;
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
 * Class CompanyProductController
 *
 * @package App\Controller
 */
class CompanyProductController extends AbstractController
{
    /**
     * @param CompanyProductRepository $repository
     * @param SerializerInterface      $serializer
     *
     * @return Response
     *
     * @Route("/api/companyproduct", methods={"GET"}, name="companyproduct_get")
     * @Security("is_granted('ROLE_TEAM_LEAD') or is_granted('ROLE_USER_STANDARD')")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get a list of all the Company products",
     * )
     * @SWG\Tag(name="company_product")
     *
     *
     * @Areas({"internal"})
     */
    public function getAction(
        CompanyProductRepository $repository,
        SerializerInterface $serializer
    )
    {
        $companyProduct = $repository->all();

        return new Response(
            $serializer->serialize(
                $companyProduct,
                'json',
                SerializationContext::create()->setGroups(["companyProduct", "minimalInfo"])
            ),
            200,
            ['Content-type' => 'application/json']
        );
    }

    /**
     * @param CompanyProductRepository $repository
     *
     * @param SerializerInterface      $serializer
     * @param Validator                $validator
     * @param Request                  $request
     * @param ApiErrorsService         $apiErrorsService
     *
     * @return Response
     * @Route("/api/companyproduct", methods={"post"}, name="companyproduct_post")
     * @Security("is_granted('ROLE_TEAM_LEAD') or is_granted('ROLE_USER_STANDARD')")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Posts to Company Product.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=CompanyProduct::class, groups={"write"})
     *     )
     * )
     * @SWG\Tag(name="company_product")
     *
     * @Areas({"internal"})
     */
    public function postAction(
        CompanyProductRepository $repository,
        SerializerInterface $serializer,
        Validator $validator,
        Request $request,
        ApiErrorsService $apiErrorsService
    )
    {
        /** @var CompanyProduct $companyProduct */
        $companyProduct = $serializer->deserialize(
            $request->getContent(),
            CompanyProduct::class,
            'json',
            DeserializationContext::create()->setGroups(['companyProduct'])
        );

        /** @var JsonResponse $response */
        if (($response = $validator->validate($companyProduct)) !== false) {
            return $response;
        }

        // Valid Entity
        try {
            $response = $repository->save($companyProduct);

            return new Response(
                $serializer->serialize(
                    $response,
                    'json',
                    SerializationContext::create()->setGroups(["companyProduct", "queued", "minimalInfo"])
                ),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (Exception $e) {
           return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param CompanyProductRepository $repository
     * @param SerializerInterface      $serializer
     * @param Validator                $validator
     * @param Request                  $request
     * @param CompanyProduct           $companyProduct
     *
     * @param ApiErrorsService         $apiErrorsService
     *
     * @return Response
     * @Route("/api/companyproduct/{id}", methods={"patch"}, name="companyproduct_update")
     * @ParamConverter("companyproduct", class="App\Entity\CompanyProduct")
     * @IsGranted("ROLE_SUPER_ADMIN")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Posts to Company Product.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=CompanyProduct::class, groups={"write"})
     *     )
     * )
     * @SWG\Tag(name="company_product")
     *
     *
     * @Areas({"internal"})
     */
    public function updateAction(
        CompanyProductRepository $repository,
        SerializerInterface $serializer,
        Validator $validator,
        Request $request,
        CompanyProduct $companyProduct,
        ApiErrorsService $apiErrorsService
    )
    {
        $data = json_decode($request->getContent(), true);
        $data['id'] = $companyProduct->getId();

        /** @var CompanyProduct $companyProduct */
        $companyProduct = $serializer->deserialize(
            json_encode($data),
            CompanyProduct::class,
            'json',
            DeserializationContext::create()->setGroups(['write'])
        );

        /** @var JsonResponse $response */
        if (($response = $validator->validate($companyProduct)) !== false) {
            return $response;
        }

        // Valid Entity
        try {
            $response = $repository->save($companyProduct);

            return new Response(
                $serializer->serialize(
                    $response,
                    'json',
                    SerializationContext::create()->setGroups(["companyProduct", "queued", "minimalInfo"])
                ),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (Exception $e) {
           return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param SerializerInterface      $serializer
     * @param Company                  $company
     * @param CompanyProductRepository $repository ,
     *
     * @param ApiErrorsService         $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/companyproduct/{id}", methods={"GET"}, name="companyproduct_getbyid")
     * @ParamConverter("Company", class="App\Entity\Company")
     * @IsGranted("ROLE_SUPER_ADMIN")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns a specific subject.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=CompanyProduct::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="company_product")
     *
     * @Areas({"internal"})
     */
    public function getById(
        SerializerInterface $serializer,
        Company $company,
        CompanyProductRepository $repository,
        ApiErrorsService $apiErrorsService
    )
    {
        // Valid Entity
        try {
            $response = $repository->getById($company);

            return new Response(
                $serializer->serialize(
                    $response,
                    'json',
                    SerializationContext::create()->setGroups(["companyProduct", "minimalInfo"])
                ),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (Exception $e) {
           return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param SerializerInterface      $serializer
     * @param CompanyProduct           $companyProduct
     * @param CompanyProductRepository $repository
     * @param Request                  $request
     * @param Validator                $validator
     *
     *
     * @param ApiErrorsService         $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/companyproduct/add-to-bundle/{id}", methods={"POST"}, name="companyproduct_add_bundle")
     * @ParamConverter("companyproduct", class="App\Entity\CompanyProduct")
     * @IsGranted("ROLE_SUPER_ADMIN")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns a specific subject.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=CompanyProduct::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="company_product_add_bundle")
     *
     * @Areas({"internal"})
     */
    public function addToBundle(
        SerializerInterface $serializer,
        CompanyProduct $companyProduct,
        CompanyProductRepository $repository,
        Request $request,
        Validator $validator,
        ApiErrorsService $apiErrorsService
    )
    {
        $data = json_decode($request->getContent(), true);
        $data['id'] = $companyProduct->getId();

        /** @var CompanyProduct $companyProduct */
        $companyProduct = $serializer->deserialize(
            json_encode($data),
            CompanyProduct::class,
            'json',
            DeserializationContext::create()->setGroups(['write'])
        );

        /** @var JsonResponse $response */
        if (($response = $validator->validate($companyProduct)) !== false) {
            return $response;
        }


        // Valid Entity
        try {

            $response = $repository->addToBundle($companyProduct,$data['add_unit']);

            return new Response(
                $serializer->serialize(
                    $response,
                    'json',
                    SerializationContext::create()->setGroups(["companyProduct", "minimalInfo"])
                ),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (Exception $e) {
           return $apiErrorsService->errorFiveHundred($e);
        }
    }
}
