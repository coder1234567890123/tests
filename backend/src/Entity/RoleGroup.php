<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class RoleGroup
 *
 * @package App\Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="role_groups")
 */
class RoleGroup
{
    /**
     * @var string The GUID of the role group.
     *
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     * @Serializer\Accessor(getter="getId")
     */
    private $id;

    /**
     * @var string The name of the role group.
     *
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $radio = false;

    /**
     * @var Collection The roles that belong to this group.
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Role", mappedBy="roleGroup")
     */
    private $roles;

    /**
     * RoleGroup constructor.
     */
    public function __construct()
    {
        $this->roles = new ArrayCollection();
    }

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
     * @return RoleGroup
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    /**
     * @param Role $role
     *
     * @return RoleGroup
     */
    public function addRole(Role $role): self
    {
        $this->roles->add($role);

        return $this;
    }

    /**
     * @return bool
     */
    public function isRadio(): bool
    {
        return $this->radio;
    }

    /**
     * @param bool $radio
     *
     * @return RoleGroup
     */
    public function setRadio(bool $radio): self
    {
        $this->radio = $radio;

        return $this;
    }
}