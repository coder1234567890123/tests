<?php declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\AccessType;
use JMS\Serializer\Annotation\Type;

/**
 * Class MessageQueue
 *
 * @package App\Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="message_queues")
 */
class MessageQueue
{

    use TimestampableEntity;

    /**
     * @var string The MessageQueue's guid.
     *
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     * @Serializer\Type("uuid")
     * @Groups({"read"})
     */
    private $id;

    /**
     * @var Subject
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Subject", inversedBy="MessageQueue")
     * @Groups({"read", "write"})
     * @Serializer\Type("App\Entity\Subject")
     */
    private $subject;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100)
     * @Groups({"write", "read"})
     */
    private $searchType;

    /**
     * @var string The search phrase in the form of: [first_name]
     *
     * @ORM\Column(type="string", length=250)
     * @Groups({"write", "read"})
     */
    private $phrase;

    /**
     * @var $token
     *
     * @ORM\Column(type="string")
     * @Groups({"write", "read"})
     */
    private $token;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     * @Groups({"read"})
     */
    private $messageReceived = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     * @Groups({"read"})
     */
    private $overWritten = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     * @Groups({"read"})
     */
    private $systemOverWrite = false;

    /**
     * @return string
     *
     * @Groups({"read"})
     */
    public function getId(): string
    {
        return (string)$this->id;
    }

    /**
     * @return Subject
     */
    public function getSubject(): Subject
    {
        return $this->subject;
    }

    /**
     * @param Subject $subject
     *
     * @return $this
     */
    public function setSubject(Subject $subject): MessageQueue
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhrase(): string
    {
        return $this->phrase;
    }

    /**
     * @param string $phrase
     *
     * @return $this
     */
    public function setPhrase(string $phrase): MessageQueue
    {
        $this->phrase = $phrase;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param $token
     *
     * @return $this
     */
    public function setToken($token): MessageQueue
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return bool
     */
    public function isMessageReceived(): bool
    {
        return $this->messageReceived;
    }

    /**
     * @param bool $messageReceived
     *
     * @return $this
     */
    public function setMessageReceived(?bool $messageReceived):? MessageQueue
    {
        $this->messageReceived = $messageReceived;

        return $this;
    }

    /**
     * @return bool
     */
    public function isOverWritten(): bool
    {
        return $this->overWritten;
    }

    /**
     * @param bool $overWritten
     *
     * @return MessageQueue
     */
    public function setOverWritten(?bool $overWritten):? MessageQueue
    {
        $this->overWritten = $overWritten;

        return $this;
    }

    /**
     * @return string
     */
    public function getSearchType(): string
    {
        return $this->searchType;
    }

    /**
     * @param string $searchType
     *
     * @return $this
     */
    public function setSearchType(string $searchType): MessageQueue
    {
        $this->searchType = $searchType;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSystemOverWrite(): bool
    {
        return $this->systemOverWrite;
    }

    /**
     * @param bool $systemOverWrite
     *
     * @return $this
     */
    public function setSystemOverWrite(bool $systemOverWrite): MessageQueue
    {
        $this->systemOverWrite = $systemOverWrite;

        return $this;
    }

}