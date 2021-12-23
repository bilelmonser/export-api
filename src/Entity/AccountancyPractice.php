<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\AccountancyPracticeRepository;
use Doctrine\ORM\Mapping as ORM;
/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass=AccountancyPracticeRepository::class)
 */
class AccountancyPractice
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
     * @ORM\Column(type="string", length=255)
     */
    private $originSageApplication;
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $contactEmail;

    /**
     * @var \SageModel
     *
     * @ORM\ManyToOne(targetEntity="SageModel")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sage_model_id", referencedColumnName="id",onDelete="CASCADE")
     * })
     */
    private $sageModel;

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

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getOriginSageApplication(): ?string
    {
        return $this->originSageApplication;
    }

    public function setOriginSageApplication(string $originSageApplication): self
    {
        $this->originSageApplication = $originSageApplication;

        return $this;
    }

    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }

    public function setContactEmail(?string $contactEmail): self
    {
        $this->contactEmail = $contactEmail;

        return $this;
    }
    /**
     * Set SageModel
     *
     * @param \App\Entity\SageModel $sageModel
     * @return Cars
     */
    public function setSageModel(\App\Entity\SageModel $sageModel = null)
    {
        $this->sageModel = $sageModel;

        return $this;
    }

    /**
     * Get SageModel
     *
     * @return \App\Entity\SageModel 
     */
    public function getSageModel()
    {
        return $this->sageModel;
    }
}
