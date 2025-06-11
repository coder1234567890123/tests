<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Question;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Hateoas\Configuration\Annotation\Relation;
use Hateoas\Configuration\Annotation\Route;
use Hateoas\Configuration\Annotation\Exclusion;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class User
 *
 * @package App\Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="users")
 * @UniqueEntity("email")
 * @Relation("self", href=@Route("user_get", parameters={"id"="expr(object.getId())"}), exclusion=@Exclusion(groups={"read"}))
 */
class User implements UserInterface, EquatableInterface
{
    use TimestampableEntity;

    /**
     * @var string The user's guid.
     *
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     * @Serializer\Accessor(getter="getId")
     *
     * @Groups({"read"})
     */
    private $id;

    /**
     * @var Group The group that the user belongs to.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Group", inversedBy="users")
     */
    private $group;

    /**
     * @var Team The team that is assigned to this company.
     *
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @ORM\ManyToOne(targetEntity="App\Entity\Team", inversedBy="users")
     * @Groups({"write"})
     */
    private $team;

    /**
     * @var string The user's email address.
     *
     * @ORM\Column(type="string", unique=true)
     * @Assert\Email()
     * @Assert\NotBlank()
     * @Groups({"read", "write" , "user"})
     */
    private $email;

    /**
     * @var string The user's password.
     *
     * @ORM\Column(type="string", length=60)
     * @Assert\NotBlank()
     * @Groups({"write"})
     */
    private $password;

    /**
     * @var string The user's first name.
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     *
     * @Groups({"read", "write", "minimalInfo" , "user"})
     *
     */
    private $firstName;

    /**
     * @var string The user's last name.
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     *
     * @Groups({"read", "write", "minimalInfo" , "user" })
     *
     */
    private $lastName;

    /**
     * @var string The user's Telephone Number.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"write", "read" , "user" })
     */
    private $telNumber;

    /**
     * @var string The user's Fax Number.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"write", "read" , "user"})
     */

    private $faxNumber;

    /**
     * @var string The user's Mobile Number.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"write", "read" , "user"})
     */
    private $mobileNumber;

    /**
     * @var string The user's Website.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"write", "read" })
     */
    private $website;

    /**
     * @var bool Whether the user is currently enabled or not.
     *
     * @ORM\Column(type="boolean")
     * @Groups({"read"})
     */
    private $enabled = true;

    /**
     * @var Saves Subject Image.
     *
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"write", "read"})
     */
    private $imageFile;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     * @Groups({"read"})
     *
     */
    private $archived = false;

    /**
     * @var array The list of roles assigned to the user.
     *
     * @ORM\Column(type="json_array")
     * @Groups({"read", "write" , "user"})
     */
    private $roles = [];

    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true, nullable=true)
     */
    private $token;

    /**
     * @var DateTimeImmutable
     *
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $tokenRequested;

    /**
     * @var Company The Users's company.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Company")
     * @Serializer\Type("App\Entity\Company")
     * @Groups({"write", "read" })
     */
    private $company;

    /**
     * @return string
     */
    public function getId(): ?string
    {
        return (string)$this->id;
    }

    /**
     * @return Group
     */
    public function getGroup(): Group
    {
        return $this->group;
    }

    /**
     * @param Group $group
     *
     * @return User
     */
    public function setGroup(Group $group): self
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @return Team
     */
    public function getTeam(): ?Team
    {
        return $this->team;
    }

    /**
     * @param Team $team
     *
     * @return User
     */
    public function setTeam($team = Null): ?User
    {
        $this->team = $team;

        return $this;
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->getFirstName() . " " . $this->getLastName();
    }

    /**
     * @return string
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     *
     * @return User
     */
    public function setFirstName(string $firstName): User
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     *
     * @return User
     */
    public function setLastName(string $lastName): User
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return string
     */
    public function getTelNumber(): ?string
    {
        return $this->telNumber;
    }

    /**
     * @param string $telNumber
     *
     * @return User
     */
    public function setTelNumber(string $telNumber): User
    {
        $this->telNumber = $telNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getFaxNumber(): ?string
    {
        return $this->faxNumber;
    }

    /**
     * @param string $faxNumber
     *
     * @return User
     */
    public function setFaxNumber(string $faxNumber): User
    {
        $this->faxNumber = $faxNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getMobileNumber(): ?string
    {
        return $this->mobileNumber;
    }

    /**
     * @param string $mobileNumber
     *
     * @return User
     */
    public function setMobileNumber(string $mobileNumber): User
    {
        $this->mobileNumber = $mobileNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getWebsite(): ?string
    {
        return $this->website;
    }

    /**
     * @param string $website
     *
     * @return User
     */
    public function setWebsite(string $website): User
    {
        $this->website = $website;

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
     * @return User
     */
    public function setEnabled(bool $enabled): User
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isArchived(): bool
    {
        return $this->archived;
    }

    /**
     *
     * @param bool $archived
     *
     * @return User
     */
    public function setArchived(bool $archived): User
    {
        $this->archived = $archived;

        return $this;
    }

    /**
     * @param string $role
     *
     * @return User
     */
    public function addRole(string $role): User
    {
        if (!in_array($role, $this->roles)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    /**
     * @param string $role
     *
     * @return User
     */
    public function removeRole(string $role): User
    {
        return $this;
    }

    /**
     * @param string $role
     *
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles());
    }

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return array The user roles
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @return User
     */
    public function resetRoles(): User
    {
        $this->roles = [];

        return $this;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     *
     * @return User
     */
    public function setToken(?string $token): User
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getTokenRequested(): DateTimeImmutable
    {
        return $this->tokenRequested;
    }

    /**
     * @param DateTimeImmutable $tokenRequested
     *
     * @return User
     */
    public function setTokenRequested(?DateTimeImmutable $tokenRequested): User
    {
        $this->tokenRequested = $tokenRequested;

        return $this;
    }

    /**
     * The equality comparison should neither be done by referential equality
     * nor by comparing identities (i.e. getId() === getId()).
     *
     * However, you do not need to compare every attribute, but only those that
     * are relevant for assessing whether re-authentication is required.
     *
     * @param UserInterface $user
     *
     * @return bool
     */
    public function isEqualTo(UserInterface $user)
    {
        if (!$user instanceof User) return false;
        if ($user->getPassword() != $this->getPassword()) return false;
        if ($user->getEmail() != $this->getEmail()) return false;

        return true;
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string|null $password
     *
     * @return User
     */
    public function setPassword(?string $password): User
    {
        if ($password !== '' && !is_null($password)) {
            $this->password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return User
     */
    public function setEmail(string $email): User
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername(): ?string
    {
        return $this->email;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
    }

    /**
     * @return null|string
     */
    public function __toString()
    {
        return "{$this->getFirstName()} {$this->getLastName()} ({$this->getEmail()})";
    }

    /**
     * @return Company|null
     */
    public function getCompany(): ?Company
    {
        return $this->company;
    }

    /**
     * @param Company $company
     *
     * @return User
     */
    public function setCompany(?Company $company): User
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return string|null
     *
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("company")
     * @Serializer\Type("string")
     * @Groups({"write", "read"})
     */
    public function getCompanyName(): ?string
    {
        if ($this->company === null) {
            return null;
        }

        return $this->company->getName();
    }

    /**
     *
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("company")
     * @Groups({"write", "read"})
     * @return string|null
     */
    public function getCompanyId(): ?string
    {
        if ($this->company === null) {
            return null;
        }

        return $this->company->getId();
    }

    /**
     * @return string|null
     */
    public function getImageFile(): ?string
    {
        return $this->imageFile;
    }

    /**
     * @param $imageFile
     *
     * @return User
     */
    public function setImageFile($imageFile): User
    {
        $this->imageFile = $imageFile;
        return $this;
    }
}
