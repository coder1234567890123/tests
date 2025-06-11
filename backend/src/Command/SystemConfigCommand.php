<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Repository\SystemConfigRepository;

class SystemConfigCommand extends Command
{
    /**
     * @var SystemConfigRepository
     */
    private $systemConfigRepository;

    /**
     * SystemConfigCommand constructor.
     *
     * @param SystemConfigRepository $systemConfigRepository
     */
    public function __construct(SystemConfigRepository $systemConfigRepository)
    {
        $this->systemConfigRepository = $systemConfigRepository;
        parent::__construct();
    }

    protected static $defaultName = 'app:create-systemconfig';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Creating System Config...");

        $this->systemConfigRepository->create();
    }
}