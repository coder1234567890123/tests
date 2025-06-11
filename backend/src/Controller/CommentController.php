<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Subject;
use App\Repository\CommentRepository;
use App\Service\ApiErrorsService;
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
 * Class CommentController
 *
 * @package App\Controller
 */
class CommentController extends AbstractController
{
    // deprecated code start
    /**
     * @param CommentRepository   $repository
     *
     * @param SerializerInterface $serializer
     * @param Validator           $validator
     * @param Request             $request
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     * @Route("/api/comment", methods={"POST"}, name="comment_post")
     * @Security("is_granted('ROLE_TEAM_LEAD') or is_granted('ROLE_ANALYST')")
     * @SWG\Response(
     *     response="200",
     *     description="Posts to comment.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Comment::class, groups={"write"})
     *     )
     * )
     * @SWG\Tag(name="comment")
     *
     * @Areas({"internal"})
     */
    public function postAction(
        CommentRepository $repository,
        SerializerInterface $serializer,
        Validator $validator,
        Request $request,
        ApiErrorsService $apiErrorsService
    )
    {
        /** @var Comment $comment */
        $comment = $serializer->deserialize(
            $request->getContent(),
            Comment::class,
            'json',
            DeserializationContext::create()->setGroups(['write'])
        );

        /** @var JsonResponse $response */
        if (($response = $validator->validate($comment)) !== false) {
            return $response;
        }

        // Valid Entity
        try {
            $comment->setCommentBy($this->getUser());
            print_r($repository->save($comment));

            return new Response(
                $serializer->serialize(
                    $comment,
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
     * @param CommentRepository   $repository
     * @param SerializerInterface $serializer
     * @param Validator           $validator
     * @param Request             $request
     * @param Comment             $comment
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/comment/{id}", methods={"PATCH"}, name="comment_update")
     * @ParamConverter("comment", class="App\Entity\Comment")
     * @Security("is_granted('ROLE_TEAM_LEAD') or is_granted('ROLE_ANALYST')")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Update the comment entity.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Comment::class, groups={"write"})
     *     )
     * )
     * @SWG\Tag(name="comment")
     *
     * @Areas({"internal"})
     */
    public function updateAction(
        CommentRepository $repository,
        SerializerInterface $serializer,
        Validator $validator,
        Request $request,
        Comment $comment,
        ApiErrorsService $apiErrorsService
    )
    {
        $data = json_decode($request->getContent(), true);
        $data['id'] = $comment->getId();

        /** @var Comment $comment */
        $comment = $serializer->deserialize(
            json_encode($data),
            Comment::class,
            'json',
            DeserializationContext::create()->setGroups(['write'])
        );

        /** @var JsonResponse $response */
        if (($response = $validator->validate($comment)) !== false) {
            return $response;
        }

        // Valid Entity
        try {
            if ($comment->getCommentBy()->getId() === $this->getUser()->getId()) {
                $repository->save($comment);

                return new Response(
                    $serializer->serialize(
                        $comment,
                        'json',
                        SerializationContext::create()->setGroups(["read"])
                    ),
                    200,
                    ['Content-Type' => 'application/json']
                );
            }
            return new JsonResponse([
                'message' => "you are not allowed to edit someone's comment"
            ], 400);
        } catch (Exception $e) {
           return $apiErrorsService->errorFiveHundred($e);
        }
    }

    // deprecated code start

    /**
     * @param SerializerInterface $serializer
     * @param Comment             $comment
     *
     * @return Response
     *
     * @Route("/api/comment/{id}", methods={"GET"}, name="comment_get_id")
     * @ParamConverter("subject", class="App\Entity\Comment")
     * @Security("is_granted('ROLE_TEAM_LEAD') or is_granted('ROLE_ANALYST')")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns a specific comment.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Comment::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="comment")
     *
     *  @Areas({"internal"})
     */
    public function getIDAction(SerializerInterface $serializer, Comment $comment)
    {
        return new Response(
            $serializer->serialize(
                $comment,
                'json',
                SerializationContext::create()->setGroups(['read'])
            ), 200, [
            'Content-Type' => 'application/json'
        ]);
    }


    /**
     * @param SerializerInterface $serializer
     * @param CommentRepository   $repository
     * @param Subject             $subject
     *
     * @return Response
     *
     * @Route("/api/comment/subject/{id}", methods={"GET"}, name="comment_get_subject")
     * @ParamConverter("subject", class="App\Entity\Subject")
     * @Security("is_granted('ROLE_TEAM_LEAD') or is_granted('ROLE_ANALYST')")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns a specific comment.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Comment::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="comment")
     *
     * @Areas({"internal"})
     */
    public function getSubjectComments(
        SerializerInterface $serializer,
        CommentRepository $repository,
        Subject $subject
    )
    {
        $comment = $repository->getCommentBySubject($subject);

        return new Response(
            $serializer->serialize(
                $comment,
                'json',
                SerializationContext::create()->setGroups(['read'])
            ), 200, [
            'Content-Type' => 'application/json'
        ]);
    }

// deprecated code end

    /**
     * @param CommentRepository   $repository
     * @param SerializerInterface $serializer
     * @param Comment             $comment
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/comment/{id}", methods={"delete"}, name="comment_delete")
     * @ParamConverter("comment", class="App\Entity\Comment")
     * @Security("is_granted('ROLE_TEAM_LEAD') or is_granted('ROLE_ANALYST')")
     *
     * @SWG\Response(
     *     response="200",
     *     description="detele to comment.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Comment::class, groups={"write"})
     *     )
     * )
     * @SWG\Tag(name="comment")
     *
     * @Areas({"internal"})
     */
    public function deleteAction(
        CommentRepository $repository,
        SerializerInterface $serializer,
        Comment $comment,
        ApiErrorsService $apiErrorsService
    )
    {
        try {
            $repository->delete($comment);

            return new JsonResponse([
                'message' => 'comment deleted successfully'
            ], 200);
        } catch (Exception $e) {
           return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param CommentRepository   $repository
     * @param SerializerInterface $serializer
     * @param Comment             $comment
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/comment/{id}/disable", methods={"DELETE"}, name="comment_enable")
     * @ParamConverter("comment", class="App\Entity\Comment")
     * @Security("is_granted('ROLE_TEAM_LEAD') or is_granted('ROLE_ANALYST')")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Disables a comment",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Comment::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="comment")
     *
     * @Areas({"internal"})
     */
    public function disableAction(
        CommentRepository $repository,
        SerializerInterface $serializer,
        Comment $comment,
        ApiErrorsService $apiErrorsService
    )
    {
        try {
            $repository->disable($comment);

            return new Response(
                $serializer->serialize(
                    $comment,
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
     * @param CommentRepository   $repository
     * @param SerializerInterface $serializer
     * @param Comment             $comment
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/comment/{id}/enable", methods={"PUT"}, name="comment_enable")
     * @ParamConverter("comment", class="App\Entity\Comment")
     * @Security("is_granted('ROLE_TEAM_LEAD') or is_granted('ROLE_ANALYST')")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Enables a comment",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Comment::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="comment")
     *
     * @Areas({"internal"})
     */
    public function enableAction(
        CommentRepository $repository,
        SerializerInterface $serializer,
        Comment $comment,
        ApiErrorsService $apiErrorsService
    )
    {
        try {
            $repository->enable($comment);

            return new Response(
                $serializer->serialize(
                    $comment,
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
     * @param CommentRepository   $repository
     * @param SerializerInterface $serializer
     * @param Comment             $comment
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/comment/{id}/hide", methods={"PATCH"}, name="comment_hide")
     * @ParamConverter("comment", class="App\Entity\Comment")
     * @Security("is_granted('ROLE_TEAM_LEAD') or is_granted('ROLE_ANALYST')")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Sets a comment's visibilty to false",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Comment::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="comment")
     *
     * @Areas({"internal"})
     */
    public function hideAction(
        CommentRepository $repository,
        SerializerInterface $serializer,
        Comment $comment,
        ApiErrorsService $apiErrorsService
    )
    {
        try {
            $repository->hide($comment);

            return new Response(
                $serializer->serialize(
                    $comment,
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
     * @param CommentRepository   $repository
     * @param SerializerInterface $serializer
     * @param Comment             $comment
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/comment/{id}/show", methods={"PATCH"}, name="comment_show")
     * @ParamConverter("comment", class="App\Entity\Comment")
     * @Security("is_granted('ROLE_TEAM_LEAD') or is_granted('ROLE_ANALYST')")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Sets a comment's visibility to true",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Comment::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="comment")
     *
     *
     *  @Areas({"internal"})
     */
    public function showAction(
        CommentRepository $repository,
        SerializerInterface $serializer,
        Comment $comment,
        ApiErrorsService $apiErrorsService
    )
    {
        try {
            $repository->show($comment);

            return new Response(
                $serializer->serialize(
                    $comment,
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
