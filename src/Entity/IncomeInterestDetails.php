<?php

namespace App\Entity;

use App\Repository\IncomeInterestDetailsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IncomeInterestDetailsRepository::class)]
class IncomeInterestDetails
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'interestDetails')]
    #[ORM\JoinColumn(nullable: false, unique: true)]
    private ?Income $income = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $accountRef = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $instrument = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 11, scale: 2, nullable: true)]
    private ?string $principal = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 11, scale: 2, nullable: true)]
    private ?string $interestRate = null;

    #[ORM\Column(nullable: true)]
    private ?int $startDay = null;

    #[ORM\Column(nullable: true)]
    private ?int $startYear = null;

    #[ORM\Column(nullable: true)]
    private ?int $endDay = null;

    #[ORM\Column(nullable: true)]
    private ?int $endYear = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $calcMethod = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 11, scale: 2, nullable: true)]
    private ?string $interestEarned = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 11, scale: 2, nullable: true)]
    private ?string $netPaid = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $paymentTerms = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $disputeWindow = null;

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

    public function getAccountRef(): ?string
    {
        return $this->accountRef;
    }

    public function setAccountRef(?string $accountRef): static
    {
        $this->accountRef = $accountRef;
        return $this;
    }

    public function getInstrument(): ?string
    {
        return $this->instrument;
    }

    public function setInstrument(?string $instrument): static
    {
        $this->instrument = $instrument;
        return $this;
    }

    public function getPrincipal(): ?string
    {
        return $this->principal;
    }

    public function setPrincipal(?string $principal): static
    {
        $this->principal = $principal;
        return $this;
    }

    public function getInterestRate(): ?string
    {
        return $this->interestRate;
    }

    public function setInterestRate(?string $interestRate): static
    {
        $this->interestRate = $interestRate;
        return $this;
    }

    public function getStartDay(): ?int
    {
        return $this->startDay;
    }

    public function setStartDay(?int $startDay): static
    {
        $this->startDay = $startDay;
        return $this;
    }

    public function getStartYear(): ?int
    {
        return $this->startYear;
    }

    public function setStartYear(?int $startYear): static
    {
        $this->startYear = $startYear;
        return $this;
    }

    public function getEndDay(): ?int
    {
        return $this->endDay;
    }

    public function setEndDay(?int $endDay): static
    {
        $this->endDay = $endDay;
        return $this;
    }

    public function getEndYear(): ?int
    {
        return $this->endYear;
    }

    public function setEndYear(?int $endYear): static
    {
        $this->endYear = $endYear;
        return $this;
    }

    public function getCalcMethod(): ?string
    {
        return $this->calcMethod;
    }

    public function setCalcMethod(?string $calcMethod): static
    {
        $this->calcMethod = $calcMethod;
        return $this;
    }

    public function getInterestEarned(): ?string
    {
        return $this->interestEarned;
    }

    public function setInterestEarned(?string $interestEarned): static
    {
        $this->interestEarned = $interestEarned;
        return $this;
    }

    public function getNetPaid(): ?string
    {
        return $this->netPaid;
    }

    public function setNetPaid(?string $netPaid): static
    {
        $this->netPaid = $netPaid;
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

    public function getDisputeWindow(): ?string
    {
        return $this->disputeWindow;
    }

    public function setDisputeWindow(?string $disputeWindow): static
    {
        $this->disputeWindow = $disputeWindow;
        return $this;
    }
}
