<?php declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Report;
use App\Entity\Question;
use App\Entity\Profile;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use App\Annotation\IsEnabled;
use JMS\Serializer\Annotation\Type;

/**
 * @package App\Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="comments")
 * * @IsEnabled(field="enabled")
 */
class Comment
{
    use TimestampableEntity;

    /**
     * @var string The comment's guid.
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
     * @var string Comment
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Groups({"write", "read"})
     */
    private $comment;

    /**
     * @var Report The report being commented on.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Report", inversedBy="comments")
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"write"})
     */
    private $report;

    /**
     * @var User The user that made this comment.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @Groups({"read"})
     */
    private $commentBy;

    /**
     * @var string Type of comment (approval/normal/null)
     *
     * @ORM\Column(type="string")
     * @Groups({"write", "read"})
     */
    private $commentType = 'normal';

    /**
     * @var string Type of comment approval (yes/no)
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"write", "read"})
     */
    private $approval;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     * @Groups({})
     */
    private $enabled = true;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     * @Groups({"write"})
     */
    private $private = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     * @Groups({"write"})
     */
    private $hidden = false;

    /**
     * @return string
     */
    public function getId(): string
    {
        return (string) $this->id;
    }

    /**
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     *
     * @return Comment
     */
    public function setComment(string $comment): Comment
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return string
     */
    public function getCommentType(): string
    {
        return $this->commentType;
    }

    /**
     * @param string $commentType
     *
     * @return Comment
     * @throws \Exception
     */
    public function setCommentType(string $commentType): Comment
    {
        if (!in_array($commentType, ['normal', 'approval']) && !in_array($commentType, Profile::PLATFORMS)) {
            throw new \Exception('Invalid comment type, please choose between "normal" or "approval" types Or according to valid platforms ');
        }
        $this->commentType = $commentType;

        return $this;
    }

    /**
     * @return Report
     */
    public function getReport(): ?Report
    {
        return $this->report;
    }

    /**
     * @param Report|null $report
     *
     * @return Comment
     */
    public function setReport(Report $report): Comment
    {
        $this->report = $report;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getCommentBy(): ?User
    {
        return $this->commentBy;
    }

    /**
     * @param User $commentBy
     *
     * @return Comment
     */
    public function setCommentBy(User $commentBy): Comment
    {
        $this->commentBy = $commentBy;

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
     * @return Comment
     */
    public function setEnabled(bool $enabled): Comment
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPrivate(): bool
    {
        return $this->private;
    }

    /**
     *
     * @param bool $private
     *
     * @return Comment
     */
    public function setPrivate(bool $private): Comment
    {
        $this->private = $private;

        return $this;
    }

    /**
     * @return bool
     */
    public function isHidden(): bool
    {
        return $this->hidden;
    }

    /**
     * 
     * @param bool $hidden
     * 
     * @return Comment
     */
    public function setHidden(bool $hidden): Comment
    {
        $this->hidden = $hidden;

        return $this;
    }

    /**
     * @return DateTime
     *
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("created_at")
     * @Type("DateTime<'Y-m-d'>")
     * @Groups({"read"})
     */
    public function getCreatedDate(): DateTime
    {
        return $this->getCreatedAt();
    }

    /**
     * @return string
     */
    public function getApproval(): string
    {
        if($this->approval){
            return $this->approval;
        }else{

            return '';
        }

    }

    /**
     * @param string $approval
     *
     * @return Comment
     */
    public function setApproval(string $approval): Comment
    {
        $this->approval = $approval;

        return $this;
    }


}
