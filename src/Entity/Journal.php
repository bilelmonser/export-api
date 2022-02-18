<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\JournalRepository;
use App\Entity\FinancialPeriod;
use App\Entity\Company;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Table;

/**
 * @ORM\Entity(repositoryClass=JournalRepository::class)
 * @Table(schema="public")
 */
class Journal
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
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $shortName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $originalJournalType;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $normalizedJournalType;

    /**
     * @ORM\Column(type="integer")
     */
    private $accountingDocumentLength;

    /**
     * @ORM\Column(type="string", length=255, nullable="true")
     */
    private $bankAccount;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $accountsForbidden;

    /**
     * @ORM\Column(type="boolean")
     */
    private $withoutPropagationDate;

    /**
     * @ORM\Column(type="boolean")
     */
    private $withoutPropagationReference;

    /**
     * @ORM\Column(type="datetime")
     */
    private $lockEndDate;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $uuid;

    /**
     * @var Company
     *
     * @ORM\ManyToOne(targetEntity="Company")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     * })
     */
    private $company;

    /**
     * @var FinancialPeriod
     *
     * @ORM\ManyToOne(targetEntity="FinancialPeriod")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="peiod_id", referencedColumnName="id")
     * })
     */
    private $period;

    /**
     * @param mixed $name
     * @return Journal
     */
    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $shortName
     * @return Journal
     */
    public function setShortName($shortName): self
    {
        $this->shortName = $shortName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getShortName()
    {
        return $this->shortName;
    }

    /**
     * @param mixed $originalJournalType
     * @return Journal
     */
    public function setOriginalJournalType($originalJournalType): self
    {
        $this->originalJournalType = $originalJournalType;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOriginalJournalType()
    {
        return $this->originalJournalType;
    }

    /**
     * @param mixed $normalizedJournalType
     * @return Journal
     */
    public function setNormalizedJournalType($normalizedJournalType): self
    {
        $this->normalizedJournalType = $normalizedJournalType;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNormalizedJournalType()
    {
        return $this->normalizedJournalType;
    }

    /**
     * @param mixed $accountingDocumentLength
     * @return Journal
     */
    public function setAccountingDocumentLength($accountingDocumentLength): self
    {
        $this->accountingDocumentLength = $accountingDocumentLength;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAccountingDocumentLength()
    {
        return $this->accountingDocumentLength;
    }

    /**
     * @param mixed $bankAccount
     * @return Journal
     */
    public function setBankAccount($bankAccount): self
    {
        $this->bankAccount = $bankAccount;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBankAccount()
    {
        return $this->bankAccount;
    }

    /**
     * @param mixed $accountsForbidden
     * @return Journal
     */
    public function setAccountsForbidden($accountsForbidden): self
    {
        $this->accountsForbidden = $accountsForbidden;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAccountsForbidden()
    {
        return $this->accountsForbidden;
    }

    /**
     * @param mixed $withoutPropagationDate
     * @return Journal
     */
    public function setWithoutPropagationDate($withoutPropagationDate): self
    {
        $this->withoutPropagationDate = $withoutPropagationDate;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getWithoutPropagationDate()
    {
        return $this->withoutPropagationDate;
    }

    /**
     * @param mixed $withoutPropagationReference
     * @return Journal
     */
    public function setWithoutPropagationReference($withoutPropagationReference): self
    {
        $this->withoutPropagationReference = $withoutPropagationReference;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getWithoutPropagationReference()
    {
        return $this->withoutPropagationReference;
    }

    /**
     * @param mixed $lockEndDate
     * @return Journal
     */
    public function setLockEndDate($lockEndDate): self
    {
        $this->lockEndDate = $lockEndDate;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLockEndDate()
    {
        return $this->lockEndDate;
    }



    public function getId(): ?int
    {
        return $this->id;
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
     * @param Company $company
     * @return Company
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
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @return FinancialPeriod
     */
    public function getPeriod(): FinancialPeriod
    {
        return $this->period;
    }

    /**
     * @param FinancialPeriod $period
     * @return Journal
     */
    public function setPeriod(FinancialPeriod $period): self
    {
        $this->period = $period;

        return $this;
    }
}
