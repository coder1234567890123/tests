<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Question;
use App\Entity\Subject;
use App\Exception\AnswerOptionException;
use App\Exception\InvalidAnswerTypeException;
use App\Exception\InvalidPlatformException;
use App\Exception\InvalidReportTypeException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Throwable;

/**
 * Class QuestionRepository
 *
 * @package App\Repository
 */
final class QuestionRepository
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @var TokenStorageInterface
     */
    private $userToken;

    /**
     * QuestionRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param TokenStorageInterface  $token
     */
    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $token)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Question::class);
        $this->userToken = $token->getToken()->getUser();
    }

    /**
     * @param $question
     *
     * @return array
     */
    public function getById($question)
    {
        if ($question) {
            $getQuestion = $this->repository->find($question->getId());

            return [
                "id" => $getQuestion->getId(),
                "slider_values" => $getQuestion->getSliderValues(),
                "platform" => $getQuestion->getPlatform(),
                "enabled" => $getQuestion->isEnabled(),
                "slider" => $getQuestion->isSlider(),
                "slider_average" => $getQuestion->getSliderAverage(),
                "order_number" => $getQuestion->getOrderNumber(),
                "default_questions" => $getQuestion->isDefaultQuestions(),
                "default_name" => $getQuestion->getDefaultName(),
                "report_label" => $getQuestion->getReportLabel(),
                "answer_type" => $getQuestion->getAllReportTypes(),
                'question' => $getQuestion->getQuestion(),
                'answers' => $this->getAnswers($getQuestion->getAnswers()),
                "answer_type" => $getQuestion->getAnswerType(),
                "report_types" => $getQuestion->getReportTypes(),
                "answer_options" => $getQuestion->getAnswerOptions(),
                "answer_score" => $getQuestion->getAnswerScore(),
            ];
        } else {
            return [];
        }
    }

    /**
     * @param $answers
     *
     * @return array
     */
    public function getAnswers($answers)
    {
        $response = [];
        if ($answers) {
            foreach ($answers as $getData) {
                $response[] = [

                    'id' => $getData->getId(),
                    'answer' => $getData->getAnswer(),
                    'slider_value' => $getData->getSliderValue(),
                    'proofs' => $this->getProof($getData->getProofs()),
                    'enabled' => $getData->isEnabled(),
                ];
            }
            return $response;
        } else {
            return [];
        }
    }

    /**
     * @param $answers
     *
     * @return array
     */
    public function getProof($answers)
    {
        $response = [];
        if ($answers) {
            foreach ($answers as $getData) {
                $response[] = [

                    'id' => $getData->getId(),
                ];
            }
            return $response;
        } else {
            return [];
        }
    }


    /**
     * @param string $id
     *
     * @return null|object
     */
    public function find(string $id)
    {
        return $this->repository->find($id);
    }

    /**
     * @return Question[]|array|object[]
     */
    public function all()
    {
        return $this->repository->findAll();
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->repository->count([]);
    }

    /**
     * @param int    $offset
     * @param int    $limit
     * @param string $sort
     * @param bool   $descending
     * @param string $search
     *
     * @return Question[]|array|object[]
     */
    public function paginated(
        int $offset,
        int $limit,
        string $sort,
        bool $descending,
        string $search,
        string $platform
    )
    {
        // Find Sort
        switch ($sort) {
            case 'question':
                $sort = 'question';
                break;
            case 'orderNumber':
            default:
                $sort = 'orderNumber';
                break;
        }

        $qb = $this->repository->createQueryBuilder('s');

        if ($platform !== '') {
            $qb
                ->where('s.platform = :platform')
                ->setParameter('platform', $platform);
        } else if ($search !== '') {
            $qb
                ->where('s.question LIKE :query')
                ->setParameter('query', "%$search%");
        }

        $qb->orderBy("s.$sort", $descending === true ? 'DESC' : 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $qb->getQuery()->execute();
    }

    /**
     * @param Question $question
     *
     * @throws AnswerOptionException
     * @throws InvalidAnswerTypeException
     * @throws InvalidPlatformException
     * @throws InvalidReportTypeException
     */
    public function enable(Question $question)
    {
        $question->setEnabled(true);

        $this->save($question);
    }

    /**
     * @param Question $question
     *
     * @throws AnswerOptionException
     * @throws InvalidAnswerTypeException
     * @throws InvalidPlatformException
     * @throws InvalidReportTypeException
     */
    public function save(Question $question)
    {
        $question->setPlatform($question->getPlatform());
        $question->setAnswerType($question->getAnswerType());
        $question->setReportTypes($question->getReportTypes());
        $question->setAnswerOptions($question->getAnswerOptions());
        if ($question->isDefaultQuestions()) {
            $question->setDefaultName($question->getDefaultName());
        } else {
            $question->setDefaultName('standard_question');
        }

        $question->setOrderNumber($question->getOrderNumber());
        $this->entityManager->persist($question);
        $this->entityManager->flush();
    }

    /**
     * @param Question $question
     *
     * @throws AnswerOptionException
     * @throws InvalidAnswerTypeException
     * @throws InvalidPlatformException
     * @throws InvalidReportTypeException
     */
    public function disable(Question $question)
    {
        $question->setEnabled(false);

        $this->save($question);
    }

    /**
     * @throws Throwable
     * @var Question $question
     *
     * @var int      $oldOrderNumber
     */
    public function onOrderNumberUpdate(int $oldOrderNumber, Question $question)
    {
        try {
            // begin database transaction
            $this->entityManager->beginTransaction();

            // get all records with greater or equal order
            foreach ($this->getGreaterThanOrder($question->getOrderNumber() <= $oldOrderNumber ? $question->getOrderNumber() : $oldOrderNumber) as $existingQuestion) {
                if ($question->getId() !== $existingQuestion->getId()) {// do not edit order of edited (patch) record
                    if ($existingQuestion->getOrderNumber() === $question->getOrderNumber()) {
                        $existingQuestion->setOrderNumber($oldOrderNumber);// switch order with edited record
                        $this->entityManager->persist($existingQuestion);
                    }
                }
            }

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Throwable $th) {
            $this->entityManager->rollback();
            throw $th;
        }
    }

    /**
     * @return Question[]|array|object[]
     * @var int $orderNumber
     */
    public function getGreaterThanOrder(int $orderNumber)
    {
        $qb = $this->repository->createQueryBuilder('q')
            ->where('q.orderNumber >= :orderNumber')
            ->setParameter('orderNumber', $orderNumber)
            ->andWhere('q.enabled = :enabled')
            ->setParameter('enabled', true)
            ->orderBy('q.orderNumber', 'ASC')
            ->getQuery();

        return $qb->execute();
    }

    /**
     * @throws Throwable
     * @var Question $question
     *
     */
    public function onOrderNumberCreate(Question $question)
    {
        try {
            // begin database transaction
            $this->entityManager->beginTransaction();

            // get all records with greater or equal order
            foreach ($this->getGreaterThanOrder($question->getOrderNumber()) as $existingQuestion) {
                if ($question->getId() !== $existingQuestion->getId()) {// do not edit order of new record
                    $value = $existingQuestion->getOrderNumber();
                    $value++;
                    $existingQuestion->setOrderNumber($value);// update all other order numbers to match new record
                    $this->entityManager->persist($existingQuestion);
                }
            }

            $this->entityManager->flush();
            $this->entityManager->commit();// apply all changes
        } catch (Throwable $th) {
            $this->entityManager->rollback();
            throw $th;
        }
    }

    /**
     * @param string $reportType Get all questions that match the subject report type
     *
     * @return Question[]|array|object[]
     */
    public function getByReportType(string $reportType)
    {
        $condition = "JSON_SEARCH(q.report_types, 'one', '$reportType') IS NOT NULL";

        $all = Subject::REPORT_TYPE_ALL;
        $conditionAll = "JSON_SEARCH(q.report_types, 'one', '$all') IS NOT NULL";

        $sql = "SELECT * FROM questions q 
                WHERE q.enabled = true 
                AND  ($condition) OR ($conditionAll)     
                ORDER BY q.order_number ASC";

        $resultMapping = new ResultSetMappingBuilder($this->entityManager);
        $resultMapping->addRootEntityFromClassMetadata('App\Entity\Question', 'q'); // map results to question class

        $nativeQuery = $this->entityManager->createNativeQuery($sql, $resultMapping);
        return $nativeQuery->getResult();
    }

    /**
     * @param string $reportType Get all questions that match the subject report type and report section
     *
     * @return Question[]|array|object[]
     */
    public function getByReportTypeAndReportSection(string $reportType, string $reportSectionId)
    {
        $condition = "JSON_SEARCH(q.report_types, 'one', '$reportType') IS NOT NULL";
        $sql = "SELECT * FROM questions q WHERE ($condition) AND q.report_section_id = '$reportSectionId' AND q.enabled = true ORDER BY q.order_number ASC";

        $resultMapping = new ResultSetMappingBuilder($this->entityManager);
        $resultMapping->addRootEntityFromClassMetadata('App\Entity\Question', 'q'); // map results to question class

        $nativeQuery = $this->entityManager->createNativeQuery($sql, $resultMapping);
        return $nativeQuery->getResult();
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    public function getScore($data)
    {
        $questions = $this->repository->find($data['question']['id']);

        foreach ($questions->getAnswerOptions() as $key => $getData) {
            if ($getData === $data['answer']) {
                return $this->showScore($key, $data['question']['id']);
            }
        }
    }

    /**
     * @param $key
     * @param $id
     *
     * @return mixed
     */
    public function showScore($key, $id)
    {
        $questions = $this->repository->find($id);

        return $questions->getAnswerScore()[$key];
    }

}
