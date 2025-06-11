<?php

declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Answer;
use App\Entity\Comment;
use App\Entity\Subject;
use App\Entity\Question;
use App\Entity\EmailTracker;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Type;

/**
 * @package App\Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="reports")
 */
class Report
{
    // Valid report statuses
    const REPORT_NEW = 'new_request';
    const REPORT_UNASSIGNED = 'unassigned';
    const REPORT_SEARCH_START = 'search_started';
    const REPORT_SEARCH_COMPLETE = 'search_completed';
    const REPORT_VALID = 'validated';
    const REPORT_TYPE_APPROVE = 'report_type_approved';
    const REPORT_INVESTIGATE = 'under_investigation';
    const REPORT_INVESTIGATE_COMPLETED = 'investigation_completed';
    const REPORT_TEAM_LEAD = 'team_lead_approved';
    const REPORT_COMPLETE = 'completed';
    const REPORT_ABANDON = 'abandoned';

    // Valid brandingTypes
    const BRANDING_TYPE_DEFAULT = 'default';
    const BRANDING_TYPE_WHITE_LABEL = 'white_label';
    const BRANDING_TYPE_CO_BRANDED = 'co_branded';

    const BRANDING_TYPES = [
        Report::BRANDING_TYPE_DEFAULT,
        Report::BRANDING_TYPE_WHITE_LABEL,
        Report::BRANDING_TYPE_CO_BRANDED
    ];

    const REPORT_STATUSES = [
        Report::REPORT_NEW,
        Report::REPORT_UNASSIGNED,
        Report::REPORT_SEARCH_START,
        Report::REPORT_SEARCH_COMPLETE,
        Report::REPORT_VALID,
        Report::REPORT_TYPE_APPROVE,
        Report::REPORT_INVESTIGATE,
        Report::REPORT_INVESTIGATE_COMPLETED,
        Report::REPORT_TEAM_LEAD,
        Report::REPORT_COMPLETE,
        Report::REPORT_ABANDON
    ];
    use TimestampableEntity;

    /**
     * @var string The report's guid.
     *
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     * @Serializer\Type("uuid")
     * @Groups({"read", "report", "queued"})
     */
    private $id;

    /**
     * @var string Unique report name/sequence.
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Groups({"write", "read", "report", "queued"})
     */
    private $sequence;

    /**
     * @var User The user assigned to this report.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"write"})
     */
    private $assignedTo;

    /**
     * @var User The user assigned to this report.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"write", "read"})
     */
    private $approvedBy;

    /**
     *
     * @ORM\Column(type="string")
     * @Groups({"write", "read", "queued", "queued_write"})
     */
    private $requestType;

    /**
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"write", "read", "queued", "queued_write"})
     */
    private $risk;

    /**
     * @var float
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"write", "read", "queued_write"})
     */
    private $riskScore;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"write", "read", "queued"})
     */
    private $completedDate = Null;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime" , nullable=true)
     * @Groups({"write", "read", "queued"})
     * @Type("DateTime<'Y-m-d H:i:s'>")
     */
    private $dueDate;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Answer", mappedBy="report")
     * @Groups({"report"})
     */
    private $answers;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="report")
     * @Groups({"report", "read"})
     */
    private $comments;

    /**
     * @var array Report scores.
     *
     * @ORM\Column(type="json", nullable=true)
     * @Groups({"write", "report"})
     */
    private $reportScores = null;

    /**
     * @var array Report scoresUpdated.
     *
     * @Type("array")
     *
     * @ORM\Column(type="json", nullable=true)
     * @Groups({"write", "report"})
     */
    private $reportScoresUpdated = [];

    /**
     * @var User The user that created the report.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @Groups({"write"})
     */
    private $user;

    /**
     * @var Company|null The Report's company.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Company")
     * @Serializer\Type("App\Entity\Company")
     * @Groups({"write"})
     */
    private $company;

    /**
     * This property is used by the marking store
     *
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"read", "queued"})
     */
    private $status;

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
     * @Groups({"read","write"})
     */
    private $overWriteReportScores = false;


    /**
     * @var array Report scores.
     *
     * @ORM\Column(type="json", nullable=true)
     * @Groups({"write", "report", "read"})
     */
    private $socialMediaScores = [];


    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     * @Groups({"read"})
     */
    private $open = true;

    /**
     * @var Subject The subject this report is based on.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Subject", inversedBy="reports")
     * @Groups({"write", "queued"})
     */
    private $subject;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     * @Groups({"read"})
     */
    private $hideGeneralComments = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     * @Groups({"read"})
     */
    private $hideReportScore = false;

    /**
     * @var int Options 0 => new,  1 => duplicate, 2 => duplicate with new search
     *
     * @ORM\Column(type="integer")
     * @Groups({"write", "read", "queued"})
     */
    private $optionValue = 0;

    /**
     * @var string
     *
     * @ORM\Column(type="text" , nullable=true)
     * @Groups({"write", "read"})
     */
    private $riskComment = null;

    /**
     * @var string report pdf storage location.
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"write", "read", "report"})
     */
    private $blobUrl;

    /**
     * @var string report pdf storage file Name.
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"write", "read", "report"})
     */
    private $pdfFilename;

    /**
     * Report constructor.
     */
    public function __construct()
    {
        $this->answers = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    /**
     * @return Collection
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    /**
     * @param Answer $answer
     *
     * @return Report
     */
    public function addAnswer(Answer $answer): Report
    {
        $this->answers->add($answer);

        return $this;
    }

    /**
     * @return Collection
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    /**
     * @param Comment $comment
     *
     * @return Report
     */
    public function addComment(Comment $comment): Report
    {
        $this->comments->add($comment);

        return $this;
    }

    /**
     * @return string
     */
    public function getSequence(): string
    {
        return $this->sequence;
    }

    /**
     * @param string $sequence
     *
     * @return Report
     */
    public function setSequence(string $sequence): Report
    {
        $this->sequence = $sequence;

        return $this;
    }

    /**
     * @return string
     */
    public function getBlobUrl(): ?string
    {
        return $this->blobUrl;
    }

    /**
     * @param string $url
     *
     * @return Report
     */
    public function setBlobUrl(string $url): Report
    {
        $this->blobUrl = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return (string)$this->id;
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
     * @return Report
     */
    public function setUser(User $user): Report
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string|null
     *
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("user")
     * @Groups({"read", "queued"})
     */
    public function getUserName(): ?string
    {
        if ($this->getUser()) {
            return $this->getUser()->getFullName();
        }

        return null;
    }

    /**
     * @return array|null
     */
    public function getReportScores(): ?array
    {
        return $this->reportScores;
    }

    /**
     * @param array $reportScores
     *
     * @return Report
     */
    public function setReportScores(array $reportScores): Report
    {
        $this->reportScores = $reportScores;

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
     * @return Report
     */
    public function setEnabled(bool $enabled): Report
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function isOpen(): bool
    {
        return $this->open;
    }

    /**
     *
     * @param bool $open
     *
     * @return Report
     */
    public function setOpen(bool $open): Report
    {
        $this->open = $open;

        return $this;
    }

    /**
     * @return Subject
     */
    public function getSubject(): ?Subject
    {
        return $this->subject;
    }

    /**
     * @param Subject $subject
     *
     * @return Report
     */
    public function setSubject(?Subject $subject): Report
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return string|null
     *
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("subject")
     * @Groups({"read"})
     */
    public function getSubjectName(): ?string
    {
        if ($this->getSubject()) {
            return $this->getSubject()->getFirstName() . ' ' . $this->getSubject()->getLastName();
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     *
     * @param string $status
     *
     * @return Report
     */
    public function setStatus(string $status): Report
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return bool
     */
    public function getHideGeneralComments(): bool
    {
        return $this->hideGeneralComments;
    }

    /**
     * @param bool $hide
     *
     * @return $this
     */
    public function setHideGeneralComments(bool $hide): Report
    {
        $this->hideGeneralComments = $hide;

        return $this;
    }

    /**
     * @return bool
     */
    public function getHideReportScore(): bool
    {
        return $this->hideReportScore;
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
     * @return Report
     */
    public function setAssignedTo(User $assignedTo): Report
    {
        $this->assignedTo = $assignedTo;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getDueDate(): ?DateTime
    {
        return $this->dueDate;
    }

    /**
     * @param DateTime $dueDate
     *
     * @return Report
     */
    public function setDueDate(DateTime $dueDate): Report
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getRequestType(): string
    {
        return $this->requestType;
    }

    /**
     * @param string $requestType
     *
     * @return Report
     */
    public function setRequestType(string $requestType): Report
    {
        $this->requestType = $requestType;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRisk(): ?string
    {
        return $this->risk;
    }

    /**
     * @return float|null
     */
    public function getRiskScore(): ?float
    {
        return $this->riskScore;
    }

    /**
     * @param float $riskScore
     *
     * @return Report
     */
    public function setRiskScore(float $riskScore): Report
    {
        $this->riskScore = $riskScore;

        return $this;
    }

    /**
     * @param string $risk
     *
     * @return Report
     */
    public function setRisk(string $risk): Report
    {
        $this->risk = $risk;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getCompletedDate(): ?DateTime
    {
        return $this->completedDate;
    }

    /**
     * @param DateTime $completedDate
     *
     * @return Report
     */
    public function setCompletedDate(DateTime $completedDate): Report
    {
        $this->completedDate = $completedDate;
        return $this;
    }

    /**
     * @param bool $hide
     *
     * @return $this
     */
    public function setHideReportScore(bool $hide): Report
    {
        $this->hideReportScore = $hide;

        return $this;
    }

    /**
     * @return Company|null
     */
    public function getCompany(): ?Company
    {
        return $this->company;
    }

    /**
     * @param Company|null $company
     *
     * @return Report
     */
    public function setCompany(?Company $company): Report
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return string|null
     *
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("company_name")
     * @Groups({"queued"})
     */
    public function getCompanyName(): ?string
    {
        if ($this->getSubject()->getCompany()) {
            return $this->getSubject()->getCompany()->getName();
        }

        return null;
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
     * @return DateTime
     *
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("created_at")
     * @Type("DateTime<'Y-m-d'>")
     * @Groups({"read"})
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }


    /**
     * @return int
     */
    public function getOptionValue(): int
    {
        return $this->optionValue;
    }

    /**
     * @param int $optionValue
     *
     * @return Report
     */
    public function setOptionValue(int $optionValue): Report
    {
        $this->optionValue = $optionValue;

        return $this;
    }

    /**
     * @return string | null
     */
    public function getRiskComment(): ?string
    {
        return $this->riskComment;
    }

    /**
     * @param string $riskComment
     *
     * @return Report
     */
    public function setRiskComment(string $riskComment): Report
    {
        $this->riskComment = $riskComment;

        return $this;
    }

    /**
     * @return array
     *
     *
     */
    public function getReportScoresUpdated(): ?array
    {
        return $this->reportScoresUpdated;
    }

    /**
     * @param array $reportScoresUpdated
     * @Serializer\VirtualProperty()
     *
     * @Groups({"write", "report"})
     *
     * @return Report|null
     *
     */
    public function setReportScoresUpdated(array $reportScoresUpdated): ?Report
    {
        $this->reportScoresUpdated = $reportScoresUpdated;

        return $this;
    }

    /**
     * @return bool
     */
    public function isOverWriteReportScores(): bool
    {
        return $this->overWriteReportScores;
    }

    /**
     * @param bool $overWriteReportScores
     *
     * @return Report
     */
    function setOverWriteReportScores(bool $overWriteReportScores): Report
    {
        $this->overWriteReportScores = $overWriteReportScores;

        return $this;
    }

    /**
     * @return array
     */
    public function getSocialMediaScores(): array
    {
        return $this->socialMediaScores;
    }

    /**
     * @param array $socialMediaScores
     *
     * @return Report
     */
    public function setSocialMediaScores(array $socialMediaScores): Report
    {
        $this->socialMediaScores = $socialMediaScores;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPdfFilename(): ?string
    {
        return $this->pdfFilename;
    }

    /**
     * @param string $pdfFilename
     *
     * @return $this|null
     */
    public function setPdfFilename(string $pdfFilename): ?Report
    {
        $this->pdfFilename = $pdfFilename;

        return $this;
    }

    /**
     * @return User
     */
    public function getApprovedBy(): ?User
    {
        return $this->approvedBy;
    }

    /**
     * @param User $approvedBy
     *
     * @return $this
     */
    public function setApprovedBy(User $approvedBy): Report
    {
        $this->approvedBy = $approvedBy;

        return $this;
    }
}
