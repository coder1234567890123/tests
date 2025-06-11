<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class CommentRepository
 *
 * @package App\Repository
 */
final class CommentRepository
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
     * CommentRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param TokenStorageInterface  $token
     */
    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $token)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Comment::class);
        $this->userToken = $token->getToken()->getUser();
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function getComments($id)
    {
        $query = $this->entityManager->createQuery(
            'SELECT p
        FROM App\Entity\Comment p
        WHERE p.report = :id
        AND p.enabled = 1
        AND p.commentType = :commentType'
        )
            ->setParameter('id', $id)
            ->setParameter('commentType', 'normal');

        $response = [];

        if ($query->execute()) {
            foreach ($query->execute() as $getData)
                $response[] = [
                    'id' => $getData->getId(),
                    'comment' => $getData->getComment(),
                    'comment_by' => $getData->getCommentBy()->getFullName()
                ];

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
     * @return Comment[]|array|object[]
     */
    public function all()
    {
        return $this->repository->findAll();
    }

    /**
     * @param $subject
     *
     * @return mixed
     */
    public function getCommentBySubject($subject)
    {
        if (!empty($subject->getCurrentReport())) {
            $qb = $this->repository->createQueryBuilder('p')
                ->where('p.report = :subject_id')
                ->setParameter('subject_id', $subject->getCurrentReport()->getId())
                ->getQuery();

            if (count($qb->execute()) >= 1) {
                return $qb->execute();
            } else {
                return [];
            }
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
     * @return Comment[]|array|object[]
     */
    public function enabled()
    {
        return $this->repository->findBy([
            'enabled' => true,
            'private' => false
        ]);
    }

    /**
     * @param Comment $comment
     */

    public function enable(Comment $comment)
    {
        $comment->setEnabled(true);

        $this->save($comment);
    }

    /**
     * @param Comment $comment
     */

    public function disable(Comment $comment)
    {
        $comment->setEnabled(false);

        $this->save($comment);
    }

    /**
     * @param Comment $comment
     */

    public function delete(Comment $comment)
    {
        $this->entityManager->remove($comment);
        $this->entityManager->flush();
    }

    /**
     * @param Comment $comment
     */
    public function save(Comment $comment)
    {
        $this->entityManager->persist($comment);
        $this->entityManager->flush();
    }

    /**
     * @param Comment $comment
     */
    function hide(Comment $comment)
    {
        $comment->setHidden(false);
        $this->save($comment);
    }

    /**
     * @param Comment $comment
     */
    function show(Comment $comment)
    {
        $comment->setHidden(true);
        $this->save($comment);
    }
}
