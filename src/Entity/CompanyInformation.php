<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\CompanyInformationRepository;
use App\Entity\FinancialPeriod;
use App\Entity\Company;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Table;

/**
 * @ORM\Entity(repositoryClass=CompanyInformationRepository::class)
 * @Table(schema="public")
 */
class CompanyInformation
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
    private $uuid;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $ape;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $naf;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $siret;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $siren;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $taxSystem;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $taxPeriod;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $fiscalSystem;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $fiscalStatus;

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
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param mixed $uuid
     * @return CompanyInformation
     */
    public function setUuid($uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getApe()
    {
        return $this->ape;
    }

    /**
     * @param mixed $ape
     * @return CompanyInformation
     */
    public function setApe($ape): self
    {
        $this->ape = $ape;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNaf()
    {
        return $this->naf;
    }

    /**
     * @param mixed $naf
     * @return CompanyInformation
     */
    public function setNaf($naf): self
    {
        $this->naf = $naf;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSiret()
    {
        return $this->siret;
    }

    /**
     * @param mixed $siret
     * @return CompanyInformation
     */
    public function setSiret($siret): self
    {
        $this->siret = $siret;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSiren()
    {
        return $this->siren;
    }

    /**
     * @param mixed $siren
     * @return CompanyInformation
     */
    public function setSiren($siren): self
    {
        $this->siren = $siren;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTaxSystem()
    {
        return $this->taxSystem;
    }

    /**
     * @param mixed $taxSystem
     * @return CompanyInformation
     */
    public function setTaxSystem($taxSystem): self
    {
        $this->taxSystem = $taxSystem;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTaxPeriod()
    {
        return $this->taxPeriod;
    }

    /**
     * @param mixed $taxPeriod
     * @return CompanyInformation
     */
    public function setTaxPeriod($taxPeriod): self
    {
        $this->taxPeriod = $taxPeriod;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFiscalSystem()
    {
        return $this->fiscalSystem;
    }

    /**
     * @param mixed $fiscalSystem
     * @return CompanyInformation
     */
    public function setFiscalSystem($fiscalSystem): self
    {
        $this->fiscalSystem = $fiscalSystem;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFiscalStatus()
    {
        return $this->fiscalStatus;
    }

    /**
     * @param mixed $fiscalStatus
     * @return CompanyInformation
     */
    public function setFiscalStatus($fiscalStatus): self
    {
        $this->fiscalStatus = $fiscalStatus;

        return $this;
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
     * @return CompanyInformation
     */
    public function setCompany(\App\Entity\Company $company): self
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return \App\Entity\FinancialPeriod
     */
    public function getPeriod(): \App\Entity\FinancialPeriod
    {
        return $this->period;
    }

    /**
     * @param \App\Entity\FinancialPeriod $period
     * @return CompanyInformation
     */
    public function setPeriod(\App\Entity\FinancialPeriod $period): self
    {
        $this->period = $period;

        return $this;
    }

}
