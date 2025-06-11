<?php

namespace App\Command;

use App\Entity\Question;
use App\Entity\Subject;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class QuestionsMainUpdateCommand extends Command
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
        $this->repository = $entityManager->getRepository(Question::class);
        parent::__construct();
    }

    protected static $defaultName = 'app:questions-update';

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     * @throws \App\Exception\AnswerOptionException
     * @throws \App\Exception\InvalidAnswerTypeException
     * @throws \App\Exception\InvalidPlatformException
     * @throws \App\Exception\InvalidReportTypeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        echo "\n";
        $output->writeln("Updating Questions to default");

        $platforms = [
            'pinterest',
            'twitter',
            'instagram',
            'linkedin',
            'youtube',
            'flickr',
            'facebook',
            'web'
        ];

        //Do they have an account?
        foreach ($platforms as $platform) {
            $info =
                [
                    'report_types' => ["basic", "full", "standard", "high_profile"],
                    'answer_options' => ["Yes, different purpose", "No", "Yes, 2", "Yes, 3", "Yes, 4", "Yes, 5 ", "Yes, more then 5", "Highly negative content"],
                    'answer_score' => ["0", "0", "-1", "-2", "-3", "-4", "-5", "-5"],
                    'default_name' => $platform . '_has_account',
                    'set_question' => 'Do they have an account?',
                    'set_report_label' => 'Do they have an account?',
                    'set_answer_type' => 'multiple_choice',
                    'platform' => $platform,
                    'order_number' => '1'
                ];
            $this->addQuestions($info);
        }

        //Multiple accounts
        foreach ($platforms as $platform) {
            $info =
                [
                    'report_types' => ["basic", "full", "standard", "high_profile"],
                    'answer_options' => ["Yes, different purpose", "No", "Yes, 2", "Yes, 3", "Yes, 4", "Yes, 5 ", "Yes, more then 5", "Highly negative content"],
                    'answer_score' => ["0", "0", "-1", "-2", "-3", "-4", "-5", "-5"],
                    'default_name' => $platform . '_multiple_account',
                    'set_question' => 'Do they have multiple accounts? multiple accounts apply if the accounts are active at the same time and don’t serve different purposes.',
                    'set_report_label' => 'Do they have multiple accounts? multiple accounts apply if the accounts are active at the same time and don’t serve different purposes.',
                    'set_answer_type' => 'multiple_choice',
                    'platform' => $platform,
                    'order_number' => '7'
                ];

            $this->addQuestions($info);
        }

        //How active are they?
        foreach ($platforms as $platform) {
            $info =
                [
                    'report_types' => ["basic", "full", "standard", "high_profile"],
                    'answer_options' => ["None", "High privacy", "Low", "Well below", "Below", "Slightly below", "Average ", "Slightly above", "Above", "Well above", "High", "High, negative", "Average, negative", "Low, negative"],
                    'answer_score' => ["2.95", "2.5", "0.63", "1.26", "1.94", "2.13", "2.5", "2.78", "3.84", "4.26", "5", "0.22", "1.16", "1.99"],
                    'default_name' => $platform . '_active_account',
                    'set_question' => 'How active are they?',
                    'set_report_label' => 'How active are they?',
                    'set_answer_type' => 'multiple_choice',
                    'platform' => $platform,
                    'order_number' => '2'
                ];

            $this->addQuestions($info);
        }

        //Is there negative content?
        foreach ($platforms as $platform) {
            $info =
                [
                    'report_types' => ["basic", "full", "standard", "high_profile"],
                    'answer_options' => ["No negative content. ", "1/2 instances or less severe, nudity, swearing, fake news ", "3/5 instances of less severe, reactive behaviors", "five to ten or severe, controversial issue engagement ", "continuous instances or neg. content against company, boss, co-workers ", "Illegal/discrimination ", "Unintentional discrimination, bad humour", "Unprofessional content, mild but not great"],
                    'answer_score' => ["0", "-1", "-2", "-3", "-4", "-5", "-2.16", "-1.15"],
                    'default_name' => $platform . '_negative_content',
                    'set_question' => 'Is there negative content?',
                    'set_report_label' => 'Is there negative content?',
                    'set_answer_type' => 'multiple_choice',
                    'platform' => $platform,
                    'order_number' => '6'
                ];

            $this->addQuestions($info);
        }

        //Is there positive content?
        foreach ($platforms as $platform) {
            $info =
                [
                    'report_types' => ["basic", "full", "standard", "high_profile"],
                    'answer_options' => ["Neutral", "High privacy", "Inactive", "No beneficial content, selfie profile", "Very limited positive ", "Limited positive", "more then 3 engagement with interests", "more the 5 interests", "active engagement in positive ", "Active in charity / activism / hobby", "multiple activities, interest, engagement ", "Highly positive / professional / personal brand", "Overall unprofessional, negative impression", "Illegal content found", "Discrimination"],
                    'answer_score' => ["2.5", "2.5", "2.83", "1.44", "2.62", "2.78", "2.94", "3.00", "3.61", "3.97", "4.21", "5", "1.99", "0.32", "1.07"],
                    'default_name' => $platform . '_positive_content',
                    'set_question' => 'Is there positive content?',
                    'set_report_label' => 'Is there positive content?',
                    'set_answer_type' => 'multiple_choice',
                    'platform' => $platform,
                    'order_number' => '5'
                ];

            $this->addQuestions($info);
        }

        //What are their privacy settings?
        foreach ($platforms as $platform) {
            $info =
                [
                    'report_types' => ["basic", "full", "standard", "high_profile"],
                    'answer_options' => ["No access, inactive", "Low", "Well below", "Below", "Average", "Above ", "Well above", "High", "Disclosure of confidential", "High privacy, negative ", "Average privacy, negative ", "Low privacy, negative"],
                    'answer_score' => ["5", "2.99", "3.62", "3.83", "4.01", "4.46", "4.73", "5", "2.14", "2.18", "1.22", "0.31"],
                    'default_name' => $platform . '_privacy_settings',
                    'set_question' => 'What are their privacy settings?',
                    'set_report_label' => 'What are their privacy settings?',
                    'set_answer_type' => 'multiple_choice',
                    'platform' => $platform,
                    'order_number' => '3'
                ];

            $this->addQuestions($info);
        }

        //What level of information has been disclosed?
        foreach ($platforms as $platform) {
            $info =
                [
                    'report_types' => ["basic", "full", "standard", "high_profile"],
                    'answer_options' => ["No content disclosed", "High privacy", "only standard identification criteria available ", "date of birth", "email address ", "contact number", "continuous location check-ins", "no warning on harmful or violent content (less sever)", "home address", "medical information ", "child made vulnerable; school, home, in bath", "confidential company content", "Drivers licence image unprotected", "No warning on harmful or violent content (sever)", "ID ", "banking content disclosed", "Illegal behavior", "no warning on harmful or violent content (high severity)", "drug/alcohol abuse (excessive)"],
                    'answer_score' => ["5", "5", "4.99", "4.15", "3.95", "3.82", "3.47", "2.89", "2.34", "1.57", "2.13", "0.81", "0.84", "1.02", "1.98", "0.68", "0.06", "0.23", "0.46"],
                    'default_name' => $platform . '_information_disclosed',
                    'set_question' => 'What level of information has been disclosed? 
                                            (select the highest disclosure answer in the case of multiple infringements)',
                    'set_report_label' => 'What level of information has been disclosed? 
                                            (select the highest disclosure answer in the case of multiple infringements)',
                    'set_answer_type' => 'multiple_choice',
                    'platform' => $platform,
                    'order_number' => '8'
                ];

            $this->addQuestions($info);
        }

        //How many connections?
        foreach ($platforms as $platform) {
            //How many connections?

            $info =
                [
                    'report_types' => ["basic", "full", "standard", "high_profile"],
                    'answer_options' => ["High privacy, not visable", "No connections", "Low connections", "Well below average connections", "Below average connections", "Slightly below average connections", "Average connections.", "Slightly above average connections", "Above average connections", "Well above average connections", "High connections", "High connections, high negative content", "Average connections, high negative content", "Low connections, high negative content"],
                    'answer_score' => ["2.5", "2.97", "0.82", "1.56", "2.00", "2.72", "2.5", "2.72", "3.56", "4.23", "5", "0.19", "0.91", "1.11"],
                    'default_name' => $platform . '_connections',
                    'set_question' => 'How many connections?',
                    'set_report_label' => 'How many connections?',
                    'set_answer_type' => 'multiple_choice',
                    'platform' => $platform,
                    'order_number' => '4'
                ];

            $slider =
                [

                    'facebook' =>
                        [
                            'platform' => 'facebook',
                            'slider' => ["1", "2500"],
                            'average' => '350'
                        ],
                    'linkedin' =>
                        [
                            'platform' => 'linkedin',
                            'slider' => ["1", "500"],
                            'average' => '441'
                        ],
                    'twitter' =>
                        [
                            'platform' => 'twitter',
                            'slider' => ["1", "3000"],
                            'average' => '345'
                        ],
                    'pinterest' =>
                        [
                            'platform' => 'pinterest',
                            'slider' => ["1", "1000"],
                            'average' => '46'
                        ],
                    'instagram' =>
                        [
                            'platform' => 'instagram',
                            'slider' => ["1", "5000"],
                            'average' => '144'
                        ],
                    'youtube' =>
                        [
                            'platform' => 'youtube',
                            'slider' => ["1", "5000"],
                            'average' => '144'
                        ],
                    'flickr' =>
                        [
                            'platform' => 'flickr',
                            'slider' => ["1", "1000"],
                            'average' => '46'
                        ],
                    'web' =>
                        [
                            'platform' => 'flickr',
                            'slider' => ["1", "5000"],
                            'average' => '46'
                        ]
                ];

            $this->addQuestionsSider($info, $slider);
        }
    }

    /**
     * @param $id
     * @param $platform
     *
     * @return string
     */
    private function updateDatabase($id, $info)
    {
        $question = $this->repository->find($id[0]->getId());

        $question->setDefaultName($info['default_name']);
        $question->setDefaultQuestions(true);
        $this->entityManager->flush();
    }


    /**
     * @param $info
     *
     * @throws \App\Exception\AnswerOptionException
     * @throws \App\Exception\InvalidAnswerTypeException
     * @throws \App\Exception\InvalidPlatformException
     * @throws \App\Exception\InvalidReportTypeException
     */
    private function addQuestions($info)
    {
        $question = new Question();
        $question->setQuestion($info['set_question']);
        $question->setReportLabel($info['set_question']);
        $question->setAnswerType($info['set_answer_type']);
        $question->setReportTypes($info['report_types']);
        $question->setAnswerOptions($info['answer_options']);
        $question->setAnswerScore($info['answer_score']);
        $question->setEnabled(true);
        $question->setSliderValues([]);
        $question->setOrderNumber($info['order_number']);
        $question->setPlatform($info['platform']);
        $question->setSlider(false);
        $question->setDefaultName($info['default_name']);
        $question->setDefaultQuestions(true);

        $this->entityManager->persist($question);
        $this->entityManager->flush();
    }

    /**
     * @param $info
     *
     * @throws \App\Exception\AnswerOptionException
     * @throws \App\Exception\InvalidAnswerTypeException
     * @throws \App\Exception\InvalidPlatformException
     * @throws \App\Exception\InvalidReportTypeException
     */
    private function addQuestionsSider($info, $slider)
    {
        if ($slider[$info['platform']]) {
            $question = new Question();
            $question->setQuestion($info['set_question']);
            $question->setReportLabel($info['set_question']);
            $question->setAnswerType($info['set_answer_type']);
            $question->setReportTypes($info['report_types']);
            $question->setAnswerOptions($info['answer_options']);
            $question->setAnswerScore($info['answer_score']);
            $question->setEnabled(true);
            $question->setSliderValues($slider[$info['platform']]['slider']);
            $question->setSliderAverage($slider[$info['platform']]['average']);
            $question->setOrderNumber($info['order_number']);
            $question->setPlatform($info['platform']);
            $question->setSlider(true);
            $question->setDefaultName($info['default_name']);
            $question->setDefaultQuestions(true);

            $this->entityManager->persist($question);
            $this->entityManager->flush();
        }
    }

}