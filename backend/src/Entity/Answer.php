<?php

declare(strict_types=1);

namespace App\Entity;

use App\Exception\InvalidPlatformException;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Subject;
use App\Entity\Proof;
use App\Entity\User;
use App\Entity\Question;
use App\Entity\Report;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @package App\Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="answers")
 */
class Answer
{
    use TimestampableEntity;

    /**
     * @var string The answer's guid.
     *
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     * @Serializer\Type("uuid")
     * @Groups({"read", "investigate"})
     */
    private $id;

    /**
     * @var string The answer to the question.
     *
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank()
     * @Groups({"write", "read", "investigate", "report"})
     */
    private $answer;

    /**
     * @var string The answer  score to the question.
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"write", "read", "investigate"})
     */
    private $score;

    /**
     * @var string The answer to the question.
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"write", "read", "investigate", "report"})
     */
    private $labelAnswer;

    /**
     * @var int The answer to the question.
     *
     * @ORM\Column(type="integer")
     * @Groups({"write", "read", "investigate"})
     */
    private $sliderValue = 0;

    /**
     * @var Question|null The question this answer belongs to.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Question", inversedBy="answers")
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"write", "read"})
     */
    private $question;

    /**
     * @var Report The report this answer belongs to.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Report", inversedBy="answers")
     * @ORM\JoinColumn()
     * @Groups({"write"})
     */
    private $report;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Proof", mappedBy="answer")
     * @Groups({"read", "investigate", "report"})
     */
    private $proofs;

    /**
     * @var User The user that made this answer.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @Groups({"write", "read"})
     */
    private $user;

    /**
     * @var Subject The subject this answer is based on.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Subject")
     * @Groups({"write", "read"})
     */
    private $subject;

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
     * @Groups({"read"})
     */
    private $notApplicable = false;

    /**
     * @var string Question default name.
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"write", "read", "investigate"})
     */
    private $defaultName;

    /**
     * @var string The platform the answer will apply to.
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"write", "read", "investigate"})
     */
    private $platform;

    /**
     * Answer constructor.
     */
    public function __construct()
    {
        $this->proofs = new ArrayCollection();
    }

    /**
     * @return Collection|null
     */
    public function getProofs(): ?Collection
    {
        return $this->proofs;
    }

    /**
     * @param Proof $proof
     *
     * @return Answer
     */
    public function addProof(Proof $proof): self
    {
        if (is_null($this->proofs)) {
            $this->proofs = new ArrayCollection();
        }

        $this->proofs->add($proof);

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
     * @return string|null
     */
    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    /**
     * @param string $answer
     *
     * @return Answer
     */
    public function setAnswer(string $answer): Answer
    {
        $this->answer = $answer;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLabelAnswer(): ?string
    {
        return $this->labelAnswer;
    }

    /**
     * @param string $labelAnswer
     *
     * @return Answer
     */
    public function setLabelAnswer(string $labelAnswer): Answer
    {
        $this->labelAnswer = $labelAnswer;

        return $this;
    }

    /**
     * @return int
     */
    public function getSliderValue(): int
    {
        return $this->sliderValue;
    }

    /**
     * @param int $sliderValue
     *
     * @return Answer
     */
    public function setSliderValue(int $sliderValue): Answer
    {
        $this->sliderValue = $sliderValue;

        return $this;
    }


    /**
     * @return Question|null
     */
    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    /**
     * @param Question $question
     *
     * @return Answer
     */
    public function setQuestion(Question $question): Answer
    {
        $this->question = $question;

        return $this;
    }

    /**
     * @return Report
     */
    public function getReport(): Report
    {
        return $this->report;
    }

    /**
     * @param Report report
     *
     * @return Answer
     */
    public function setReport(Report $report): Answer
    {
        $this->report = $report;

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
     * @return Answer
     */
    public function setUser(User $user): Answer
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Subject|null
     */
    public function getSubject(): ?Subject
    {
        return $this->subject;
    }

    /**
     * @param Subject $subject
     *
     * @return Answer
     */
    public function setSubject(Subject $subject): Answer
    {
        $this->subject = $subject;

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
     * @return Answer
     */
    public function setEnabled(bool $enabled): Answer
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function isNotApplicable(): bool
    {
        return $this->notApplicable;
    }

    /**
     *
     * @param bool $notApplicable
     *
     * @return Answer
     */
    public function setNotApplicable(bool $notApplicable): Answer
    {
        $this->notApplicable = $notApplicable;

        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultName(): ?string
    {
        return $this->defaultName;
    }

    /**
     * @param string $defaultName
     *
     * @return Answer
     */
    public function setDefaultName(string $defaultName): Answer
    {
        $this->defaultName = $defaultName;

        return $this;
    }

    /**
     * @return string | null
     */
    public function getScore(): ?string
    {
        return $this->score;
    }

    /**
     * @param string $score
     *
     * @return Answer
     */
    public function setScore(string $score): Answer
    {
        $this->score = $score;
        return $this;
    }

    /**
     * @return string
     */
    public function getPlatform(): ?string
    {
        return $this->platform;
    }

    /**
     * @param string $platform
     *
     * @return Answer
     */
    public function setPlatform(string $platform): Answer
    {
        $this->platform = $platform;

        return $this;
    }
}
