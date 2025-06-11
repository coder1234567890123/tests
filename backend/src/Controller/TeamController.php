<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Subject;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserTracking;
use App\Repository\TeamRepository;
use App\Repository\UserRepository;
use App\Service\ApiErrorsService;
use App\Service\ApiTeamsService;
use App\Service\EventTrackingService;
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

/**
 * Class TeamController
 *
 * @package App\Controller
 */
class TeamController extends AbstractController
{
    /**
     * @param TeamRepository      $repository
     * @param SerializerInterface $serializer
     *
     * @return Response
     *
     * @Route("/api/team", methods={"GET"}, name="team_get")
     * @IsGranted("ROLE_SUPER_ADMIN")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get a paginated list of Teams",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Team::class, groups={"read"}))
     *     )
     * )
     * @SWG\Tag(name="team")
     *
     * @Areas({"internal"})
     */
    public function getAction(
        TeamRepository $repository,
        SerializerInterface $serializer
    )
    {
        // Configure Pagination
        $teams = $repository->all();

        return new Response(
            $serializer->serialize(
                $teams,
                'json',
                SerializationContext::create()->setGroups(["team"])
            ),
            200,
            ['Content-type' => 'application/json']
        );
    }

    /**
     * @param TeamRepository      $repository
     * @param SerializerInterface $serializer
     * @param Request             $request
     * @Security("is_granted('ROLE_ANALYST') or is_granted('ROLE_USER_STANDARD')")
     *
     * @return Response
     *
     * @Route("/api/team/paginated", methods={"GET"}, name="team_get_paginated")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get a paginated list of teams",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Team::class, groups={"read"}))
     *     )
     * )
     * @SWG\Tag(name="team")
     *
     * @Areas({"internal"})
     */
    public function getPaginatedAction(
        TeamRepository $repository,
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
        $teams = $repository->paginated($offset, $limit, $sort, $descending, $search, $this->getUser());

        $count = $repository->count();
        $pages = (int)ceil($count / $limit);
        $paginatedCollection = new PaginatedRepresentation(
            new CollectionRepresentation(
                $teams,
                'teams',
                'teams'
            ),
            'team_get_paginated',
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
                SerializationContext::create()->setGroups(['Default', 'read', 'teams' => ['team']])
            ),
            200,
            ['Content-type' => 'application/json']
        );
    }

    /**
     * @param SerializerInterface $serializer
     * @param TeamRepository      $repository
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/team/teamLead", methods={"GET"}, name="team_get_by_team_id")
     * @IsGranted("ROLE_TEAM_LEAD")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns a specific team."
     * )
     *
     * @SWG\Tag(name="team")
     *
     * @Areas({"internal","default"})
     */
    public function getByLeaderIDAction(
        SerializerInterface $serializer,
        TeamRepository $repository,
        ApiErrorsService $apiErrorsService
    )
    {
        try {
            $team = $repository->findByTeamLead($this->getUser()->getId());
            return new Response(
                $serializer->serialize(
                    $team,
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
     * @param TeamRepository      $repository
     *
     * @param SerializerInterface $serializer
     * @param Validator           $validator
     * @param Request             $request
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     * @Route("/api/team", methods={"POST"}, name="team_post")
     * @IsGranted("ROLE_SUPER_ADMIN")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Create team.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Team::class, groups={"write"})
     *     )
     * )
     * @SWG\Tag(name="team")
     *
     * @Areas({"internal"})
     */
    public function postAction(
        TeamRepository $repository,
        SerializerInterface $serializer,
        Validator $validator,
        Request $request,
        ApiErrorsService $apiErrorsService
    )
    {
        /** @var Team $team */
        $team = $serializer->deserialize(
            $request->getContent(),
            Team::class,
            'json',
            DeserializationContext::create()->setGroups(['write'])
        );

        /** @var JsonResponse $response */

        if (($response = $validator->validate($team)) !== false) {
            return $response;
        }

        try {
            if ($team->getTeamLeader()->hasRole('ROLE_TEAM_LEAD')) {
                $repository->save($team);

                return new Response(
                    $serializer->serialize(
                        $team,
                        'json',
                        SerializationContext::create()->setGroups(["read"])
                    ),
                    200,
                    ['Content-Type' => 'application/json']
                );
            } else {
                return new JsonResponse([
                    'message' => "Team leader should have (TEAM_LEAD) role"
                ], 400);
            }
        } catch (Exception $e) {
           return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param Subject          $subject
     *
     * @param ApiErrorsService $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/team/subject/{id}", methods={"GET"}, name="team_by_subject")
     * @ParamConverter("subject", class="App\Entity\Subject")
     * @IsGranted("ROLE_TEAM_LEAD")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get the team by subject.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Team::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="team")
     *
     * @Areas({"internal"})
     */
    public function getTeamAction(
        Subject $subject,
        ApiErrorsService $apiErrorsService
    )
    {
        try {
            $team = $subject->getCompany()->getTeam();

            if ($team) {
                $members = [];
                /** @var User $teamMember */
                foreach ($team->getUsers() as $teamMember) {
                    $value['id'] = $teamMember->getId();
                    $value['name'] = $teamMember->getFullName();

                    $members [] = $value;
                }
                $team_lead = ['id' => $team->getTeamLeader()->getId(), 'name' => $team->getTeamName()];
                return new JsonResponse([
                    'id' => $team->getId(),
                    'team_lead' => $team_lead,
                    'analysts' => $members
                ], 200);
            } else {
                return new JsonResponse([
                    'team' => [],
                    'message' => 'no team assigned. Assign team to company'
                ], 200);
            }
        } catch (Exception $e) {
           return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param TeamRepository      $repository
     * @param SerializerInterface $serializer
     * @param Validator           $validator
     * @param Request             $request
     * @param Team                $team
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/team/{id}", methods={"PATCH"}, name="team_update")
     * @ParamConverter("team", class="App\Entity\Team")
     * @IsGranted("ROLE_SUPER_ADMIN")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Update the team entity.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Team::class, groups={"write"})
     *     )
     * )
     *
     *
     * @SWG\Tag(name="team")
     *
     * @Areas({"internal"})
     */
    public function updateAction(
        TeamRepository $repository,
        SerializerInterface $serializer,
        Validator $validator,
        Request $request,
        Team $team,
        ApiErrorsService $apiErrorsService
    )
    {
        $data = json_decode($request->getContent(), true);
        $data['id'] = $team->getId();

        /** @var Team $team */
        $team = $serializer->deserialize(
            json_encode($data),
            Team::class,
            'json',
            DeserializationContext::create()->setGroups(['write'])
        );

        /** @var JsonResponse $response */

        if (($response = $validator->validate($team)) !== false) {
            return $response;
        }

        // Valid Entity
        try {
            $repository->save($team);

            return new Response(
                $serializer->serialize(
                    $team,
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
     * @param UserRepository       $userRepository
     * @param Request              $request
     * @param Team                 $team
     * @param EventTrackingService $eventTrackingService
     *
     * @param ApiErrorsService     $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/team/{id}/assign", methods={"POST"}, name="team_assign_members")
     * @ParamConverter("team", class="App\Entity\Team")
     * @IsGranted("ROLE_SUPER_ADMIN")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Update the team entity.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Team::class, groups={"write"})
     *     )
     * )
     *
     * @SWG\Tag(name="team")
     *
     * @Areas({"internal"})
     *
     */
    public function assignAction(
        UserRepository $userRepository,
        Request $request,
        Team $team,
        EventTrackingService $eventTrackingService,
        ApiErrorsService $apiErrorsService
    )
    {
        $data = json_decode($request->getContent(), true);

        try {
            $userSource = $request->headers->has('user-type') ? $request->headers->get('user-type') : UserTracking::SOURCE_CUSTOM;
            foreach ($data['users'] as $item) {
                /** @var User $user */
                $user = $userRepository->find($item['id']);
                if ($user->getTeam() && $user->getTeam()->getId() !== $team->getId()) {
                    return new JsonResponse([
                        'message' => 'User: ' . $user->getFullName() . ' is already in team (' . $user->getTeam()->getTeamName() . ')'
                    ], 400);
                }
                if (!$user->hasRole('ROLE_ANALYST')) {
                    return new JsonResponse([
                        'message' => 'Team member should have (ANALYST) role'
                    ], 400);
                }
                $user->setTeam($team);
                $userRepository->save($user);
                $eventTrackingService->track(UserTracking::ACTION_USER_TEAM_ASSIGNMENT, $this->getUser(), $userSource);
            }

            return new JsonResponse([
                'message' => 'Successfully assigned team member to team (' . $team->getTeamName() . ')'
            ], 200);
        } catch (Exception $e) {
           return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param SerializerInterface $serializer
     * @param Team                $team
     * @param ApiTeamsService     $apiTeamsService
     *
     * @return Response
     *
     * @Route("/api/team/{id}", methods={"GET"}, name="team_get_id")
     * @ParamConverter("team", class="App\Entity\Team")
     * @IsGranted("ROLE_SUPER_ADMIN")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns a specific team.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Team::class, groups={"read"})
     *     )
     * )
     *
     * @SWG\Tag(name="team")
     *
     * @Areas({"internal"})
     */
    public function getIDAction(
        SerializerInterface $serializer,
        Team $team,
        ApiTeamsService $apiTeamsService
    )
    {
        return new Response(
            $serializer->serialize(
                $apiTeamsService->teamMembers($team),
                'json',
                SerializationContext::create()->setGroups(['read'])
            ), 200, [
            'Content-Type' => 'application/json'
        ]);
    }


    /**
     * @param SerializerInterface $serializer
     * @param Team                $team
     * @param ApiTeamsService     $apiTeamsService
     *
     * @return Response
     *
     * @Route("/api/team/companies/{id}", methods={"GET"}, name="team_companies")
     * @ParamConverter("team", class="App\Entity\Team")
     * @IsGranted("ROLE_SUPER_ADMIN")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns a specific team.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Team::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="team")
     *
     * @Areas({"internal"})
     *
     */
    public function getCompany(
        SerializerInterface $serializer,
        Team $team,
        ApiTeamsService $apiTeamsService
    )
    {
        return new Response(
            $serializer->serialize(
                $apiTeamsService->companyIndex($team),
                'json',
                SerializationContext::create()->setGroups(['read'])
            ), 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @param TeamRepository   $repository
     * @param Team             $team
     *
     * @param ApiErrorsService $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/team/{id}", methods={"delete"}, name="team_delete")
     * @ParamConverter("team", class="App\Entity\Team")
     * @IsGranted("ROLE_SUPER_ADMIN")
     *
     * @SWG\Response(
     *     response="200",
     *     description="delete a team.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Team::class, groups={"write"})
     *     )
     * )
     *
     * @SWG\Tag(name="team")
     *
     * @Areas({"internal"})
     */
    public function deleteAction(
        TeamRepository $repository,
        Team $team,
        ApiErrorsService $apiErrorsService
    )
    {
        try {
            $repository->delete($team);

            return new JsonResponse([
                'success' => true,
                'message' => "Team has been deleted."
            ], 200);
        } catch (Exception $e) {
           return $apiErrorsService->errorFiveHundred($e);
        }
    }

}