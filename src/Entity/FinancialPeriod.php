<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\FinancialPeriodRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @Table(schema="public")
 * @ORM\Entity(repositoryClass="App\Repository\FinancialPeriodRepository", repositoryClass=FinancialPeriodRepository::class)
 */
class FinancialPeriod
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $financialPeriodName;

    /**
     * @ORM\Column(type="datetime")
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $endDate;

    /**
     * @ORM\Column(type="boolean")
     */
    private $closed;

    /**
     * @ORM\Column(type="datetime")
     */
    private $extrasFirstFinancialDate;
    /**
     * @ORM\Column(type="datetime")
     */
    private $extrasFiscalEndOfTheFirstFiscalPeriod;

    /**
     * @ORM\Column(type="integer")
     */
    private $extrasAccountLabelLength;

    /**
     * @ORM\Column(type="integer")
     */
    private $extrasTradingAccountLength;

    /**
     * @ORM\Column(type="integer")
     */
    private $extrasAccountingLineLabelLength;

    /**
     * @ORM\Column(type="integer")
     */
    private $extrasAccountLength;

    /**
     * @ORM\Column(type="boolean")
     */
    private $extrasAuthorizationAlphaAccounts;

    /**
     * @ORM\Column(type="integer")
     */
    private $extrasAmountsLength;

    /**
     * @ORM\Column(type="boolean")
     */
    private $extrasWithQuantities;

    /**
     * @ORM\Column(type="boolean")
     */
    private $extrasWithDueDates;

    /**
     * @ORM\Column(type="boolean")
     */
    private $extrasWithMultipleDueDates;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $uuid;

    /**
     * @var Company
     *
     * @ORM\ManyToOne(targetEntity="Company")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="company_id", referencedColumnName="id",onDelete="CASCADE")
     * })
     */
    private $company;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return $this
     */
    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFinancialPeriodName(): ?string
    {
        return $this->financialPeriodName;
    }

    /**
     * @param string $financialPeriodName
     * @return $this
     */
    public function setFinancialPeriodName(string $financialPeriodName): self
    {
        $this->financialPeriodName = $financialPeriodName;

        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getStartDate(): ?DateTimeInterface
    {
        return $this->startDate;
    }

    /**
     * @param DateTimeInterface $startDate
     * @return $this
     */
    public function setStartDate(DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getEndDate(): ?DateTimeInterface
    {
        return $this->endDate;
    }

    /**
     * @param DateTimeInterface $endDate
     * @return $this
     */
    public function setEndDate(DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getClosed(): ?bool
    {
        return $this->closed;
    }

    /**
     * @param bool $closed
     * @return $this
     */
    public function setClosed(bool $closed): self
    {
        $this->closed = $closed;

        return $this;
    }


    /**
     * @return DateTimeInterface|null
     */
    public function getExtrasFirstFinancialDate(): ?DateTimeInterface
    {
        return $this->extrasFirstFinancialDate;
    }

    /**
     * @param DateTimeInterface $extrasFirstFinancialDate
     * @return $this
     */
    public function setExtrasFirstFinancialDate(DateTimeInterface $extrasFirstFinancialDate): self
    {
        $this->extrasFirstFinancialDate = $extrasFirstFinancialDate;

        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getExtrasFiscalEndOfTheFirstFiscalPeriod(): ?DateTimeInterface
    {
        return $this->extrasFiscalEndOfTheFirstFiscalPeriod;
    }

    /**
     * @param DateTimeInterface $extrasFiscalEndOfTheFirstFiscalPeriod
     * @return $this
     */
    public function setExtrasFiscalEndOfTheFirstFiscalPeriod(DateTimeInterface $extrasFiscalEndOfTheFirstFiscalPeriod): self
    {
        $this->extrasFiscalEndOfTheFirstFiscalPeriod = $extrasFiscalEndOfTheFirstFiscalPeriod;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getExtrasAccountLabelLength(): ?int
    {
        return $this->extrasAccountLabelLength;
    }

    /**
     * @param int $extrasAccountLabelLength
     * @return $this
     */
    public function setExtrasAccountLabelLength(int $extrasAccountLabelLength): self
    {
        $this->extrasAccountLabelLength = $extrasAccountLabelLength;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getExtrasTradingAccountLength(): ?int
    {
        return $this->extrasTradingAccountLength;
    }

    /**
     * @param int $extrasTradingAccountLength
     * @return $this
     */
    public function setExtrasTradingAccountLength(int $extrasTradingAccountLength): self
    {
        $this->extrasTradingAccountLength = $extrasTradingAccountLength;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getExtrasAccountingLineLabelLength(): ?int
    {
        return $this->extrasAccountingLineLabelLength;
    }

    /**
     * @param int $extrasAccountingLineLabelLength
     * @return $this
     */
    public function setExtrasAccountingLineLabelLength(int $extrasAccountingLineLabelLength): self
    {
        $this->extrasAccountingLineLabelLength = $extrasAccountingLineLabelLength;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getExtrasAccountLength(): ?int
    {
        return $this->extrasAccountLength;
    }

    /**
     * @param int $extrasAccountLength
     * @return $this
     */
    public function setExtrasAccountLength(int $extrasAccountLength): self
    {
        $this->extrasAccountLength = $extrasAccountLength;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getExtrasAuthorizationAlphaAccounts(): ?bool
    {
        return $this->extrasAuthorizationAlphaAccounts;
    }

    /**
     * @param bool $extrasAuthorizationAlphaAccounts
     * @return $this
     */
    public function setExtrasAuthorizationAlphaAccounts(bool $extrasAuthorizationAlphaAccounts): self
    {
        $this->extrasAuthorizationAlphaAccounts = $extrasAuthorizationAlphaAccounts;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getExtrasAmountsLength(): ?int
    {
        return $this->extrasAmountsLength;
    }

    /**
     * @param int $extrasAmountsLength
     * @return $this
     */
    public function setExtrasAmountsLength(int $extrasAmountsLength): self
    {
        $this->extrasAmountsLength = $extrasAmountsLength;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getExtrasWithQuantities(): ?bool
    {
        return $this->extrasWithQuantities;
    }

    /**
     * @param bool $extrasWithQuantities
     * @return $this
     */
    public function setExtrasWithQuantities(bool $extrasWithQuantities): self
    {
        $this->extrasWithQuantities = $extrasWithQuantities;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getExtrasWithDueDates(): ?bool
    {
        return $this->extrasWithDueDates;
    }

    /**
     * @param bool $extrasWithDueDates
     * @return $this
     */
    public function setExtrasWithDueDates(bool $extrasWithDueDates): self
    {
        $this->extrasWithDueDates = $extrasWithDueDates;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getExtrasWithMultipleDueDates(): ?bool
    {
        return $this->extrasWithMultipleDueDates;
    }

    /**
     * @param bool $extrasWithMultipleDueDates
     * @return $this
     */
    public function setExtrasWithMultipleDueDates(bool $extrasWithMultipleDueDates): self
    {
        $this->extrasWithMultipleDueDates = $extrasWithMultipleDueDates;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    /**
     * @param string $uuid
     * @return $this
     */
    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }
    /**
     * Set company
     *
     * @param Company|null $company
     * @return self
     */
    public function setCompany(Company $company = null): self
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get Company
     *
     * @return Company
     */
    public function getCompany(): Company
    {
        return $this->company;
    }
}
