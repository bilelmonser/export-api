<?php

namespace App\Entity;

use App\Repository\SageModelRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SageModelRepository::class)
 */
class SageModel
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
    private $urlAuth;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $grantType;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $clientId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $clientSecret;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $audiance;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $appId;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $token;

    /**
     * @ORM\Column(type="datetime",nullable=true)
     */
    private $expiredtoken;
    /**
     * @ORM\Column(type="string", length=255, nullable=false, unique=true)
     */
    private $AccountancyPractice;

    /**
     * @var \App\Entity\User
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * 
     */
    private $idUser;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrlAuth(): ?string
    {
        return $this->urlAuth;
    }

    public function setUrlAuth(?string $urlAuth): self
    {
        $this->urlAuth = $urlAuth;

        return $this;
    }

    public function getGrantType(): ?string
    {
        return $this->grantType;
    }

    public function setGrandType(?string $grandType): self
    {
        $this->grandType = $grandType;

        return $this;
    }

    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    public function setClientId(?string $clientId): self
    {
        $this->clientId = $clientId;

        return $this;
    }

    public function getClientSecret(): ?string
    {
        return $this->clientSecret;
    }

    public function setClientSecret(?string $clientSecret): self
    {
        $this->clientSecret = $clientSecret;

        return $this;
    }

    public function getAudience(): ?string
    {
        return $this->audiance;
    }

    public function setAudience(?string $audiance): self
    {
        $this->audiance = $audiance;

        return $this;
    }

    public function getAppId(): ?string
    {
        return $this->appId;
    }

    public function setAppId(?string $appId): self
    {
        $this->appId = $appId;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getExpiredtoken(): ?DateTime
    {
        return $this->expiredtoken;
    }

    public function setExpiredtoken($expiredtoken): self
    {
        $this->expiredtoken = $expiredtoken;

        return $this;
    }
    public function getAccountancyPractice(): ?string
    {
        return $this->AccountancyPractice;
    }

    public function setAccountancyPractice(?string $AccountancyPractice): self
    {
        $this->AccountancyPractice = $AccountancyPractice;

        return $this;
    }
}
