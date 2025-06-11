<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserTracking;
use App\Exception\InvalidTrackingActionException;
use App\Repository\TeamRepository;
use App\Repository\UserRepository;
use App\Service\ApiErrorsService;
use App\Service\EventTrackingService;
use App\Service\User\UserManager;
use App\Service\Validator;
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
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Environment;

/**
 * Class UserController
 *
 * @package App\Controller
 */
class UserController extends AbstractController
{
    /**
     * @param SerializerInterface $serializer
     * @param UserRepository      $repository
     *
     * @return Response
     *
     * @Route("/api/users/me", methods={"GET"})
     *
     *
     * @SWG\Response(
     *     response="200",
     *     description="Gets the current logged in user information",
     * )
     * @SWG\Tag(name="user")
     *
     * @Areas({"internal","default"})
     *
     */
    public function currentUserAction(
        SerializerInterface $serializer,
        UserRepository $repository
    )
    {
        $user = $this->getUser();


        $json = $serializer->serialize(
            $repository->myProfile($user),
            //$user,
            'json',
            SerializationContext::create()->setGroups(['user', 'default'])
        );

        return new Response($json, 200, array(
            'Content-Type' => 'application/json'
        ));
    }

    /**
     * @param SerializerInterface  $serializer
     * @param Request              $request
     * @param EventTrackingService $eventTrackingService
     * @param UserRepository       $repository
     * @param Validator            $validator
     * @param User                 $user
     *
     * @return Response
     *
     * @Route("/api/users/reset-company/{id}", methods={"patch"})
     *
     * @ParamConverter("user", class="App\Entity\User")
     *
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Resets Current Profile Company",
     * )
     * @SWG\Tag(name="user")
     * @throws InvalidTrackingActionException
     *
     * @Areas({"internal"})
     *
     */
    public function resetCompany(
        SerializerInterface $serializer,
        Request $request,
        EventTrackingService $eventTrackingService,
        UserRepository $repository,
        Validator $validator,
        User $user
    )
    {
        $userSource = $request->headers->has('user-type') ? $request->headers->get('user-type') : UserTracking::SOURCE_CUSTOM;

        $response = $repository->resetCompany($user);

        $eventTrackingService->track(UserTracking::ACTION_USER_EDIT, $this->getUser(), $userSource);

        $json = $serializer->serialize(
            $response,
            'json',
            SerializationContext::create()->setGroups(['default'])
        );

        return new Response($json, 200, array(
            'Content-Type' => 'application/json'
        ));
    }

    /**
     * @param SerializerInterface  $serializer
     * @param Request              $request
     * @param EventTrackingService $eventTrackingService
     * @param UserRepository       $repository
     * @param Validator            $validator
     *
     * @return Response
     *
     * @Route("/api/users/update", methods={"patch"})
     *
     * @SWG\Response(
     *     response="200",
     *     description="Updates Current Profile",
     * )
     * @SWG\Tag(name="user")
     * @throws InvalidTrackingActionException
     *
     * @Areas({"internal" , "default"})
     *
     */
    public function updateProfile(
        SerializerInterface $serializer,
        Request $request,
        EventTrackingService $eventTrackingService,
        UserRepository $repository,
        Validator $validator
    )
    {
        $userSource = $request->headers->has('user-type') ? $request->headers->get('user-type') : UserTracking::SOURCE_CUSTOM;

        $data = json_decode($request->getContent(), true);
        $data['id'] = $this->getUser()->getId();
        unset($data['company']);
        unset($data['email']);

        /** @var User $updatedUser */
        $updatedUser = $serializer->deserialize(
            json_encode($data),
            User::class,
            'json',
            DeserializationContext::create()->setGroups(['write'])
        );

        /** @var User $updatedUser */
        $updatedUser = $serializer->deserialize(
            json_encode($data),
            User::class,
            'json',
            DeserializationContext::create()->setGroups(['write'])
        );

        // Validate Entity
        if (($response = $validator->validate($updatedUser)) !== false) {
            /** @var JsonResponse $response */
            return $response;
        }

        $repository->updateUser($updatedUser);
        $eventTrackingService->track(UserTracking::ACTION_USER_EDIT, $this->getUser(), $userSource);

        $json = $serializer->serialize(
            $updatedUser,
            'json',
            SerializationContext::create()->setGroups(['default'])
        );

        return new Response($json, 200, array(
            'Content-Type' => 'application/json'
        ));
    }

    /**
     * @param UserRepository      $repository
     * @param SerializerInterface $serializer
     * @param Request             $request
     *
     * @return Response
     *
     * @Route("/api/users", methods={"GET"}, name="user_get")
     * @Security("is_granted('ROLE_TEAM_LEAD') or is_granted('ROLE_USER_MANAGER')")
     * @SWG\Response(
     *     response="200",
     *     description="Get a paginated list of users",
     * )
     *
     *
     * @SWG\Tag(name="user")
     *
     * @Areas({"internal","default"})
     *
     */
    public function getAction(
        UserRepository $repository,
        SerializerInterface $serializer,
        Request $request
    )
    {
        // Get Parameters
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $descending = $request->get('descending', false);
        $descending = $descending === 'true';
        $sort = $request->get('sort', 'email');
        $searchFirstName = $request->get('search_first_name', '');
        $searchLastName = $request->get('search_last_name', '');
        $searchEmail = $request->get('search_email', '');

        // Configure Pagination
        $offset = ($page - 1) * $limit;
        $users = $repository->paginated(
            $offset,
            $limit,
            $sort,
            $descending,
            $searchFirstName,
            $searchLastName,
            $searchEmail,
            $this->getUser());

        $count = $repository->count();
        $pages = (int)ceil($count / $limit);

        $paginatedCollection = new PaginatedRepresentation(
            new CollectionRepresentation(
                $users,
                'users',
                'users'
            ),
            'user_get',
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
                SerializationContext::create()->setGroups(['Default', 'users' => ['read']])
            ),
            200,
            ['Content-type' => 'application/json']
        );
    }

    /**
     * @param SerializerInterface $serializer
     * @param User                $user
     * @param UserRepository      $repository
     *
     * @return Response
     *
     * @Route("/api/users/{id}", methods={"GET"})
     * @ParamConverter("user", class="App\Entity\User")
     * @Security("is_granted('ROLE_ANALYST', user) or is_granted('ROLE_USER_STANDARD', user)")
     * @SWG\Response(
     *     response="200",
     *     description="Get a specific user",
     * )
     * @SWG\Tag(name="user")
     *
     * @Areas({"internal","default"})
     */
    public function getIDAction(
        SerializerInterface $serializer,
        User $user,
        UserRepository $repository
    )
    {
        return new Response(
            $serializer->serialize(
                $repository->getById($user),
                'json',
                SerializationContext::create()->setGroups(['read'])
            ), 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @param Request              $request
     * @param UserRepository       $repository
     * @param TeamRepository       $teamRepository
     * @param SerializerInterface  $serializer
     * @param ValidatorInterface   $validator
     * @param EventTrackingService $eventTrackingService
     * @param Environment          $twig
     *
     * @param ApiErrorsService     $apiErrorsService
     *
     * @return JsonResponse|Response
     *
     * @Route("/api/users/company", methods={"POST"}, name="user_post_company")
     * @IsGranted("ROLE_USER_MANAGER")
     * @SWG\Response(response="200", description="User successfully created.")
     * @SWG\Response(response="400", description="Validation failed.")
     * @SWG\Response(response="500", description="Unknown error occurred.")
     * @SWG\Tag(name="user")
     *
     * @Areas({"default"})
     *
     */
    public function postCompanyAction(
        Request $request,
        UserRepository $repository,
        TeamRepository $teamRepository,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        EventTrackingService $eventTrackingService,
        Environment $twig,
        ApiErrorsService $apiErrorsService
    )
    {
        try {
            $userSource = $request->headers->has('user-type') ? $request->headers->get('user-type') : UserTracking::SOURCE_CUSTOM;

            /** @var User $user */
            $user = $serializer->deserialize(
                $request->getContent(),
                User::class,
                'json',
                DeserializationContext::create()->setGroups(['write'])
            );

            $rolesTrim = trim($user->getRoles()[0]);

            /** @var User $currentUser */
            $currentUser = $this->getUser();

            if (!empty($rolesTrim)) {
                if (empty($rolesTrim) || $rolesTrim === "") {
                    return new JsonResponse([
                        'message' => 'Validation failed!',
                        'errors' => [
                            'roles' => 'Role cant be empty'
                        ]
                    ], 400);
                } elseif (empty($rolesTrim)) {
                    return new JsonResponse([
                        'message' => 'Validation failed!',
                        'errors' => [
                            'roles' => 'Only 1 role at a time'
                        ]
                    ], 400);
                }
            } else {
                return new JsonResponse([
                    'message' => 'Validation failed!',
                    'errors' => [
                        'roles' => 'Role cant be empty'
                    ]
                ], 400);
            }

            switch ($rolesTrim) {
                case "ROLE_SUPER_ADMIN":
                case "ROLE_TEAM_LEAD":
                case "ROLE_ADMIN_USER":
                case "ROLE_ANALYST":
                    return new JsonResponse([
                        'message' => 'Validation failed!',
                        'errors' => [
                            'roles' => 'Roles are not valid'
                        ]
                    ], 400);
            }

            if ($currentUser->getRoles()[0] === 'ROLE_USER_MANAGER') {
                if ($rolesTrim !== 'ROLE_USER_STANDARD') {
                    return new JsonResponse([
                        'message' => 'Validation failed!',
                        'errors' => [
                            'roles' => 'Roles are not valid'
                        ]
                    ], 400);
                }
            }


            $user->setPassword($this->createPassword()); // give user random password

            // Validate Entity
            $errors = $validator->validate($user);
            
            if (count($errors) > 0) {
                $errorArray = [];
                /** @var ConstraintViolation $error */
                foreach ($errors as $error) {
                    $errorArray[$error->getPropertyPath()] = $error->getMessage();
                }

                return new JsonResponse([
                    'message' => 'Validation failed!',
                    'errors' => $errorArray
                ], 400);
            }

            // Ensure Password is Hashed
            $user->setPassword($user->getPassword());
            $user->setCompany($currentUser->getCompany());

            $eventTrackingService->track(UserTracking::ACTION_USER_CREATE, $this->getUser(), $userSource);

            //Send New email
            $eventTrackingService->sendMail($user);

            return new Response(
                $serializer->serialize(
                    $repository->getById($user),
                    'json',
                    SerializationContext::create()->setGroups(['read'])
                ),
                200
            );
        } catch
        (Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @return false|string
     */
    private function createPassword()
    {
        return substr(md5(microtime()), rand(10, 26), 30);
    }

    /**
     * @param Request              $request
     * @param UserRepository       $repository
     * @param TeamRepository       $teamRepository
     * @param SerializerInterface  $serializer
     * @param ValidatorInterface   $validator
     * @param EventTrackingService $eventTrackingService
     * @param Environment          $twig
     *
     * @param ApiErrorsService     $apiErrorsService
     *
     * @return JsonResponse|Response
     *
     * @Route("/api/users", methods={"POST"}, name="user_post")
     * @IsGranted("ROLE_SUPER_ADMIN")
     * @SWG\Response(response="200", description="User successfully created.")
     * @SWG\Response(response="400", description="Validation failed.")
     * @SWG\Response(response="500", description="Unknown error occurred.")
     * @SWG\Parameter(name="body", in="body",
     *     @SWG\Schema(ref=@Model(type=User::class, groups={"write"}))
     * )
     * @SWG\Tag(name="user")
     *
     * @Areas({"internal"})
     *
     */
    public function postAction(
        Request $request,
        UserRepository $repository,
        TeamRepository $teamRepository,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        EventTrackingService $eventTrackingService,
        Environment $twig,
        ApiErrorsService $apiErrorsService
    )
    {
        try {
            $userSource = $request->headers->has('user-type') ? $request->headers->get('user-type') : UserTracking::SOURCE_CUSTOM;

            /** @var User $user */
            $user = $serializer->deserialize(
                $request->getContent(),
                User::class,
                'json',
                DeserializationContext::create()->setGroups(['write'])
            );

            /** @var User $currentUser */
            $currentUser = $this->getUser();

            if ($user->hasRole("ROLE_SUPER_ADMIN") // create another super user
                || $user->hasRole("ROLE_TEAM_LEAD") // create internal team lead
                || $user->hasRole("ROLE_ANALYST") // create internal analyst
            ) {
                $user->setCompany(null);
            } elseif ($user->hasRole("ROLE_ADMIN_USER") || $user->hasRole("ROLE_USER_MANAGER") || $user->hasRole("ROLE_USER_STANDARD")) {
                if ($user->getCompany() === null) {
                    return new JsonResponse(['message' => 'Please select company for this user'], 400);
                }
            }

            $randomPassword = substr(md5(microtime()), rand(10, 26), 30);

            $user->setPassword($randomPassword); // give user random password

            // Validate Entity
            $errors = $validator->validate($user);
            if (count($errors) > 0) {
                $errorArray = [];
                /** @var ConstraintViolation $error */
                foreach ($errors as $error) {
                    $errorArray[$error->getPropertyPath()] = $error->getMessage();
                }

                return new JsonResponse([
                    'message' => 'Validation failed!',
                    'errors' => $errorArray
                ], 400);
            }

            // Ensure Password is Hashed
            $user->setPassword($user->getPassword());

            // deny user creation if user does not belong to similar company as creator
            $this->denyAccessUnlessGranted('ROLE_USER_MANAGER', $user);

            // Valid Entity
            $repository->save($user);
            if ($user->hasRole("ROLE_TEAM_LEAD")) { // create team on role
                $team = new Team();
                $team->setTeamLeader($user);
                $teamRepository->save($team);
                $eventTrackingService->track(UserTracking::ACTION_TEAM_CREATE, $this->getUser(), $userSource);
            }
            $eventTrackingService->track(UserTracking::ACTION_USER_CREATE, $this->getUser(), $userSource);

            //Send New email
            $eventTrackingService->sendMail($user);

            return new Response(
                $serializer->serialize(
                    $user,
                    'json',
                    SerializationContext::create()->setGroups(['read'])
                ),
                200
            );
        } catch (Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param Request              $request
     * @param UserRepository       $repository
     * @param TeamRepository       $teamRepository
     * @param SerializerInterface  $serializer
     * @param Validator            $validator
     * @param User                 $user
     * @param EventTrackingService $eventTrackingService
     *
     * @param ApiErrorsService     $apiErrorsService
     *
     * @return JsonResponse|Response
     *
     * @Route("/api/users/{id}", methods={"PATCH"}, name="user_patch")
     * @IsGranted("ROLE_USER_MANAGER", subject="user")
     * @ParamConverter("user", class="App\Entity\User")
     * @SWG\Response(response="200", description="User successfully updated.")
     * @SWG\Response(response="400", description="Validation failed.")
     * @SWG\Response(response="403", description="Access Denied.")
     * @SWG\Response(response="500", description="Unknown error occurred.")
     * @SWG\Parameter(name="body", in="body",
     *     @SWG\Schema(ref=@Model(type=User::class, groups={"write"}))
     * )
     * @SWG\Tag(name="user")
     *
     * @Areas({"internal"})
     *
     */
    public function patchAction(
        Request $request,
        UserRepository $repository,
        TeamRepository $teamRepository,
        SerializerInterface $serializer,
        Validator $validator,
        User $user,
        EventTrackingService $eventTrackingService,
        ApiErrorsService $apiErrorsService
    )
    {
        try {
            $data = json_decode($request->getContent(), true);
            $data['id'] = $user->getId();

            $userSource = $request->headers->has('user-type') ? $request->headers->get('user-type') : UserTracking::SOURCE_CUSTOM;

            /** @var User $updatedUser */
            $updatedUser = $serializer->deserialize(
                json_encode($data),
                User::class,
                'json',
                DeserializationContext::create()->setGroups(['write'])
            );

            /** @var User $currentUser */
            $currentUser = $this->getUser();
            if (
                $currentUser->getCompany() !== null &&
                $currentUser->getCompany()->getId() !== $updatedUser->getCompany()->getId()
            ) {
                return new JsonResponse(['message' => 'User Company Error'], 403);
            }

            if ($user->hasRole("ROLE_SUPER_ADMIN") // create another super user
                || $user->hasRole("ROLE_TEAM_LEAD") // create internal team lead
                || $user->hasRole("ROLE_ANALYST") // create internal analyst
            ) {
                $user->setCompany(null);
            } elseif ($user->hasRole("ROLE_ADMIN_USER") || $user->hasRole("ROLE_USER_MANAGER") || $user->hasRole("ROLE_USER_STANDARD")) {
                if ($user->getCompany() === null) {
                    return new JsonResponse(['message' => 'Please select company for this user'], 400);
                }
            }

            // Validate Entity
            if (($response = $validator->validate($updatedUser)) !== false) {
                /** @var JsonResponse $response */
                return $response;
            }

            $teams = $teamRepository->findByTeamLead($user->getId());
            if ($user->hasRole('ROLE_TEAM_LEAD') && $teams === null) { // create team on role update
                $team = new Team();
                $team->setTeamLeader($user);
                $teamRepository->save($team);
                $eventTrackingService->track(UserTracking::ACTION_TEAM_CREATE, $this->getUser(), $userSource);
            } elseif (!$user->hasRole('ROLE_TEAM_LEAD') && $teams !== null) { // delete team on update (cascade set to null)
                $teamRepository->delete($teams);
                $eventTrackingService->track(UserTracking::ACTION_TEAM_DELETE, $this->getUser(), $userSource);
            }

            // Valid Entity
            $repository->save($updatedUser);
            $eventTrackingService->track(UserTracking::ACTION_USER_EDIT, $this->getUser(), $userSource);
            if (array_key_exists('team', $data)) {
                $eventTrackingService->track(UserTracking::ACTION_USER_TEAM_ASSIGNMENT, $this->getUser(), $userSource);
            }

            return new Response(
                $serializer->serialize(
                    $updatedUser,
                    'json',
                    SerializationContext::create()->setGroups(['read'])
                ),
                200
            );
        } catch (Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param UserRepository      $repository
     * @param SerializerInterface $serializer
     * @param User                $user
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/user/{id}/enable", methods={"PUT"}, name="user_enable")
     * @IsGranted("ROLE_USER_MANAGER", subject="user")
     * @ParamConverter("user", class="App\Entity\User")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Enables a user",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=User::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="user")
     *
     * @Areas({"internal"})
     */
    public function enableAction(
        UserRepository $repository,
        SerializerInterface $serializer,
        User $user,
        ApiErrorsService $apiErrorsService
    )
    {
        try {
            $repository->enable($user);

            return new Response(
                $serializer->serialize(
                    $user,
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
     * @param UserRepository      $repository
     * @param SerializerInterface $serializer
     * @param User                $user
     * @param UserManager         $userManager
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/user/{id}", methods={"DELETE"}, name="user_delete")
     * @IsGranted("ROLE_ADMIN_USER", subject="user")
     * @ParamConverter("user", class="App\Entity\User")
     * @SWG\Response(
     *     response="200",
     *     description="Soft deletes a user",
     * )
     * @SWG\Tag(name="user")
     *
     * @Areas({"internal"})
     *
     */
    public function deleteAction(
        UserRepository $repository,
        SerializerInterface $serializer,
        User $user,
        UserManager $userManager,
        ApiErrorsService $apiErrorsService
    )
    {
        try {
            $user = $userManager->revokeUserToken($user)
                ->setEnabled(false);

            $repository->save($user);

            return new Response(
                $serializer->serialize(
                    $user,
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
     * @param UserRepository      $repository
     * @param SerializerInterface $serializer
     * @param User                $user
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/user/{id}/archive", methods={"PUT"}, name="user_archive")
     * @IsGranted("ROLE_ADMIN_USER", subject="user")
     * @ParamConverter("user", class="App\Entity\User")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Archive a User",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=User::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="user")
     *
     * @Areas({"internal"})
     *
     */
    public function archiveAction(
        UserRepository $repository,
        SerializerInterface $serializer,
        User $user,
        ApiErrorsService $apiErrorsService
    )
    {
        try {
            $repository->archive($user);

            return new Response(
                $serializer->serialize(
                    $user,
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
     * @param Request          $request
     * @param UserRepository   $repository
     * @param User             $user
     *
     * @param ApiErrorsService $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/user/{id}/image", methods={"POST"}, name="user_add_image")
     * @Security("is_granted('ROLE_ANALYST', user) or is_granted('ROLE_USER_STANDARD', user)")
     * @ParamConverter("user", class="App\Entity\User")
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
     * @SWG\Tag(name="user")
     *
     * @Areas({"internal"})
     *
     */
    public function addImageAction(
        Request $request,
        UserRepository $repository,
        User $user,
        ApiErrorsService $apiErrorsService
    )
    {
        // Valid Entity
        try {
            $repository->saveImage($user, $request->files->get('file'));

            return new JsonResponse([
                'message' => "File Added"
            ], 200);
        } catch (Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param UserRepository   $repository
     * @param User             $user
     *
     * @param ApiErrorsService $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/user/{id}/image", methods={"DELETE"}, name="user_delete_image")
     * @Security("is_granted('ROLE_ANALYST', user) or is_granted('ROLE_USER_STANDARD', user)")
     * @ParamConverter("user", class="App\Entity\User")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Delete image.",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="message", type="string"),
     *     )
     * )
     * @SWG\Tag(name="user")
     *
     * @Areas({"internal"})
     *
     */
    public function deleteImageAction(
        UserRepository $repository,
        User $user,
        ApiErrorsService $apiErrorsService
    )
    {
        // Valid Entity
        try {
            $repository->deleteImage($user);

            return new JsonResponse([
                'message' => "File Deleted"
            ], 200);
        } catch (Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param SerializerInterface $serializer
     * @param UserRepository      $repository
     * @param User                $user
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/user/image/{id}/list", methods={"GET"}, name="user_image_list")
     * @Security("is_granted('ROLE_ANALYST', user) or is_granted('ROLE_USER_STANDARD', user)")
     * @ParamConverter("user", class="App\Entity\User")
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
     * @SWG\Tag(name="user")
     *
     * @Areas({"internal"})
     *
     */
    public function listImageAction(
        SerializerInterface $serializer,
        UserRepository $repository,
        User $user,
        ApiErrorsService $apiErrorsService
    )
    {
        // Valid Entity
        try {
            $repository->listImage($user);

            return new Response(
                $serializer->serialize(
                    $repository->listImage($user),
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
     * @param UserRepository   $repository
     * @param User             $user
     *
     * @param ApiErrorsService $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/user/team-remove/{id}", methods={"DELETE"}, name="user_remove_team")
     * @Security("is_granted('ROLE_ANALYST', user) or is_granted('ROLE_USER_STANDARD', user)")
     * @ParamConverter("user", class="App\Entity\User")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Delete remove user from team.",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="message", type="string"),
     *     )
     * )
     * @SWG\Tag(name="user")
     *
     * @Areas({"internal"})
     *
     */
    public function removeUserFromTeam(
        UserRepository $repository,
        User $user,
        ApiErrorsService $apiErrorsService
    )
    {
        // Valid Entity
        try {
            $repository->removefromTeams($user);

            return new JsonResponse([
                $user
            ], 200);
        } catch (Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }


}
