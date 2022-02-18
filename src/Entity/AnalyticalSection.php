<?php

namespace App\Entity;

use App\Repository\AnalyticalSectionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AnalyticalSectionRepository::class)
 * @Table(schema="public")
 */
class AnalyticalSection
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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $label;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $axe;

    /**
     * @ORM\Column(type="integer", length=255)
     */
    private $superSection;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
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
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     * @return AnalyticalSection
     */
    public function setCode($code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param mixed $label
     * @return AnalyticalSection
     */
    public function setLabel($label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAxe()
    {
        return $this->axe;
    }

    /**
     * @param mixed $axe
     * @return AnalyticalSection
     */
    public function setAxe($axe): self
    {
        $this->axe = $axe;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSuperSection()
    {
        return $this->superSection;
    }

    /**
     * @param mixed $superSection
     * @return AnalyticalSection
     */
    public function setSuperSection($superSection): self
    {
        $this->superSection = $superSection;

        return $this;
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
     * @return AnalyticalSection
     */
    public function setUuid($uuid): self
    {
        $this->uuid = $uuid;

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
     * @return AnalyticalSection
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
     * @return AnalyticalSection
     */
    public function setPeriod(\App\Entity\FinancialPeriod $period): self
    {
        $this->period = $period;

        return $this;
    }

}
