<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\Comment;
use App\Entity\Proof;
use App\Entity\Subject;
use App\Repository\AnswerRepository;
use App\Repository\CommentRepository;
use App\Repository\ProofRepository;
use App\Repository\QuestionRepository;
use App\Service\ApiErrorsService;
use Exception;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\Areas;
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

/**
 * Class AnswerController
 *
 * @package App\Controller
 */
class AnswerController extends AbstractController
{
    /**
     * @param AnswerRepository    $repository
     * @param SerializerInterface $serializer
     * @param Request             $request
     *
     * @return Response
     *
     * @Route("/api/answer", methods={"GET"}, name="answer_get")
     *
     * @Security("is_granted('ROLE_TEAM_LEAD') or is_granted('ROLE_ANALYST')")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get a paginated list of answers",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Answer::class, groups={"read"}))
     *     )
     * )
     * @SWG\Tag(name="answer")
     *
     * @Areas({"internal"})
     */
    public function getAction(
        AnswerRepository $repository,
        SerializerInterface $serializer,
        Request $request
    )
    {
        // Get Parameters
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $descending = $request->get('descending', false);
        $descending = $descending == 'true' ? true : false;
        $sort = $request->get('sort', 'answer');
        $search = $request->get('search', '');

        // Configure Pagination
        $count = $repository->count();
        $offset = ($page - 1) * $limit;
        $answers = $repository->paginated($offset, $limit, $sort, $descending, $search);
        $pages = (int)ceil($count / $limit);

        $paginatedCollection = new PaginatedRepresentation(
            new CollectionRepresentation(
                $answers,
                'answers',
                'answers'
            ),
            'answer_get',
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
                SerializationContext::create()->setGroups(['Default', 'answers' => ['read']])
            ),
            200,
            ['Content-type' => 'application/json']
        );
    }

    /**
     * @param AnswerRepository    $repository
     * @param CommentRepository   $commentRepository
     * @param QuestionRepository  $questionRepository
     *
     * @param ProofRepository     $proofRepository
     * @param SerializerInterface $serializer
     * @param Validator           $validator
     * @param Request             $request
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     * @throws Exception
     * @Route("/api/answer", methods={"POST"}, name="answer_post")
     *
     * @Security("is_granted('ROLE_TEAM_LEAD') or is_granted('ROLE_ANALYST')")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Posts to answer.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Answer::class, groups={"write"})
     *     )
     * )
     * @SWG\Tag(name="answer")
     *
     * @Areas({"internal"})
     */
    public function postAction(
        AnswerRepository $repository,
        CommentRepository $commentRepository,
        QuestionRepository $questionRepository,
        ProofRepository $proofRepository,
        SerializerInterface $serializer,
        Validator $validator,
        Request $request,
        ApiErrorsService $apiErrorsService
    )
    {
        $data = json_decode($request->getContent(), true);
        $data['score'] = $questionRepository->getScore($data);

        if (array_key_exists('id', $data)) {
            $answer = $this->updateAnswer($data, $repository, $serializer);
        } else {
            /** @var Answer $answer */
            $answer = $serializer->deserialize(
                $request->getContent(),
                Answer::class,
                'json',
                DeserializationContext::create()->setGroups(['write'])
            );
        }

        /** @var JsonResponse $response */
        if (($response = $validator->validate($answer)) !== false) {
            return $response;
        }

        // Valid Entity
        try {
            $answer->setUser($this->getUser());
            $repository->save($answer);

            return new Response(
                $serializer->serialize(
                    $repository->getById($answer),
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
     * @param array               $data
     * @param AnswerRepository    $repository
     * @param SerializerInterface $serializer
     *
     * @return Answer
     * @throws Exception
     *
     * @Security("is_granted('ROLE_TEAM_LEAD') or is_granted('ROLE_ANALYST')")
     *
     * @Areas({"internal"})
     */
    private function updateAnswer(array $data, AnswerRepository $repository, SerializerInterface $serializer)
    {
        $answer = $repository->find($data['id']);// get existing answer and update only updated fields

        /** @var Answer $answer */
        $answer = $serializer->deserialize(
            json_encode($data),
            Answer::class,
            'json',
            DeserializationContext::create()->setGroups(['write'])
        );

        return $answer;
    }

    /**
     * @param AnswerRepository    $repository
     * @param SerializerInterface $serializer
     * @param Validator           $validator
     * @param Request             $request
     * @param Answer              $answer
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/answer/{id}", methods={"PATCH"}, name="answer_update")
     * @ParamConverter("answer", class="App\Entity\Answer")
     *
     * @Security("is_granted('ROLE_TEAM_LEAD') or is_granted('ROLE_ANALYST')")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Update the answer entity.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Answer::class, groups={"write"})
     *     )
     * )
     * @SWG\Tag(name="answer")
     *
     * @Areas({"internal"})
     */
    public function updateAction(
        AnswerRepository $repository,
        SerializerInterface $serializer,
        Validator $validator,
        Request $request,
        Answer $answer,
        ApiErrorsService $apiErrorsService
    )
    {
        $data = json_decode($request->getContent(), true);
        $data['id'] = $answer->getId();

        /** @var Answer $answer */
        $answer = $serializer->deserialize(
            json_encode($data),
            Answer::class,
            'json',
            DeserializationContext::create()->setGroups(['write'])
        );

        /** @var JsonResponse $response */
        if (($response = $validator->validate($answer)) !== false) {
            return $response;
        }

        // Valid Entity
        try {
            $repository->save($answer);

            return new Response(
                $serializer->serialize(
                    $repository->getById($answer),
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
     * @param Answer              $answer
     *
     * @return Response
     *
     * @Route("/api/answer/{id}", methods={"GET"}, name="answer_get_id")
     * @ParamConverter("answer", class="App\Entity\Answer")
     *
     * @Security("is_granted('ROLE_TEAM_LEAD') or is_granted('ROLE_ANALYST')")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns a specific answer.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Answer::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="answer")
     *
     *
     * @Areas({"internal"})
     */
    public function getIDAction(
        SerializerInterface $serializer,
        Answer $answer)
    {
        return new Response(
            $serializer->serialize(
                $answer,
                'json',
                SerializationContext::create()->setGroups(['read'])
            ), 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @param AnswerRepository    $repository
     * @param SerializerInterface $serializer
     * @param Answer              $answer
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/answer/{id}", methods={"delete"}, name="answer_delete")
     * @ParamConverter("answer", class="App\Entity\Answer")
     *
     * @IsGranted("ROLE_SUPER_ADMIN")
     *
     * @SWG\Response(
     *     response="200",
     *     description="detele to answer.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Answer::class, groups={"write"})
     *     )
     * )
     * @SWG\Tag(name="answer")
     *
     * @Areas({"internal"})
     */
    public function deleteAction(
        AnswerRepository $repository,
        SerializerInterface $serializer,
        Answer $answer,
        ApiErrorsService $apiErrorsService
    )
    {
        try {
            $repository->disable($answer);

            return new Response(
                $serializer->serialize(
                    $answer,
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
     * @param AnswerRepository    $repository
     * @param SerializerInterface $serializer
     * @param Answer              $answer
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/answer/{id}/enable", methods={"PUT"}, name="answer_enable")
     * @ParamConverter("answer", class="App\Entity\Answer")
     * @IsGranted("ROLE_SUPER_ADMIN")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Enables a answer",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Answer::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="answer")
     *
     * @Areas({"internal"})
     */
    public function enableAction(
        AnswerRepository $repository,
        SerializerInterface $serializer,
        Answer $answer,
        ApiErrorsService $apiErrorsService
    )
    {
        try {
            $repository->enable($answer);

            return new Response(
                $serializer->serialize(
                    $answer,
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
     * @param AnswerRepository    $repository
     * @param SerializerInterface $serializer
     * @param Question            $question
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/answer/question/{id}/skip", methods={"GET"}, name="answer_question_skip")
     * @ParamConverter("question", class="App\Entity\Question")
     *
     * @Security("is_granted('ROLE_TEAM_LEAD') or is_granted('ROLE_ANALYST')")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Skips a question by tagging answer",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Answer::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="answer")
     *
     * @Areas({"internal"})
     */
    public function skipAction(
        AnswerRepository $repository,
        SerializerInterface $serializer,
        Question $question,
        ApiErrorsService $apiErrorsService
    )
    {
        try {
            $answer = new Answer();
            $answer->setQuestion($question);
            $repository->skip($answer);

            return new Response(
                $serializer->serialize(
                    $answer,
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
     * @param AnswerRepository    $repository
     * @param SerializerInterface $serializer
     * @param Question            $question
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/answer/question/{id}/applicable", methods={"GET"}, name="answer_question_applicable")
     * @ParamConverter("question", class="App\Entity\Question")
     *
     * @Security("is_granted('ROLE_TEAM_LEAD') or is_granted('ROLE_ANALYST')")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Marks a question as not applicable by tagging answer",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Answer::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="answer")
     *
     * @Areas({"internal"})
     */
    public function applicableAction(
        AnswerRepository $repository,
        SerializerInterface $serializer,
        Question $question,
        ApiErrorsService $apiErrorsService
    )
    {
        try {
            $answer = new Answer();
            $answer->setQuestion($question);
            $repository->notApplicable($answer);

            return new Response(
                $serializer->serialize(
                    $answer,
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
