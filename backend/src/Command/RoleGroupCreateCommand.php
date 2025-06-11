<?php

namespace App\Command;

use App\Entity\RoleGroup;
use App\Repository\RoleGroupRepository;
use function foo\func;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Class RoleGroupCreateCommand
 *
 * @package App\Command
 */
class RoleGroupCreateCommand extends ContainerAwareCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'role:group:create';

    /**
     * @var RoleGroupRepository
     */
    private $roleGroupRepository;

    /**
     * UserCreateCommand constructor.
     *
     * @param RoleGroupRepository $roleGroupRepository
     */
    public function __construct(RoleGroupRepository $roleGroupRepository)
    {
        $this->roleGroupRepository = $roleGroupRepository;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Create a new role group.');
    }

    /**
     * @param $radio
     * @return bool
     */
    private function yesNo($radio)
    {
        if ($radio == "Yes") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return void
     */
    private function createDefaultGroup(){
        $group = $this->roleGroupRepository->byName('UserGroup');
        if(!$group){
            $roleGroup = new RoleGroup();
            $roleGroup->setName('UserGroup');
            $roleGroup->setRadio(true);

            $this->roleGroupRepository->save($roleGroup);
        }
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->createDefaultGroup();

        $defaultGroups = $this->roleGroupRepository->all();
        $roleGroupDisplay = array_map(function (RoleGroup $roleGroup) {
            return $roleGroup->getName();
        }, $defaultGroups);

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $io     = new SymfonyStyle($input, $output);

        $io->comment('Defaut Role Groups Already loaded:');
        $io->comment(implode(',', $roleGroupDisplay));

        // Set the question validator
        $validator = function ($answer) {
            if (!is_string($answer) || trim($answer) == '') {
                throw new \RuntimeException("This value is required!");
            }
            return $answer;
        };


        // Configure Questions
        $nameQuestion = new Question("Please enter the role group's name: ");
        $name         = $helper->ask($input, $output, $nameQuestion->setValidator($validator));


        $helper   = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Please select Radio Flag',
            ['Yes', 'No']

        );
        $question->setErrorMessage('Radio Flag %s is invalid.');

        $radio = $helper->ask($input, $output, $question);
        $output->writeln('You have just selected: ' . $radio);
        $radioAnswer = $this->yesNo($radio);

        // Save the User
        try {
            $roleGroup = new RoleGroup();
            $roleGroup->setName($name);
            $roleGroup->setRadio($radioAnswer);

            $this->roleGroupRepository->save($roleGroup);

            $io->success("Role Group Created!");
        } catch (\Exception $e) {
            $io->error($e->getMessage());
        }
    }
}
