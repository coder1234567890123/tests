<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResetForTestingCommand extends Command
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
     * ResetForTestingCommand constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected static $defaultName = 'app:reset-for-testing';

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        echo "\n";
        $output->writeln("Reset tables for testing...");

        $this->truncateTable('subjects');
        $this->truncateTable('subject_profiles');
        $this->truncateTable('comments');
        $this->truncateTable('answers');
        $this->truncateTable('proofs');
        $this->truncateTable('proofstorages');
        $this->truncateTable('user_tracking');
        $this->truncateTable('reports');
        $this->truncateTable('qualifications');
        $this->truncateTable('employments');
        $this->truncateTable('company_product');
        $this->truncateTable('message_queues');
        $this->truncateTable('accounts_tracking');
        $this->truncateTable('accounts');
        echo "\n";
    }


    /**
     * @param $table
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function truncateTable($table): void
    {
        try {
            $connection = $this->entityManager->getConnection();
            $platform = $connection->getDatabasePlatform();

            $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 0;');
            $truncateSql = $platform->getTruncateTableSQL($table);
            $connection->executeUpdate($truncateSql);
            $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 1;');

            echo "\n";
            echo "Truncate table: " . $table;
            echo "\n";
        } catch (customException $e) {
            echo $e->errorMessage();
        }
    }
}