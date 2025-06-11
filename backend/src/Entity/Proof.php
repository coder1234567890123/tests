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
 * @ORM\Table(name="proofs")
 * @IsEnabled(field="enabled")
 */
class Proof
{
    use TimestampableEntity;

    /**
     * @var string The proof's guid.
     *
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     * @Serializer\Type("uuid")
     * @Groups({"read", "investigate", "report","proof"})
     */
    private $id;

    /**
     * @var ProofStorage details about where is the proof is stored
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ProofStorage" )
     * @Groups({"write", "read", "investigate", "report"})
     */
    private $proofStorage;

    /**
     * @var Answer The answer this proof belongs to.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Answer", inversedBy="proofs")
     * @Groups({"write", "read"})
     */
    private $answer;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"write", "read","proof", "report"})
     */
    private $comment;

    /**
     * @var array
     *
     * @ORM\Column(type="json", nullable=true)
     * @Serializer\Type("array")
     * @Groups({"write", "read","proof"})
     */
    private $behaviourScores;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     * @Groups({"read","proof"})
     */
    private $enabled = true;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     * @Groups({"write","read","proof", "report"})
     */
    private $trait;

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
    public function getProofStorage(): ProofStorage
    {
        return $this->proofStorage;
    }

    /**
     * @param ProofStorage $proofStorage
     *
     * @return Proof
     */
    public function setProofStorage(ProofStorage $proofStorage): Proof
    {
        $this->proofStorage = $proofStorage;

        return $this;
    }

    /**
     * @return Answer
     */
    public function getAnswer(): ?Answer
    {
        return $this->answer;
    }

    /**
     * @param Answer $answer
     *
     * @return Proof
     */
    public function setAnswer(Answer $answer): Proof
    {
        $this->answer = $answer;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getBehaviourScores(): ?array
    {
        return $this->behaviourScores;
    }

    /**
     * @param array $behaviourScores
     *
     * @return Proof
     */
    public function setBehaviourScores($behaviourScores): Proof
    {
        $this->behaviourScores = $behaviourScores;

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
     * @return Proof
     */
    public function setEnabled(bool $enabled): Proof
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @param comment $comment
     *
     * @return Proof
     */
    public function setComment(comment $comment): Proof
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return bool
     */
    public function isTrait(): bool
    {
        return $this->trait;
    }

    /**
     * @param bool $trait
     *
     * @return Proof
     */
    public function setTrait(bool $trait): Proof
    {
        $this->trait = $trait;

        return $this;
    }
}
