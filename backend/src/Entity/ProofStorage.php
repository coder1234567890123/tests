<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @package App\Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="proofstorages")
 */
class ProofStorage
{
    use TimestampableEntity;

    /**
     * @var string The ProofStorage's guid.
     *
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     * @Serializer\Type("uuid")
     * @Groups({"read", "investigate", "report"})
     */
    private $id;

    /**
     * @var Subject
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Subject", inversedBy="qualifications")
     * @Groups({"read", "write", "investigate", "report"})
     * @Serializer\Type("App\Entity\Subject")
     */
    private $subject;

    /**
     * @var User The user that created this image upload.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @Groups({"write", "read"})
     */
    private $createdBy;

    /**
     * @var Saves ProofStorage Image.
     *
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"write", "read", "investigate", "report"})
     */
    private $imageFile;


    /**
     * @return string
     */
    public function getId(): string
    {
        return (string) $this->id;
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
     * @return ProofStorage
     */
    public function setSubject(Subject $subject): ProofStorage
    {
        $this->subject = $subject;

        return $this;
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
     * @return ProofStorage
     */
    public function setCreatedBy(User $createdBy): ProofStorage
    {
        $this->createdBy = $createdBy;

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
     * @return ProofStorage
     */
    public function setImageFile(string $imageFile): ProofStorage
    {
        $this->imageFile = $imageFile;

        return $this;
    }

}
