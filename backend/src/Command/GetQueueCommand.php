<?php

namespace App\Command;

use App\Repository\PhraseRepository;
use App\Repository\ProfileRepository;
use App\Repository\SubjectRepository;
use App\Service\SearchPhrase\Parser;
use Exception;
use MicrosoftAzure\Storage\Common\ServicesBuilder as StorageServiceBuilder;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WindowsAzure\Common\ServicesBuilder;
use WindowsAzure\ServiceBus\Models\BrokeredMessage;
use WindowsAzure\ServiceBus\ServiceBusRestProxy;
use WindowsAzure\ServiceBus\Models\ReceiveMessageOptions;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use MicrosoftAzure\Storage\Queue\Models\PeekMessagesOptions;
use MicrosoftAzure\Storage\Queue\QueueRestProxy;

/**
 * Class TestQueuePubCommand
 *
 * @package App\Command
 */
class GetQueueCommand extends ContainerAwareCommand
{

    /**
     * @var string
     */
    protected static $defaultName = 'queue:get';

    protected function configure()
    {
        $this->setDescription('Getting Test the queue by publishing a message.');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     * @throws Exception
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    )
    {
        $connectionString = "Endpoint=https://qaams00sb-farosian.servicebus.windows.net/;SharedAccessKeyName=RootManageSharedAccessKey;SharedAccessKey=yuaItBYE94JbRpdtLhja1+b/X1yr8xHR0jyxvK25iBQ=";
        $serviceBusRestProxy = ServicesBuilder::getInstance()->createServiceBusService($connectionString);

        try {
            // Set receive mode to PeekLock (default is ReceiveAndDelete)
            $options = new ReceiveMessageOptions();
            $options->setPeekLock();

            for ($i = 1; $i <= 5000; $i++) {
                // Get message.
                $message = $serviceBusRestProxy->receiveQueueMessage("test", $options);

                if ($message) {
                    if ($message->getMessageId()) {
                        echo "MessageID: " . $message->getMessageId() . "\n";
                    } else {
                        echo "MessageID: Empty " . '' . "\n";
                    }

                    if ($message->getBody()) {
                        echo "Body: " . $message->getBody() . "\n";
                    } else {
                        echo "Body: Empty " . '' . "\n";
                    }

                    if ($message->getLabel()) {
                        echo "GetLabel: " . $message->getLabel() . "\n";
                    } else {
                        echo "GetLabel: Empty " . '' . "\n";
                    }
                    
                    if ($message->getLabel() === 'M1') {
                        echo 'M1';
                    }
                }else{
                    echo "Message: Empty " . '' . "\n";
                }
            }

            /*---------------------------
                Process message here.
            ----------------------------*/

            // Delete message. Not necessary if peek lock is not set.
//            echo "Deleting message...<br />";
//            $serviceBusRestProxy->deleteMessage($message);

        } catch (ServiceException $e) {
            // Handle exception based on error codes and messages.
            // Error codes and messages are here:
            // https://docs.microsoft.com/rest/api/storageservices/Common-REST-API-Error-Codes
            $code = $e->getCode();
            $error_message = $e->getMessage();
            echo $code . ": " . $error_message . "<br />";
        }
    }
}
