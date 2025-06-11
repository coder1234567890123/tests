<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\UserTracking;
use App\Repository\CompanyProductRepository;
use App\Service\ApiErrorsService;
use App\Service\EventTrackingService;
use App\Service\Validator;
use function count;
use Exception;
use Hateoas\Representation\CollectionRepresentation;
use Hateoas\Representation\PaginatedRepresentation;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
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
use App\Entity\Company;
use App\Repository\CompanyRepository;
use App\Repository\CountryRepository;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\File\File;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Class CompanyController
 *
 * @package App\Controller
 */
class CompanyController extends AbstractController
{
    private $bioFile;

    /**
     * @param SerializerInterface $serializer
     * @param CompanyRepository   $repository
     *
     * @return Response
     *
     * @Route("/api/companies/current-company", methods={"GET"})
     *
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get the current logged in user",
     * )
     * @SWG\Tag(name="user")
     *
     * @Areas({"internal"})
     */
    public function currentCompanyAction(
        SerializerInterface $serializer,
        CompanyRepository $repository
    ) {
        $user = $this->getUser();

        $json = $serializer->serialize(
            $repository->myCompany($user),
            //$user,
            'json',
            SerializationContext::create()->setGroups(['Company', 'default'])
        );

        return new Response($json, 200, array(
            'Content-Type' => 'application/json'
        ));
    }

    /**
     * @param CompanyRepository   $repository
     * @param SerializerInterface $serializer
     * @param Request             $request
     *
     * @return Response
     *
     * @Route("/api/companies", methods={"GET"}, name="company_get")
     * @IsGranted("ROLE_ANALYST")
     * @SWG\Response(
     *     response="200",
     *     description="Get a paginated list of companies.",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Company::class, groups={"read"}))
     * )
     *)
     * @SWG\Tag(name="companies")
     *
     * @Areas({"internal"})
     */
    public function getAction(
        CompanyRepository $repository,
        SerializerInterface $serializer,
        Request $request
    ) {
        // Get Parameters
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $descending = $request->get('descending', false);
        $descending = $descending == 'true' ? true : false;
        $sort = $request->get('sort', 'name');
        $search = $request->get('search', '');

        // Configure Pagination
        $offset = ($page - 1) * $limit;
        $companies = $repository->paginated($offset, $limit, $sort, $descending, $search, $this->getUser());
        $count = $repository->count();
        $pages = (int)ceil($count / $limit);

        $paginatedCollection = new PaginatedRepresentation(
            new CollectionRepresentation(
                $companies,
                'companies',
                'companies'
            ),
            'company_get',
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
                SerializationContext::create()->setGroups(['Default', 'companies' => ['read']])
            ),
            200,
            ['Content-type' => 'application/json']
        );
    }

    /**
     * @param SerializerInterface $serializer
     * @param Company             $company
     * @param CompanyRepository   $repository
     *
     * @return Response
     *
     * @Route("/api/companies/{id}", methods={"GET"}, name="company_get_id")
     * @ParamConverter("company", class="App\Entity\Company")
     * @Security("is_granted('ROLE_ANALYST', company) or is_granted('ROLE_TEAM_LEAD', company)")
     * @SWG\Response(
     *     response="200",
     *     description="Get company data",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Company::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="companies")
     *
     * @Areas({"internal"})
     */
    public function getIDAction(
        SerializerInterface $serializer,
        Company $company,
        CompanyRepository $repository
    ) {
        //check company roles
        //* @Security("is_granted('ROLE_TEAM_LEAD', company) or is_granted('ROLE_ADMIN_USER', company)")

        return new Response(
            $serializer->serialize(
                $repository->getCompanyById($company),
                'json',
                SerializationContext::create()->setGroups(["read"])
            ),
            200,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * @param CompanyRepository        $repository
     * @param SerializerInterface      $serializer
     * @param Validator                $validator
     * @param Request                  $request
     * @param EventTrackingService     $eventTrackingService
     *
     * @param CompanyProductRepository $companyProductrepository
     * @param ApiErrorsService         $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/companies", methods={"POST"}, name="company_post")
     * @IsGranted("ROLE_SUPER_ADMIN")
     * @SWG\Response(
     *     response="200",
     *     description="Create a company object.",
     * )
     * @SWG\Parameter(name="body", in="body",
     *     @SWG\Schema(ref=@Model(type=Company::class, groups={"write"}))
     * )
     * @SWG\Tag(name="companies")
     *
     * @Areas({"internal"})
     */
    public function postAction(
        CompanyRepository $repository,
        CountryRepository $countryRepository,
        SerializerInterface $serializer,
        Validator $validator,
        Request $request,
        EventTrackingService $eventTrackingService,
        CompanyProductRepository $companyProductrepository,
        ApiErrorsService $apiErrorsService
    ) {
        $userSource = $request->headers->has('user-type') ? $request->headers->get('user-type') : UserTracking::SOURCE_CUSTOM;

        /** @var Company $company */
        $company = $serializer->deserialize(
            $request->getContent(),
            Company::class,
            'json',
            DeserializationContext::create()->setGroups(['write'])
        );

        try {
            if ($company->getCountry() == null) {
                // Getting country in format of string: vuetify-country-region-select
                $countryJsonArr = json_decode($request->getContent(), true);
                $country = $countryJsonArr['country'];
                $company->setCountry($countryRepository->byName($country));
            }
        } catch (Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }

        /** @var User $currentUser */
        $currentUser = $this->getUser();
        if (
            $currentUser->getCompany() !== null &&
            $currentUser->getCompany()->getId() !== $company->getId()
        ) {
            return new JsonResponse(['message' => 'User Company Error'], 403);
        }

        /** @var JsonResponse $response */
        if (($response = $validator->validate($company)) !== false) {
            return $response;
        }

        // deny company creation if user does not have permission
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        // Valid Entity
        try {
            $company->setCreatedBy($currentUser);
            $getCompany = $repository->save($company);
            $eventTrackingService->track(UserTracking::ACTION_COMPANY_CREATE, $this->getUser(), $userSource, null, null, $company);

            //adds company production
            $companyProductrepository->createCompanyProduct($company);

            return new Response(
                $serializer->serialize(
                    $repository->getCompanyById($company),
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
     * @param CompanyRepository    $repository
     * @param SerializerInterface  $serializer
     * @param Validator            $validator
     * @param Request              $request
     * @param Company              $company
     * @param EventTrackingService $eventTrackingService
     *
     * @param ApiErrorsService     $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/companies/{id}", methods={"PATCH"}, name="company_update")
     * @ParamConverter("company", class="App\Entity\Company")
     * @IsGranted("ROLE_SUPER_ADMIN")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Update the company entity.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Company::class, groups={"write"})
     *     )
     * )
     * @SWG\Tag(name="companies")
     *
     * @Areas({"internal"})
     */
    public function updateAction(
        CompanyRepository $repository,
        SerializerInterface $serializer,
        Validator $validator,
        Request $request,
        Company $company,
        EventTrackingService $eventTrackingService,
        ApiErrorsService $apiErrorsService
    ) {
        //        Roles Check
        //* @IsGranted("ROLE_ADMIN_USER", subject="company")

        $data = json_decode($request->getContent(), true);
        $data['id'] = $company->getId();

        $userSource = $request->headers->has('user-type') ? $request->headers->get('user-type') : UserTracking::SOURCE_CUSTOM;

        /** @var Company $company */
        $company = $serializer->deserialize(
            json_encode($data),
            Company::class,
            'json',
            DeserializationContext::create()->setGroups(['write'])
        );

        /** @var User $currentUser */
        $currentUser = $this->getUser();
        if (
            $currentUser->getCompany() !== null &&
            $currentUser->getCompany()->getId() !== $company->getId()
        ) {
            return new JsonResponse(['message' => 'User Company Error'], 403);
        }

        /** @var JsonResponse $response */
        if (($response = $validator->validate($company)) !== false) {
            return $response;
        }

        // Valid Entity
        try {
            $getCompany = $repository->save($company);

            $eventTrackingService->track(UserTracking::ACTION_COMPANY_EDIT, $this->getUser(), $userSource, null, null, $company);

            return new Response(
                $serializer->serialize(
                    $repository->getCompanyById($company),
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
     * @param CompanyRepository   $repository
     * @param SerializerInterface $serializer
     * @param Company             $company
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/companies/{id}", methods={"DELETE"}, name="company_delete")
     * @ParamConverter("company", class="App\Entity\Company")
     * @IsGranted("ROLE_SUPER_ADMIN")
     * @SWG\Response(
     *     response="200",
     *     description="Soft deletes a company",
     * )
     * @SWG\Tag(name="companies")
     *
     * @Areas({"internal"})
     */
    public function deleteAction(
        companyRepository $repository,
        SerializerInterface $serializer,
        Company $company,
        ApiErrorsService $apiErrorsService
    ) {
        // check roles
        //* @IsGranted("ROLE_SUPER_ADMIN", subject="company")
        try {
            $repository->disable($company);

            return new Response(
                $serializer->serialize(
                    $repository->getCompanyById($company),
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
     * @param CompanyRepository   $repository
     * @param SerializerInterface $serializer
     * @param Company             $company
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/companies/{id}/enable", methods={"PUT"}, name="company_enable")
     * @ParamConverter("company", class="App\Entity\Company")
     * @IsGranted("ROLE_SUPER_ADMIN")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Enables a company",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Company::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="companies")
     *
     * @Areas({"internal"})
     */
    public function enableAction(
        CompanyRepository $repository,
        SerializerInterface $serializer,
        Company $company,
        ApiErrorsService $apiErrorsService
    ) {
        //        roles checks
        // * @IsGranted("ROLE_SUPER_ADMIN", subject="company")
        try {
            $repository->enable($company);

            return new Response(
                $serializer->serialize(
                    $repository->getCompanyById($company),
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
     * @param CompanyRepository   $repository
     * @param SerializerInterface $serializer
     * @param Company             $company
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/companies/{id}/archive", methods={"PUT"}, name="company_archive")
     * @ParamConverter("company", class="App\Entity\Company")
     * @IsGranted("ROLE_SUPER_ADMIN")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Archive a company",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Company::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="companies")
     *
     * @Areas({"internal"})
     */
    public function archiveAction(
        companyRepository $repository,
        SerializerInterface $serializer,
        Company $company,
        ApiErrorsService $apiErrorsService
    ) {
        // check roles
        //* @IsGranted("ROLE_SUPER_ADMIN", subject="company")
        try {
            $repository->archive($company);

            return new Response(
                $serializer->serialize(
                    $repository->getCompanyById($company),
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
     * @param CompanyRepository   $repository
     * @param Request             $request
     * @param Company             $company
     * @param SerializerInterface $serializer
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/companies/{id}/image", methods={"POST"}, name="company_add_image")
     * @ParamConverter("company", class="App\Entity\Company")
     * @IsGranted("ROLE_ADMIN_USER", subject="company")
     *
     * @SWG\Parameter(
     *         description="Upload file with form-data",
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
     * @SWG\Tag(name="companies")
     *
     * @Areas({"internal"})
     */
    public function addImageAction(
        CompanyRepository $repository,
        Request $request,
        Company $company,
        SerializerInterface $serializer,
        ApiErrorsService $apiErrorsService
    ) {
        // Valid Entity
        try {
            $repository->saveImage($company, $request->files->get('file'));

            return new Response(
                $serializer->serialize(
                    $repository->getCompanyById($company),
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
     * @param CompanyRepository   $repository
     * @param Company             $company
     * @param SerializerInterface $serializer
     *
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/companies/{id}/image", methods={"DELETE"}, name="company_delete_image")
     * @ParamConverter("company", class="App\Entity\Company")
     * @IsGranted("ROLE_ADMIN_USER", subject="company")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Delete image.",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="message", type="string"),
     *     )
     * )
     * @SWG\Tag(name="companies")
     *
     * @Areas({"internal"})
     */
    public function deleteImageAction(
        CompanyRepository $repository,
        Company $company,
        SerializerInterface $serializer,
        ApiErrorsService $apiErrorsService
    ) {
        // Valid Entity
        try {
            $repository->deleteImage($company);

            return new Response(
                $serializer->serialize(
                    $repository->getCompanyById($company),
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
     * @param CompanyRepository   $repository
     * @param Request             $request
     * @param Company             $company
     * @param SerializerInterface $serializer
     *
     * @param ValidatorInterface  $validator
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/companies/{id}/imagefooterlogo", methods={"POST"}, name="company_add_image_footer_logo")
     * @ParamConverter("company", class="App\Entity\Company")
     * @IsGranted("ROLE_ADMIN_USER", subject="company")
     *
     * @SWG\Parameter(
     *         description="Upload file with form-data",
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
     * @SWG\Tag(name="companies")
     *
     * @Areas({"internal"})
     */
    public function addImageFooterAction(
        CompanyRepository $repository,
        Request $request,
        Company $company,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        ApiErrorsService $apiErrorsService
    ) {
        // Valid Entity
        try {
            $this->imageValitionCheck($request->files->get('file'));

            $repository->saveImageFooterLogo($company, $request->files->get('file'));

            return new Response(
                $serializer->serialize(
                    $repository->getCompanyById($company),
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
     * @param $uploadedFile
     *
     * @return JsonResponse
     */
    public function imageValitionCheck($uploadedFile)
    {
        $uploadedFileType = $uploadedFile->getMimeType();
        $uploadedFileSize = $uploadedFile->getClientSize();

        $fileType = ["image/jpg", "image/jpeg", "image/png"];

        if (!in_array($uploadedFileType, $fileType, true)) {
            $message = 'Please upload a valid Image .jpg, .jpeg, .png';
            return new JsonResponse([
                'message' => $message
            ], 500);

            die();
        }

        if ($uploadedFileSize >= '75000') {
            $message = 'Max Size 750k';
            return new JsonResponse([
                'message' => $message
            ], 500);

            die();
        }
    }

    /**
     * @param CompanyRepository   $repository
     * @param Company             $company
     * @param SerializerInterface $serializer
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/companies/{id}/imagefooterlogo", methods={"DELETE"}, name="company_delete_image_footer_logo")
     * @ParamConverter("company", class="App\Entity\Company")
     * @IsGranted("ROLE_ADMIN_USER", subject="company")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Delete image.",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="message", type="string"),
     *     )
     * )
     * @SWG\Tag(name="companies")
     *
     * @Areas({"internal"})
     */
    public function deleteImageFooterAction(
        CompanyRepository $repository,
        Company $company,
        SerializerInterface $serializer,
        ApiErrorsService $apiErrorsService
    ) {
        // Valid Entity
        try {
            $repository->deleteImageFooterLogo($company);

            return new Response(
                $serializer->serialize(
                    $repository->getCompanyById($company),
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
     * @param CompanyRepository   $repository
     * @param Request             $request
     * @param Company             $company
     * @param SerializerInterface $serializer
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/companies/{id}/imagefrontpage", methods={"POST"}, name="company_add_image_front_page")
     * @ParamConverter("company", class="App\Entity\Company")
     * @IsGranted("ROLE_SUPER_ADMIN", subject="company")
     *
     * @SWG\Parameter(
     *         description="Upload file with form-data",
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
     * @SWG\Tag(name="companies")
     *
     * @Areas({"internal"})
     */
    public function addImageFrontPageAction(
        CompanyRepository $repository,
        Request $request,
        Company $company,
        SerializerInterface $serializer,
        ApiErrorsService $apiErrorsService
    ) {
        // Valid Entity
        try {
            $this->imageValitionCheck($request->files->get('file'));
            $repository->saveImageFrontPage($company, $request->files->get('file'));

            return new Response(
                $serializer->serialize(
                    $repository->getCompanyById($company),
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
     * @param CompanyRepository   $repository
     * @param Request             $request
     * @param Company             $company
     * @param SerializerInterface $serializer
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/companies/{id}/imagefrontlogo", methods={"POST"}, name="company_add_image_front_logo_page")
     * @ParamConverter("company", class="App\Entity\Company")
     * @IsGranted("ROLE_SUPER_ADMIN", subject="company")
     *
     * @SWG\Parameter(
     *         description="Upload file with form-data",
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
     * @SWG\Tag(name="companies")
     *
     * @Areas({"internal"})
     */
    public function addImageFrontLogoAction(
        CompanyRepository $repository,
        Request $request,
        Company $company,
        SerializerInterface $serializer,
        ApiErrorsService $apiErrorsService
    ): ?Response {
        // Valid Entity
        try {
            $this->imageValitionCheck($request->files->get('file'));
            $repository->saveFontLogoPage($company, $request->files->get('file'));

            return new Response(
                $serializer->serialize(
                    $repository->getCompanyById($company),
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
     * @param CompanyRepository   $repository
     * @param Company             $company
     * @param SerializerInterface $serializer
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/companies/{id}/imagefrontpage", methods={"DELETE"}, name="company_delete_image_front_page")
     * @ParamConverter("company", class="App\Entity\Company")
     * @IsGranted("ROLE_ADMIN_USER", subject="company")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Delete image.",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="message", type="string"),
     *     )
     * )
     * @SWG\Tag(name="companies")
     *
     * @Areas({"internal"})
     */
    public function deleteImageFrontPageAction(
        CompanyRepository $repository,
        Company $company,
        SerializerInterface $serializer,
        ApiErrorsService $apiErrorsService
    ) {
        // Valid Entity
        try {
            $repository->deleteImageFrontPage($company);

            return new Response(
                $serializer->serialize(
                    $repository->getCompanyById($company),
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
     * @param CompanyRepository   $repository
     * @param Request             $request
     * @param Company             $company
     * @param SerializerInterface $serializer
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/companies/{id}/imagecofrontpage", methods={"POST"}, name="company_add_image_co_front_page")
     * @ParamConverter("company", class="App\Entity\Company")
     * @IsGranted("ROLE_ADMIN_USER", subject="company")
     *
     * @SWG\Parameter(
     *         description="Upload file with form-data",
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
     * @SWG\Tag(name="companies")
     *
     * @Areas({"internal"})
     */
    public function addImageCoFrontPageAction(
        CompanyRepository $repository,
        Request $request,
        Company $company,
        SerializerInterface $serializer,
        ApiErrorsService $apiErrorsService
    ) {
        // Valid Entity
        try {
            $this->imageValitionCheck($request->files->get('file'));
            $repository->saveImageCoFrontPage($company, $request->files->get('file'));

            return new Response(
                $serializer->serialize(
                    $repository->getCompanyById($company),
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
     * @param CompanyRepository   $repository
     * @param Company             $company
     * @param SerializerInterface $serializer
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/companies/{id}/imagecofrontpage", methods={"DELETE"}, name="company_delete_image_co_front_page")
     * @ParamConverter("company", class="App\Entity\Company")
     * @IsGranted("ROLE_ADMIN_USER", subject="company")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Delete image.",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="message", type="string"),
     *     )
     * )
     * @SWG\Tag(name="companies")
     *
     * @Areas({"internal"})
     */
    public function deleteImageCoFrontPageAction(
        CompanyRepository $repository,
        Company $company,
        SerializerInterface $serializer,
        ApiErrorsService $apiErrorsService
    ) {
        // Valid Entity
        try {
            $repository->deleteImageCoFrontPage($company);

            return new Response(
                $serializer->serialize(
                    $repository->getCompanyById($company),
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
     * @param CompanyRepository   $repository
     * @param Company             $company
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/companies/image/{id}/list", methods={"GET"}, name="company_image_list")
     * @ParamConverter("company", class="App\Entity\Company")
     * @IsGranted("ROLE_ADMIN_USER", subject="company")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get a list of all the files in the folder.",
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
     * @SWG\Tag(name="companies")
     *
     * @Areas({"internal"})
     */
    public function listImageAction(
        SerializerInterface $serializer,
        CompanyRepository $repository,
        Company $company,
        ApiErrorsService $apiErrorsService
    ) {
        // Valid Entity
        try {
            $repository->listImage($company);

            return new Response(
                $serializer->serialize(
                    $repository->getCompanyById($company),
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
     * @param CompanyRepository $repository
     * @param Company           $company
     *
     * @param ApiErrorsService  $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/companies/company-remove/{id}", methods={"DELETE"}, name="company_remove_company")
     * @Security("is_granted('ROLE_ANALYST', user) or is_granted('ROLE_USER_STANDARD', user)")
     * @ParamConverter("company", class="App\Entity\Company")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Delete remove company from team.",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="message", type="string"),
     *     )
     * )
     * @SWG\Tag(name="companies")
     *
     * @Areas({"internal"})
     */
    public function removeUserFromTeam(
        CompanyRepository $repository,
        Company $company,
        ApiErrorsService $apiErrorsService
    ) {
        // Valid Entity
        try {
            $repository->removefromCompany($company);

            return new JsonResponse([
                $company
            ], 200);
        } catch (Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }
}
