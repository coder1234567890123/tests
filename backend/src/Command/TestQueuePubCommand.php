<?php

namespace App\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WindowsAzure\Common\ServicesBuilder;
use WindowsAzure\ServiceBus\Models\BrokeredMessage;
use WindowsAzure\ServiceBus\ServiceBusRestProxy;

/**
 * Class TestQueuePubCommand
 *
 * @package App\Command
 */
class TestQueuePubCommand extends ContainerAwareCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'queue:pub';

    protected function configure()
    {
        $this->setDescription('Test the queue by publishing a message.');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sb      = ServicesBuilder::getInstance()->createServiceBusService(
            "SOMETHING"
        );
        $message = new BrokeredMessage();
        $message->setLabel('Label_test');
        $message->setBody(json_encode([
            'subject_id'    => '4d0d42d7-edd1-4fc0-8451-5d59a15d6cc5',
            'search_phrase' => 'geevcookie',
            'platform'      => 'linkedin'
        ]));

      $sb->sendQueueMessage("test", $message);


    }
}
