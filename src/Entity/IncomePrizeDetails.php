<?php

namespace App\Entity;

use App\Repository\IncomePrizeDetailsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IncomePrizeDetailsRepository::class)]
class IncomePrizeDetails
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'prizeDetails')]
    #[ORM\JoinColumn(nullable: false, unique: true)]
    private ?Income $income = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $caseRef = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $jurisdiction = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $legalBasis = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $prizeDescription = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 11, scale: 2, nullable: true)]
    private ?string $estimatedValue = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $disposition = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $paymentTerms = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $shareSplit = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $awardTrigger = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIncome(): ?Income
    {
        return $this->income;
    }

    public function setIncome(Income $income): static
    {
        $this->income = $income;
        return $this;
    }

    public function getCaseRef(): ?string
    {
        return $this->caseRef;
    }

    public function setCaseRef(?string $caseRef): static
    {
        $this->caseRef = $caseRef;
        return $this;
    }

    public function getJurisdiction(): ?string
    {
        return $this->jurisdiction;
    }

    public function setJurisdiction(?string $jurisdiction): static
    {
        $this->jurisdiction = $jurisdiction;
        return $this;
    }

    public function getLegalBasis(): ?string
    {
        return $this->legalBasis;
    }

    public function setLegalBasis(?string $legalBasis): static
    {
        $this->legalBasis = $legalBasis;
        return $this;
    }

    public function getPrizeDescription(): ?string
    {
        return $this->prizeDescription;
    }

    public function setPrizeDescription(?string $prizeDescription): static
    {
        $this->prizeDescription = $prizeDescription;
        return $this;
    }

    public function getEstimatedValue(): ?string
    {
        return $this->estimatedValue;
    }

    public function setEstimatedValue(?string $estimatedValue): static
    {
        $this->estimatedValue = $estimatedValue;
        return $this;
    }

    public function getDisposition(): ?string
    {
        return $this->disposition;
    }

    public function setDisposition(?string $disposition): static
    {
        $this->disposition = $disposition;
        return $this;
    }

    public function getPaymentTerms(): ?string
    {
        return $this->paymentTerms;
    }

    public function setPaymentTerms(?string $paymentTerms): static
    {
        $this->paymentTerms = $paymentTerms;
        return $this;
    }

    public function getShareSplit(): ?string
    {
        return $this->shareSplit;
    }

    public function setShareSplit(?string $shareSplit): static
    {
        $this->shareSplit = $shareSplit;
        return $this;
    }

    public function getAwardTrigger(): ?string
    {
        return $this->awardTrigger;
    }

    public function setAwardTrigger(?string $awardTrigger): static
    {
        $this->awardTrigger = $awardTrigger;
        return $this;
    }
}
