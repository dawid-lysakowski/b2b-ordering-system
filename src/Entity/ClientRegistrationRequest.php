<?php

namespace App\Entity;

use App\Repository\ClientRegistrationRequestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClientRegistrationRequestRepository::class)]
class ClientRegistrationRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $companyName = null;

    #[ORM\Column(length: 20)]
    private ?string $taxNumber = null;

    #[ORM\Column(length: 255)]
    private ?string $companyEmail = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $companyPhone = null;

    #[ORM\Column]
    private array $billingAddressData = [];

    #[ORM\Column]
    private array $deliveryAddressData = [];

    #[ORM\Column(length: 100)]
    private ?string $userFirstName = null;

    #[ORM\Column(length: 100)]
    private ?string $userLastName = null;

    #[ORM\Column(length: 255)]
    private ?string $userEmail = null;

    #[ORM\Column(length: 255)]
    private ?string $passwordHash = null;

    #[ORM\Column(length: 30)]
    private ?string $status = null;

    #[ORM\ManyToOne]
    private ?CustomerCompany $matchedCustomerCompany = null;

    #[ORM\ManyToOne]
    private ?User $reviewedBy = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $reviewedAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(string $companyName): static
    {
        $this->companyName = $companyName;

        return $this;
    }

    public function getTaxNumber(): ?string
    {
        return $this->taxNumber;
    }

    public function setTaxNumber(string $taxNumber): static
    {
        $this->taxNumber = $taxNumber;

        return $this;
    }

    public function getCompanyEmail(): ?string
    {
        return $this->companyEmail;
    }

    public function setCompanyEmail(string $companyEmail): static
    {
        $this->companyEmail = $companyEmail;

        return $this;
    }

    public function getCompanyPhone(): ?string
    {
        return $this->companyPhone;
    }

    public function setCompanyPhone(?string $companyPhone): static
    {
        $this->companyPhone = $companyPhone;

        return $this;
    }

    public function getBillingAddressData(): array
    {
        return $this->billingAddressData;
    }

    public function setBillingAddressData(array $billingAddressData): static
    {
        $this->billingAddressData = $billingAddressData;

        return $this;
    }

    public function getDeliveryAddressData(): array
    {
        return $this->deliveryAddressData;
    }

    public function setDeliveryAddressData(array $deliveryAddressData): static
    {
        $this->deliveryAddressData = $deliveryAddressData;

        return $this;
    }

    public function getUserFirstName(): ?string
    {
        return $this->userFirstName;
    }

    public function setUserFirstName(string $userFirstName): static
    {
        $this->userFirstName = $userFirstName;

        return $this;
    }

    public function getUserLastName(): ?string
    {
        return $this->userLastName;
    }

    public function setUserLastName(string $userLastName): static
    {
        $this->userLastName = $userLastName;

        return $this;
    }

    public function getUserEmail(): ?string
    {
        return $this->userEmail;
    }

    public function setUserEmail(string $userEmail): static
    {
        $this->userEmail = $userEmail;

        return $this;
    }

    public function getPasswordHash(): ?string
    {
        return $this->passwordHash;
    }

    public function setPasswordHash(string $passwordHash): static
    {
        $this->passwordHash = $passwordHash;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getMatchedCustomerCompany(): ?CustomerCompany
    {
        return $this->matchedCustomerCompany;
    }

    public function setMatchedCustomerCompany(?CustomerCompany $matchedCustomerCompany): static
    {
        $this->matchedCustomerCompany = $matchedCustomerCompany;

        return $this;
    }

    public function getReviewedBy(): ?User
    {
        return $this->reviewedBy;
    }

    public function setReviewedBy(?User $reviewedBy): static
    {
        $this->reviewedBy = $reviewedBy;

        return $this;
    }

    public function getreviewedAt(): ?\DateTimeImmutable
    {
        return $this->reviewedAt;
    }

    public function setreviewedAt(?\DateTimeImmutable $reviewedAt): static
    {
        $this->reviewedAt = $reviewedAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
