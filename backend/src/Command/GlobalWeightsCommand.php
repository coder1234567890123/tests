<?php

namespace App\Command;

use App\Entity\GlobalWeights;
use App\Entity\Profile;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Repository\GlobalWeightsRepository;

class GlobalWeightsCommand extends Command
{
    /**
     * @var GlobalWeightsRepository
     */
    private $globalWeightsRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * GlobalWeightsCommand constructor.
     *
     * @param GlobalWeightsRepository $globalWeightsRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(GlobalWeightsRepository $globalWeightsRepository, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(GlobalWeights::class);

        $this->globalWeightsRepository = $globalWeightsRepository;
        parent::__construct();
    }

    protected static $defaultName = 'app:create-globalweights';

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Creating Global Weights...");

        try {
            $order = 1;

            foreach (Profile::PLATFORMS as $data) {
                $globalWeights = new GlobalWeights();
                $globalWeights->setSocialPlatform($data);
                $globalWeights->setGlobalUsageWeighting(12);
                $globalWeights->setVersion(1);
                $globalWeights->setOrdering($order);
                $globalWeights->setStdComments(['test'.$order]);
                $this->entityManager->persist($globalWeights);
                $order++;
            }

            $this->entityManager->flush();
        } catch (customException $e) {
            echo $e->errorMessage();
        }
    }
}