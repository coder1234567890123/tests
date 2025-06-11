<?php declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\AccessType;
use JMS\Serializer\Annotation\Type;

/**
 * Class Employment
 *
 * @package App\Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="employments")
 */
class Employment
{

    /**
     * @var string The Employment's guid.
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Subject", inversedBy="employments")
     * @Groups({"read", "write"})
     * @Serializer\Type("App\Entity\Subject")
     */
    private $subject;

    /**
     * @var string The subject's Employer.
     *
     * @ORM\Column(type="string")
     * @Groups({"write", "read", "queued"})
     */
    private $employer;

    /**
     * @var DateTimeImmutable
     *
     * @ORM\Column(type="datetime")
     * @Groups({"write", "read", "queued"})
     */
    private $startDate;

    /**
     * @var DateTimeImmutable
     *
     * @ORM\Column(type="datetime")
     * @Groups({"write", "read", "queued"})
     */
    private $endDate;

    /**
     * @var string The subject's Job Title.
     *
     * @ORM\Column(type="string")
     * @Groups({"write", "read", "queued"})
     */
    private $jobTitle;

    /**
     * @ORM\Embedded(class="App\Entity\Address")
     * @Serializer\Type("App\Entity\Address")
     * @Groups({"write", "read"})
     */
    private $address;

    /**
     * @var string The Employment's province.
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"write", "read"})
     */
    private $province;

    /**
     * @var Country The employer's country.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Country")
     * @Serializer\Type("App\Entity\Country")
     * @Groups({"write", "read"})
     *
     */
    private $country;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"write", "read", "queued"})
     */
    private $currentlyEmployed;

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
    public function getEmployer(): string
    {
        return $this->employer;
    }

    /**
     * @param string $employer
     *
     * @return Employment
     */
    public function setEmployer(string $employer): Employment
    {
        $this->employer = $employer;

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
     * @param Subject $subject
     *
     * @return Employment
     */
    public function setSubject(Subject $subject): Employment
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getStartDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    /**
     * @param DateTimeImmutable $startDate
     *
     * @return $this
     */
    public function setStartDate(DateTimeImmutable $startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getEndDate(): DateTimeImmutable
    {
        return $this->endDate;
    }

    /**
     * @param DateTimeImmutable $endDate
     *
     * @return Employment
     */
    public function setEndDate(DateTimeImmutable $endDate): Employment
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getJobTitle(): string
    {
        return $this->jobTitle;
    }

    /**
     * @param string $jobTitle
     *
     * @return Employment
     */
    public function setJobTitle(string $jobTitle): Employment
    {
        $this->jobTitle = $jobTitle;

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
     * @return Employment
     */
    public function setAddress(Address $address): Employment
    {
        $this->address = $address;

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
     * @return Employment
     */
    public function setProvince(string $province): Employment
    {
        $this->province = $province;

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
     * @param Country $country
     *
     * @return Employment
     */
    public function setCountry(?Country $country): Employment
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCurrentlyEmployed(): bool
    {
        return $this->currentlyEmployed;
    }

    /**
     * @param bool $currentlyEmployed
     *
     * @return Employment
     */
    public function setCurrentlyEmployed(bool $currentlyEmployed): Employment
    {
        $this->currentlyEmployed = $currentlyEmployed;

        return $this;
    }

    /**
     * @return string
     *
     */
    public function getStartDateFormatted(): string
    {
        return date_format($this->startDate, "Y-m-d");
    }

    /**
     * @return string
     *
     */
    public function getEndDateFormatted(): string
    {
        return date_format($this->endDate, "Y-m-d");
    }

}