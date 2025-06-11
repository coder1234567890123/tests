<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Question;
use App\Entity\Answer;
use App\Entity\Report;
use App\Entity\Subject;
use App\Entity\Profile;
use App\Entity\Comment;
use App\Repository\CommentRepository;
use App\Repository\GlobalWeightsRepository;
use App\Helper\InvestigationHelper;
use App\Repository\QuestionRepository;
use App\Repository\AnswerRepository;
use App\Service\ApiErrorsService;
use function count;
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
use Throwable;
use function ucfirst;

/**
 * Class QuestionController
 *
 * @package App\Controller
 */
class QuestionController extends AbstractController
{
    /**
     * @param QuestionRepository  $repository
     * @param SerializerInterface $serializer
     * @param Request             $request
     *
     * @return Response
     *
     * @Route("/api/question", methods={"GET"}, name="question_get")
     * @Security("is_granted('ROLE_TEAM_LEAD') or is_granted('ROLE_ANALYST')")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get a paginated list of questions",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Question::class, groups={"read"}))
     *     )
     * )
     * @SWG\Tag(name="question")
     *
     * @Areas({"internal"})
     */
    public function getAction(
        QuestionRepository $repository,
        SerializerInterface $serializer,
        Request $request
    )
    {
        // Get Parameters
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $descending = $request->get('descending', false);
        $descending = $descending == 'true' ? true : false;
        $sort = $request->get('sort', 'orderNumber');
        $search = $request->get('search', '');
        $platform = $request->get('platform', '');

        // Configure Pagination
        $count = $repository->count();
        $offset = ($page - 1) * $limit;
        $questions = $repository->paginated($offset, $limit, $sort, $descending, $search, $platform);
        $pages = (int)ceil($count / $limit);

        $paginatedCollection = new PaginatedRepresentation(
            new CollectionRepresentation(
                $questions,
                'questions',
                'questions'
            ),
            'question_get',
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
                SerializationContext::create()->setGroups(['Default', 'questions' => ['read']])
            ), 200,
            ['Content-type' => 'application/json']
        );
    }

    /**
     * @param QuestionRepository  $repository
     *
     * @param SerializerInterface $serializer
     * @param Validator           $validator
     * @param Request             $request
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     * @Route("/api/question", methods={"POST"}, name="question_post")
     * @Security("is_granted('ROLE_TEAM_LEAD') or is_granted('ROLE_ANALYST')")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Posts to Question.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Question::class, groups={"write"})
     *     )
     * )
     * @SWG\Tag(name="question")
     *
     * @Areas({"internal"})
     */
    public function postAction(
        QuestionRepository $repository,
        SerializerInterface $serializer,
        Validator $validator,
        Request $request,
        ApiErrorsService $apiErrorsService
    )
    {
        /** @var Question $question */
        $question = $serializer->deserialize(
            $request->getContent(),
            Question::class,
            'json',
            DeserializationContext::create()->setGroups(['write'])
        );

        /** @var JsonResponse $response */
        if (($response = $validator->validate($question)) !== false) {
            return $response;
        }

        // Valid Entity
        try {
            $repository->save($question);

            return new Response(
                $serializer->serialize(
                    $question,
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
     * @param QuestionRepository  $repository
     * @param SerializerInterface $serializer
     * @param Validator           $validator
     * @param Request             $request
     * @param Question            $question
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/question/{id}", methods={"PATCH"}, name="question_update")
     * @ParamConverter("question", class="App\Entity\Question")
     * @Security("is_granted('ROLE_TEAM_LEAD') or is_granted('ROLE_ANALYST')")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Update the question entity.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Question::class, groups={"write"})
     *     )
     * )
     * @SWG\Tag(name="question")
     *
     * @Areas({"internal"})
     */
    public function updateAction(
        QuestionRepository $repository,
        SerializerInterface $serializer,
        Validator $validator,
        Request $request,
        Question $question,
        ApiErrorsService $apiErrorsService
    )
    {
        $data = json_decode($request->getContent(), true);
        $data['id'] = $question->getId();

        $oldOrderNumber = $question->getOrderNumber();

        /** @var Question $question */
        $question = $serializer->deserialize(
            json_encode($data),
            Question::class,
            'json',
            DeserializationContext::create()->setGroups(['write'])
        );

        /** @var JsonResponse $response */
        if (($response = $validator->validate($question)) !== false) {
            return $response;
        }

        // Valid Entity
        try {
            $repository->save($question);

            return new Response(
                $serializer->serialize(
                    $repository->getById($question),
                    'json',
                    SerializationContext::create()->setGroups(["read"])
                ),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }

        // orderNumber is updated check to see if should be deleted
//            if ($question->getOrderNumber() !== $oldOrderNumber) {
//                $repository->onOrderNumberUpdate($oldOrderNumber, $question);
//            }

    }

    /**
     * @param QuestionRepository      $repository
     * @param AnswerRepository        $answerRepository
     * @param GlobalWeightsRepository $globalWeightsRepository
     * @param CommentRepository       $commentRepository
     * @param SerializerInterface     $serializer
     * @param Subject                 $subject
     *
     * @param ApiErrorsService        $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/question/investigate/{id}", methods={"GET"}, name="question_get_investigate")
     * @ParamConverter("subject", class="App\Entity\Subject")
     * @Security("is_granted('ROLE_TEAM_LEAD') or is_granted('ROLE_ANALYST')")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get list of questions for investigation",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Question::class, groups={"read"}))
     *     )
     * )
     * @SWG\Tag(name="question")
     *
     * @Areas({"internal"})
     */
    public function investigateAction(
        QuestionRepository $repository,
        AnswerRepository $answerRepository,
        GlobalWeightsRepository $globalWeightsRepository,
        CommentRepository $commentRepository,
        SerializerInterface $serializer,
        Subject $subject,
        ApiErrorsService $apiErrorsService
    )
    {
        try {
            $investigationQuestions = array();

            // Get Questions by subject report type
            $questions = $repository->getByReportType($subject->getReportType());

            /** @var Report $report */
            $report = $subject->getCurrentReport();

            $generalComment = $answerRepository->findBySubject($subject->getId(), '', $report->getId());
            $reportQuestions = $commentRepository->getComments($report->getId());

            $unasnswered = InvestigationHelper::prepareQuestions($globalWeightsRepository, $questions, $subject, $answerRepository, $this->getUser());

            $investigationQuestions['questions'] = $unasnswered;
            if (count($generalComment) > 0) {
                $investigationQuestions['generalComment'] = $generalComment;
            }
            $investigationQuestions['report_question'] = $reportQuestions;

            return new Response(
                $serializer->serialize(
                    $investigationQuestions,
                    'json',
                    SerializationContext::create()->setGroups(["investigate"])
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
     * @param Question            $question
     * @param QuestionRepository  $repository
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/question/{id}", methods={"GET"}, name="question_get_id")
     * @ParamConverter("question", class="App\Entity\Question")
     * @Security("is_granted('ROLE_TEAM_LEAD') or is_granted('ROLE_ANALYST')")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns a specific question.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Question::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="question")
     *
     * @Areas({"internal"})
     */
    public function getIDAction(
        SerializerInterface $serializer,
        Question $question,
        QuestionRepository $repository,
        ApiErrorsService $apiErrorsService
    )
    {
        try {
            return new Response(
                $serializer->serialize(
                    $repository->getById($question),
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
     * @param QuestionRepository  $repository
     * @param SerializerInterface $serializer
     * @param Question            $question
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/question/{id}", methods={"delete"}, name="question_delete")
     * @ParamConverter("question", class="App\Entity\Question")
     *
     * @Security("is_granted('ROLE_TEAM_LEAD') or is_granted('ROLE_ANALYST')")
     *
     * @SWG\Response(
     *     response="200",
     *     description="detele to Question.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Question::class, groups={"write"})
     *     )
     * )
     * @SWG\Tag(name="question")
     *
     * @Areas({"internal"})
     */
    public function deleteAction(
        QuestionRepository $repository,
        SerializerInterface $serializer,
        Question $question,
        ApiErrorsService $apiErrorsService
    )
    {
        try {
            $repository->disable($question);

            return new Response(
                $serializer->serialize(
                    $question,
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
     * @param QuestionRepository  $repository
     * @param SerializerInterface $serializer
     * @param Question            $question
     *
     * @param ApiErrorsService    $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/question/{id}/enable", methods={"PUT"}, name="question_enable")
     * @ParamConverter("question", class="App\Entity\Question")
     * @Security("is_granted('ROLE_TEAM_LEAD') or is_granted('ROLE_ANALYST')")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Enables a question",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Question::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="question")
     *
     * @Areas({"internal"})
     */
    public function enableAction(
        QuestionRepository $repository,
        SerializerInterface $serializer,
        Question $question,
        ApiErrorsService $apiErrorsService
    )
    {
        try {
            $repository->enable($question);

            return new Response(
                $serializer->serialize(
                    $question,
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
     * @param Question $question
     * @param Subject  $subject
     *
     * @return bool
     *
     */
    private function checkPlatformsProfiles(Question $question, Subject $subject)
    {
        $profile = false;
        $platform = $question->getPlatform() === Profile::PLATFORM_ALL ? "" : ucfirst($question->getPlatform());
        $profileFunction = 'get' . $platform . 'Profiles';
        if (count($subject->$profileFunction()) > 0) {
            $profile = true;
        }
        return $profile;
    }

}
