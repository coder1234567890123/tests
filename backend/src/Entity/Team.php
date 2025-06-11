<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: kuda
 * Date: 7/22/19
 * Time: 2:49 PM
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Team
 *
 * @package App\Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="teams")
 */
class Team
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
     * @Groups({"read", "team"})
     */
    private $id;

    /**
     * @var Collection The users that belong to this team.
     *
     * @ORM\OneToMany(targetEntity="App\Entity\User", mappedBy="team")
     * @Groups({"read"})
     */
    private $users;

    /**
     * @var Collection The companies maintained by this team.
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Company", mappedBy="team")
     * @Groups({"read"})
     */
    private $companies;

    /**
     * @var User The Team Leader for the team.
     *
     * @ORM\OneToOne(targetEntity="App\Entity\User")
     * @Groups({"write", "read"})
     */
    private $teamLeader;

    /**
     * Team constructor.
     */
    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->companies = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return (string) $this->id;
    }

    /**
     * @return Collection
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * @param User $user
     *
     * @return Team
     */
    public function addUser(User $user): self
    {
        $this->users->add($user);

        return $this;
    }

    /**
     * @return Collection
     */
    public function getCompanies(): Collection
    {
        return $this->companies;
    }

    /**
     * @param Company $company
     *
     * @return Team
     */
    public function addCompany(Company $company): self
    {
        $this->companies->add($company);

        return $this;
    }

    /**
     * @return User
     */
    public function getTeamLeader(): User
    {
        return $this->teamLeader;
    }

    /**
     * @param User $teamLeader
     *
     * @return Team
     */
    public function setTeamLeader(User $teamLeader): self
    {
        $this->teamLeader = $teamLeader;

        return $this;
    }

    /**
     * @return string|null
     *
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("team_name")
     * @Groups({"team"})
     */
    public function getTeamName(): ?string
    {
        if ($this->getTeamLeader()) {
            return $this->getTeamLeader()->getFullName();
        }

        return null;
    }

    /**
     * @return string|null
     *
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("team_lead_email")
     * @Groups({"team"})
     */
    public function getTeamLeadEmail(): ?string
    {
        if ($this->getTeamLeader()) {
            return $this->getTeamLeader()->getEmail();
        }

        return null;
    }
}