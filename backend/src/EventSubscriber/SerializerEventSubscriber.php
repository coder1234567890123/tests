<?php declare(strict_types=1);

namespace App\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\EventDispatcher\PreDeserializeEvent;
use JMS\Serializer\Metadata\PropertyMetadata;

/**
 * Class SerializerEventSubscriber
 *
 * @package App\EventSubscriber
 */
class SerializerEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * SerializerEventSubscriber constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns the events to which this class has subscribed.
     *
     * Return format:
     *     array(
     *         array('event' => 'the-event-name', 'method' => 'onEventName', 'class' => 'some-class', 'format' => 'json'),
     *         array(...),
     *     )
     *
     * The class may be omitted if the class wants to subscribe to events of all classes.
     * Same goes for the format key.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            [
                'event'    => 'serializer.pre_deserialize',
                'method'   => 'onPreDeserialize',
                'format'   => 'json',
                'priority' => 0
            ],
            [
                'event'    => 'serializer.post_deserialize',
                'method'   => 'onPostDeserialize',
                'format'   => 'json',
                'priority' => 0
            ]
        ];
    }

    /**
     * @param PreDeserializeEvent $event
     */
    public function onPreDeserialize(PreDeserializeEvent $event)
    {
        $data      = $event->getData();
        $type      = $event->getType();
        $relations = [];

        if (is_array($data)) {
            $metaData = $event->getContext()->getMetadataFactory()->getMetadataForClass($event->getType()['name']);

            // Preload All Relations
            foreach ($metaData->propertyMetadata as $item) {
                if ($item instanceof PropertyMetadata && isset($data[$item->name])) {
                    if (is_array($item->type) && strstr($item->type['name'], 'App\Entity') !== false) {
                        // Skip Embeddable
                        if (strstr($item->reflection->getDocComment(), 'ORM\Embedded') !== false) {
                            continue;
                        }

                        $object = $this->entityManager->getRepository($item->type['name'])->find(
                            $data[$item->name]
                        );

                        $relations[$item->name] = $object;
                        unset($data[$item->name]);
                        //$data[$item->name] = $object;
                    }
                }
            }

            $event->setType($type['name'], array_merge($relations, $type['params']));
            $event->setData($data);
        }
    }

    /**
     * @param ObjectEvent $event
     */
    public function onPostDeserialize(ObjectEvent $event)
    {
        $object = $event->getObject();

        foreach ($event->getType()['params'] as $name => $param) {
            $setter = 'set' . ucfirst($name);
            if (method_exists($object, $setter)) {
                $object->$setter($param);
            }
        }
    }
}