<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use App\Annotation\IsEnabled;

/**
 * @package App\Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="identity_confirmations")
 * @IsEnabled(field="enabled")
 */
class IdentityConfirm

{

    use TimestampableEntity;

    /**
     * @var string The IdentityConfirm's guid.
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Subject")
     * @Groups({"write", "read"})
     */
    private $subject;

    /**
     * @var string The platform the answer will apply to.
     *
     * @ORM\Column(type="string")
     * @Groups({"write", "read"})
     */
    private $platform;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"write", "read"})
     */
    private $identityName = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"write", "read"})
     */
    private $identityMiddleName = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"write", "read"})
     */
    private $identityInitials = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"write", "read"})
     */
    private $identitySurname = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"write", "read"})
     */
    private $identityImage = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"write", "read"})
     */
    private $identityLocation = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"write", "read"})
     */
    private $identityEmploymentHistory = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"write", "read"})
     */
    private $identityAcademicHistory = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"write", "read"})
     */
    private $identityCountry = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"write", "read"})
     */
    private $identityProfileImage = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"write", "read"})
     */
    private $identityIdNumber = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"write", "read"})
     */
    private $identityContactNumber = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"write", "read"})
     */
    private $identityEmailAddress = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"write", "read"})
     */
    private $identityPhysicalAddress = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"write", "read"})
     */
    private $identityTag = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"write", "read"})
     */
    private $identityAlias = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"write", "read"})
     */
    private $identityLink = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"write", "read"})
     */
    private $identityLocationHistory = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"write", "read"})
     */
    private $identityHandle = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"write", "read"})
     */
    private $identityTitle = false;

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
     * @return IdentityConfirm
     */
    public function setSubject(Subject $subject): IdentityConfirm
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
     * @return IdentityConfirm
     */
    public function setPlatform(string $platform): IdentityConfirm
    {
        $this->platform = $platform;
        return $this;
    }

    /**
     * @return bool
     */
    public function isIdentityName():  ?bool
    {
        return $this->identityName;
    }

    /**
     * @param bool $identityName
     *
     * @return IdentityConfirm
     */
    public function setIdentityName(bool $identityName): IdentityConfirm
    {
        $this->identityName = $identityName;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIdentityMiddleName():  ?bool
    {
        return $this->identityMiddleName;
    }

    /**
     * @param bool $identityMiddleName
     *
     * @return IdentityConfirm
     */
    public function setIdentityMiddleName(bool $identityMiddleName): IdentityConfirm
    {
        $this->identityMiddleName = $identityMiddleName;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIdentityInitials():  ?bool
    {
        return $this->identityInitials;
    }

    /**
     * @param bool $identityInitials
     *
     * @return IdentityConfirm
     */
    public function setIdentityInitials(bool $identityInitials): IdentityConfirm
    {
        $this->identityInitials = $identityInitials;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIdentitySurname():  ?bool
    {
        return $this->identitySurname;
    }

    /**
     * @param bool $identitySurname
     *
     * @return IdentityConfirm
     */
    public function setIdentitySurname(bool $identitySurname): IdentityConfirm
    {
        $this->identitySurname = $identitySurname;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIdentityImage():  ?bool
    {
        return $this->identityImage;
    }

    /**
     * @param bool $identityImage
     *
     * @return IdentityConfirm
     */
    public function setIdentityImage(bool $identityImage): IdentityConfirm
    {
        $this->identityImage = $identityImage;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIdentityLocation():  ?bool
    {
        return $this->identityLocation;
    }

    /**
     * @param bool $identityLocation
     *
     * @return IdentityConfirm
     */
    public function setIdentityLocation(bool $identityLocation): IdentityConfirm
    {
        $this->identityLocation = $identityLocation;
        return $this;
    }

    /**
     * @return bool
     */
    public function isIdentityEmploymentHistory():  ?bool
    {
        return $this->identityEmploymentHistory;
    }

    /**
     * @param bool $identityEmploymentHistory
     *
     * @return IdentityConfirm
     */
    public function setIdentityEmploymentHistory(bool $identityEmploymentHistory): IdentityConfirm
    {
        $this->identityEmploymentHistory = $identityEmploymentHistory;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIdentityCountry():  ?bool
    {
        return $this->identityCountry;
    }

    /**
     * @param bool $identityCountry
     *
     * @return IdentityConfirm
     */
    public function setIdentityCountry(bool $identityCountry): IdentityConfirm
    {
        $this->identityCountry = $identityCountry;
        return $this;
    }

    /**
     * @return bool
     */
    public function isIdentityProfileImage():  ?bool
    {
        return $this->identityProfileImage;
    }

    /**
     * @param bool $identityProfileImage
     *
     * @return IdentityConfirm
     */
    public function setIdentityProfileImage(bool $identityProfileImage): IdentityConfirm
    {
        $this->identityProfileImage = $identityProfileImage;
        return $this;
    }

    /**
     * @return bool
     */
    public function isIdentityIdNumber():  ?bool
    {
        return $this->identityIdNumber;
    }

    /**
     * @param bool $identityIdNumber
     *
     * @return IdentityConfirm
     */
    public function setIdentityIdNumber(bool $identityIdNumber): IdentityConfirm
    {
        $this->identityIdNumber = $identityIdNumber;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIdentityContactNumber():  ?bool
    {
        return $this->identityContactNumber;
    }

    /**
     * @param bool $identityContactNumber
     *
     * @return IdentityConfirm
     */
    public function setIdentityContactNumber(bool $identityContactNumber): IdentityConfirm
    {
        $this->identityContactNumber = $identityContactNumber;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIdentityEmailAddress():  ?bool
    {
        return $this->identityEmailAddress;
    }

    /**
     * @param bool $identityEmailAddress
     *
     * @return IdentityConfirm
     */
    public function setIdentityEmailAddress(bool $identityEmailAddress): IdentityConfirm
    {
        $this->identityEmailAddress = $identityEmailAddress;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIdentityPhysicalAddress():  ?bool
    {
        return $this->identityPhysicalAddress;
    }

    /**
     * @param bool $identityPhysicalAddress
     *
     * @return IdentityConfirm
     */
    public function setIdentityPhysicalAddress(bool $identityPhysicalAddress): IdentityConfirm
    {
        $this->identityPhysicalAddress = $identityPhysicalAddress;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIdentityTag():  ?bool
    {
        return $this->identityTag;
    }

    /**
     * @param bool $identityTag
     *
     * @return IdentityConfirm
     */
    public function setIdentityTag(bool $identityTag): IdentityConfirm
    {
        $this->identityTag = $identityTag;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIdentityAlias():  ?bool
    {
        return $this->identityAlias;
    }

    /**
     * @param bool $identityAlias
     *
     * @return IdentityConfirm
     */
    public function setIdentityAlias(bool $identityAlias): IdentityConfirm
    {
        $this->identityAlias = $identityAlias;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIdentityLink():  ?bool
    {
        return $this->identityLink;
    }

    /**
     * @param bool $identityLink
     *
     * @return IdentityConfirm
     */
    public function setIdentityLink(bool $identityLink): IdentityConfirm
    {
        $this->identityLink = $identityLink;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIdentityLocationHistory():  ?bool
    {
        return $this->identityLocationHistory;
    }

    /**
     * @param bool $identityLocationHistory
     *
     * @return IdentityConfirm
     */
    public function setIdentityLocationHistory(bool $identityLocationHistory): IdentityConfirm
    {
        $this->identityLocationHistory = $identityLocationHistory;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIdentityHandle():  ?bool
    {
        return $this->identityHandle;
    }

    /**
     * @param bool $identityHandle
     *
     * @return IdentityConfirm
     */
    public function setIdentityHandle(bool $identityHandle): IdentityConfirm
    {
        $this->identityHandle = $identityHandle;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIdentityTitle():  ?bool
    {
        return $this->identityTitle;
    }

    /**
     * @param bool $identityTitle
     *
     * @return IdentityConfirm
     */
    public function setIdentityTitle(bool $identityTitle): IdentityConfirm
    {
        $this->identityTitle = $identityTitle;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIdentityAcademicHistory(): ?bool
    {
        return $this->identityAcademicHistory;
    }

    /**
     * @param bool $identityAcademicHistory
     *
     * @return IdentityConfirm
     */
    public function setIdentityAcademicHistory(bool $identityAcademicHistory): IdentityConfirm
    {
        $this->identityAcademicHistory = $identityAcademicHistory;

        return $this;
    }


}