<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

use Symfony\Component\Console\Command\Command;

/**
 * Class UserCreateCommand
 *
 * @package App\Command
 */
class UserCreateCommand extends ContainerAwareCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'user:create';

    /**
     * @var UserRepository
     */
    private $repository;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * UserCreateCommand constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(User::class);
        parent::__construct();
    }


    protected function configure()
    {
        $this
            ->setDescription('Create a new user.')
            ->addOption('admin', 'a', InputOption::VALUE_NONE, 'Makes the user an admin');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $io = new SymfonyStyle($input, $output);

        // Set the question validator
        $validator = function ($answer) {
            if (!is_string($answer) || trim($answer) == '') {
                throw new \RuntimeException("This value is required!");
            }
            return $answer;
        };

        // Configure Questions
        $emailQuestion = new Question("Please enter the user's email: ");
        $email = $helper->ask($input, $output, $emailQuestion->setValidator($validator));
        $passwordQuestion = new Question("Please enter the user's password: ");
        $password = $helper->ask($input, $output, $passwordQuestion->setValidator($validator)->setHidden(true));
        $firstNameQuestion = new Question("Please enter the user's first name: ");
        $firstName = $helper->ask($input, $output, $firstNameQuestion->setValidator($validator));
        $lastNameQuestion = new Question("Please enter the user's last name: ");
        $lastName = $helper->ask($input, $output, $lastNameQuestion->setValidator($validator));

        // Save the User
        try {

            $user = new User();
            $user
                ->setEnabled(true)
                ->setEmail($email)
                ->setPassword($password)
                ->setFirstName($firstName)
                ->setLastName($lastName)
                ->addRole("ROLE_SUPER_ADMIN");

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $io->success("User Created!");
        } catch (\Exception $e) {
            $io->error($e->getMessage());
        }
    }
}
