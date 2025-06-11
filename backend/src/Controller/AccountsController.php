<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Accounts;
use App\Entity\Company;
use App\Entity\CompanyProduct;
use App\Entity\Subject;
use App\Entity\Profile;
use App\Entity\Product;
use App\Repository\AccountsRepository;
use App\Repository\BundleUsedRepository;
use App\Repository\CompanyRepository;
use App\Repository\ProfileRepository;
use App\Repository\ProductRepository;
use App\Service\ApiErrorsService;
use App\Service\EventService;
use App\Service\SpreadSheetService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use JMS\Serializer\SerializationContext;
use App\Service\Validator;
use Hateoas\Representation\CollectionRepresentation;
use Hateoas\Representation\PaginatedRepresentation;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Areas;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Workflow\Exception\TransitionException;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Class BundleUsedController
 *
 * @package App\Controller
 */
class AccountsController extends AbstractController
{

    /**
     * @param SerializerInterface $serializer
     * @param Request             $request
     * @param CompanyRepository   $companyRepository
     * @param AccountsRepository  $repository
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/accounts/usage", methods={"GET"}, name="accounts_get_usage")
     * @Security("is_granted('ROLE_SUPER_ADMIN', subject) or is_granted('ROLE_ADMIN_USER', subject)")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns company bundle usage",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Accounts::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="accounts")
     *
     *  @Areas({"internal"})
     */
    public function getCompanyUsageAction(
        SerializerInterface $serializer,
        Request $request,
        CompanyRepository $companyRepository,
        AccountsRepository $repository,
        ApiErrorsService $apiErrorsService
    )
    {
        try {
            // Get Parameters
            $page = (int)$request->get('page', 1);
            $limit = (int)$request->get('limit', 10);
            $id = $request->get('company', '');
            $descending = $request->get('descending', false);
            $descending = $descending == 'true' ? true : false;
            $sort = $request->get('sort', 'created_at');
            $date_from = $request->get('date_from', null);
            $date_to = $request->get('date_to', null);
            // Configure Pagination
            $offset = ($page - 1) * $limit;

            $company = $companyRepository->find($id);

            $bundles = $repository->getCompanyUsage(
                $company,
                $offset,
                $limit,
                $sort,
                $descending,
                $date_from,
                $date_to
            );
            $count = count($bundles);

            $pages = (int)ceil($count / $limit);

            $paginatedCollection = new PaginatedRepresentation(
                new CollectionRepresentation(
                    $bundles,
                    'bundles',
                    'bundles'
                ),
                'accounts_get_usage',
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
                    SerializationContext::create()->setGroups(['Default', 'accounts', 'default'])
                ),
                200,
                ['Content-type' => 'application/json']
            );
        } catch (Exception $e) {
           return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param SpreadSheetService $spreadSheet
     * @param AccountsRepository $repository
     * @param CompanyRepository  $companyRepository
     * @param Request            $request
     *
     * @param ApiErrorsService   $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/accounts/usage-export", methods={"GET"}, name="accounts_get_usage_export")
     * @Security("is_granted('ROLE_SUPER_ADMIN', subject) or is_granted('ROLE_ADMIN_USER', subject)")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns company bundle usage",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Accounts::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="accounts")
     * @Areas({"internal"})
     */
    public function exportBundleUsage(
        SpreadSheetService $spreadSheet,
        AccountsRepository $repository,
        CompanyRepository $companyRepository,
        Request $request,
        ApiErrorsService $apiErrorsService
    )
    {
        try {
            // Get Parameters
            $page = (int)$request->get('page', 1);
            $limit = (int)$request->get('limit', 10);
            $id = $request->get('company', '');
            $descending = $request->get('descending', false);
            $descending = $descending == 'true' ? true : false;
            $sort = $request->get('sort', 'created_at');
            $date_from = $request->get('date_from', null);
            $date_to = $request->get('date_to', null);
            // Configure Pagination
            $offset = ($page - 1) * $limit;

            $company = $companyRepository->find($id);

            $bundles = $repository->getCompanyUsage(
                $company,
                $offset,
                $limit,
                $sort,
                $descending,
                $date_from,
                $date_to
            );


            $file = $spreadSheet->exportAccounts($company->getName(), $bundles);
            // Return the excel file as an attachment
            return $this->file($file['path'], $file['name'], ResponseHeaderBag::DISPOSITION_INLINE);
        } catch (Exception $e) {
           return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param SerializerInterface $serializer
     * @param CompanyProduct      $companyProduct
     * @param AccountsRepository  $repository
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/accounts/monthly-reset/{id}", methods={"GET"}, name="accounts_monthly_reset")
     * @ParamConverter("CompanyProduct", class="App\Entity\CompanyProduct")
     * @IsGranted("ROLE_SUPER_ADMIN")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Resets monthly Accounts",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Accounts::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="accounts")
     *
     * @Areas({"internal"})
     */
    public function monthlyReset(
        SerializerInterface $serializer,
        CompanyProduct $companyProduct,
        AccountsRepository $repository,
        ApiErrorsService $apiErrorsService
    )
    {
        // Valid Entity
        try {
            $response = $repository->monthlyReset($companyProduct);

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