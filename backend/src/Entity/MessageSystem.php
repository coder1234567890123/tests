<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation\Groups;
use phpDocumentor\Reflection\Types\Boolean;
use App\Exception\InvalidCurrentStatusTypeException;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use App\Exception\InvalidReportTypeException;

/**
 *
 * @package App\Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="message_systems")
 */
class MessageSystem
{
    use TimestampableEntity;

    /**
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
     * @var Company The CompanyProduct's company.
     *
     * @ORM\ManyToOne(targetEntity="Company")
     * @Groups({"read","write"})
     */
    private $company;

    /**
     * @var Subject
     *
     * @ORM\ManyToOne(targetEntity="Subject")
     * @Groups({"read", "write"})
     * @Serializer\Type("Subject")
     */
    private $subject;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @Groups({"read", "write"})
     * @ORM\JoinColumn(nullable=true)
     * @Serializer\Type("App\Entity\User")
     */
    private $user;

    /**
     * @var User The user assigned to this report.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"write"})
     */
    private $assignedTo;

    /**
     * @var string.
     *
     * @ORM\Column(type="string")
     * @Groups({"write", "read"})
     */
    private $messageHeader;

    /**
     * @var string.
     *
     * @ORM\Column(type="string")
     * @Groups({"write", "read"})
     */
    private $message;

    /**
     * @var string.
     *
     * @ORM\Column(type="string")
     * @Groups({"write", "read"})
     */
    private $messageType;

    /**
     * This property is used by the marking store
     *
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"write", "read"})
     */
    private $status;

    /**
     * @var User The user assigned to this report.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"write"})
     */
    private $teamLead;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @Groups({"read", "write"})
     * @ORM\JoinColumn(nullable=true)
     * @Serializer\Type("App\Entity\User")
     */
    private $messageFor;


    /**
     * @return string
     */
    public function getId(): string
    {
        return (string)$this->id;
    }

    /**
     * @var boolean.
     *
     * @ORM\Column(type="boolean")
     * @Groups({"write", "read"})
     */
    private $messageRead = false;

    /**
     * @return Company
     */
    public function getCompany(): Company
    {
        return $this->company;
    }

    /**
     * @param Company $company
     *
     * @return MessageSystem
     */
    public function setCompany(Company $company): MessageSystem
    {
        $this->company = $company;

        return $this;
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
     * @return MessageSystem
     */
    public function setSubject(Subject $subject): MessageSystem
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     *
     * @return MessageSystem
     */
    public function setMessage(string $message): MessageSystem
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessageType(): string
    {
        return $this->messageType;
    }

    /**
     * @param string $messageType
     *
     * @return MessageSystem
     */
    public function setMessageType(string $messageType): MessageSystem
    {
        $this->messageType = $messageType;

        return $this;
    }

    /**
     * @return bool
     */
    public function getMessageRead(): Boolean
    {
        return $this->messageRead;
    }

    /**
     * @param bool $messageRead
     *
     * @return MessageSystem
     */
    public function setMessageRead(bool $messageRead): MessageSystem
    {
        $this->messageRead = $messageRead;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return MessageSystem
     */
    public function setUser(?User $user): MessageSystem
    {
        $this->user = $user;

        return $this;
    }


    /**
     * @return string
     */
    public function getMessageHeader(): string
    {
        return $this->messageHeader;
    }

    /**
     * @param string $messageHeader
     *
     * @return $this
     */
    public function setMessageHeader(string $messageHeader): MessageSystem
    {
        $this->messageHeader = $messageHeader;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string|null $status
     *
     * @return $this
     */
    public function setStatus(?string $status): MessageSystem
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getAssignedTo(): ?User
    {
        return $this->assignedTo;
    }

    /**
     * @return string|null
     *
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("assigned_to")
     * @Groups({"queued"})
     */
    public function getAssignedToName(): ?string
    {
        if ($this->getAssignedTo()) {
            return $this->getAssignedTo()->getFullName();
        }

        return null;
    }

    /**
     * @param User $assignedTo
     *
     * @return MessageSystem
     */
    public function setAssignedTo(?User $assignedTo): MessageSystem
    {
        $this->assignedTo = $assignedTo;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getTeamLead(): ?User
    {
        return $this->teamLead;
    }

    /**
     * @param User|null $teamLead
     *
     * @return $this
     */
    public function setTeamLead(?User $teamLead): MessageSystem
    {
        $this->teamLead = $teamLead;

        return $this;
    }

    /**
     * @return User
     */
    public function getMessageFor(): User
    {
        return $this->messageFor;
    }

    /**
     * @param User $messageFor
     *
     * @return $this
     */
    public function setMessageFor(User $messageFor): MessageSystem
    {
        $this->messageFor = $messageFor;

        return $this;
    }


}