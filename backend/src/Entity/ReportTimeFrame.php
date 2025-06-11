<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation as Serializer;

/**
 * @package App\Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="report_time_frames")
 */
class ReportTimeFrame
{
    use TimestampableEntity;

    /**
     * @var string The report's guid.
     *
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     * @Serializer\Type("uuid")
     * @Groups({"read", "reportTimeFrame"})
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     * @Groups({"read","reportTimeFrame"})
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"write", "read","reportTimeFrame"})
     */
    private $hours;

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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param $name
     *
     * @return ReportTimeFrame
     */
    public function setName($name): ReportTimeFrame
    {
        $this->name = $name;

        return  $this;
    }

    /**
     * @return int
     */
    public function getHours(): int
    {
        return $this->hours;
    }

    /**
     * @param $hours
     *
     * @return ReportTimeFrame
     */
    public function setHours($hours): ReportTimeFrame
    {
        $this->hours = $hours;

        return $this;
    }
}
