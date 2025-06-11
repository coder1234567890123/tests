<?php

namespace App\Command;

use App\Entity\CompanyProduct;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MonthlyAccountsResetCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository
     */
    private $repository;

    /**
     * MonthlyAccountsResetCommand constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(CompanyProduct::class);
        parent::__construct();
    }

    protected static $defaultName = 'cronjob:reset-monthly-accounts';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Monthly Accounts Reset...");

                    $qb = $this->repository->createQueryBuilder('p')
                                ->where('p.monthlyRecurring = :monthly')
                                ->setParameter('monthly', true)
                                ->getQuery();

                            foreach ($qb->execute() as $products){

                                echo  $products->getId();
                            }
    }
}