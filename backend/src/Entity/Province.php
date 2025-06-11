<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Country
 *
 * @package App\Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="provinces")
 */
class Province
{
    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100)
     */
    private $code;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param string $code
     *
     * @return Province
     */
    public function setCode(string $code): Province
    {
        $this->code = $code;

        return $this;
    }


    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
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
    public function setName(string $name): Province
    {
        $this->name = $name;

        return $this;
    }
}
