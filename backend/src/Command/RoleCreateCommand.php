<?php

namespace App\Command;

use App\Entity\Role;
use App\Entity\RoleGroup;
use App\Repository\RoleGroupRepository;
use App\Repository\RoleRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class RoleCreateCommand
 *
 * @package App\Command
 */
class RoleCreateCommand extends ContainerAwareCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'role:create';

    /**
     * @var RoleGroupRepository
     */
    private $roleGroupRepository;

    /**
     * @var RoleRepository
     */
    private $roleRepository;

    /**
     * UserCreateCommand constructor.
     *
     * @param RoleGroupRepository $roleGroupRepository
     * @param RoleRepository      $roleRepository
     */
    public function __construct(RoleGroupRepository $roleGroupRepository, RoleRepository $roleRepository)
    {
        $this->roleGroupRepository = $roleGroupRepository;
        $this->roleRepository      = $roleRepository;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Create a new role.');
    }

    /**
     * @return void
     */
    private function createDefaultRoles(){
        $roles = array(
            'ROLE_USER_STANDARD',
            'ROLE_USER_MANAGER',
            'ROLE_ADMIN_USER',
            'ROLE_ANALYST',
            'ROLE_TEAM_LEAD',
            'ROLE_SUPER_ADMIN'
        );

        $group = $this->roleGroupRepository->byName('UserGroup');
        if($group && count($this->roleRepository->all()) === 0){
            foreach ($roles as $value) {
                $role = new Role();
                $role->setName($value);
                $role->setValue($value);
                $role->setRoleGroup($group);

                $this->roleRepository->save($role);

            }
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
        $this->createDefaultRoles();

        $defaultRoles = $this->roleRepository->all();
        $roleDisplay = array_map(function (Role $role) {
            return $role->getValue();
        }, $defaultRoles);

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $io     = new SymfonyStyle($input, $output);

        $io->comment('Default Roles Already loaded:');
        $io->comment(implode(',', $roleDisplay));

        // Set the question validator
        $validator = function ($answer) {
            if (!is_string($answer) || trim($answer) == '') {
                throw new \RuntimeException("This value is required!");
            }
            return $answer;
        };

        // Get roles for display.
        $roleGroups   = $this->roleGroupRepository->all();
        $groupDisplay = array_map(function (RoleGroup $roleGroup) {
            return $roleGroup->getName();
        }, $roleGroups);

        // Configure Questions
        $roleGroupQuestion = new ChoiceQuestion(
            'Please select the group that this role will belong to:',
            $groupDisplay
        );
        $roleGroup         = $helper->ask($input, $output, $roleGroupQuestion);
        $nameQuestion      = new Question("Please enter the role's name: ");
        $name              = $helper->ask($input, $output, $nameQuestion->setValidator($validator));
        $valueQuestion     = new Question("Please enter the role's value: ");
        $value             = $helper->ask($input, $output, $valueQuestion->setValidator($validator));

        // Save the User
        try {
            $group = $this->roleGroupRepository->byName($roleGroup);
            $role  = new Role();
            $role
                ->setRoleGroup($group)
                ->setName($name)
                ->setValue($value);

            $this->roleRepository->save($role);

            $io->success("Role Created!");
        } catch (\Exception $e) {
            $io->error($e->getMessage());
        }
    }
}
