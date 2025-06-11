<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\MessageQueue;
use App\Entity\Phrase;
use App\Entity\Profile;
use App\Entity\Subject;
use App\Repository\MessageQueueRepository;
use App\Repository\PhraseRepository;
use App\Repository\ProfileRepository;
use App\Repository\SubjectRepository;
use App\Service\SearchPhrase\Parser;
use Exception;
use phpDocumentor\Reflection\Types\Null_;
use WindowsAzure\Common\ServicesBuilder;
use WindowsAzure\ServiceBus\Internal\IServiceBus;
use WindowsAzure\ServiceBus\Models\BrokeredMessage;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class EventService
 *
 * @package App\Service
 */
class EventService
{
    /**
     * @var IServiceBus
     */
    private $serviceBus;

    /**
     * @var PhraseRepository
     */
    private $repository;

    /**
     * @var SubjectRepository
     */
    private $subjectRepository;

    /**
     * @var ProfileRepository
     */
    private $profileRepository;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    /**
     * EventService constructor.
     *
     * @param PhraseRepository      $repository
     * @param SubjectRepository     $subjectRepository
     * @param ProfileRepository     $profileRepository
     * @param Parser                $parser
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(
        PhraseRepository $repository,
        SubjectRepository $subjectRepository,
        ProfileRepository $profileRepository,
        MessageQueueRepository $messageQueueRepository,
        Parser $parser,
        ParameterBagInterface $parameterBag
    )
    {
        $this->parameterBag = $parameterBag;
        if ($this->parameterBag->get('SERVICE_BUS_ACTIVE') != false) {
            $this->serviceBus = ServicesBuilder::getInstance()->createServiceBusService(
                $parameterBag->get('SERVICE_BUS_URL')
            );
        }

        $this->repository = $repository;
        $this->subjectRepository = $subjectRepository;
        $this->profileRepository = $profileRepository;
        $this->messageQueueRepository = $messageQueueRepository;

        $this->parser = $parser;
    }

    /**
     * @param Subject $subject
     *
     * @throws SearchPhrase\Exception\InvalidTokenException
     * @throws Exception
     */
    public function queue(Subject $subject)
    {
        $phrases = $this->repository->enabled();

        /** @var Phrase $phrase */
        foreach ($phrases as $phrase) {
            $phraseText = $this->parser->replace($subject, $phrase->getPhrase());

            if ($phraseText === null) continue;

            $token = date("dmYhis");
            $b = bin2hex(random_bytes(5));

            // Send EventBus Message
            $message = new BrokeredMessage();
            $message->setBody(json_encode([
                'subject_id' => $subject->getId(),
                'search_phrase' => $phraseText,
                'platform' => $phrase->getSearchType(),
                'priority' => $phrase->getPriority(),
                'token' => $b . '-' . $token
            ]));

            $queue = $this->parameterBag->get('SERVICE_BUS_QUEUE');
            
            // Updates Message Queue of messages sent
            $messageQueue = new MessageQueue();
            $messageQueue
                ->setSubject($subject)
                ->setSearchType($phrase->getSearchType())
                ->setPhrase($phraseText)
                ->setToken($b . '-' . $token)
                ->setMessageReceived(false)
                ->setOverWritten(false);

            $this->messageQueueRepository->save($messageQueue);

            //Daniel to check
            if ($this->parameterBag->get('SERVICE_BUS_ACTIVE') == false) continue;

            $this->serviceBus->sendQueueMessage($queue, $message);
        }
    }
}
