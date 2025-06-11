<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use App\Exception\InvalidReportTypeException;

/**
 * Class Product
 *
 * @package App\Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="products")
 */
class Product
{
    use TimestampableEntity;
    /**
     * @var string The Product's guid.
     *
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     * @Serializer\Type("uuid")
     * @Groups({"read", "investigate", "companyProduct"})
     */
    private $id;

    /**
     *
     * @ORM\Column(type="string" ,nullable=true)
     * @Groups({"write", "read", "queued", "companyProduct"})
     */
    private $name;

    /**
     *
     * @ORM\Column(type="string" ,nullable=true)
     * @Groups({"write", "read", "queued", "companyProduct"})
     */
    private $type;

    /**
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"write", "read", "queued", "companyProduct"})
     */
    private $bundle;

    /**
     *
     * @ORM\Column(type="boolean")
     * @Groups({"write", "read", "queued"})
     */
    private $enable = true;

    /**
     * @return string
     */
    public function getId(): string
    {
        return (string)$this->id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     *
     * @return Product
     */
    public function setName($name): Product
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $type
     *
     * @return Product
     */
    public function setType($type): Product
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBundle()
    {
        return $this->bundle;
    }

    /**
     * @param $bundle
     *
     * @return Product
     */
    public function setBundle($bundle): Product
    {
        $this->bundle = $bundle;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEnable()
    {
        return $this->enable;
    }

    /**
     * @param $enable
     *
     * @return Product
     */
    public function setEnable($enable): Product
    {
        $this->enable = $enable;
        return $this;
    }
}