<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Profile;
use App\Entity\Report;
use App\Entity\Subject;
use App\Entity\UserTracking;
use App\Repository\MessageQueueRepository;
use App\Repository\ProfileRepository;
use App\Repository\ReportRepository;
use App\Repository\SubjectRepository;
use App\Repository\CountryRepository;
use App\Service\ApiErrorsService;
use App\Service\EventService;
use App\Service\EventTrackingService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\Areas;
use Nelmio\ApiDocBundle\Annotation\Security as SecurityDoc;
use App\Service\Validator;
use Hateoas\Representation\CollectionRepresentation;
use Hateoas\Representation\PaginatedRepresentation;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
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

/**
 * Class SubjectController
 *
 * @package App\Controller
 */
class SubjectController extends AbstractController
{
    /**
     * @param SubjectRepository   $repository
     * @param SerializerInterface $serializer
     * @param Request             $request
     *
     * @return Response
     *
     * @Route("/api/subject", methods={"GET"}, name="subject_get")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get a paginated list of subjects",
     *)
     *
     *
     * @SWG\Tag(name="subject")
     *
     * @SecurityDoc(name="Bearer")
     *
     * @Areas({"internal", "default"})
     */
    public function getAction(
        SubjectRepository $repository,
        SerializerInterface $serializer,
        Request $request
    )
    {
        // Get Parameters
        $page       = (int)$request->get('page', 1);
        $limit      = (int)$request->get('limit', 10);
        $descending = $request->get('descending', false);
        $descending = $descending == 'true' ? true : false;
        $sort       = $request->get('sort', 'first_name');
        $search     = $request->get('search', '');

        $searchFirstName = $request->get('search_first_name', '');
        $searchLastName  = $request->get('search_last_name', '');
        $searchIdNo      = $request->get('search_id_no', '');

        // Configure Pagination
        $offset   = ($page - 1) * $limit;
        $subjects = $repository->paginated(
            $this->getUser(),
            $offset,
            $limit,
            $sort,
            $descending,
            $searchFirstName,
            $searchLastName,
            $searchIdNo
        );

        $count = $repository->count();
        $pages = (int)ceil($count / $limit);

        $paginatedCollection = new PaginatedRepresentation(
            new CollectionRepresentation(
                $subjects,
                'subjects',
                'subjects'
            ),
            'subject_get',
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
                SerializationContext::create()->setGroups(['Default', 'default', 'subjects' => ['read']])
            ),
            200,
            ['Content-type' => 'application/json']
        );
    }

    /**
     * @param SubjectRepository    $repository
     * @param SerializerInterface  $serializer
     * @param Validator            $validator
     * @param EventTrackingService $eventTrackingService
     *
     * @param Request              $request
     * @param ApiErrorsService     $apiErrorsService
     *
     * @return Response
     * @Route("/api/subject", methods={"POST"}, name="subject_post")
     *
     * @Security("is_granted('ROLE_TEAM_LEAD') or is_granted('ROLE_USER_STANDARD')")
     *
     *
     * @SWG\Response(
     *     response="200",
     *     description="Posts to Subject."
     * )
     *
     *
     * @SWG\Tag(name="subject")
     *
     * @Areas({"internal","default"})
     */
    public function postAction(
        SubjectRepository $repository,
        CountryRepository $countryRepository,
        SerializerInterface $serializer,
        Validator $validator,
        EventTrackingService $eventTrackingService,
        Request $request,
        ApiErrorsService $apiErrorsService
    )
    {
        $userSource = $request->headers->has('user-type') ? $request->headers->get('user-type') : UserTracking::SOURCE_CUSTOM;

        /** @var Subject $subject */
        $subject = $serializer->deserialize(
            $request->getContent(),
            Subject::class,
            'json',
            DeserializationContext::create()->setGroups(['write'])
        );

        /** @var JsonResponse $response */
        if (($response = $validator->validate($subject)) !== false) {
            return $response;
        }

        try{
            if($subject->getCountry()==null) {
                // Getting country in format of string: vuetify-country-region-select
                $subjectJsonArr = json_decode($request->getContent(),true);
                $country = $subjectJsonArr['country'];
                $subject->setCountry($countryRepository->byName($country));
            }
        }
        catch (Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }

        // Valid Entity
        try {
            $subject->setCreatedBy($this->getUser());
            // set initial status
            $subject->setStatus("new_subject");

            switch ($this->getUser()->getRoles()[0]) {
                case "ROLE_USER_STANDARD":
                case "ROLE_USER_MANAGER":
                case "ROLE_ADMIN_USER":
                    $subject->setCompany($this->getUser()->getCompany());
                    $newSubject = $repository->save($subject);
                    break;
                default:
                    $newSubject = $repository->save($subject);
            }

//            remove duplicate id check for client leaving in case the want to implement it again
//            $subs = $repository->getSubjectByIdCompany($subject->getIdentification(), $subject->getCompany()->getId());
//
//            if (count($subs) >= 1) {
//                /** @var Subject $sub */
//                $sub = $subs[0];
//                return new JsonResponse([
//                    'error' => true,
//                    'message' => 'Subject with id: ' . $subject->getIdentification() . ' already exists for this company',
//                    'id' => $sub->getId()
//                ], 200);
//            }

            // $newSubject = $repository->save($subject);

            $eventTrackingService->track(UserTracking::ACTION_SUBJECT_CREATE, $this->getUser(), $userSource, $subject);

            // Queue all search events.

            return new Response(
                $serializer->serialize(
                    $newSubject,
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
     * @param SubjectRepository    $repository
     * @param SerializerInterface  $serializer
     * @param Validator            $validator
     * @param Request              $request
     * @param Subject              $subject
     * @param Registry             $workflows
     * @param EventTrackingService $eventTrackingService
     *
     * @return Response
     *
     * @Route("/api/subject/{id}", methods={"PATCH"}, name="subject_update")
     * @ParamConverter("subject", class="App\Entity\Subject")
     *
     * @IsGranted("ROLE_ANALYST", subject="subject")
     * @IsGranted("ROLE_USER_STANDARD", subject="subject")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Update the subject entity."
     * )
     * @SWG\Tag(name="subject")
     *
     * @Areas({"internal", "default"})
     */
    public function updateAction(
        SubjectRepository $repository,
        SerializerInterface $serializer,
        Validator $validator,
        Request $request,
        Registry $workflows,
        Subject $subject,
        EventTrackingService $eventTrackingService,
        ApiErrorsService $apiErrorsService
    )
    {
        $data       = json_decode($request->getContent(), true);
        $userSource = $request->headers->has('user-type') ? $request->headers->get('user-type') : UserTracking::SOURCE_CUSTOM;

        $data['id'] = $subject->getId();
        $workflow   = $workflows->get($subject);

        // Remove Non Update Data
        unset($data['created_by']);

        /** @var Subject $subject */
        $subject = $serializer->deserialize(
            json_encode($data),
            Subject::class,
            'json',
            DeserializationContext::create()->setGroups(['write'])
        );

        /** @var JsonResponse $response */
        if (($response = $validator->validate($subject)) !== false) {
            return $response;
        }

        // Valid Entity
        try {
            switch ($this->getUser()->getRoles()[0]) {
                case "ROLE_USER_STANDARD":
                case "ROLE_USER_MANAGER":
                case "ROLE_ADMIN_USER":
                    $subject->setCompany($this->getUser()->getCompany());
                    $savedData = $repository->save($subject);
                    break;
                default:
                    $savedData = $repository->save($subject);
            }

            // $savedData = $repository->save($subject);
            $eventTrackingService->track(UserTracking::ACTION_SUBJECT_EDIT, $this->getUser(), $userSource, $subject);

            return new Response(
                $serializer->serialize(
                    $savedData,
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
     * @param Subject             $subject
     * @param SubjectRepository   $repository
     *
     * @return Response
     *
     * @Route("/api/subject/{id}", methods={"GET"}, name="subject_get_id")
     * @ParamConverter("subject", class="App\Entity\Subject")
     *
     * @IsGranted("ROLE_ANALYST", subject="subject", statusCode=404,  message="No access is Granted")
     * @IsGranted("ROLE_USER_STANDARD", subject="subject")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns a specific subject."
     * )
     * @SWG\Tag(name="subject")
     *
     *
     * @Areas({"internal","default"})
     */
    public function getIDAction(
        SerializerInterface $serializer,
        Subject $subject,
        SubjectRepository $repository
    )
    {
        $subject = $repository->getSubjectById($subject);

        return new Response(
            $serializer->serialize(
                $subject,
                'json',
                SerializationContext::create()->setGroups(['read'])
            ), 200, [
                'Content-Type' => 'application/json'
            ]
        );
    }

    /**
     * @param EventService           $eventService
     * @param ProfileRepository      $repository
     *
     * @param MessageQueueRepository $messageQueueRepository
     * @param Subject                $subject
     *
     * @return Response
     *
     * @Route("/api/subject/{id}/refresh", methods={"GET"}, name="subject_refresh")
     * @ParamConverter("subject", class="App\Entity\Subject")
     *
     * @IsGranted("ROLE_TEAM_LEAD", subject="subject")
     * @IsGranted("ROLE_ADMIN_USER", subject="subject")
     * @IsGranted("ROLE_USER_STANDARD", subject="subject")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Runs social refresh on a profile.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Subject::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="subject")
     *
     * @Areas({"internal"})
     */
    public function refreshAction(
        EventService $eventService,
        ProfileRepository $repository,
        MessageQueueRepository $messageQueueRepository,
        Subject $subject
    )
    {
        try {
            // Delete all Message Queue all search events.
            $messageQueueRepository->deleteAll($subject);

            // Queue all search events.
            $eventService->queue($subject);

            return new JsonResponse(['message' => 'Subject search queued!'], 200);
        } catch (Exception $e) {
            return new JsonResponse(['message' => 'Could not refresh subject!'], 500);
        }
    }

    /**
     * @param SubjectRepository    $repository
     * @param SerializerInterface  $serializer
     * @param Subject              $subject
     * @param EventTrackingService $eventTrackingService
     * @param Request              $request
     *
     * @param ApiErrorsService     $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/subject/{id}", methods={"DELETE"}, name="subject_delete")
     * @ParamConverter("subject", class="App\Entity\Subject")
     *
     * @IsGranted("ROLE_USER_STANDARD", subject="subject", statusCode=404,  message="No access is Granted")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Soft deletes a subject",
     * )
     * @SWG\Tag(name="subject")
     *
     * @Areas({"internal"})
     */
    public function deleteAction(
        SubjectRepository $repository,
        SerializerInterface $serializer,
        Subject $subject,
        EventTrackingService $eventTrackingService,
        Request $request,
        ApiErrorsService $apiErrorsService
    )
    {
        try {
            $userSource = $request->headers->has('user-type') ? $request->headers->get('user-type') : UserTracking::SOURCE_CUSTOM;
            $repository->disable($subject);
            $eventTrackingService->track(UserTracking::ACTION_SUBJECT_DISABLE, $this->getUser(), $userSource, $subject);

            return new Response(
                $serializer->serialize(
                    $subject,
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
     * @param SubjectRepository    $repository
     * @param SerializerInterface  $serializer
     * @param Subject              $subject
     * @param EventTrackingService $eventTrackingService
     * @param Request              $request
     *
     * @param ApiErrorsService     $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/subject/{id}/enable", methods={"PUT"}, name="subject_enable")
     * @ParamConverter("subject", class="App\Entity\Subject")
     *
     * @IsGranted("ROLE_USER_STANDARD", subject="subject", statusCode=404,  message="No access is Granted")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Enables a subject",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Subject::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="subject")
     *
     * @Areas({"internal"})
     */

    public function enableAction(
        SubjectRepository $repository,
        SerializerInterface $serializer,
        Subject $subject,
        EventTrackingService $eventTrackingService,
        Request $request,
        ApiErrorsService $apiErrorsService
    )
    {
        try {
            $userSource = $request->headers->has('user-type') ? $request->headers->get('user-type') : UserTracking::SOURCE_CUSTOM;
            $repository->enable($subject);
            $eventTrackingService->track(UserTracking::ACTION_SUBJECT_ENABLE, $this->getUser(), $userSource, $subject);

            return new Response(
                $serializer->serialize(
                    $subject,
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
     * @param Profile             $profile
     *
     * @return Response
     *
     * @Route("/api/profile/{id}", methods={"GET"}, name="profile_get_id")
     * @ParamConverter("profile", class="App\Entity\Profile")
     *
     *
     * @IsGranted("ROLE_TEAM_LEAD", subject="profile", statusCode=404,  message="No access is Granted")
     * @IsGranted("ROLE_USER_STANDARD", subject="profile", statusCode=404,  message="No access is Granted")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns a specific profile.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Profile::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="profile")
     *
     * @Areas({"internal"})
     */
    public function getProfileIDAction(SerializerInterface $serializer, Profile $profile)
    {
        return new Response(
            $serializer->serialize($profile, 'json'), 200, [
            'Content-Type' => 'application/json'
        ]
        );
    }

    /**
     * @param SerializerInterface $serializer
     * @param Validator           $validator
     * @param ProfileRepository   $repository
     * @param Request             $request
     * @param Subject             $subject
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     * @Route("/api/subject/{id}/profile", methods={"POST"}, name="profile_create")
     * @ParamConverter("subject", class="App\Entity\Subject")
     * @Security("is_granted('ROLE_ANALYST', subject)")
     *
     *
     *
     * @SWG\Response(
     *     response="200",
     *     description="Create a new profile.",
     * )
     * @SWG\Parameter(name="body", in="body",
     *     @SWG\Schema(ref=@Model(type=Profile::class, groups={"write"}))
     * )
     * @SWG\Tag(name="profile")
     *
     * @Areas({"internal"})
     */
    public function postProfileAction(
        SerializerInterface $serializer,
        Validator $validator,
        ProfileRepository $repository,
        Request $request,
        Subject $subject,
        ApiErrorsService $apiErrorsService
    )
    {
        //* @IsGranted("ROLE_ANALYST", subject="profile", statusCode=404,  message="No access is Granted")

        /** @var Profile $profile */
        $profile = $serializer->deserialize(
            $request->getContent(),
            Profile::class,
            'json',
            DeserializationContext::create()->setGroups(['write'])
        );
        $profile->setSubject($subject);

        // Check if this is a manual entry.
        if (!$profile->getPhrase()) {
            $profile->setPhrase('Manual Entry');
        }

        /** @var JsonResponse $response */
        if (($response = $validator->validate($profile)) !== false) {
            return $response;
        }

        try {
            $repository->save($profile);

            return new Response(
                $serializer->serialize($profile, 'json'), 200, [
                'Content-Type' => 'application/json'
            ]
            );
        } catch (Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param ProfileRepository      $repository
     * @param MessageQueueRepository $messageQueueRepository
     * @param Request                $request
     * @param Subject                $subject
     *
     * @param ApiErrorsService       $apiErrorsService
     *
     * @return JsonResponse
     *
     * @Route("/internal_api/subject/{id}/profile/bulk", methods={"POST"}, name="profile_create_bulk")
     * @ParamConverter("subject", class="App\Entity\Subject")
     *
     * @Areas({"internal"})
     */
    public function bulkPostProfileAction(
        EntityManagerInterface $entityManager,
        ProfileRepository $repository,
        MessageQueueRepository $messageQueueRepository,
        Request $request,
        Subject $subject,
        ApiErrorsService $apiErrorsService
    )
    {
        try {
            $entityManager->beginTransaction();
            $data = json_decode($request->getContent(), true);

            foreach ($data['platforms'] as $platform => $profiles) {
                if (is_array($profiles) && !empty($profiles)) {
                    foreach ($profiles as $profileData) {
                        // Skip if link already exists for this subject.
                        $existingProfile = $repository->byLink($subject, $profileData['profile_url']);
                        if ($existingProfile !== null) continue;

                        // Create new Profile
                        $profile = new Profile();
                        $profile
                            ->setSubject($subject)
                            ->setPlatform($platform)
                            ->setFirstName('')
                            ->setLastName('')
                            ->setPhrase($data['phrase'])
                            ->setPriority($data['priority'])
                            ->setLink($profileData['profile_url']);

                        $repository->save($profile);
                    }
                }
            }

            $messageQueueRepository->messageFound($data['token']);
            $messageQueueRepository->checkSearchComplete($subject);
            $entityManager->commit();

            return new JsonResponse([], 200);
        } catch (Exception $e) {
            $entityManager->rollback();

            return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param ProfileRepository   $repository
     * @param SerializerInterface $serializer
     * @param Validator           $validator
     * @param Request             $request
     * @param Profile             $profile
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/profile/{id}", methods={"PATCH"}, name="profile_update")
     * @ParamConverter("profile", class="App\Entity\Profile")
     * @Security("is_granted('ROLE_ANALYST') or is_granted('ROLE_USER_STANDARD', profile)")
     *
     * @IsGranted("ROLE_ANALYST", subject="profile", statusCode=404,  message="No access is Granted")
     * @IsGranted("ROLE_USER_STANDARD", subject="profile", statusCode=404,  message="No access is Granted")
     *
     *
     * @SWG\Response(
     *     response="200",
     *     description="Update a profile.",
     * )
     * @SWG\Parameter(name="body", in="body",
     *     @SWG\Schema(ref=@Model(type=Profile::class, groups={"write"}))
     * )
     * @SWG\Tag(name="profile")
     *
     * @Areas({"internal"})
     */
    public function updateProfileAction(
        ProfileRepository $repository,
        SerializerInterface $serializer,
        Validator $validator,
        Request $request,
        Profile $profile,
        ApiErrorsService $apiErrorsService
    )
    {
        $data       = json_decode($request->getContent(), true);
        $data['id'] = $profile->getId();

        /** @var Profile $profile */
        $profile = $serializer->deserialize(
            json_encode($data),
            Profile::class,
            'json',
            DeserializationContext::create()->setGroups(['write'])
        );

        /** @var JsonResponse $response */
        if (($response = $validator->validate($profile)) !== false) {
            return $response;
        }

        try {
            $repository->save($profile);

            return new Response(
                $serializer->serialize($profile, 'json'), 200, [
                'Content-Type' => 'application/json'
            ]
            );
        } catch (Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param ProfileRepository $repository
     * @param Profile           $profile
     *
     * @param ApiErrorsService  $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/profile/{id}", methods={"DELETE"}, name="profile_delete")
     * @ParamConverter("profile", class="App\Entity\Profile")
     *
     *
     * @IsGranted("ROLE_TEAM_LEAD", subject="profile", statusCode=404,  message="No access is Granted")
     * @IsGranted("ROLE_USER_STANDARD", subject="profile", statusCode=404,  message="No access is Granted")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Delete a profile",
     * )
     * @SWG\Tag(name="profile")
     *
     * @Areas({"internal"})
     */
    public function deleteProfileAction(
        ProfileRepository $repository,
        Profile $profile,
        ApiErrorsService $apiErrorsService
    )
    {
        // Valid Entity
        try {
            $repository->delete($profile);

            return new JsonResponse(null, 204);
        } catch (Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param ProfileRepository    $repository
     * @param SubjectRepository    $subjectRepository
     * @param ReportRepository     $reportRepository
     * @param Profile              $profile
     * @param Registry             $workflows
     * @param EventTrackingService $eventTrackingService
     * @param Request              $request
     *
     * @param ApiErrorsService     $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/profile/{id}/validate", methods={"PUT"}, name="profile_validate")
     * @ParamConverter("profile", class="App\Entity\Profile")
     * @IsGranted("ROLE_ANALYST")
     *
     *
     * @SWG\Response(
     *     response="200",
     *     description="Validate a profile",
     * )
     * @SWG\Tag(name="profile")
     *
     * @Areas({"internal"})
     */
    public function validateProfileAction(
        ProfileRepository $repository,
        SubjectRepository $subjectRepository,
        ReportRepository $reportRepository,
        Profile $profile,
        Registry $workflows,
        EventTrackingService $eventTrackingService,
        Request $request,
        ApiErrorsService $apiErrorsService
    )
    {
        // Valid Entity
        try {
            $userSource = $request->headers->has('user-type') ? $request->headers->get('user-type') : UserTracking::SOURCE_CUSTOM;
            $repository->validate($profile);
            if ($profile->isValid()) {// Change subject status to validated soon as a priofile has been validated
                /** @var Subject $subject */
                $subject  = $profile->getSubject();
                $workflow = $workflows->get($subject);
                // Update the status on the subject
                if ($workflow->can($subject, 'valid')) {
                    $workflow->apply($subject, 'valid');
                    $subjectRepository->save($subject);
                    $eventTrackingService->track(UserTracking::ACTION_SUBJECT_PROFILE_VALID, $this->getUser(), $userSource, $subject);
                    if ($subject->getCurrentReport()) {
                        /** @var Report $report */
                        $report = $subject->getCurrentReport();
                        $report->setStatus($subject->getStatus());
                        $reportRepository->save($report);
                        $eventTrackingService->track(UserTracking::ACTION_SUBJECT_PROFILE_VALID, $this->getUser(), $userSource, $subject, $report);
                    }
                }
            }

            return new JsonResponse(null, 204);
        } catch (Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param ProfileRepository $repository
     * @param Profile           $profile
     *
     * @param ApiErrorsService  $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/profile/{id}/invalidate", methods={"PUT"}, name="profile_invalidate")
     * @ParamConverter("profile", class="App\Entity\Profile")
     * @IsGranted("ROLE_ANALYST")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Invalidate a profile",
     * )
     * @SWG\Tag(name="profile")
     *
     * @Areas({"internal"})
     */
    public function invalidateProfileAction(
        ProfileRepository $repository,
        Profile $profile,
        ApiErrorsService $apiErrorsService
    )
    {
        // Valid Entity
        try {
            $repository->invalidate($profile);

            return new JsonResponse(null, 204);
        } catch (Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param Request           $request
     * @param SubjectRepository $repository
     * @param Subject           $subject
     *
     * @param ApiErrorsService  $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/subject/{id}/image", methods={"POST"}, name="subject_add_image")
     * @ParamConverter("subject", class="App\Entity\Subject")
     *
     * @IsGranted("ROLE_TEAM_LEAD", subject="subject", statusCode=404,  message="No access is Granted")
     * @IsGranted("ROLE_USER_STANDARD", subject="subject", statusCode=404,  message="No access is Granted")
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
     * @SWG\Tag(name="subject")
     *
     * @Areas({"internal"})
     */
    public function addImageAction(
        Request $request,
        SubjectRepository $repository,
        Subject $subject,
        ApiErrorsService $apiErrorsService
    )
    {
        // Valid Entity
        try {
            $repository->saveImage($subject, $request->files->get('file'));

            return new JsonResponse(
                [
                    'message' => "File Added"
                ], 200
            );
        } catch (Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param Subject           $subject
     *
     * @param SubjectRepository $repository
     * @param ApiErrorsService  $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/subject/{id}/image", methods={"DELETE"}, name="subject_delete_image")
     * @ParamConverter("subject", class="App\Entity\Subject")
     *
     * @IsGranted("ROLE_TEAM_LEAD", subject="subject", statusCode=404,  message="No access is Granted")
     * @IsGranted("ROLE_USER_STANDARD", subject="subject", statusCode=404,  message="No access is Granted")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Delete image.",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="message", type="string"),
     *     )
     * )
     * @SWG\Tag(name="subject")
     */
    public function deleteImageAction(
        Subject $subject,
        SubjectRepository $repository,
        ApiErrorsService $apiErrorsService
    )
    {
        // Valid Entity
        try {
            $repository->deleteImage($subject);

            return new JsonResponse(
                [
                    'message' => "File Deleted"
                ], 200
            );
        } catch (Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param SerializerInterface $serializer
     * @param Subject             $subject
     *
     * @param SubjectRepository   $repository
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/subject/image/{id}/list", methods={"GET"}, name="subject_image_list")
     * @ParamConverter("subject", class="App\Entity\Subject")
     *
     * @IsGranted("ROLE_TEAM_LEAD", subject="subject", statusCode=404,  message="No access is Granted")
     * @IsGranted("ROLE_USER_STANDARD", subject="subject", statusCode=404,  message="No access is Granted")
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
     * @SWG\Tag(name="subject")
     */
    public function listImageAction(
        SerializerInterface $serializer,
        Subject $subject,
        SubjectRepository $repository,
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


    /**
     * @param SerializerInterface    $serializer
     * @param Subject                $subject
     *
     * @param SubjectRepository      $repository
     * @param MessageQueueRepository $messageQueueRepository
     * @param ApiErrorsService       $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/subject/message_bus/{id}", methods={"GET"}, name="subject_message_bus")
     * @ParamConverter("subject", class="App\Entity\Subject")
     * @Security("is_granted('ROLE_ANALYST')")
     *
     * @IsGranted("ROLE_ANALYST", statusCode=404,  message="No access is Granted")
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
     * @SWG\Tag(name="subject")
     */
    public function messageQueue(
        SerializerInterface $serializer,
        Subject $subject,
        SubjectRepository $repository,
        MessageQueueRepository $messageQueueRepository,
        ApiErrorsService $apiErrorsService
    )
    {
        // Valid Entity
        try {
            return new Response(
                $serializer->serialize(
                    $messageQueueRepository->messageList($subject),
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
     * @param SerializerInterface    $serializer
     * @param Subject                $subject
     *
     * @param SubjectRepository      $repository
     * @param MessageQueueRepository $messageQueueRepository
     * @param ApiErrorsService       $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/subject/message_bus/overwrite/{id}", methods={"GET"}, name="subject_message_bus_overwrite")
     * @ParamConverter("subject", class="App\Entity\Subject")
     *
     * @IsGranted("ROLE_ANALYST", statusCode=404,  message="No access is Granted")
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
     * @SWG\Tag(name="subject")
     *
     * @Areas({"internal"})
     */
    public function messageQueueOverWrite(
        SerializerInterface $serializer,
        Subject $subject,
        SubjectRepository $repository,
        MessageQueueRepository $messageQueueRepository,
        ApiErrorsService $apiErrorsService
    )
    {
        // Valid Entity
        try {
            return new Response(
                $serializer->serialize(
                    $messageQueueRepository->messageOverwrite($subject),
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
