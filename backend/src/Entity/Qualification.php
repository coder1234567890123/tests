<?php declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Groups;

/**
 * Class Qualification
 *
 * @package App\Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="qualifications")
 */
class Qualification
{
    /**
     * @var string The qualification's guid.
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Subject", inversedBy="qualifications")
     * @Groups({"read"})
     * @Serializer\Type("App\Entity\Subject")
     */
    private $subject;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=250)
     * @Groups({"read","write"})
     */
    private $name;

    /**
     * @var DateTimeImmutable
     *
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"write"})
     * @Serializer\Type("DateTimeImmutable<'Y'>")
     */
    private $startDate;

    /**
     * @var DateTimeImmutable
     *
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"write"})
     * @Serializer\Type("DateTimeImmutable<'Y'>")
     */
    private $endDate;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=250)
     * @Groups({"write", "read"})
     */
    private $institute;

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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Qualification
     */
    public function setName(string $name): Qualification
    {
        $this->name = $name;

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
     * @return Qualification
     */
    public function setSubject(Subject $subject): Qualification
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return null|DateTimeImmutable
     */
    public function getStartDate(): ?DateTimeImmutable
    {
        return $this->startDate;
    }

    /**
     * @param DateTimeImmutable $startDate
     *
     * @return Qualification
     */
    public function setStartDate(DateTimeImmutable $startDate): Qualification
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @return null|DateTimeImmutable
     */
    public function getEndDate(): ?DateTimeImmutable
    {
        return $this->endDate;
    }

    /**
     * @param DateTimeImmutable $endDate
     *
     * @return Qualification
     */
    public function setEndDate(DateTimeImmutable $endDate): Qualification
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getInstitute(): string
    {
        return $this->institute;
    }

    /**
     * @param string
     *
     * @return Qualification
     */
    public function setInstitute(string $institute): Qualification
    {
        $this->institute = $institute;

        return $this;
    }

    /**
     * @return string
     *
     */
    public function getStartDateFormatted(): string
    {
        return date_format($this->startDate, "Y");
    }

    /**
     * @return string
     *
     */
    public function getEndDateFormatted(): string
    {
        return date_format($this->endDate, "Y");
    }

}