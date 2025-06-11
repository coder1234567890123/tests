<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Embeddable
 */
class Address
{
    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Serializer\Type("string")
     * @Groups({"write", "read"})
     */
    private $street;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Serializer\Type("string")
     * @Groups({"write", "read"})
     */
    private $suburb;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Serializer\Type("string")
     * @Groups({"write", "read"})
     */
    private $postalCode;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Serializer\Type("string")
     * @Groups({"write", "read"})
     */
    private $city;

    /**
     * @return string|null
     */
    public function getStreet(): ?string
    {
        return $this->street;
    }

    /**
     * @param string $street
     *
     * @return Address
     */
    public function setStreet(string $street): self
    {
        $this->street = $street;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSuburb(): ?string
    {
        return $this->suburb;
    }

    /**
     * @param string $suburb
     *
     * @return Address
     */
    public function setSuburb(string $suburb): self
    {
        $this->suburb = $suburb;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    /**
     * @param string $postalCode
     *
     * @return Address
     */
    public function setPostalCode(string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string $city
     *
     * @return Address
     */
    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }
}
