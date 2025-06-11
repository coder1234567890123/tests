<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use App\Exception\InvalidReportTypeException;

/**
 * Class Country
 *
 * @package App\Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="company_product")
 */
class CompanyProduct
{
    use TimestampableEntity;
    /**
     * @var string The Company Product Used guid.
     *
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     * @Serializer\Type("uuid")
     * @Groups({"read", "companyProduct","bundleUsed"})
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
     *
     * @ORM\Column(type="string")
     * @Groups({"write", "read", "companyProduct", "default"})
     */
    private $productType;

    /**
     *
     * @ORM\Column(type="boolean")
     * @Groups({"write", "read", "companyProduct"})
     */
    private $monthlyRecurring = true;

    /**
     *
     * @ORM\Column(type="boolean")
     * @Groups({"write", "read", "companyProduct"})
     */
    private $unitsCarryOver = false;

    /**
     * @var User The user that created this subject.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @Groups({"write", "companyProduct","bundleUsed"})
     */
    private $createdBy;

    /**
     * @var User The user that created this subject.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @Groups({"write", "companyProduct"})
     */
    private $updatedBy;

    /**
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"write", "read", "companyProduct"})
     */
    private $bundleAmount;

    /**
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"write", "read", "companyProduct"})
     */
    private $rushedUnitPrice;

    /**
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"write", "read", "companyProduct"})
     */
    private $normalUnitPrice;

    /**
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"write", "read", "companyProduct"})
     */
    private $testUnitPrice;

    /**
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"write", "read", "companyProduct"})
     */
    private $amountCompleted;

    /**
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"write", "read", "companyProduct"})
     */
    private $additionalRequested;

    /**
     *
     * @ORM\Column(type="boolean")
     * @Groups({"write", "read", "companyProduct"})
     */
    private $suspended = false;

    /**
     * @return string
     */
    public function getId(): string
    {
        return (string)$this->id;
    }

    /**
     * @return Company|null
     */
    public function getCompany(): ?Company
    {
        return $this->company;
    }

    /**
     * @param Company|null $company
     *
     * @return CompanyProduct
     */
    public function setCompany(?Company $company): CompanyProduct
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return User
     */
    public function getCreatedBy(): User
    {
        return $this->createdBy;
    }

    /**
     * @param User $createdBy
     *
     * @return CompanyProduct
     */
    public function setCreatedBy(User $createdBy): CompanyProduct
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * @return User
     */
    public function getUpdatedBy(): User
    {
        return $this->updatedBy;
    }

    /**
     * @param User $updatedBy
     *
     * @return CompanyProduct
     */
    public function setUpdatedBy(User $updatedBy): CompanyProduct
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBundleAmount()
    {
        return $this->bundleAmount;
    }

    /**
     * @param $bundleAmount
     *
     * @return CompanyProduct
     */
    public function setBundleAmount($bundleAmount): CompanyProduct
    {
        $this->bundleAmount = $bundleAmount;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAmountCompleted()
    {
        return $this->amountCompleted;
    }

    /**
     * @param $amountCompleted
     *
     * @return CompanyProduct
     */
    public function setAmountCompleted($amountCompleted): CompanyProduct
    {
        $this->amountCompleted = $amountCompleted;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAdditionalRequested()
    {
        return $this->additionalRequested;
    }

    /**
     * @param $additionalRequested
     *
     * @return CompanyProduct
     */
    public function setAdditionalRequested($additionalRequested): CompanyProduct
    {
        $this->additionalRequested = $additionalRequested;

        return $this;
    }

    /**
     * @return bool
     */
    public function getSuspended(): bool
    {
        return $this->suspended;
    }

    /**
     * @param $suspended
     *
     * @return CompanyProduct
     */
    public function setSuspended(bool $suspended): CompanyProduct
    {
        $this->suspended = $suspended;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductType()
    {
        return $this->productType;
    }

    /**
     * @param $productType
     *
     * @return CompanyProduct
     */
    public function setProductType($productType): CompanyProduct
    {
        $this->productType = $productType;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMonthlyRecurring()
    {
        return $this->monthlyRecurring;
    }

    /**
     * @param $monthlyRecurring
     *
     * @return CompanyProduct
     */
    public function setMonthlyRecurring($monthlyRecurring): CompanyProduct
    {
        $this->monthlyRecurring = $monthlyRecurring;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRushedUnitPrice()
    {
        return $this->rushedUnitPrice;
    }

    /**
     * @param $rushedUnitPrice
     *
     * @return CompanyProduct
     */
    public function setRushedUnitPrice($rushedUnitPrice): CompanyProduct
    {
        $this->rushedUnitPrice = $rushedUnitPrice;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNormalUnitPrice()
    {
        return $this->normalUnitPrice;
    }

    /**
     * @param $normalUnitPrice
     *
     * @return CompanyProduct
     */
    public function setNormalUnitPrice($normalUnitPrice): CompanyProduct
    {
        $this->normalUnitPrice = $normalUnitPrice;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTestUnitPrice()
    {
        return $this->testUnitPrice;
    }

    /**
     * @param $testUnitPrice
     *
     * @return CompanyProduct
     */
    public function setTestUnitPrice($testUnitPrice): CompanyProduct
    {
        $this->testUnitPrice = $testUnitPrice;

        return $this;
    }

    /**
     * @return bool
     */
    public function isUnitsCarryOver(): bool
    {
        return $this->unitsCarryOver;
    }

    /**
     * @param bool $unitsCarryOver
     *
     * @return CompanyProduct
     */
    public function setUnitsCarryOver(bool $unitsCarryOver): CompanyProduct
    {
        $this->unitsCarryOver = $unitsCarryOver;

        return $this;
    }
}


