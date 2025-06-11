<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Groups;

/**
 * Class GlobalWeights
 *
 * @package App\Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="global_weights")
 */
class GlobalWeights
{
    use TimestampableEntity;

    /**
     * @var string The GUID of the group.
     *
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     * @Serializer\Accessor(getter="getId")
     * @Groups({"read"})
     */
    private $id;

    /**
     * @var string The name socialPlatform.
     *
     * @ORM\Column(type="string")
     * @Groups({"write","read"})
     */
    private $socialPlatform;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Groups({"write","read"})
     */
    private $globalUsageWeighting;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Groups({"write","read"})
     */
    private $version;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Groups({"write","read"})
     */
    private $ordering;

    /**
     * @var string[]
     *
     * @ORM\Column(type="json", nullable=true)
     * @Serializer\Type("array")
     * @Groups({"write", "read"})
     */
    private $stdComments = [];

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
    public function getSocialPlatform(): string
    {
        return $this->socialPlatform;
    }

    /**
     * @param string $socialPlatform
     *
     * @return GlobalWeights
     */
    public function setSocialPlatform(string $socialPlatform): GlobalWeights
    {
        $this->socialPlatform = $socialPlatform;

        return $this;
    }

    /**
     * @return int
     */
    public function getGlobalUsageWeighting(): int
    {
        return $this->globalUsageWeighting;
    }

    /**
     * @param integer $globalUsageWeighting
     *
     * @return GlobalWeights
     */
    public function setGlobalUsageWeighting(int $globalUsageWeighting): GlobalWeights
    {
        $this->globalUsageWeighting = $globalUsageWeighting;

        return $this;
    }

    /**
     * @return int
     */
    public function getPrePlatformScoringMetric(): int
    {
        return $this->prePlatformScoringMetric;
    }

    /**
     * @param int $prePlatformScoringMetric
     *
     * @return GlobalWeights
     */
    public function setPrePlatformScoringMetric(int $prePlatformScoringMetric): GlobalWeights
    {
        $this->prePlatformScoringMetric = $prePlatformScoringMetric;

        return $this;
    }

    /**
     * @return int
     */
    public function getPostPlatformScoringMetric(): int
    {
        return $this->postPlatformScoringMetric;
    }

    /**
     * @param int $postPlatformScoringMetric
     *
     * @return GlobalWeights
     */
    public function setPostPlatformScoringMetric(int $postPlatformScoringMetric): GlobalWeights
    {
        $this->postPlatformScoringMetric = $postPlatformScoringMetric;

        return $this;
    }

    /**
     * @return int
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @param int $version
     *
     * @return GlobalWeights
     */
    public function setVersion(int $version): GlobalWeights
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return int
     */
    public function getOrdering(): int
    {
        return $this->ordering;
    }

    /**
     * @param int $ordering
     *
     * @return GlobalWeights
     */
    public function setOrdering(int $ordering): GlobalWeights
    {
        $this->ordering = $ordering;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getStdComments(): ?array
    {
        return $this->stdComments;
    }

    /**
     * @param array $stdComments
     *
     * @return GlobalWeights
     */
    public function setStdComments(array $stdComments): GlobalWeights
    {
        $this->stdComments = $stdComments;

        return $this;
    }
}