<?php declare(strict_types=1);

namespace App\Repository;

use App\Contracts\AnswerRepositoryInterface;
use App\Entity\Answer;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class AnswerRepository
 *
 * @package App\Repository
 */
final class AnswerRepository implements AnswerRepositoryInterface
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
     * AnswerRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param TokenStorageInterface  $token
     */
    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $token)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Answer::class);
        $this->userToken = $token->getToken()->getUser();
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
     * @return Answer[]|array|object[]
     */
    public function all()
    {
        return $this->repository->findAll();
    }


    /**
     * @param $answers
     *
     * @return array
     */
    public function getById($answers)
    {
        if ($answers) {
            return [

                "id" => $answers->getId(),
                "answer" => $answers->getAnswer(),
                "slider_value" => $answers->getSliderValue(),
                "question" => $this->getQuestionAnswers($answers->getQuestion())
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
    public function getQuestionAnswers($answers)
    {
        if ($answers) {
            return [
                "id" => $answers->getId(),
                "question" => $answers->getQuestion(),
                "answers" => $this->getAnswers($answers->getAnswers()),
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
        if ($answers) {
            return [
                'id' => $answers[0]->getId(),
                'answer' => $answers[0]->getAnswer(),
                "slider_value" => $answers[0]->getSliderValue()
            ];
        } else {
            return [];
        }
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
     * @return Answer[]|array|object[]
     */
    public function paginated(int $offset, int $limit, string $sort, bool $descending, string $search)
    {
        // Find Sort
        switch ($sort) {
            case 'answer':
            default:
                $sort = 'answer';
                break;
        }

        $qb = $this->repository->createQueryBuilder('s');

        if ($search != '') {
            $qb
                ->where('s.answer LIKE :query')
                ->setParameter('query', "$search%");
        }

        $qb->orderBy("s.$sort", $descending === true ? 'DESC' : 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $qb->getQuery()->execute();
    }

    /**
     * @param string $subjectId
     * @param string $questionId
     * @param string $reportId
     *
     * @return Answer[]|array|object[]
     */
    public function findBySubject($subjectId, $questionId, $reportId)
    {
        $qb = $this->repository->createQueryBuilder('s');
        $qb->where('s.subject = :subject')->setParameter('subject', $subjectId);
        $qb->andWhere('s.report = :report')->setParameter('report', $reportId);

        if ($questionId === '') {
            $qb->andWhere('s.question IS null');
        } else {
            $qb->andWhere('s.question = :question')->setParameter('question', $questionId);
        }

        return $qb->getQuery()->execute();
    }

    /**
     * @param Answer $answer
     */

    public function enable(Answer $answer)
    {
        $answer->setEnabled(true);

        $this->save($answer);
    }

    /**
     * @param Answer $answer
     */
    public function save(Answer $answer)
    {
        $this->entityManager->persist($answer);
        $this->entityManager->flush();
    }

    /**
     * @param Answer $answer
     */

    public function disable(Answer $answer)
    {
        $answer->setEnabled(false);

        $this->save($answer);
    }

    /**
     * @param Answer $answer
     */

    public function skip(Answer $answer)
    {
        $answer->setSkipped(true);

        $this->save($answer);
    }

    /**
     * @param Answer $answer
     */

    public function notApplicable(Answer $answer)
    {
        $answer->setNotApplicable(true);

        $this->save($answer);
    }

    /**
     * @param $subject
     *
     * @return mixed
     */
    public function getSubjectAnswers($subject)
    {
        $qb = $this->repository->createQueryBuilder('p')
            ->andWhere('p.subject = :id')
            ->setParameter('id', $subject->getId())
            ->getQuery();

        return $qb->execute();
    }
}
