<?php declare(strict_types=1);

namespace App\Service\ReportTimeFrames;

use App\Entity\ReportTimeFrame;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Class TimeFramesService
 *
 * @package App\Service
 */
class TimeFramesService
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
     * ReportRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(ReportTimeFrame::class);
    }

    /**
     * @param $requestType
     *
     * @return false|string
     */
    public function getTimeFrame($requestType)
    {
        //fixes test time frame issue
        if ($requestType === 'new_test') {
            $requestType = 'test';
        }

        $db = $this->repository->createQueryBuilder('a')
            ->where('a.name = :name')
            ->setParameter('name', $requestType)
            ->getQuery()
            ->execute();

        $response = date("Y-m-d H:i:s", strtotime("+" . $db[0]->getHours() . " hours"));

        return $response;
    }
}
