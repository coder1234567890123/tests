<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\ReportTimeFrame;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Class ReportRepository
 *
 * @package App\Repository
 */
final class ReportTimeFrameRepository
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
     * Creates default settings.
     *
     * @return void
     */
    public function create()
    {
        $config = [
            [
                'name' => 'normal',
                'hours' => '72'
            ],
            [
                'name' => 'rush',
                'hours' => '2'
            ],
            [
                'name' => 'test',
                'hours' => '72'
            ]
        ];

        foreach ($config as $data) {
            $reportTimeFrame = new ReportTimeFrame();
            $reportTimeFrame->setName($data['name']);
            $reportTimeFrame->setHours($data['hours']);
            $this->entityManager->persist($reportTimeFrame);
        }

        $this->entityManager->flush();
    }

    /**
     * @return array|object[]
     */
    public function all()
    {
        return $this->repository->findAll();
    }

    /**
     * @param ReportTimeFrame $reporttimeframe
     *
     * @return ReportTimeFrame
     */
    public function save(ReportTimeFrame $reporttimeframe,$days)
    {
        if ($reporttimeframe->getHours() == 0) {
            $daysToHours = $days * 24;

            $reporttimeframe->setHours($daysToHours);
        }

        $this->entityManager->persist($reporttimeframe);
        $this->entityManager->flush();

        return $reporttimeframe;
    }
}
