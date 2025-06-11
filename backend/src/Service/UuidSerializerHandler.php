<?php declare(strict_types=1);

namespace App\Service;

use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * Class UuidSerializerHandler
 *
 * @package App\Service\UuidSerializer\Uuid
 */
class UuidSerializerHandler implements \JMS\Serializer\Handler\SubscribingHandlerInterface
{
    private const TYPE_UUID = 'uuid';

    /**
     * @return string[][]
     */
    public static function getSubscribingMethods(): array
    {
        $methods = [
            [
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'type'      => self::TYPE_UUID,
                'format'    => 'json',
                'method'    => 'serializeUuid',
            ],
            [
                'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                'type'      => self::TYPE_UUID,
                'format'    => 'json',
                'method'    => 'deserializeUuid',
            ]
        ];

        return $methods;
    }

    /**
     * @param JsonDeserializationVisitor $visitor
     * @param mixed                      $data
     * @param mixed[]                    $type
     * @param \JMS\Serializer\Context    $context
     *
     * @return \Ramsey\Uuid\UuidInterface
     */
    public function deserializeUuid(
        JsonDeserializationVisitor $visitor,
        $data,
        array $type,
        Context $context
    ): UuidInterface
    {
        $uuidString = (string) $data;
        if (!Uuid::isValid($uuidString)) {
            throw new InvalidUuidStringException();
        }

        return Uuid::fromString($uuidString);
    }

    /**
     * @param JsonSerializationVisitor   $visitor
     * @param \Ramsey\Uuid\UuidInterface $uuid
     * @param mixed[]                    $type
     * @param Context                    $context
     *
     * @return string|object
     */
    public function serializeUuid(
        JsonSerializationVisitor $visitor,
        UuidInterface $uuid,
        array $type,
        Context $context
    )
    {
        return $uuid->toString();
    }
}