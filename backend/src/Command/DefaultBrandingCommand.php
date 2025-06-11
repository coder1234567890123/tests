<?php

namespace App\Command;

use App\Repository\DefaultBrandingRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DefaultBrandingCommand extends Command
{
    /**
     * @var DefaultBrandingRepository
     */
    private $defaultBrandingRepository;

    /**
     * DefaultBrandingCommand constructor.
     *
     * @param DefaultBrandingRepository $defaultBrandingRepository
     */
    public function __construct(DefaultBrandingRepository $defaultBrandingRepository)
    {
        $this->defaultBrandingRepository = $defaultBrandingRepository;
        parent::__construct();
    }

    protected static $defaultName = 'app:create-defaultbranding';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Creating Default Branding...");

        $this->defaultBrandingRepository->create();
    }
}