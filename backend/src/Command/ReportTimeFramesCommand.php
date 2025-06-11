<?php

// src/Command/CreateUserCommand.php
namespace App\Command;

use App\Repository\ReportTimeFrameRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReportTimeFramesCommand extends Command
{
    /**
     * @var ReportTimeFrameRepository
     */
    private $reportTimeFrameRepository;

    /**
     * ReportTimeFramesCommand constructor.
     *
     * @param ReportTimeFrameRepository $reportTimeFrameRepository
     */
    public function __construct(ReportTimeFrameRepository $reportTimeFrameRepository)
    {
        $this->reportTimeFrameRepository = $reportTimeFrameRepository;
        parent::__construct();
    }

    /**
     * @var string
     */
    protected static $defaultName = 'app:create-report-times';

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Creating Report time frames...");
        $this->reportTimeFrameRepository->create();
    }
}