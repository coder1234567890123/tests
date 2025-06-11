<?php

declare(strict_types=1);

namespace App\Entity;

use App\Exception\InvalidBrandingTypeException;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Groups;
use phpDocumentor\Reflection\Types\Null_;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Company
 *
 * @package App\Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="companies")
 */
class Company
{
    use TimestampableEntity;

    /**
     * @var string The company's guide.
     *
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     * @Serializer\Accessor(getter="getId")
     *
     * @Groups({"read","minimalInfo","user_tracker", "default"})
     *
     */
    private $id;

    /**
     * @var User The user that created this phrase.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @Groups({})
     */
    private $createdBy;

    /**
     * @var Team The team that is assigned to this company.
     *
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @ORM\ManyToOne(targetEntity="App\Entity\Team", inversedBy="companies")
     * @Groups({"write"})
     */
    private $team;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=250)
     * @Assert\NotBlank()
     *
     * @Groups({"write", "read","minimalInfo", "user_tracker" , "default"})
     */
    private $name;
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"write", "read"})
     */
    private $registrationNumber;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"write", "read"})
     */
    private $vatNumber;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Groups({"write", "read"})
     */
    private $telNumber;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"write", "read"})
     */
    private $faxNumber;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"write", "read"})
     */
    private $mobileNumber;
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"write", "read"})
     */
    private $website;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\Email()
     * @Assert\NotBlank()
     * @Groups({"write", "read"})
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Groups({"write", "read"})
     */
    private $street1;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Groups({"write", "read"})
     */
    private $street2;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Groups({"write", "read"})
     */
    private $suburb;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Groups({"write", "read"})
     */
    private $postalCode;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Groups({"write", "read"})
     */
    private $province;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Groups({"write", "read"})
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"write", "read"})
     */
    private $note;

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
     * @var string
     *
     * @ORM\Column(type="string", length=250)
     * @Assert\NotBlank()
     * @Groups({"write", "read"})
     */
    private $contactFirstname;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=250)
     * @Assert\NotBlank()
     * @Groups({"write", "read"})
     */
    private $contactLastname;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"write", "read"})
     */
    private $contactTelephone;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\Email()
     * @Assert\NotBlank()
     * @Groups({"write", "read"})
     */
    private $contactEmail;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=250)
     * @Assert\NotBlank()
     * @Groups({"write", "read"})
     */
    private $accountHolderFirstName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=250)
     * @Assert\NotBlank()
     * @Groups({"write", "read"})
     */
    private $accountHolderLastName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"write", "read"})
     */
    private $accountHolderPhone;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\Email()
     * @Assert\NotBlank()
     * @Groups({"write", "read"})
     */
    private $accountHolderEmail;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     * @Groups({"read"})
     */
    private $enabled = true;

    /**
     *
     * @var string|null Subject Image.
     *
     *
     * @ORM\Column(type="string", nullable=true)
     *
     * @Groups({"write", "read"})
     */
    private $imageFile;

    /**
     *
     * @var string|null Company FrontPage.
     *
     *
     * @ORM\Column(type="string", nullable=true)
     *
     * @Groups({"write", "read"})
     */
    private $imageFrontPage;

    /**
     *
     * @var string|null Company Footer Logo Image.
     *
     *
     * @ORM\Column(type="string", nullable=true)
     *
     * @Groups({"write", "read"})
     */
    private $imageFooterLogo;

    /**
     *
     * @var string|null Company Cover Logo.
     *
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"write", "read"})
     */
    private $coverLogo;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     * @Groups({"read"})
     *
     */
    private $archived = false;

    /**
     *
     * @var string|null Company PDF Password.
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"write", "read"})
     */
    private $pdfPassword;

    /**
     *
     * @var string|null Saves Company theme color.
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"write", "read"})
     */
    private $themeColor;

    /**
     *
     * @var string|null Saves Company theme Second color.
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"write", "read"})
     */
    private $themeColorSecond;

    /**
     *
     * @var string|null Company Footer link.
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"write", "read"})
     */
    private $footerLink;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     * @Groups({"write", "read"})
     */
    private $passwordSet = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     * @Groups({"write", "read"})
     */
    private $useDisclaimer = false;

    /**
     * @var string The type of branding for the pdf report.
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Groups({"write", "read"})
     */
    private $brandingType;

    /**
     * @var string
     *
     * @ORM\Column(type="text" , nullable=true)
     * @Groups({"write", "read"})
     */
    private $disclaimer;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     * @Groups({"read", "write"})
     */
    private $allowTrait = true;

    /**
     * @var string
     *
     * @ORM\Column(type="string",)
     * @Groups({"write", "read"})
     */
    private $companyTypes;

    /**
     * @return string
     */
    public function getId(): string
    {
        return (string)$this->id;
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
     * @return array|null
     *
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("team")
     * @Groups({"read"})
     */
    public function getTeamDetails(): ?array
    {
        if ($this->getTeam()) {
            return ["id" => $this->getTeam()->getId()];
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
     * @return Company
     */
    public function setCreatedBy(User $createdBy): Company
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Company
     */
    public function setName(string $name): Company
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getRegistrationNumber(): string
    {
        return $this->registrationNumber;
    }

    /**
     * @param string $registrationNumber
     *
     * @return Company
     */
    public function setRegistrationNumber(string $registrationNumber): Company
    {
        $this->registrationNumber = $registrationNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getVatNumber(): string
    {
        return $this->vatNumber;
    }

    /**
     * @param string $vatNumber
     *
     * @return Company
     */
    public function setVatNumber(string $vatNumber): Company
    {
        $this->vatNumber = $vatNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getNote(): ?string
    {
        return $this->note;
    }

    /**
     * @param string $note
     *
     * @return Company
     */
    public function setNote(string $note): Company
    {
        $this->note = $note;

        return $this;
    }

    /**
     * @return string
     */
    public function getTelNumber(): string
    {
        return $this->telNumber;
    }

    /**
     * @param string $telNumber
     *
     * @return Company
     */
    public function setTelNumber(string $telNumber): Company
    {
        $this->telNumber = $telNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getFaxNumber(): string
    {
        return $this->faxNumber;
    }

    /**
     * @param string $faxNumber
     *
     * @return Company
     */
    public function setFaxNumber(string $faxNumber): Company
    {
        $this->faxNumber = $faxNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getMobileNumber(): string
    {
        return $this->mobileNumber;
    }

    /**
     * @param string $mobileNumber
     *
     * @return Company
     */
    public function setMobileNumber(string $mobileNumber): Company
    {
        $this->mobileNumber = $mobileNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getWebsite(): string
    {
        return $this->website;
    }

    /**
     * @param string $website
     *
     * @return Company
     */
    public function setWebsite(string $website): Company
    {
        $this->website = $website;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return Company
     */
    public function setEmail(string $email): Company
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getStreet1(): string
    {
        return $this->street1;
    }

    /**
     * @param string $street1
     *
     * @return Company
     */
    public function setStreet1(string $street1): Company
    {
        $this->street1 = $street1;

        return $this;
    }

    /**
     * @return string
     */
    public function getStreet2(): string
    {
        return $this->street2;
    }

    /**
     * @param string $street2
     *
     * @return Company
     */
    public function setStreet2(string $street2): Company
    {
        $this->street2 = $street2;

        return $this;
    }

    /**
     * @return string
     */
    public function getSuburb(): string
    {
        return $this->suburb;
    }

    /**
     * @param string $suburb
     *
     * @return Company
     */
    public function setSuburb(string $suburb): Company
    {
        $this->suburb = $suburb;

        return $this;
    }

    /**
     * @return string
     */
    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    /**
     * @param string $postalCode
     *
     * @return Company
     */
    public function setPostalCode(string $postalCode): Company
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getProvince(): string
    {
        return $this->province;
    }

    /**
     * @param string $province
     *
     * @return Company
     */
    public function setProvince(string $province): self
    {
        $this->province = $province;

        return $this;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @param string $city
     *
     * @return Company
     */
    public function setCity(string $city): Company
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return Country
     */
    public function getCountry(): ?Country
    {
        return $this->country;
    }

    /**
     * @param Country $country
     *
     * @return Company
     */
    public function setCountry(?Country $country): Company
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return string
     *
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("country")
     * @Serializer\Type("string")
     * @Groups({"read"})
     */
    public function getCountryName(): string
    {
        return $this->country->getName();
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
     * @return Company
     */
    public function setEnabled(bool $enabled): Company
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
     *
     * @param bool $archived
     *
     * @return Company
     */
    public function setArchived(bool $archived): Company
    {
        $this->archived = $archived;

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
     * @param $imageFile
     *
     * @return Company
     */
    public function setImageFile($imageFile): Company
    {
        $this->imageFile = $imageFile;
        return $this;
    }

    /**
     * @return string
     */
    public function getContactFirstName(): string
    {
        return $this->contactFirstname;
    }

    /**
     * @param string $contactFirstName
     *
     * @return Company
     */
    public function setContactFirstName(string $contactFirstName): Company
    {
        $this->contactFirstname = $contactFirstName;
        return $this;
    }

    /**
     * @return string
     */
    public function getContactLastName(): string
    {
        return $this->contactLastname;
    }

    /**
     * @param string $contactLastName
     *
     * @return Company
     */
    public function setContactLastName(string $contactLastName): Company
    {
        $this->contactLastname = $contactLastName;
        return $this;
    }

    /**
     * @return string
     */
    public function getContactTelephone(): string
    {
        return $this->contactTelephone;
    }

    /**
     * @param string $contactTelephone
     *
     * @return Company
     */
    public function setContactTelephone(string $contactTelephone): Company
    {
        $this->contactTelephone = $contactTelephone;
        return $this;
    }

    /**
     * @return string
     */
    public function getContactEmail(): string
    {
        return $this->contactEmail;
    }

    /**
     * @param string $contactEmail
     *
     * @return Company
     */
    public function setContactEmail(string $contactEmail): Company
    {
        $this->contactEmail = $contactEmail;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getImage(): ?string
    {
        return $this->imageHeader;
    }

    /**
     * @return string|null
     */
    public function getImageFooterLogo(): ?string
    {
        return $this->imageFooterLogo;
    }

    /**
     * @param $imageFooterLogo
     *
     * @return Company
     */
    public function setImageFooterLogo($imageFooterLogo): Company
    {
        $this->imageFooterLogo = $imageFooterLogo;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getImageFrontPage(): ?string
    {
        return $this->imageFrontPage;
    }

    /**
     * @param string|null $imageFrontPage
     *
     * @return $this
     */
    public function setImageFrontPage(?string $imageFrontPage): Company
    {
        $this->imageFrontPage = $imageFrontPage;

        return $this;
    }


    /**
     * @return string
     */
    public function getAccountHolderFirstName(): string
    {
        return $this->accountHolderFirstName;
    }

    /**
     * @param string $accountHolderFirstName
     *
     * @return Company
     */
    public function setAccountHolderFirstName(string $accountHolderFirstName): Company
    {
        $this->accountHolderFirstName = $accountHolderFirstName;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccountHolderLastName(): string
    {
        return $this->accountHolderLastName;
    }

    /**
     * @param string $accountHolderLastName
     *
     * @return Company
     */
    public function setAccountHolderLastName(string $accountHolderLastName): Company
    {
        $this->accountHolderLastName = $accountHolderLastName;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccountHolderEmail(): string
    {
        return $this->accountHolderEmail;
    }

    /**
     * @param string $accountHolderEmail
     *
     * @return Company
     */
    public function setAccountHolderEmail(string $accountHolderEmail): Company
    {
        $this->accountHolderEmail = $accountHolderEmail;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccountHolderPhone(): string
    {
        return $this->accountHolderPhone;
    }

    /**
     * @param string $accountHolderPhone
     *
     * @return Company
     */
    public function setAccountHolderPhone(string $accountHolderPhone): Company
    {
        $this->accountHolderPhone = $accountHolderPhone;
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
    public function getPdfPassword(): string
    {
        return $this->pdfPassword;
    }

    /**
     * @param string $pdfPassword
     *
     * @return Company
     */
    public function setPdfPassword(string $pdfPassword): Company
    {
        $this->pdfPassword = $pdfPassword;

        return $this;
    }

    /**
     * @return string
     */
    public function getThemeColor(): string
    {
        return $this->themeColor;
    }

    /**
     * @param string $themeColor
     *
     * @return Company
     */
    public function setThemeColor(string $themeColor): Company
    {
        $this->themeColor = $themeColor;

        return $this;
    }

    /**
     * @return string
     */
    public function getFooterLink(): string
    {
        return $this->footerLink;
    }

    /**
     * @param string $footerLink
     *
     * @return Company
     */
    public function setFooterLink(string $footerLink): Company
    {
        $this->footerLink = $footerLink;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPasswordSet(): bool
    {
        return $this->passwordSet;
    }

    /**
     *
     * @param bool $passwordSet
     *
     * @return Company
     */
    public function setPasswordSet(bool $passwordSet): Company
    {
        $this->passwordSet = $passwordSet;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBrandingType(): ?string
    {
        return $this->brandingType;
    }

    /**
     * @param string $brandingType
     *
     * @return Company
     * @throws InvalidBrandingTypeException
     */
    public function setBrandingType(string $brandingType): Company
    {
        if (!in_array($brandingType, Report::BRANDING_TYPES)) {
            throw new InvalidBrandingTypeException();
        }

        $this->brandingType = $brandingType;

        return $this;
    }

    /**
     * @return Team|null
     */
    public function getTeam(): ?Team
    {
        return $this->team;
    }

    /**
     * @param Team $team
     *
     * @return Company
     */
    public function setTeam($team = Null): self
    {
        $this->team = $team;

        return $this;
    }

    /**
     * @return string| null
     */
    public function getDisclaimer(): ?string
    {
        return $this->disclaimer;
    }

    /**
     * @param string $disclaimer
     *
     * @return Company
     */
    public function setDisclaimer(string $disclaimer): Company
    {
        $this->disclaimer = $disclaimer;

        return $this;
    }

    /**
     * @return bool
     */
    public function isUseDisclaimer(): bool
    {
        return $this->useDisclaimer;
    }

    /**
     * @param bool $useDisclaimer
     *
     * @return Company
     */
    public function setUseDisclaimer(bool $useDisclaimer): Company
    {
        $this->useDisclaimer = $useDisclaimer;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCoverLogo(): ?string
    {
        return $this->coverLogo;
    }

    /**
     * @param string|null $coverLogo
     *
     * @return Company
     */
    public function setCoverLogo(?string $coverLogo): Company
    {
        $this->coverLogo = $coverLogo;

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
     * @return Company
     */
    public function setAllowTrait(bool $allowTrait): Company
    {
        $this->allowTrait = $allowTrait;

        return $this;
    }

    /**
     * @return string
     */
    public function getCompanyTypes(): string
    {
        return $this->companyTypes;
    }

    /**
     * @param string $companyTypes
     *
     * @return Company
     */
    public function setCompanyTypes(string $companyTypes): Company
    {
        $this->companyTypes = $companyTypes;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getThemeColorSecond(): ?string
    {
        return $this->themeColorSecond;
    }

    /**
     * @param string|null $themeColorSecond
     *
     * @return $this
     */
    public function setThemeColorSecond(?string $themeColorSecond): Company
    {
        $this->themeColorSecond = $themeColorSecond;

        return $this;
    }
}
