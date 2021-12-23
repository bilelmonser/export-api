<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\CompanyRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass=CompanyRepository::class)
 */
class Company
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
    private $SageId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $businessId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isAccountancyPractice;

    /**
     * @var \AccountancyPractice
     *
     * @ORM\ManyToOne(targetEntity="AccountancyPractice")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="accountancy_practice_id", referencedColumnName="id",onDelete="CASCADE")
     * })
     */
    private $accountancyPractice;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSageId(): ?string
    {
        return $this->SageId;
    }

    public function setSageId(string $SageId): self
    {
        $this->SageId = $SageId;

        return $this;
    }

    public function getBusinessId(): ?string
    {
        return $this->businessId;
    }

    public function setBusinessId(string $businessId): self
    {
        $this->businessId = $businessId;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getIsAccountancyPractice(): ?bool
    {
        return $this->isAccountancyPractice;
    }

    public function setIsAccountancyPractice(bool $isAccountancyPractice): self
    {
        $this->isAccountancyPractice = $isAccountancyPractice;

        return $this;
    }
    /**
     * Set AccountancyPractice
     *
     * @param \App\Entity\AccountancyPractice $accountancyPractice
     * @return Company
     */
    public function setAccountancyPractice(\App\Entity\AccountancyPractice $accountancyPractice = null)
    {
        $this->accountancyPractice = $accountancyPractice;

        return $this;
    }

    /**
     * Get AccountancyPractice
     *
     * @return \App\Entity\AccountancyPractice
     */
    public function getAccountancyPractice()
    {
        return $this->accountancyPractice;
    }

}
