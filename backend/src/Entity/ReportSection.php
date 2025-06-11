<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @package App\Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="report_sections")
 */
class ReportSection
{
    use TimestampableEntity;

    /**
     * @var string The report section's guid.
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
     * @var string The report section name.
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Groups({"write", "read"})
     */
    private $name;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Question", mappedBy="reportSection")
     */
    private $questions;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     * @Groups({"read"})
     */
    private $enabled = true;

    /**
     * ReportSection constructor.
     */
    public function __construct()
    {
        $this->questions = new ArrayCollection();
    }

    /**
     * @return Collection
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    /**
     * @param Question $question
     * @return ReportSection
     */
    public function addQuestion(Question $question): self
    {
        $this->questions->add($question);

        return $this;
    }

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
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return ReportSection
     */
    public function setName(string $name): ReportSection
    {
        $this->name = $name;

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
     * @return ReportSection
     */
    public function setEnabled(bool $enabled): ReportSection
    {
        $this->enabled = $enabled;

        return $this;
    }
}
