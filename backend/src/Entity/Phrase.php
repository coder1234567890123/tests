<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Groups;

/**
 * Class Phrase
 *
 * @package App\Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="phrases")
 */
class Phrase
{
    use TimestampableEntity;

    /**
     * @var string The phrase's guid.
     *
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     * @Serializer\Accessor(getter="getId")
     * @Groups({"read"})
     */
    private $id;

    /**
     * @var User The user that created this phrase.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @Groups({"read"})
     */
    private $createdBy;

    /**
     * @var string The search phrase in the form of: [first_name]
     *
     * @ORM\Column(type="string", length=250)
     * @Groups({"write", "read"})
     */
    private $phrase;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100)
     * @Groups({"write", "read"})
     */
    private $searchType;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     * @Groups({"read"})
     */
    private $enabled = true;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     * @Groups({"read"})
     *
     */
    private $archived = false;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"read", "write"})
     */
    private $priority = 0;

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
     * @return User|null
     */
    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    /**
     * @param User $createdBy
     *
     * @return Phrase
     */
    public function setCreatedBy(User $createdBy): Phrase
    {
        $this->createdBy = $createdBy;

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
     * @return Phrase
     */
    public function setPhrase(string $phrase): Phrase
    {
        $this->phrase = $phrase;

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
     * @return Phrase
     */
    public function setSearchType(string $searchType): Phrase
    {
        $this->searchType = $searchType;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     *
     * @param bool $enabled
     *
     * @return Phrase
     */
    public function setEnable(bool $enabled): Phrase
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isArchived(): bool
    {
        return $this->archived;
    }

    /**
     * @param bool $archived
     *
     * @return Phrase
     */
    public function setArchived(bool $archived): Phrase
    {
        $this->archived = $archived;

        return $this;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     *
     * @return Phrase
     */
    public function setPriority(int $priority): Phrase
    {
        $this->priority = $priority;

        return $this;
    }
}
