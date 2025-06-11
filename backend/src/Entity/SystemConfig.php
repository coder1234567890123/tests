<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Question;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class SystemConfig
 *
 * @package App\Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="system_config")
 */
class SystemConfig
{
    use TimestampableEntity;
    /**
     * @var string
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
     * @var string
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Groups({"read"})
     */
    private $opt;
    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Groups({"write", "read"})
     */
    private $val;
    /**
     * @var string
     *
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     * @Groups({"write", "read"})
     */
    private $systemType;

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
    public function getOpt(): string
    {
        return $this->opt;
    }

    /**
     * @param string $opt
     *
     * @return SystemConfig
     */
    public function setOpt(string $opt): SystemConfig
    {
        $this->opt = $opt;

        return $this;
    }

    /**
     * @return string
     */
    public function getVal(): string
    {
        return $this->val;
    }

    /**
     * @param string $val
     *
     * @return SystemConfig
     */
    public function setVal(string $val): SystemConfig
    {
        $this->val = $val;

        return $this;
    }

    /**
     * @return string
     */
    public function getSystemType(): string
    {
        return $this->systemType;
    }

    /**
     * @param string $systemType
     *
     * @return SystemConfig
     */
    public function setSystemType(string $systemType): SystemConfig
    {
        $this->systemType = $systemType;

        return $this;
    }
}