<?php

namespace App\Command;

use App\Entity\Subject;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DevSubjectsResetCommand extends Command
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
        $this->repository = $entityManager->getRepository(Subject::class);
        parent::__construct();
    }

    protected static $defaultName = 'dev:subject-reset';

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
        $output->writeln("Reset Subjects for testing...");

        $subject = $this->repository->findAll();

        foreach ($subject as $resetSubject) {
            $this->resetSubject($resetSubject->getId());
        }

        $this->truncateTable('subject_profiles');
        $this->truncateTable('message_systems');
        $this->truncateTable('comments');
        $this->truncateTable('answers');
        $this->truncateTable('proofs');
        $this->truncateTable('proofstorages');
        $this->truncateTable('user_tracking');
        $this->truncateTable('reports');
        $this->truncateTable('qualifications');
        $this->truncateTable('employments');
        $this->truncateTable('accounts_tracking');
        $this->truncateTable('accounts');
        $this->truncateTable('message_queues');
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

    public function resetSubject($id)
    {
        //trigger exception in a "try" block
        try {
            $subject = $this->entityManager->getRepository(Subject::class)->find($id);

            if (!$subject) {
                throw $this->createNotFoundException(
                    'No Subject found for id ' . $id
                );
            }
            $subject->setStatus('new_subject');
            $this->entityManager->flush();
        } //catch exception
        catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}