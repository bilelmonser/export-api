<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\FinancialAccountRepository;
use App\Entity\FinancialPeriod;
use App\Entity\Company;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Table;

/**
 * @ORM\Entity(repositoryClass=FinancialAccountRepository::class)
 * @Table(schema="public")
 */
class FinancialAccount
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
    private $normalizedTradingAccountType;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $extrasCollectiveAccountFrom;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $extrasCollectiveAccountTo;

    /**
     * @ORM\Column(type="integer", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $finAccKey;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="integer", length=255, nullable="true")
     */
    private $extrasLettrableAccount;

    /**
     * @ORM\Column(type="integer", length=255)
     */
    private $extrasWithQuantities;

    /**
     * @ORM\Column(type="boolean")
     */
    private $locked;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $cpt1;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $cpt2;

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
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getNormalizedTradingAccountType()
    {
        return $this->normalizedTradingAccountType;
    }

    /**
     * @param mixed $normalizedTradingAccountType
     * @return FinancialAccount
     */
    public function setNormalizedTradingAccountType($normalizedTradingAccountType): self
    {
        $this->normalizedTradingAccountType = $normalizedTradingAccountType;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getExtrasCollectiveAccountFrom()
    {
        return $this->extrasCollectiveAccountFrom;
    }

    /**
     * @param mixed $extrasCollectiveAccountFrom
     * @return FinancialAccount
     */
    public function setExtrasCollectiveAccountFrom($extrasCollectiveAccountFrom): self
    {
        $this->extrasCollectiveAccountFrom = $extrasCollectiveAccountFrom;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getExtrasCollectiveAccountTo()
    {
        return $this->extrasCollectiveAccountTo;
    }

    /**
     * @param mixed $extrasCollectiveAccountTo
     * @return FinancialAccount
     */
    public function setExtrasCollectiveAccountTo($extrasCollectiveAccountTo): self
    {
        $this->extrasCollectiveAccountTo = $extrasCollectiveAccountTo;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     * @return FinancialAccount
     */
    public function setType($type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFinAccKey()
    {
        return $this->finAccKey;
    }

    /**
     * @param mixed $finAccKey
     * @return FinancialAccount
     */
    public function setFinAccKey($finAccKey): self
    {
        $this->finAccKey = $finAccKey;

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
     * @param mixed $name
     * @return FinancialAccount
     */
    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getExtrasLettrableAccount()
    {
        return $this->extrasLettrableAccount;
    }

    /**
     * @param mixed $extrasLettrableAccount
     * @return FinancialAccount
     */
    public function setExtrasLettrableAccount($extrasLettrableAccount): self
    {
        $this->extrasLettrableAccount = $extrasLettrableAccount;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getExtrasWithQuantities()
    {
        return $this->extrasWithQuantities;
    }

    /**
     * @param mixed $extrasWithQuantities
     * @return FinancialAccount
     */
    public function setExtrasWithQuantities($extrasWithQuantities): self
    {
        $this->extrasWithQuantities = $extrasWithQuantities;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLocked()
    {
        return $this->locked;
    }

    /**
     * @param mixed $locked
     * @return FinancialAccount
     */
    public function setLocked($locked): self
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCpt1()
    {
        return $this->cpt1;
    }

    /**
     * @param mixed $cpt1
     * @return FinancialAccount
     */
    public function setCpt1($cpt1): self
    {
        $this->cpt1 = $cpt1;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCpt2()
    {
        return $this->cpt2;
    }

    /**
     * @param mixed $cpt2
     * @return FinancialAccount
     */
    public function setCpt2($cpt2): self
    {
        $this->cpt2 = $cpt2;

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
     * @return FinancialAccount
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
     * @return FinancialAccount
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
     * @return FinancialAccount
     */
    public function setPeriod(\App\Entity\FinancialPeriod $period): self
    {
        $this->period = $period;

        return $this;
    }

}
