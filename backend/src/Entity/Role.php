<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Role
 *
 * @package App\Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="roles")
 */
class Role
{
    use TimestampableEntity;

    /**
     * @var string The GUID of the role.
     *
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     * @Serializer\Accessor(getter="getId")
     */
    private $id;

    /**
     * @var string The human readable name of the role.
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string The internal value of the role.
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $value;

    /**
     * @var RoleGroup The group that this role belongs to.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\RoleGroup", inversedBy="roles")
     */
    private $roleGroup;

    /**
     * @return string
     */
    public function getId(): string
    {
        return (string) $this->id;
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
     * @return Role
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return Role
     */
    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return RoleGroup
     */
    public function getRoleGroup(): RoleGroup
    {
        return $this->roleGroup;
    }

    /**
     * @param RoleGroup $roleGroup
     *
     * @return Role
     */
    public function setRoleGroup(RoleGroup $roleGroup): self
    {
        $this->roleGroup = $roleGroup;

        return $this;
    }
}
