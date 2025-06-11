<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Company;
use App\Entity\CompanyProduct;
use App\Entity\Subject;
use Doctrine\ORM\Mapping as ORM;

use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation\Groups;
use phpDocumentor\Reflection\Types\Boolean;
use App\Exception\InvalidCurrentStatusTypeException;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use App\Exception\InvalidReportTypeException;
use JMS\Serializer\Annotation\Type;

/**
 *
 * @package App\Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="accounts")
 */
class Accounts
{
    use TimestampableEntity;
    /**
     * @var string The Bundle Used guid.
     *
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     * @Serializer\Type("uuid")
     * @Groups({"read", "accounts"})
     */
    private $id;

    /**
     * @var Company The CompanyProduct's company.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Company")
     * @Groups({"write", "companyProduct"})
     */
    private $company;

    /**
     * @var CompanyProduct
     * @ORM\ManyToOne(targetEntity="App\Entity\CompanyProduct")
     * @Groups({"read", "write", "accounts"})
     */
    private $companyProduct;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"write", "read", "accounts"})
     */
    private $monthlyUnits;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"write", "read", "accounts"})
     */
    private $addUnit;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"write", "read", "accounts"})
     */
    private $unitUsed;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"write", "read", "accounts"})
     */
    private $rejectUnit;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"write", "read", "accounts"})
     */
    private $totalUnitUsed;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"write", "read", "accounts"})
     */
    private $totalUnitAdd;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"write", "read", "accounts"})
     */
    private $monthlyReset = false;

    /**
     * @var Subject
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Subject")
     * @Groups({"read", "write", "accounts", "queued"})
     */
    private $subject = null;

    /**
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"write", "read", "accounts", "accounts_write"})
     */
    private $requestType;

    /**
     *
     * @ORM\Column(type="boolean")
     * @Groups({"write", "read", "accounts"})
     */
    private $monthlyRecurring = true;

    /**
     * @var User The user that created this subject.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @Groups({"write"})
     */
    private $createdBy;

    /**
     *
     * @ORM\Column(type="boolean")
     * @Groups({"write", "read", "accounts"})
     */
    private $monthlyResetAmounts = true;


    /**
     *
     * @ORM\Column(type="boolean")
     * @Groups({"write", "read", "accounts"})
     */
    private $resetMonthlyAmounts = false;


    /**
     * @return string
     */
    public function getId(): string
    {
        return (string)$this->id;
    }

    /**
     * @return \App\Entity\Company
     */
    public function getCompany(): \App\Entity\Company
    {
        return $this->company;
    }

    /**
     * @param \App\Entity\Company $company
     *
     * @return Accounts
     */
    public function setCompany(\App\Entity\Company $company): Accounts
    {
        $this->company = $company;
        return $this;
    }

    /**
     * @return \App\Entity\CompanyProduct
     */
    public function getCompanyProduct(): \App\Entity\CompanyProduct
    {
        return $this->companyProduct;
    }

    /**
     * @param \App\Entity\CompanyProduct $companyProduct
     *
     * @return Accounts
     */
    public function setCompanyProduct(\App\Entity\CompanyProduct $companyProduct): Accounts
    {
        $this->companyProduct = $companyProduct;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMonthlyUnits()
    {
        return $this->monthlyUnits;
    }

    /**
     * @param $monthlyUnits
     *
     * @return Accounts
     */
    public function setMonthlyUnits($monthlyUnits): Accounts
    {
        $this->monthlyUnits = $monthlyUnits;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddUnit()
    {
        return $this->addUnit;
    }

    /**
     * @param $addUnit
     *
     * @return Accounts
     */
    public function setAddUnit($addUnit): Accounts
    {
        $this->addUnit = $addUnit;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRejectUnit()
    {
        return $this->rejectUnit;
    }

    /**
     * @param $rejectUnit
     *
     * @return Accounts
     */
    public function setRejectUnit($rejectUnit): Accounts
    {
        $this->rejectUnit = $rejectUnit;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTotalUnitUsed()
    {
        return $this->totalUnitUsed;
    }

    /**
     * @param $totalUnitUsed
     *
     * @return Accounts
     */
    public function setTotalUnitUsed($totalUnitUsed): Accounts
    {
        $this->totalUnitUsed = $totalUnitUsed;

        return $this;
    }


    /**
     * @return bool
     */
    public function isMonthlyReset(): bool
    {
        return $this->monthlyReset;
    }

    /**
     * @param bool $monthlyReset
     *
     * @return Accounts
     */
    public function setMonthlyReset(bool $monthlyReset): Accounts
    {
        $this->monthlyReset = $monthlyReset;
        return $this;
    }

    /**
     * @return Subject|null
     */
    public function getSubject(): ?Subject
    {
        return $this->subject;
    }

    /**
     * @return bool
     *
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("subject")
     * @Serializer\Type("string")
     * @Groups({"write", "read"})
     */
    public function getSubjectyCheck(): ?bool
    {
        if ($this->subject === null) {
            return null;
        } else {
            return false;
        }
    }

    /**
     * @param Subject $subject
     *
     * @return Accounts
     */
    public function setSubject(Subject $subject): Accounts
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRequestType()
    {
        return $this->requestType;
    }

    /**
     * @param $requestType
     *
     * @return Accounts
     */
    public function setRequestType($requestType): Accounts
    {
        $this->requestType = $requestType;
        return $this;
    }

    /**
     * @return bool
     */
    public function isMonthlyRecurring(): bool
    {
        return $this->monthlyRecurring;
    }

    /**
     * @param bool $monthlyRecurring
     *
     * @return Accounts
     */
    public function setMonthlyRecurring(bool $monthlyRecurring): Accounts
    {
        $this->monthlyRecurring = $monthlyRecurring;
        return $this;
    }

    /**
     * @return bool
     */
    public function isMonthlyResetAmounts(): bool
    {
        return $this->monthlyResetAmounts;
    }

    /**
     * @param bool $monthlyResetAmounts
     *
     * @return Accounts
     */
    public function setMonthlyResetAmounts(bool $monthlyResetAmounts): Accounts
    {
        $this->monthlyResetAmounts = $monthlyResetAmounts;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUnitUsed()
    {
        return $this->unitUsed;
    }

    /**
     * @param $unitUsed
     *
     * @return Accounts
     */
    public function setUnitUsed($unitUsed): Accounts
    {
        $this->unitUsed = $unitUsed;

        return $this;
    }

    /**
     * @return DateTime
     *
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("created_at")
     * @Type("DateTime<'Y-m-d'>")
     * @Groups({"read"})
     */
    public function getCreatedDate()
    {
        return $this->getCreatedAt();
    }

    /**
     * @param \DateTime $createdAt
     *
     * @return Accounts
     */
    public function setCreatedAt(\DateTime $createdAt): Accounts
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return mixed
     */
    public function getTotalUnitAdd()
    {
        return $this->totalUnitAdd;
    }

    /**
     * @param $totalUnitAdd
     *
     * @return Accounts
     */
    public function setTotalUnitAdd($totalUnitAdd): Accounts
    {
        $this->totalUnitAdd = $totalUnitAdd;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    /**
     * @param User $createdBy
     *
     * @return Accounts
     */
    public function setCreatedBy(User $createdBy): Accounts
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * @return bool
     */
    public function isResetMonthlyAmounts(): bool
    {
        return $this->resetMonthlyAmounts;
    }

    /**
     * @param bool $resetMonthlyAmounts
     *
     * @return Accounts
     */
    public function setResetMonthlyAmounts(bool $resetMonthlyAmounts): Accounts
    {
        $this->resetMonthlyAmounts = $resetMonthlyAmounts;

        return $this;
    }


}