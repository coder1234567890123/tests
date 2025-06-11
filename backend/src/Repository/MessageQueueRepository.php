<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Accounts;
use App\Entity\AccountsTracker;
use App\Entity\Company;
use App\Entity\CompanyProduct;
use App\Entity\MessageQueue;
use App\Entity\MessageSystem;
use App\Entity\Report;
use App\Entity\Subject;
use App\Service\AccountsService;
use App\Controller\CompanyProductRepository;
use App\Repository\MessageSystemRepository;
use App\Service\ApiAccountsService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class AccountsRepository
 *
 * @package App\Repository
 */
final class MessageQueueRepository
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
     * AccountsRepository constructor.
     *
     * @param EntityManagerInterface  $entityManager
     * @param TokenStorageInterface   $token
     * @param ParameterBagInterface   $params
     * @param MessageSystemRepository $messageSystem
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $token,
        ParameterBagInterface $params,
        MessageSystemRepository $messageSystem

    )
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(MessageQueue::class);
        $this->userToken = $token->getToken()->getUser();
        $this->messageSystem = $messageSystem;
    }

    /**
     * @param $messageQueue
     */
    public function save($messageQueue)
    {
        $this->entityManager->persist($messageQueue);
        $this->entityManager->flush();
    }

    /**
     * @param $subject
     *
     * @return array
     */
    public function messageList($subject)
    {
        $response = [];

        $qb = $this->repository->createQueryBuilder('p')
            ->andWhere('p.subject = :subject_id')
            ->setParameter('subject_id', $subject)
            ->getQuery();

        if ($qb->execute()) {
            foreach ($qb->execute() as $getData) {
                $response[] = [
                    'id' => $getData->getId(),
                    'platform' => $getData->getSearchType(),
                    'message_received' => $getData->isMessageReceived(),
                    'over_written' => $getData->isOverWritten(),
                    'system_over_write' => $getData->isSystemOverWrite(),
                    'token' => $getData->getToken(),
                    'created_at' => $getData->getCreatedAt()
                ];
            }

            return $response;
        } else {
            return [];
        }
    }

    /**
     * @param $subject
     *
     * @return array
     */
    public function messageOverwrite($subject)
    {
        if ($subject) {
            $qb = $this->repository->createQueryBuilder('p')
                ->andWhere('p.subject = :subject_id')
                ->setParameter('subject_id', $subject)
                ->getQuery();

            if ($qb->execute()) {
                foreach ($qb->execute() as $getData) {
                    $messageQueue = $this->repository->find($getData->getId());
                    $messageQueue->setOverWritten(true);
                    $this->save($messageQueue);
                }

                $this->updateStatus($subject, 'search_completed');
                $this->messageSystem->messageStatusFilterSave($subject->getCurrentReport());
            }

            return $this->messageList($subject);
        } else {
            return [];
        }
    }


    /**
     * @param $subject
     * @param $statusx
     */
    public function updateStatus($subject, $status)
    {
        if ($subject && $status) {
            $this->updateReportStatus($subject, $status);
            $this->updateSubjectStatus($subject, $status);
        }
    }

    /**
     * @param $subject
     * @param $status
     */
    public function updateReportStatus($subject, $status)
    {
        $this->entityManager->persist($subject);
        if ($subject->getCurrentReport()) {
            /** @var Report $report */
            $report = $subject->getCurrentReport();
            $report->setStatus($status);
            $this->entityManager->persist($report);

            //Daniel To check
            //$this->eventTrackingService->track($action, $user, $userSource, $subject, $report);

        }
        $this->entityManager->flush();
    }

    /**
     * @param $subject
     * @param $status
     */
    public function updateSubjectStatus($subject, $status)
    {
        $subject = $this->entityManager->getRepository(Subject::class)->find($subject);
        $subject->setStatus($status);
        $this->entityManager->flush();
    }

    /**
     * @param $token
     */
    public function messageFound($token)
    {
        $qb = $this->repository->createQueryBuilder('p')
            ->andWhere('p.token = :token_id')
            ->setParameter('token_id', $token)
            ->getQuery();

        if ($qb->execute()) {
            $messageQueue = $this->repository->find($qb->execute()[0]->getId());
            $messageQueue->setMessageReceived(true);
            $this->save($messageQueue);
        }
    }

    /**
     * @param $subject
     */
    public function checkSearchComplete($subject)
    {
        $qb = $this->repository->createQueryBuilder('p')
            ->andWhere('p.subject = :subject_id')
            ->setParameter('subject_id', $subject)
            ->andWhere('p.messageReceived = :received_check')
            ->setParameter('received_check', false)
            ->getQuery();

        if (count($qb->execute()) === 0) {
            if ($subject->getCurrentReport()->getStatus() !== 'search_completed') {
                $this->updateReportStatus($subject, 'search_completed');
                $this->updateSubjectStatus($subject, 'search_completed');

                $this->messageSystem->messageStatusFilterSave($subject->getCurrentReport());
            }
        }
    }


    /**
     * @param $subject
     *
     * @return array
     */
    public function deleteAll($subject)
    {
        if ($subject) {
            $qb = $this->repository->createQueryBuilder('p')
                ->andWhere('p.subject = :subject_id')
                ->setParameter('subject_id', $subject)
                ->getQuery();

            if ($qb->execute()) {
                foreach ($qb->execute() as $getData) {
                    $messageQueue = $this->repository->find($getData->getId());
                    $this->entityManager->remove($messageQueue);

                    $this->entityManager->flush();
                }
            }
        } else {
            return [];
        }
    }

}