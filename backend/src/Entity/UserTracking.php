<?php declare(strict_types=1);

namespace App\Entity;

use App\Exception\InvalidTrackingActionException;
use DateTime;
use DateTimeImmutable;
use Exception;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class UserTracking
 *
 * @package App\Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="user_tracking")
 */
class UserTracking
{
    use TimestampableEntity;

    // Valid answerTypes
    const ACTION_SUBJECT_CREATE = 'SUBJECT_CREATED';
    const ACTION_SUBJECT_EDIT = 'SUBJECT_EDITED';
    const ACTION_SUBJECT_DISABLE = 'SUBJECT_DISABLED';
    const ACTION_SUBJECT_ENABLE = 'SUBJECT_ENABLED';
    const ACTION_SUBJECT_PROFILE_VALID = 'ACTION_SUBJECT_PROFILE_VALIDATED';
    const ACTION_INVESTIGATION_REQUEST = 'INVESTIGATION_REQUESTED';
    const ACTION_INVESTIGATION_SEARCH_START = 'INVESTIGATION_SEARCH_STARTED';
    const ACTION_INVESTIGATION_SEARCH_COMPLETE = 'INVESTIGATION_SEARCH_COMPLETED';
    const ACTION_REPORT_TYPE_APPROVAL = 'REPORT_TYPE_APPROVED';
    const ACTION_REPORT_TYPE_REJECT = 'REPORT_TYPE_REJECTED';
    const ACTION_SUBJECT_INVESTIGATION = 'SUBJECT_UNDER_INVESTIGATION';
    const ACTION_TEAM_LEAD_APPROVAL = 'TEAM_LEAD_APPROVED';
    const ACTION_TEAM_LEAD_REJECT = 'TEAM_LEAD_REJECTED';
    const ABANDON_TEAM_LEAD_REJECT = 'TEAM_LEAD_ABANDON';
    const ACTION_ABANDON_REQUEST = 'REQUEST_ABANDONED';
    const ACTION_SUPER_ABANDON = 'SUPER_ABANDONED';
    const ACTION_SUPER_ABANDON_REJECT = 'ACTION_SUPER_ABANDON_REJECT';
    const ACTION_SUPER_REJECT = 'SUPER_REJECTED';
    const ACTION_INVESTIGATION_COMPLETE = 'INVESTIGATION_COMPLETED';
    const ACTION_REPORT_COMPLETE = 'REPORT_COMPLETED';
    const ACTION_COMPANY_CREATE = 'COMPANY_CREATED';
    const ACTION_COMPANY_EDIT = 'COMPANY_EDITED';
    const ACTION_USER_CREATE = 'USER_CREATED';
    const ACTION_USER_EDIT = 'USER_EDIT';
    const ACTION_TEAM_CREATE = 'TEAM_CREATED';
    const ACTION_TEAM_DELETE = 'TEAM_DELETED';
    const ACTION_USER_TEAM_ASSIGNMENT = 'USER_ASSIGNED_TO_TEAM';
    const ACTION_UPDATED_GLOBAL_WEIGHTS = 'ACTION_UPDATED_GLOBAL_WEIGHTS';
    const ACTION_GET_STANDARD_REPORT_PDF = 'ACTION_GET_STANDARD_REPORT_PDF';
    const ACTION_REBUILD_STANDARD_REPORT_PDF = 'ACTION_REBUILD_STANDARD_REPORT_PDF';


    const ACTION_TYPES = [
        self::ACTION_SUBJECT_CREATE,
        self::ACTION_SUBJECT_EDIT,
        self::ACTION_SUBJECT_DISABLE,
        self::ACTION_SUBJECT_ENABLE,
        self::ACTION_INVESTIGATION_REQUEST,
        self::ACTION_INVESTIGATION_SEARCH_START,
        self::ACTION_INVESTIGATION_SEARCH_COMPLETE,
        self::ACTION_REPORT_TYPE_APPROVAL,
        self::ACTION_REPORT_TYPE_REJECT,
        self::ACTION_SUBJECT_INVESTIGATION,
        self::ACTION_TEAM_LEAD_APPROVAL,
        self::ACTION_TEAM_LEAD_REJECT,
        self::ACTION_SUPER_ABANDON_REJECT,
        self::ACTION_SUPER_REJECT,
        self::ACTION_INVESTIGATION_COMPLETE,
        self::ACTION_REPORT_COMPLETE,
        self::ACTION_COMPANY_CREATE,
        self::ACTION_COMPANY_EDIT,
        self::ACTION_USER_CREATE,
        self::ACTION_USER_EDIT,
        self::ACTION_USER_TEAM_ASSIGNMENT,
        self::ACTION_SUBJECT_PROFILE_VALID,
        self::ACTION_ABANDON_REQUEST,
        self::ACTION_SUPER_ABANDON,
        self::ABANDON_TEAM_LEAD_REJECT,
        self::ACTION_TEAM_CREATE,
        self::ACTION_TEAM_DELETE,
        self::ACTION_UPDATED_GLOBAL_WEIGHTS,
        self::ACTION_GET_STANDARD_REPORT_PDF,
        self::ACTION_REBUILD_STANDARD_REPORT_PDF,

    ];

    const SOURCE_FAROSIAN = 'farosian-frontend';
    const SOURCE_CUSTOM = 'custom-api';

    /**
     * @var string The Employment's guid.
     *
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     * @Serializer\Type("uuid")
     * @Groups({"read","user_tracker"})
     */
    private $id;

    /**
     * @var User The user that is performing action.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @Groups({"write","user_tracker"})
     */
    private $user;

    /**
     * @var Company The company
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Company")
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"write","user_tracker"})
     */
    private $company;

    /**
     * @var Report
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Report")
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"write", "read" ,"user_tracker"})
     * @Serializer\Type("App\Entity\Report")
     */
    private $report;

    /**
     * @var Subject
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Subject")
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"read", "write","user_tracker"})
     * @Serializer\Type("App\Entity\Subject")
     */
    private $subject;

    /**
     * @var string
     * @ORM\Column(type="string" ,nullable=true)
     * @Groups({"write", "read","user_tracker"})
     */
    private $action;

    /**
     * @var string
     * @ORM\Column(type="string" ,nullable=true)
     * @Groups({"write", "read","user_tracker"})
     */
    private $reportStatus;

    /**
     * @var string Source of request (farosian-frontend / custom-api)
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Groups({"write", "read","user_tracker"})
     */
    private $source;

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
    public function getSubject(): ?Subject
    {
        return $this->subject;
    }

    /**
     * @param Subject $subject
     *
     * @return UserTracking
     */
    public function setSubject(Subject $subject): UserTracking
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return string
     *
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("subject")
     * @Serializer\Type("string")
     * @Groups({"read"})
     */
    public function getSubjectName(): string
    {
        return $this->subject->getFirstName();
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return UserTracking
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAction(): ?string
    {
        return $this->action;
    }

    /**
     * @param string $action
     *
     * @return UserTracking
     * @throws InvalidTrackingActionException
     */
    public function setAction(string $action): UserTracking
    {
        if (!in_array($action, self::ACTION_TYPES)) {
            throw new InvalidTrackingActionException();
        }
        $this->action = $action;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getReportStatus(): ?string
    {
        return $this->reportStatus;
    }

    /**
     * @param string $status
     *
     * @return UserTracking
     */
    public function setReportStatus(string $status): UserTracking
    {
        $this->reportStatus = $status;

        return $this;
    }

    /**
     * @return Report|null
     */
    public function getReport(): ?Report
    {
        return $this->report;
    }

    /**
     * @param Report $report
     *
     * @return UserTracking
     */
    public function setReport(Report $report): UserTracking
    {
        $this->report = $report;
        return $this;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @param string $source
     *
     * @return UserTracking
     */
    public function setSource(string $source): UserTracking
    {
        if (!in_array($source, [self::SOURCE_FAROSIAN, self::SOURCE_CUSTOM])) {
            throw new Exception('Invalid source, please choose between "farosian-frontend" or "custom-api".');
        }
        $this->source = $source;

        return $this;
    }

    /**
     * @return Company
     * @return Company|null
     */
    public function getCompany(): ?Company
    {
        return $this->company;
    }

    /**
     * @param Company $company
     *
     * @return UserTracking
     */
    public function setCompany(Company $company): UserTracking
    {
        $this->company = $company;
        return $this;
    }

    /**
     * @return DateTime
     *
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("created_at")
     * @Serializer\Type("DateTime")
     * @Groups({"read", "queued"})
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
     * @Serializer\Type("DateTime")
     * @Groups({"read", "queued"})
     */
    public function getUpdatedDate(): DateTime
    {
        return $this->getUpdatedDate();
    }

    /**
     * @return DateTime
     *
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("time_created")
     * @Serializer\Type("DateTime<'H:i'>")
     * @Groups({"read","user_tracker"})
     */
    public function getCreatedAtTime(): DateTime
    {
        return $this->getCreatedAt();
    }
}