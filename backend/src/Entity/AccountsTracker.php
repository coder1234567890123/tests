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

/**
 *
 * @package App\Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="accounts_tracking")
 */
class AccountsTracker
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
     * @Groups({"read", "queued"})
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
     * @Groups({"read", "write", "queued"})
     */
    private $companyProduct;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"write", "read", "queued"})
     */
    private $monthlyUnits;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"write", "read", "queued"})
     */
    private $addUnit;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"write", "read", "queued"})
     */
    private $rejectUnit;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"write", "read", "queued"})
     */
    private $totalUnit;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"write", "read", "queued"})
     */
    private $monthlyReset = true;

    /**
     * @return string
     */
    public function getId(): string
    {
        return (string)$this->id;
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
     * @return AccountsTracker
     */
    public function setCompanyProduct(\App\Entity\CompanyProduct $companyProduct): AccountsTracker
    {
        $this->companyProduct = $companyProduct;

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
     * @return AccountsTracker
     */
    public function setAddUnit($addUnit): AccountsTracker
    {
        $this->addUnit = $addUnit;

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
     * @return AccountsTracker
     */
    public function setMonthlyUnits($monthlyUnits): AccountsTracker
    {
        $this->monthlyUnits = $monthlyUnits;

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
     * @return AccountsTracker
     */
    public function setRejectUnit($rejectUnit): AccountsTracker
    {
        $this->rejectUnit = $rejectUnit;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMonthlyReset()
    {
        return $this->monthlyReset;
    }

    /**
     * @param $monthlyReset
     *
     * @return AccountsTracker
     */
    public function setMonthlyReset($monthlyReset): AccountsTracker
    {
        $this->monthlyReset = $monthlyReset;

        return $this;
    }

    /**
     * @return Company|null
     */
    public function getCompany(): ?Company
    {
        return $this->company;
    }

    /**
     * @param \App\Entity\Company|null $company
     *
     * @return AccountsTracker
     */
    public function setCompany(?Company $company): AccountsTracker
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTotalUnit()
    {
        return $this->totalUnit;
    }

    /**
     * @param $totalUnit
     *
     * @return AccountsTracker
     */
    public function setTotalUnit($totalUnit): AccountsTracker
    {
        $this->totalUnit = $totalUnit;

        return $this;
    }


}