<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Country;
use App\Entity\Employment;
use App\Entity\Qualification;
use App\Entity\Subject;
use App\Entity\Address;
use App\Entity\Phrase;
use App\Repository\CompanyRepository;
use App\Repository\CountryRepository;
use App\Service\ApiErrorsService;
use App\Service\SearchPhrase\Parser;
use App\Service\Validator;
use DateTimeImmutable;
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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Repository\PhraseRepository;
use Throwable;

/**
 * Class PhraseController
 *
 * @package App\Controller
 */
class PhraseController extends AbstractController
{

    /**
     * @param PhraseRepository    $repository
     * @param SerializerInterface $serializer
     *
     * @return Response
     *
     * @Route("/api/phrase", methods={"GET"}, name="phrase_get_paginated")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get a paginated list of Phrase.",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Phrase::class, groups={"read"}))
     * )
     *)
     *
     * @SWG\Tag(name="phrase")
     *
     * @Areas({"internal"})
     *
     **/
    public function getAction(
        PhraseRepository $repository,
        SerializerInterface $serializer,
        Request $request
    )
    {
        // Get Parameters
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $descending = $request->get('descending', false);
        $descending = $descending == 'true' ? true : false;
        $sort = $request->get('sort', 'createdAt');
        $search = $request->get('search', '');

        // Configure Pagination
        $offset = ($page - 1) * $limit;
        $phrase = $repository->paginated($offset, $limit, $sort, $descending, $search, $this->getUser());

        $count = $repository->count();

        $pages = (int)ceil($count / $limit);

        $paginatedCollection = new PaginatedRepresentation(
            new CollectionRepresentation(
                $phrase,
                'phrase',
                'phrase'
            ),
            'phrase_get_paginated',
            [],
            $page,
            $limit,
            $pages,
            'page',
            'limit',
            false,
            $count
        );

        //TODO
//        // only return phrases that this user is allowed to view & reset collection index to 0
//        $phrases = array_values(array_filter($phrases, function (Phrase $phrase) {
//            return $this->isGranted('ROLE_SUPER_ADMIN', $phrase);
//        }));

        return new Response(
            $serializer->serialize(
                $paginatedCollection,
                'json',
                SerializationContext::create()->setGroups(['Default', 'read'])
            ),
            200,
            ['Content-type' => 'application/json']
        );
    }

    /**
     * @param PhraseRepository    $repository
     * @param SerializerInterface $serializer
     * @param Validator           $validator
     * @param Request             $request
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/phrase", methods={"POST"}, name="phrase_post")
     * @IsGranted("ROLE_SUPER_ADMIN")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Create a Phrase.",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Phrase::class, groups={"read"}))
     * )
     *)
     *
     * @SWG\Tag(name="phrase")
     *
     * @Areas({"internal"})
     *
     */
    public function postAction(
        PhraseRepository $repository,
        SerializerInterface $serializer,
        Validator $validator,
        Request $request,
        ApiErrorsService $apiErrorsService
    )
    {
        $phrase = $serializer->deserialize(
            $request->getContent(),
            Phrase::class,
            'json',
            DeserializationContext::create()->setGroups(['write'])
        );

        /** @var JsonResponse $response */
        if (($response = $validator->validate($phrase)) !== false) {
            return $response;
        }

        try {
            $repository->save($phrase);
            $repository->onPriorityCreate($phrase);

            return new Response(
                $serializer->serialize(
                    $phrase,
                    'json',
                    SerializationContext::create()->setGroups(['read'])
                ), 200, [
                'Content-Type' => 'application/json'
            ]);
        } catch (Exception $e) {
           return $apiErrorsService->errorFiveHundred($e);
        }
        //end of function
    }

    /**
     * @param PhraseRepository    $repository
     * @param SerializerInterface $serializer
     * @param Validator           $validator
     * @param Request             $request
     * @param Phrase              $phrase
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/phrase/{id}", methods={"PATCH"}, name="phrase_update")
     * @ParamConverter("phrase", class="App\Entity\Phrase")
     * @IsGranted("ROLE_SUPER_ADMIN", subject="phrase")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Update Phrase by Id.",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Phrase::class, groups={"read"}))
     * )
     *)
     *
     * @SWG\Tag(name="phrase")
     *
     * @Areas({"internal"})
     */
    public function updateAction(
        PhraseRepository $repository,
        SerializerInterface $serializer,
        Validator $validator,
        Request $request,
        Phrase $phrase,
        ApiErrorsService $apiErrorsService
    )
    {
        $data = json_decode($request->getContent(), true);
        $data['id'] = $phrase->getId();

        $oldPriority = $phrase->getPriority();

        /** @var Phrase $phrase */
        $phrase = $serializer->deserialize(
            json_encode($data),
            Phrase::class,
            'json',
            DeserializationContext::create()->setGroups(['write'])
        );

        /** @var JsonResponse $response */
        if (($response = $validator->validate($phrase)) !== false) {
            return $response;
        }

        try {
            $repository->save($phrase);

            if ($oldPriority !== $phrase->getPriority()) {
                $repository->onPriorityUpdate($oldPriority, $phrase);
            }

            return new Response($serializer->serialize(
                $phrase,
                'json',
                SerializationContext::create()->setGroups(['read'])
            ), 200, [
                'Content-Type' => 'application/json'
            ]);
        } catch (Exception $e) {
           return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param PhraseRepository $repository
     * @param Validator        $validator
     * @param Phrase           $phrase
     *
     * @param ApiErrorsService $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/phrase/{id}/disable", methods={"DELETE"}, name="phrase_delete")
     * @ParamConverter("phrase", class="App\Entity\Phrase")
     * @IsGranted("ROLE_SUPER_ADMIN", subject="phrase")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Disable Phrase by Id.",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Phrase::class, groups={"read"}))
     * )
     *)
     *
     * @SWG\Tag(name="phrase")
     *
     * @Areas({"internal"})
     */
    public function disableAction(
        PhraseRepository $repository,
        Validator $validator,
        Phrase $phrase,
        ApiErrorsService $apiErrorsService
    )
    {
        /** @var JsonResponse $response */
        if (($response = $validator->validate($phrase)) !== false) {
            return $response;
        }
        // Valid Entity
        try {
            $repository->disable($phrase);

            return new JsonResponse(["archived" => "{$phrase->getId()}"], 200);
        } catch (Exception $e) {
           return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param PhraseRepository    $repository
     * @param SerializerInterface $serializer
     *
     * @return Response
     *
     * @Route("/api/phrase/archived", methods={"GET"}, name="phrase_archive")
     * @IsGranted("ROLE_SUPER_ADMIN")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get archived Phrase.",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Phrase::class, groups={"read"}))
     * )
     *)
     *
     * @SWG\Tag(name="phrase")
     *
     * @Areas({"internal"})
     */
    public function getArchive(
        PhraseRepository $repository,
        SerializerInterface $serializer)
    {
        $archive = $repository->archived();

        return new Response($serializer->serialize(
            $archive,
            'json',
            SerializationContext::create()->setGroups(["read"])
        ), 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @param PhraseRepository    $repository
     * @param SerializerInterface $serializer
     *
     * @return Response
     *
     * @Route("/api/phrase/enabled", methods={"GET"}, name="phrase_enabled")
     * @IsGranted("ROLE_SUPER_ADMIN")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get of phrase Enabled",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Items(ref=@Model(type=Phrase::class, groups={"read"}))
     *     )
     * )
     * @SWG\Tag(name="phrase")
     *
     *
     * @Areas({"internal"})
     */
    public function getEnabled(
        PhraseRepository $repository,
        SerializerInterface $serializer
    )
    {
        $enabled = $repository->enabled();

        return new Response($serializer->serialize(
            $enabled,
            'json',
            SerializationContext::create()->setGroups(["read"])
        ), 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @param PhraseRepository    $repository
     * @param SerializerInterface $serializer
     * @param Phrase              $phrase
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/phrase/{id}/enable", methods={"PUT"}, name="phrase_enable_id")
     * @ParamConverter("phrase", class="App\Entity\Phrase")
     * @IsGranted("ROLE_SUPER_ADMIN", subject="phrase")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Enables a phrase",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Phrase::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="phrase")
     *
     *
     * @Areas({"internal"})
     */
    public function enableAction(
        PhraseRepository $repository,
        SerializerInterface $serializer,
        Phrase $phrase,
        ApiErrorsService $apiErrorsService
    )
    {
        try {
            $repository->enable($phrase);

            return new Response(
                $serializer->serialize(
                    $phrase,
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
     * @param PhraseRepository    $repository
     * @param SerializerInterface $serializer
     * @param Phrase              $phrase
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/phrase/{id}/archive", methods={"PUT"}, name="phrase_archive_id")
     * @ParamConverter("phrase", class="App\Entity\Phrase")
     * @IsGranted("ROLE_SUPER_ADMIN", subject="phrase")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Archive a phrase",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Phrase::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="phrase")
     *
     * @Areas({"internal"})
     */
    public function archiveAction(
        PhraseRepository $repository,
        SerializerInterface $serializer,
        Phrase $phrase,
        ApiErrorsService $apiErrorsService
    )
    {
        try {
            $repository->archive($phrase);

            return new Response(
                $serializer->serialize(
                    $phrase,
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
     * @param Phrase              $phrase
     *
     * @return Response
     *
     * @Route("/api/phrase/{id}", methods={"GET"}, name="phrase_get_id")
     * @ParamConverter("phrase", class="App\Entity\Phrase")
     * @IsGranted("ROLE_SUPER_ADMIN", subject="phrase")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get Phrase by Id.",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Phrase::class, groups={"read"}))
     * )
     *)
     *
     * @SWG\Tag(name="phrase")
     *
     * @Areas({"internal"})
     */
    public function getIDAction(
        SerializerInterface $serializer,
        Phrase $phrase
    )
    {
        return new Response($serializer->serialize(
            $phrase,
            'json',
            SerializationContext::create()->setGroups(['read'])
        ), 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @param SerializerInterface $serializer
     * @param CountryRepository   $countryRepository
     * @param Parser              $parser
     * @param Request             $request
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/phrase/test", methods={"POST"}, name="phrase_test")
     * @IsGranted("ROLE_SUPER_ADMIN")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Test to see if Token is valid",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Items(ref=@Model(type=Phrase::class, groups={"read"}))
     *     )
     * )
     * @SWG\Tag(name="phrase")
     *
     * @Areas({"internal"})
     */
    public function postTest(
        SerializerInterface $serializer,
        CountryRepository $countryRepository,
        Parser $parser,
        Request $request,
        ApiErrorsService $apiErrorsService
    )
    {
        $content = json_decode($request->getContent(), true);
        $phrase = $content['phrase'];

        try {
            /** @var Country $country */
            $country = $countryRepository->byName('South Africa');
            $address = new Address();
            $address->setCity('Pretoria');

            $qualification = new Qualification();
            $qualification->setName('school');

            $employment = new Employment();
            $employment->setEmployer('Adcorp');

            $subject = new Subject();
            $subject
                ->setFirstName('John')
                ->setLastName('Doe')
                ->setMiddleName("middle")
                ->setMaidenName("maiden")
                ->setCountry($country)
                ->setDateOfBirth(new DateTimeImmutable())
                ->setPrimaryEmail('peter@mail.com')
                ->setSecondaryEmail('peter2@mail.com')
                ->setEducationInstitutes(["school", "matrix"])
                ->setProvince('Eastern Cape')
                ->setAddress($address)
                ->setPrimaryMobile('072 -123456')
                ->setSecondaryMobile('083 - 98765')
                ->addQualification($qualification)
                ->addEmployments($employment)
                ->setHandles(["handles"])
                ->setNickname("nickname");

            $parser->test($subject, $phrase);

            return new Response($serializer->serialize($phrase, 'json'), 200, [
                'Content-Type' => 'application/json'
            ]);
        } catch (Exception $e) {
           return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param PhraseRepository $repository
     * @param Validator        $validator
     * @param Phrase           $phrase
     *
     * @param ApiErrorsService $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/phrase/{id}", methods={"DELETE"}, name="phrase_delete")
     * @ParamConverter("phrase", class="App\Entity\Phrase")
     * @IsGranted("ROLE_SUPER_ADMIN", subject="phrase")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Delete Phrase by Id.",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Phrase::class, groups={"read"}))
     * )
     *)
     *
     * @SWG\Tag(name="phrase")
     *
     * @Areas({"internal"})
     */
    public function deleteAction(
        PhraseRepository $repository,
        Validator $validator,
        Phrase $phrase,
        ApiErrorsService $apiErrorsService
    )
    {
        /** @var JsonResponse $response */
        if (($response = $validator->validate($phrase)) !== false) {
            return $response;
        }
        // Valid Entity
        try {
            $repository->onPhraseDelete($phrase);
            $repository->delete($phrase);

            return new JsonResponse(["deleted" => "{$phrase->getId()}"], 200);
        } catch (Exception $e) {
           return $apiErrorsService->errorFiveHundred($e);
        }
    }
}
