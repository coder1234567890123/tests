<?php declare(strict_types=1);

namespace App\Entity;

use App\Exception\InvalidPlatformException;
use App\Exception\InvalidAnswerTypeException;
use App\Exception\InvalidReportTypeException;
use App\Exception\AnswerOptionException;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Profile;
use App\Entity\Answer;
use App\Entity\Subject;
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
 * @ORM\Table(name="questions")
 */
class Question
{
    // Valid answerTypes
    const ANSWER_TYPE_TEXT = 'text';
    const ANSWER_TYPE_YESNO = 'yes_no';
    const ANSWER_TYPE_MULTIPLE = 'multiple_choice';

    const ANSWER_TYPES = [
        self::ANSWER_TYPE_TEXT,
        self::ANSWER_TYPE_YESNO,
        self::ANSWER_TYPE_MULTIPLE
    ];

    use TimestampableEntity;

    /**
     * @var string The question's guid.
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
     * @var string The question to be asked.
     *
     * @ORM\Column(type="string")
     * @Serializer\Type("string")
     * @Assert\NotBlank()
     * @Groups({"write", "read", "investigate", "report"})
     */
    private $question;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Answer", mappedBy="question")
     * @Groups({"read", "investigate", "report"})
     */
    private $answers;

    /**
     * @var string The report label.
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"write", "read", "report"})
     */
    private $reportLabel;

    /**
     * @var string The type of answer accepted by question.
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Groups({"write", "read", "investigate"})
     */
    private $answerType;

    /**
     * @var string[] The type of report this question belongs to.
     *
     * @ORM\Column(type="json")
     * @Serializer\Type("array")
     * @Groups({"write", "read", "report"})
     */
    private $reportTypes = [];

    /**
     * @var string[] Answers to select from if multiple choice.
     *
     * @ORM\Column(type="json", nullable=true)
     * @Serializer\Type("array")
     * @Groups({"write", "read", "investigate"})
     */
    private $answerOptions = [];

    /**
     * @var string[] Answers scores if multiple choice.
     *
     * @ORM\Column(type="json", nullable=true)
     * @Serializer\Type("array")
     * @Groups({"write", "read", "investigate"})
     */
    private $answerScore = [];

    /**
     * @var string[] Minimum and maximum for slider.
     *
     * @ORM\Column(type="json", nullable=true)
     * @Serializer\Type("array")
     * @Groups({"write", "read", "investigate"})
     */
    private $sliderValues = [];

    /**
     * @var string The platform the question will apply to.
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Groups({"write", "read", "report", "investigate"})
     */
    private $platform;

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
     * @Groups({"write", "read", "investigate"})
     */
    private $slider = true;

    /**
     * @var int Average if slider is used.
     *
     * @ORM\Column(type="integer")
     * @Groups({"write", "read", "investigate"})
     */
    private $sliderAverage = 0;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"read", "write"})
     */
    private $orderNumber = 0;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     * @Groups({"write", "read", "investigate"})
     */
    private $defaultQuestions = false;

    /**
     * @var string Question default name.
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"write", "read", "investigate"})
     */
    private $defaultName = null;

    /**
     * Question constructor.
     */
    public function __construct()
    {
        $this->answers = new ArrayCollection();
    }

    /**
     * @return Collection
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    /**
     * @return Question
     */
    public function clearAnswers(): self
    {
        $this->answers = new ArrayCollection();

        return $this;
    }

    /**
     * @param Answer $answer
     *
     * @return Question
     */
    public function addAnswer(Answer $answer): self
    {
        $this->answers->add($answer);

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
    public function getQuestion(): ?string
    {
        return $this->question;
    }

    /**
     * @param string $question
     *
     * @return Question
     */
    public function setQuestion(string $question): Question
    {
        $this->question = $question;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getReportLabel(): ?string
    {
        return $this->reportLabel;
    }

    /**
     * @param string $reportLabel
     *
     * @return Question
     */
    public function setReportLabel(string $reportLabel): Question
    {
        $this->reportLabel = $reportLabel;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAnswerType(): ?string
    {
        return $this->answerType;
    }

    /**
     * @param string $answerType
     *
     * @return Question
     * @throws InvalidAnswerTypeException
     */
    public function setAnswerType(string $answerType): Question
    {
        if (!in_array($answerType, self::ANSWER_TYPES)) {
            throw new InvalidAnswerTypeException();
        }

        $this->answerType = $answerType;

        return $this;
    }

    /**
     * @return array
     */
    public function getReportTypes(): array
    {
        return $this->reportTypes;
    }

    /**
     * @param array $reportTypes
     *
     * @return Question
     * @throws InvalidReportTypeException
     */
    public function setReportTypes(array $reportTypes): Question
    {
        // validate each report type before set (accepted as per current list)
        foreach ($reportTypes as $reportType) {
            if (!in_array($reportType, $this->getAllReportTypes())) {
                throw new InvalidReportTypeException();
            }
        }

        $this->reportTypes = $reportTypes;

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
     * @return Question
     * @throws InvalidPlatformException
     */
    public function setPlatform(string $platform): Question
    {
        // validate platform before set
        if (!in_array($platform, $this->getProfilePlatforms())) {
            throw new InvalidPlatformException();
        }

        $this->platform = $platform;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getAnswerOptions(): ?array
    {
        return $this->answerOptions;
    }

    /**
     * @param array $answerOptions
     *
     * @return Question
     * @throws AnswerOptionException
     */
    public function setAnswerOptions(array $answerOptions): Question
    {
        if ($this->getAnswerType() != self::ANSWER_TYPE_MULTIPLE && count($answerOptions) > 0) {
            $this->answerOptions = [];
        } else {
            if ($this->getAnswerType() === self::ANSWER_TYPE_MULTIPLE && count($answerOptions) === 0) {
                throw new AnswerOptionException();
            }

            $this->answerOptions = $answerOptions;
        }

        return $this;
    }

    /**
     * @return array|null
     */
    public function getAnswerScore(): ?array
    {
        return $this->answerScore;
    }

    /**
     * @param array $answerScore
     *
     * @return Question
     */
    public function setAnswerScore(array $answerScore): Question
    {
        if ($this->getAnswerType() != self::ANSWER_TYPE_MULTIPLE && count($this->getAnswerOptions()) > 0) {
            $this->answerScore = [];
        } else {
            $this->answerScore = $answerScore;
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getSliderAverage(): int
    {
        return $this->sliderAverage;
    }

    /**
     * @param int $sliderAverage
     *
     * @return Question
     */
    public function setSliderAverage(int $sliderAverage): Question
    {
        $this->sliderAverage = $sliderAverage;

        return $this;
    }

    /**
     * @return array
     * Get accepted list of platforms (statically)
     */
    public function getProfilePlatforms(): array
    {
        return \App\Entity\Profile::PLATFORMS;
    }

    /**
     * @return array
     * Get accepted list of report types (statically)
     */
    public function getAllReportTypes(): array
    {
        return \App\Entity\Subject::REPORT_TYPES;
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
     * @return Question
     */
    public function setEnabled(bool $enabled): Question
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSlider(): bool
    {
        return $this->slider;
    }

    /**
     *
     * @param bool $slider
     *
     * @return Question
     */
    public function setSlider(bool $slider): Question
    {
        $this->slider = $slider;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getSliderValues(): ?array
    {
        return $this->sliderValues;
    }

    /**
     * @param array $sliderValues
     *
     * @return Question
     */
    public function setSliderValues(array $sliderValues): Question
    {
        $this->sliderValues = $sliderValues;

        return $this;
    }

    /**
     * @return int
     */
    public function getOrderNumber(): int
    {
        return $this->orderNumber;
    }

    /**
     * @param int $orderNumber
     *
     * @return Question
     */
    public function setOrderNumber(int $orderNumber): Question
    {
        $this->orderNumber = $orderNumber;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDefaultQuestions(): bool
    {
        return $this->defaultQuestions;
    }

    /**
     * @param bool $defaultQuestions
     *
     * @return Question
     */
    public function setDefaultQuestions(bool $defaultQuestions): Question
    {
        $this->defaultQuestions = $defaultQuestions;

        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultName(): string
    {
        return $this->defaultName;
    }

    /**
     * @param string $defaultName
     *
     * @return Question
     */
    public function setDefaultName(string $defaultName): Question
    {
        $this->defaultName = $defaultName;

        return $this;
    }

}
