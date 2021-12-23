<?php

namespace App\Entity;

use App\Repository\IbizaModelRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=IbizaModelRepository::class)
 */
class IbizaModel
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $config1;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $config2;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $config3;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $config4;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $config5;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $config6;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $config7;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $config8;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $config9;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $config10;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConfig1(): ?string
    {
        return $this->config1;
    }

    public function setConfig1(?string $config1): self
    {
        $this->config1 = $config1;

        return $this;
    }

    public function getConfig2(): ?string
    {
        return $this->config2;
    }

    public function setConfig2(?string $config2): self
    {
        $this->config2 = $config2;

        return $this;
    }

    public function getConfig3(): ?string
    {
        return $this->config3;
    }

    public function setConfig3(?string $config3): self
    {
        $this->config3 = $config3;

        return $this;
    }

    public function getConfig4(): ?string
    {
        return $this->config4;
    }

    public function setConfig4(?string $config4): self
    {
        $this->config4 = $config4;

        return $this;
    }

    public function getConfig5(): ?string
    {
        return $this->config5;
    }

    public function setConfig5(?string $config5): self
    {
        $this->config5 = $config5;

        return $this;
    }

    public function getConfig6(): ?string
    {
        return $this->config6;
    }

    public function setConfig6(?string $config6): self
    {
        $this->config6 = $config6;

        return $this;
    }

    public function getConfig7(): ?string
    {
        return $this->config7;
    }

    public function setConfig7(?string $config7): self
    {
        $this->config7 = $config7;

        return $this;
    }

    public function getConfig8(): ?string
    {
        return $this->config8;
    }

    public function setConfig8(?string $config8): self
    {
        $this->config8 = $config8;

        return $this;
    }

    public function getConfig9(): ?string
    {
        return $this->config9;
    }

    public function setConfig9(string $config9): self
    {
        $this->config9 = $config9;

        return $this;
    }

    public function getConfig10(): ?string
    {
        return $this->config10;
    }

    public function setConfig10(?string $config10): self
    {
        $this->config10 = $config10;

        return $this;
    }
}
