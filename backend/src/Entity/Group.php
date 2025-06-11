<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Group
 *
 * @package App\Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="groups")
 */
class Group
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
     */
    private $id;

    /**
     * @var string The name of the group.
     */
    private $name;

    /**
     * @var array The roles that this group has.
     *
     * @ORM\Column(type="json_array")
     */
    private $roles;

    /**
     * @var Collection The users that belong to this group.
     *
     * @ORM\OneToMany(targetEntity="App\Entity\User", mappedBy="group")
     */
    private $users;

    /**
     * @var bool Whether or not the group is enabled.
     *
     * @ORM\Column(type="boolean")
     */
    private $enabled;

    /**
     * @var boolean Whether or not the group is archived.
     */
    private $archived;

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
     * @return Group
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     *
     * @return Group
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * @param Collection $users
     *
     * @return Group
     */
    public function setUsers(Collection $users): self
    {
        $this->users = $users;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     *
     * @return Group
     */
    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function isArchived(): bool
    {
        return $this->archived;
    }

    /**
     * @param bool $archived
     *
     * @return Group
     */
    public function setArchived(bool $archived): self
    {
        $this->archived = $archived;

        return $this;
    }
}