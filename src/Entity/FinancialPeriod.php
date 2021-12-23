<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\FinancialPeriodRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass=FinancialPeriodRepository::class)
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
    private $ExtrasFirstFinancialDate;
    /**
     * @ORM\Column(type="datetime")
     */
    private $ExtrasFiscalEndOfTheFirstFiscalPeriod;

    /**
     * @ORM\Column(type="integer")
     */
    private $ExtrasAccountLabelLength;

    /**
     * @ORM\Column(type="integer")
     */
    private $ExtrasTradingAccountLength;

    /**
     * @ORM\Column(type="integer")
     */
    private $ExtrasAccountingLineLabelLength;

    /**
     * @ORM\Column(type="integer")
     */
    private $ExtrasAccountLength;

    /**
     * @ORM\Column(type="boolean")
     */
    private $ExtrasAuthorizationAlphaAccounts;

    /**
     * @ORM\Column(type="integer")
     */
    private $ExtrasAmountsLength;

    /**
     * @ORM\Column(type="boolean")
     */
    private $ExtrasWithQuantities;

    /**
     * @ORM\Column(type="boolean")
     */
    private $ExtrasWithDueDates;

    /**
     * @ORM\Column(type="boolean")
     */
    private $ExtrasWithMultipleDueDates;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $uuid;

    /**
     * @var \Company
     *
     * @ORM\ManyToOne(targetEntity="Company")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="company_id", referencedColumnName="id",onDelete="CASCADE")
     * })
     */
    private $company;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getFinancialPeriodName(): ?string
    {
        return $this->financialPeriodName;
    }

    public function setFinancialPeriodName(string $financialPeriodName): self
    {
        $this->financialPeriodName = $financialPeriodName;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getClosed(): ?bool
    {
        return $this->closed;
    }

    public function setClosed(bool $closed): self
    {
        $this->closed = $closed;

        return $this;
    }
    

    public function getExtrasFirstFinancialDate(): ?\DateTimeInterface
    {
        return $this->ExtrasFirstFinancialDate;
    }

    public function setExtrasFirstFinancialDate(\DateTimeInterface $ExtrasFirstFinancialDate): self
    {
        $this->ExtrasFirstFinancialDate = $ExtrasFirstFinancialDate;

        return $this;
    }
    public function getExtrasFiscalEndOfTheFirstFiscalPeriod(): ?\DateTimeInterface
    {
        return $this->ExtrasFiscalEndOfTheFirstFiscalPeriod;
    }

    public function setExtrasFiscalEndOfTheFirstFiscalPeriod(\DateTimeInterface $ExtrasFiscalEndOfTheFirstFiscalPeriod): self
    {
        $this->ExtrasFiscalEndOfTheFirstFiscalPeriod = $ExtrasFiscalEndOfTheFirstFiscalPeriod;

        return $this;
    }

    public function getExtrasFinancialEndFirstFinancialPeriod(): ?\DateTimeInterface
    {
        return $this->ExtrasFinancialEndFirstFinancialPeriod;
    }

    public function setExtrasFiscalEndFirstFiscalPeriod(\DateTimeInterface $ExtrasFinancialEndFirstFinancialPeriod): self
    {
        $this->ExtrasFinancialEndFirstFinancialPeriod = $ExtrasFinancialEndFirstFinancialPeriod;

        return $this;
    }

    public function getExtrasAccountLabelLength(): ?int
    {
        return $this->ExtrasAccountLabelLength;
    }

    public function setExtrasAccountLabelLength(int $ExtrasAccountLabelLength): self
    {
        $this->ExtrasAccountLabelLength = $ExtrasAccountLabelLength;

        return $this;
    }

    public function getExtrasTradingAccountLength(): ?int
    {
        return $this->ExtrasTradingAccountLength;
    }

    public function setExtrasTradingAccountLength(int $ExtrasTradingAccountLength): self
    {
        $this->ExtrasTradingAccountLength = $ExtrasTradingAccountLength;

        return $this;
    }

    public function getExtrasAccountingLineLabelLength(): ?int
    {
        return $this->ExtrasAccountingLineLabelLength;
    }

    public function setExtrasAccountingLineLabelLength(int $ExtrasAccountingLineLabelLength): self
    {
        $this->ExtrasAccountingLineLabelLength = $ExtrasAccountingLineLabelLength;

        return $this;
    }

    public function getExtrasAccountLength(): ?int
    {
        return $this->ExtrasAccountLength;
    }

    public function setExtrasAccountLength(int $ExtrasAccountLength): self
    {
        $this->ExtrasAccountLength = $ExtrasAccountLength;

        return $this;
    }

    public function getExtrasAuthorizationAlphaAccounts(): ?bool
    {
        return $this->ExtrasAuthorizationAlphaAccounts;
    }

    public function setExtrasAuthorizationAlphaAccounts(bool $ExtrasAuthorizationAlphaAccounts): self
    {
        $this->ExtrasAuthorizationAlphaAccounts = $ExtrasAuthorizationAlphaAccounts;

        return $this;
    }

    public function getExtrasAmountsLength(): ?int
    {
        return $this->ExtrasAmountsLength;
    }

    public function setExtrasAmountsLength(int $ExtrasAmountsLength): self
    {
        $this->ExtrasAmountsLength = $ExtrasAmountsLength;

        return $this;
    }

    public function getExtrasWithQuantities(): ?bool
    {
        return $this->ExtrasWithQuantities;
    }

    public function setExtrasWithQuantities(bool $ExtrasWithQuantities): self
    {
        $this->ExtrasWithQuantities = $ExtrasWithQuantities;

        return $this;
    }

    public function getExtrasWithDueDates(): ?bool
    {
        return $this->ExtrasWithDueDates;
    }

    public function setExtrasWithDueDates(bool $ExtrasWithDueDates): self
    {
        $this->ExtrasWithDueDates = $ExtrasWithDueDates;

        return $this;
    }

    public function getExtrasWithMultipleDueDates(): ?bool
    {
        return $this->ExtrasWithMultipleDueDates;
    }

    public function setExtrasWithMultipleDueDates(bool $ExtrasWithMultipleDueDates): self
    {
        $this->ExtrasWithMultipleDueDates = $ExtrasWithMultipleDueDates;

        return $this;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }
    /**
     * Set company
     *
     * @param \App\Entity\Company $company
     * @return Company
     */
    public function setCompany(\App\Entity\Company $company = null)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get Company
     *
     * @return \App\Entity\Company
     */
    public function getCompany()
    {
        return $this->company;
    }
}
