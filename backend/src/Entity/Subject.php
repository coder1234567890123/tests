<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Report;
use App\Entity\Question;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use InvalidArgumentException;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation as Serializer;
use Ramsey\Uuid\Uuid;
use Swagger\Annotations as SWG;
use Swagger\Annotations\Property;
use Symfony\Component\Validator\Constraints as Assert;
use App\Exception\InvalidReportTypeException;

/**
 * @package App\Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="subjects")
 */
class Subject
{
    use TimestampableEntity;

    // Valid Report Types
    const REPORT_TYPE_BASIC = 'basic';
    const REPORT_TYPE_FULL = 'full';
    const REPORT_TYPE_STANDARD = 'standard';
    const REPORT_TYPE_HIGH_PROFILE = 'high_profile';
    const REPORT_TYPE_ALL = 'all';

    const REPORT_TYPES = [
        self::REPORT_TYPE_BASIC,
        self::REPORT_TYPE_FULL,
        self::REPORT_TYPE_STANDARD,
        self::REPORT_TYPE_HIGH_PROFILE,
        self::REPORT_TYPE_ALL
    ];

    /**
     * @var string The subject's guid.
     *
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     * @Serializer\Type("uuid")
     *
     * @Groups({"read","investigate","user_tracker", "queued", "report", "default"})
     */
    private $id;

    /**
     * @var string The subject's guid.
     *
     * @ORM\Column(type="uuid")
     * @Serializer\Type("uuid")
     *
     * @Groups({"read", "investigate","user_tracker", "queued", "report"})
     */
    private $blobFolder;

    /**
     * @var User The user that created this subject.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @Groups({"write"})
     */
    private $createdBy;

    /**
     * @var string Either the ID number or Passport number of the subject.
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Groups({"write", "read"})
     */
    private $identification;

    /**
     * @var string The subject's first name.
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Groups({"write", "read","queued","user_tracker","accounts", "default"})
     */
    private $firstName;

    /**
     * @var string The subject's middle name.
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"write", "read"})
     */
    private $middleName;

    /**
     * @var string The subject's last name.
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Groups({"write", "read", "queued","user_tracker","accounts"})
     */
    private $lastName;

    /**
     * @var string The subject's maiden name.
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"write", "read", "queued"})
     */
    private $maidenName;

    /**
     * @var string The subject's nickname.
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"write", "read", "queued"})
     */
    private $nickname;

    /**
     * @var string[] The subject's known social handles.
     *
     * @ORM\Column(type="json",nullable=true)
     * @Serializer\Type("array")
     * @Groups({"write", "read",})
     */
    private $handles = [];

    /**
     * @var string The subject's gender.
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"write", "read", "queued"})
     */
    private $gender;

    /**
     * @var DateTimeImmutable|null The subject's date of birth.
     *
     * @ORM\Column(type="datetime_immutable",nullable=true)
     * @Serializer\Type("DateTimeImmutable")
     * @Groups({"write", "read",})
     */
    private $dateOfBirth;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email."
     * )
     * @Groups({"write", "read", "queued"})
     */
    private $primaryEmail;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email."
     * )
     * @Groups({"write", "read", "queued"})
     */
    private $secondaryEmail;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     *
     *
     * @Groups({"write", "read", "queued"})
     */
    private $primaryMobile;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"write", "read", "queued"})
     */
    private $secondaryMobile;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Profile", mappedBy="subject")
     * @ORM\OrderBy({"priority" = "DESC"})
     * @Serializer\Exclude()
     */
    private $profiles;

    /**
     * @ORM\Embedded(class="App\Entity\Address")
     * @Serializer\Type("App\Entity\Address")
     * @Groups({"write", "read", "queued"})
     */
    private $address;

    /**
     * @var string[] The subject's educationInstitute.
     *
     * @ORM\Column(type="json", nullable=true)
     * @Serializer\Type("array")
     * @Groups({"read"})
     */
    private $educationInstitutes = [];

    /**
     * @var string The subject's province.
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"write", "read"})
     */
    private $province;

    /**
     * @var Country The subject's country.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Country")
     * @Serializer\Type("App\Entity\Country")
     * @Groups({"write", "read"})
     *
     */
    private $country;

    /**
     * @var Company|null The subject's company.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Company")
     * @Serializer\Type("App\Entity\Company")
     * @Groups({"write", "read"})
     */
    private $company;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Qualification", mappedBy="subject")
     * @Groups({"read", "queued"})
     */
    private $qualifications;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Employment", mappedBy="subject")
     * @Groups({"read", "queued"})
     */
    private $employments;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Report", mappedBy="subject")
     * @Groups({"read"})
     */
    private $reports;

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
     * @Groups({"read", "write"})
     */
    private $rushReport = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     * @Groups({"read", "write", "investigate"})
     */
    private $allowTrait = true;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"write", "read"})
     */
    private $imageFile;

    /**
     * This property is used by the marking store
     *
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"read"})
     */
    private $status;

    /**
     * @var string The type of report to generate for this subject.
     *
     * @ORM\Column(type="string")
     * @Groups({"write", "read", "queued"})
     */
    private $reportType;

    /**
     * Subject constructor.
     */
    public function __construct()
    {
        $this->profiles = new ArrayCollection();
        $this->qualifications = new ArrayCollection();
        $this->employments = new ArrayCollection();
        $this->reports = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return (string)$this->id;
    }

    /**
     * @return string
     */
    public function getBlobFolder(): string
    {
        return (string)$this->blobFolder;
    }

    /**
     * @param string $blobFolder
     *
     * @return Subject
     */
    public function setBlobFolder(string $blobFolder): Subject
    {
        if (!Uuid::isValid($blobFolder)) {
            throw new InvalidArgumentException(sprintf('%s is not a valid Uuid.', $blobFolder));
        }
        $this->blobFolder = Uuid::fromString($blobFolder);

        return $this;
    }

    /**
     * @return string|null
     *
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("created_by")
     * @Groups({"read"})
     */
    public function getCreatedByName(): ?string
    {
        if ($this->getCreatedBy()) {
            return $this->getCreatedBy()->getFullName();
        }

        return null;
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
     * @return Subject
     */
    public function setCreatedBy(User $createdBy): Subject
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getIdentification(): ?string
    {
        return $this->identification;
    }

    /**
     * @param string $identification
     *
     * @return Subject
     */
    public function setIdentification(string $identification): Subject
    {
        $this->identification = $identification;

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
     * @param string $firstName
     *
     * @return Subject
     */
    public function setFirstName(string $firstName): Subject
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMiddleName(): ?string
    {
        return $this->middleName;
    }

    /**
     * @param string $middleName
     *
     * @return Subject
     */
    public function setMiddleName(?string $middleName): Subject
    {
        $this->middleName = $middleName;

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
     * @param string $lastName the data subject's last name
     *
     * @return Subject
     */
    public function setLastName(string $lastName): Subject
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMaidenName(): ?string
    {
        return $this->maidenName;
    }

    /**
     * @param string $maidenName
     *
     * @return Subject
     */
    public function setMaidenName(?string $maidenName): Subject
    {
        $this->maidenName = $maidenName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getGender(): ?string
    {
        return $this->gender;
    }

    /**
     * @param string $gender
     *
     * @return Subject
     */
    public function setGender(?string $gender): Subject
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    /**
     * @param string $nickname
     *
     * @return Subject
     */
    public function setNickname(?string $nickname): Subject
    {
        $this->nickname = $nickname;

        return $this;
    }

    /**
     * @return array
     */
    public function getHandles(): array
    {
        return $this->handles;
    }

    /**
     * @param array $handles
     *
     * @return Subject
     */
    public function setHandles(array $handles): Subject
    {
        $this->handles = $handles;

        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getDateOfBirth(): ?DateTimeImmutable
    {
        return $this->dateOfBirth;
    }

    /**
     * @param DateTimeImmutable $dateOfBirth
     *
     * @return Subject
     */
    public function setDateOfBirth(DateTimeImmutable $dateOfBirth): Subject
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    /**
     * @return string
     */
    public function getPrimaryEmail(): string
    {
        return $this->primaryEmail;
    }

    /**
     * @param string $primaryEmail
     *
     * @return Subject
     */
    public function setPrimaryEmail(string $primaryEmail): Subject
    {
        $this->primaryEmail = $primaryEmail;

        return $this;
    }

    /**
     * @return string
     */
    public function getSecondaryEmail(): string
    {
        return $this->secondaryEmail;
    }

    /**
     * @param string $secondaryEmail
     *
     * @return Subject
     */
    public function setSecondaryEmail(?string $secondaryEmail): Subject
    {
        $this->secondaryEmail = $secondaryEmail;

        return $this;
    }

    /**
     * @return string
     */
    public function getPrimaryMobile(): string
    {
        return $this->primaryMobile;
    }

    /**
     * @param string $primaryMobile
     *
     * @return Subject
     */
    public function setPrimaryMobile(string $primaryMobile): Subject
    {
        $this->primaryMobile = $primaryMobile;

        return $this;
    }

    /**
     * @return string
     */
    public function getSecondaryMobile(): string
    {
        return $this->secondaryMobile;
    }

    /**
     * @param string $secondaryMobile
     *
     * @return Subject
     */
    public function setSecondaryMobile(?string $secondaryMobile): Subject
    {
        $this->secondaryMobile = $secondaryMobile;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getProfiles(): Collection
    {
        return $this->profiles;
    }

    /**
     * @param Profile $profile
     *
     * @return Subject
     */
    public function addProfile(Profile $profile): Subject
    {
        $this->profiles->add($profile);

        return $this;
    }

    /**
     * @return Address|null
     */
    public function getAddress(): ?Address
    {
        return $this->address;
    }

    /**
     * @param Address $address
     *
     * @return Subject
     */
    public function setAddress(Address $address): Subject
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return Country|null
     */
    public function getCountry(): ?Country
    {
        return $this->country;
    }

    /**
     * @param Country $country
     *
     * @return Subject|null
     */
    public function setCountry(?Country $country): Subject
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return string|null
     *
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("country")
     * @Serializer\Type("string")
     * @Groups({"read"})
     */
    public function getCountryName(): ?string
    {
        if ($this->country) {
            return $this->country->getName();
        } else {
            return null;
        }
    }

    /**
     * @return array
     */
    public function getEducationInstitutes(): array
    {
        return $this->educationInstitutes;
    }

    /**
     * @param array $educationInstitutes
     *
     * @return Subject
     */
    public function setEducationInstitutes(array $educationInstitutes): Subject
    {
        $this->educationInstitutes = $educationInstitutes;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getProvince(): ?string
    {
        return $this->province;
    }

    /**
     * @param string $province
     *
     * @return Subject
     */
    public function setProvince(string $province): Subject
    {
        $this->province = $province;

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
     * @return Subject
     */
    public function setCompany(?Company $company): Subject
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return string
     *
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("company")
     * @Serializer\Type("string")
     * @Groups({"write", "read"})
     */
    public function getCompanyName(): ?string
    {
        if ($this->company === null) {
            return null;
        }

        return $this->company->getName();
    }

    /**
     * @return ArrayCollection|Collection
     *
     * @Serializer\VirtualProperty()
     * @Groups({"read"})
     */
    public function getFacebookProfiles(): Collection
    {
        return $this->getPlatformProfiles(Profile::PLATFORM_FACEBOOK);
    }

    /**
     * @param $platform
     *
     * @return ArrayCollection|Collection
     */
    public function getPlatformProfiles($platform)
    {
        return !$this->profiles ? new ArrayCollection()
            : $this->profiles->filter(function (Profile $profile) use ($platform) {
                return $profile->getPlatform() === $platform;
            });
    }

    /**
     * @return ArrayCollection|Collection
     *
     * @Serializer\VirtualProperty()
     * @Groups({"read"})
     */
    public function getWebProfiles(): Collection
    {
        return $this->getPlatformProfiles(Profile::PLATFORM_WEB);
    }

    /**
     * @return ArrayCollection|Collection
     *
     * @Serializer\VirtualProperty()
     * @Groups({"read"})
     */
    public function getInstagramProfiles(): Collection
    {
        return $this->getPlatformProfiles(Profile::PLATFORM_INSTAGRAM);
    }

    /**
     * @return ArrayCollection|Collection
     *
     * @Serializer\VirtualProperty()
     * @Groups({"read"})
     */
    public function getTwitterProfiles(): Collection
    {
        return $this->getPlatformProfiles(Profile::PLATFORM_TWITTER);
    }

    /**
     * @return ArrayCollection|Collection
     *
     * @Serializer\VirtualProperty()
     * @Groups({"read"})
     */
    public function getLinkedinProfiles(): Collection
    {
        return $this->getPlatformProfiles(Profile::PLATFORM_LINKEDIN);
    }

    /**
     * @return ArrayCollection|Collection
     *
     * @Serializer\VirtualProperty()
     * @Groups({"read"})
     */
    public function getPinterestProfiles(): Collection
    {
        return $this->getPlatformProfiles(Profile::PLATFORM_PINTEREST);
    }

    /**
     * @return ArrayCollection|Collection
     *
     * @Serializer\VirtualProperty()
     * @Groups({"read"})
     */
    public function getFlickrProfiles(): Collection
    {
        return $this->getPlatformProfiles(Profile::PLATFORM_FLICKR);
    }

    /**
     * @return ArrayCollection|Collection
     *
     * @Serializer\VirtualProperty()
     * @Groups({"read"})
     */
    public function getYoutubeProfiles(): Collection
    {
        return $this->getPlatformProfiles(Profile::PLATFORM_YOUTUBE);
    }

    /**
     * @return ArrayCollection|Collection
     *
     * @Serializer\VirtualProperty()
     * @Groups({"read"})
     */
    public function getWebSearchProfiles(): Collection
    {
        return $this->getPlatformProfiles(Profile::PLATFORM_WEB);
    }

    /**
     * @return Collection|null
     */
    public function getQualifications(): ?Collection
    {
        return $this->qualifications;
    }

    /**
     * @param Qualification $qualification
     *
     * @return Subject
     */
    public function addQualification(Qualification $qualification): self
    {
        $this->qualifications->add($qualification);

        return $this;
    }

    /**
     * @return Collection|null
     */
    public function getEmployments(): ?Collection
    {
        return $this->employments;
    }

    /**
     * @param Employment $employment
     *
     * @return Subject
     */
    public function addEmployments(Employment $employment): self
    {
        $this->employments->add($employment);

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
     * @return Subject
     */
    public function setEnabled(bool $enabled): Subject
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRushReport(): bool
    {
        return $this->rushReport;
    }

    /**
     *
     * @param bool $rushReport
     *
     * @return Subject
     */
    public function setRushReport(bool $rushReport): Subject
    {
        $this->rushReport = $rushReport;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAllowTrait(): bool
    {
        return $this->allowTrait;
    }

    /**
     *
     * @param bool $allowTrait
     *
     * @return Subject
     */
    public function setAllowTrait(bool $allowTrait): Subject
    {
        $this->allowTrait = $allowTrait;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getImageFile(): ?string
    {
        return $this->imageFile;
    }

    /**
     *
     * @param string $imageFile
     *
     * @return Subject
     */
    public function setImageFile(string $imageFile): Subject
    {
        $this->imageFile = $imageFile;

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
     *
     * @param string $status
     *
     * @return Subject
     */
    public function setStatus(string $status): Subject
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return DateTime
     *
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("created_at")
     * @Serializer\Type("DateTime")
     * @Groups({"read"})
     */
    public function getCreatedDate(): DateTime
    {
        return $this->getCreatedAt();
    }

    /**
     * @return string
     */
    public function getReportType(): string
    {
        return $this->reportType;
    }

    /**
     * @param string $reportType
     *
     * @return Subject
     * @throws InvalidReportTypeException
     */
    public function setReportType(string $reportType): Subject
    {
        if (!in_array($reportType, self::REPORT_TYPES)) {
            throw new InvalidReportTypeException();
        }

        $this->reportType = $reportType;

        return $this;
    }

    /**
     * @return Collection|null
     */
    public function getReports(): ?Collection
    {
        return $this->reports;
    }

    /**
     * @return Report|null
     *
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("current_report")
     * @Groups({"read", "queue"})
     */
    public function getCurrentReport(): ?Report
    {
        if ($this->getReports() && $this->getReports()->count() > 0) {
            $reports = $this->getReports()->filter(function (Report $report) {
                if (!$report->isOpen()) {
                    return $report->setOpen(true);
                }
                return $report->isOpen();
            });
            return $reports->count() > 0 ? $reports->first() : null;
        }
        return null;
    }

    /**
     * @param Report $report
     *
     * @return Subject
     */
    public function addReport(Report $report): self
    {
        $this->reports->add($report);

        return $this;
    }
}
