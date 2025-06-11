<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class DefaultBranding
 *
 * @package App\Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="default_branding")
 */
class DefaultBranding
{

    use TimestampableEntity;

    /**
     * @var string The company's guide.
     *
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     * @Serializer\Accessor(getter="getId")
     *
     * @Groups({"read","minimalInfo","user_tracker"})
     *
     */
    private $id;

    /**
     *
     * @var string|null Saves Company theme color.
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"write", "read"})
     */
    private $themeColor;

    /**
     *
     * @var string|null Saves Company theme Second color.
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"write", "read"})
     */
    private $themeColorSecond;

    /**
     *
     * @var string|null Company Front Cover
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"write", "read"})
     */
    private $frontPage;

    /**
     *
     * @var string|null Company Co - Front Cover
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"write", "read"})
     */
    private $coFrontPage;

    /**
     *
     * @var string|null Company Footer link.
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"write", "read"})
     */
    private $logo;

    /**
     *
     * @var string|null Company Cover Logo.
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"write", "read"})
     */
    private $coverLogo;

    /**
     *
     * @var string|null Company Footer link.
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"write", "read"})
     */
    private $footerLink;

    /**
     * @var string
     *
     * @ORM\Column(type="text" , nullable=true)
     * @Groups({"write", "read"})
     */
    private $disclaimer;

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
    public function getThemeColor(): ?string
    {
        return $this->themeColor;
    }

    /**
     * @param string|null $themeColor
     *
     * @return DefaultBranding
     */
    public function setThemeColor(?string $themeColor): DefaultBranding

    {
        $this->themeColor = $themeColor;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFooterLink(): ?string
    {
        return $this->footerLink;
    }

    /**
     * @param string|null $footerLink
     *
     * @return DefaultBranding
     */
    public function setFooterLink(?string $footerLink): DefaultBranding
    {
        $this->footerLink = $footerLink;

        return $this;
    }

    /**
     * @return string
     */
    public function getDisclaimer(): string
    {
        return $this->disclaimer;
    }

    /**
     * @param string $disclaimer
     *
     * @return DefaultBranding
     */
    public function setDisclaimer(string $disclaimer): DefaultBranding
    {
        $this->disclaimer = $disclaimer;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFrontPage(): ?string
    {
        return $this->frontPage;
    }

    /**
     * @param string|null $frontPage
     *
     * @return DefaultBranding
     */
    public function setFrontPage(?string $frontPage): DefaultBranding
    {
        $this->frontPage = $frontPage;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLogo(): ?string
    {
        return $this->logo;
    }

    /**
     * @param string|null $logo
     *
     * @return DefaultBranding
     */
    public function setLogo(?string $logo): DefaultBranding
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCoverLogo(): ?string
    {
        return $this->coverLogo;
    }

    /**
     * @param string|null $coverLogo
     *
     * @return DefaultBranding
     */
    public function setCoverLogo(?string $coverLogo): DefaultBranding
    {
        $this->coverLogo = $coverLogo;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCoFrontPage(): ?string
    {
        return $this->coFrontPage;
    }

    /**
     * @param string|null $coFrontPage
     * @return $this
     */
    public function setCoFrontPage(?string $coFrontPage): DefaultBranding
    {
        $this->coFrontPage = $coFrontPage;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getThemeColorSecond(): ?string
    {
        return $this->themeColorSecond;
    }

    /**
     * @param string|null $themeColorSecond
     */
    public function setThemeColorSecond(?string $themeColorSecond): DefaultBranding
    {
        $this->themeColorSecond = $themeColorSecond;
        return $this;
    }


}