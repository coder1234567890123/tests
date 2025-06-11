<?php declare(strict_types=1);

namespace App\Entity;

use App\Exception\InvalidPlatformException;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Groups;

/**
 * Class Profile
 *
 * @package App\Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="subject_profiles")
 */
class Profile
{
    // Valid Platforms
    const PLATFORM_TWITTER   = 'twitter';
    const PLATFORM_FACEBOOK  = 'facebook';
    const PLATFORM_INSTAGRAM = 'instagram';
    const PLATFORM_PINTEREST = 'pinterest';
    const PLATFORM_LINKEDIN  = 'linkedin';
    const PLATFORM_FLICKR    = 'flickr';
    const PLATFORM_YOUTUBE   = 'youtube';
    const PLATFORM_ALL       = 'all';
    const PLATFORM_WEB       = 'web';

    // Validation Map
    const PLATFORMS = [
        self::PLATFORM_TWITTER,
        self::PLATFORM_FACEBOOK,
        self::PLATFORM_INSTAGRAM,
        self::PLATFORM_PINTEREST,
        self::PLATFORM_LINKEDIN,
        self::PLATFORM_FLICKR,
        self::PLATFORM_YOUTUBE,
        self::PLATFORM_WEB
    ];

    /**
     * @var string The subject's guid.
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
     * @var Subject
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Subject", inversedBy="profiles")
     * @Serializer\Exclude()
     */
    private $subject;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Groups({"write", "read"})
     */
    private $platform;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"write", "read"})
     */
    private $firstName;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"write", "read"})
     */
    private $lastName;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"write", "read"})
     */
    private $emailAddress;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"write", "read"})
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     * @Groups({"write", "read"})
     */
    private $link;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string")
     * @Groups({"read"})
     */
    private $phrase;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"read", "write"})
     */
    private $priority = 0;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     * @Groups({"read"})
     */
    private $valid = false;

    /**
     * @return string
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
     * @return Profile
     */
    public function setSubject(Subject $subject): Profile
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return string
     */
    public function getPlatform(): string
    {
        return $this->platform;
    }

    /**
     * @param string $platform
     *
     * @return Profile
     * @throws InvalidPlatformException
     */
    public function setPlatform(string $platform): Profile
    {
        if (!in_array($platform, self::PLATFORMS)) {
            throw new InvalidPlatformException();
        }

        $this->platform = $platform;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @param string|null $firstName
     *
     * @return Profile
     */
    public function setFirstName(?string $firstName): Profile
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @param string|null $lastName
     *
     * @return Profile
     */
    public function setLastName(?string $lastName): Profile
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    /**
     * @param string|null $emailAddress
     *
     * @return Profile
     */
    public function setEmailAddress(?string $emailAddress): Profile
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param string|null $phone
     *
     * @return Profile
     */
    public function setPhone(?string $phone): Profile
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return string
     */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * @param string $link
     *
     * @return Profile
     */
    public function setLink(string $link): Profile
    {
        $this->link = $link;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhrase(): ?string
    {
        return $this->phrase;
    }

    /**
     * @param string $phrase
     *
     * @return Profile
     */
    public function setPhrase(string $phrase): self
    {
        $this->phrase = $phrase;

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
     * @return Profile
     */
    public function setPriority(int $priority): Profile
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     *
     * @param bool $valid
     *
     * @return Profile
     */
    public function setValid(bool $valid): Profile
    {
        $this->valid = $valid;

        return $this;
    }
}
