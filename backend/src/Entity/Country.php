<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use JMS\Serializer\Annotation\Groups;

/**
 * Class Country
 *
 * @package App\Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="countries")
 */
class Country
{
    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @Groups({"read", "write"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=2)
     * @Groups({"read", "write"})
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100)
     * @Groups({"read", "write"})
     */
    private $name;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     *
     * @return Country
     */
    public function setCode(string $code): Country
    {
        $this->code = $code;

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
     * @return Country
     */
    public function setName(string $name): Country
    {
        $this->name = $name;

        return $this;
    }
}