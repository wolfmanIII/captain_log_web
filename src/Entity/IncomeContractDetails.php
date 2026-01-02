<?php

namespace App\Entity;

use App\Repository\IncomeContractDetailsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IncomeContractDetailsRepository::class)]
class IncomeContractDetails
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'contractDetails')]
    #[ORM\JoinColumn(nullable: false, unique: true)]
    private ?Income $income = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $jobType = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $objective = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $successCondition = null;

    #[ORM\Column(nullable: true)]
    private ?int $startDay = null;

    #[ORM\Column(nullable: true)]
    private ?int $startYear = null;

    #[ORM\Column(nullable: true)]
    private ?int $deadlineDay = null;

    #[ORM\Column(nullable: true)]
    private ?int $deadlineYear = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 11, scale: 2, nullable: true)]
    private ?string $bonus = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $expensesPolicy = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 11, scale: 2, nullable: true)]
    private ?string $deposit = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $restrictions = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $confidentialityLevel = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $failureTerms = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $cancellationTerms = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $paymentTerms = null;

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

    public function getJobType(): ?string
    {
        return $this->jobType;
    }

    public function setJobType(?string $jobType): static
    {
        $this->jobType = $jobType;
        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;
        return $this;
    }

    public function getObjective(): ?string
    {
        return $this->objective;
    }

    public function setObjective(?string $objective): static
    {
        $this->objective = $objective;
        return $this;
    }

    public function getSuccessCondition(): ?string
    {
        return $this->successCondition;
    }

    public function setSuccessCondition(?string $successCondition): static
    {
        $this->successCondition = $successCondition;
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

    public function getDeadlineDay(): ?int
    {
        return $this->deadlineDay;
    }

    public function setDeadlineDay(?int $deadlineDay): static
    {
        $this->deadlineDay = $deadlineDay;
        return $this;
    }

    public function getDeadlineYear(): ?int
    {
        return $this->deadlineYear;
    }

    public function setDeadlineYear(?int $deadlineYear): static
    {
        $this->deadlineYear = $deadlineYear;
        return $this;
    }

    public function getBonus(): ?string
    {
        return $this->bonus;
    }

    public function setBonus(?string $bonus): static
    {
        $this->bonus = $bonus;
        return $this;
    }

    public function getExpensesPolicy(): ?string
    {
        return $this->expensesPolicy;
    }

    public function setExpensesPolicy(?string $expensesPolicy): static
    {
        $this->expensesPolicy = $expensesPolicy;
        return $this;
    }

    public function getDeposit(): ?string
    {
        return $this->deposit;
    }

    public function setDeposit(?string $deposit): static
    {
        $this->deposit = $deposit;
        return $this;
    }

    public function getRestrictions(): ?string
    {
        return $this->restrictions;
    }

    public function setRestrictions(?string $restrictions): static
    {
        $this->restrictions = $restrictions;
        return $this;
    }

    public function getConfidentialityLevel(): ?string
    {
        return $this->confidentialityLevel;
    }

    public function setConfidentialityLevel(?string $confidentialityLevel): static
    {
        $this->confidentialityLevel = $confidentialityLevel;
        return $this;
    }

    public function getFailureTerms(): ?string
    {
        return $this->failureTerms;
    }

    public function setFailureTerms(?string $failureTerms): static
    {
        $this->failureTerms = $failureTerms;
        return $this;
    }

    public function getCancellationTerms(): ?string
    {
        return $this->cancellationTerms;
    }

    public function setCancellationTerms(?string $cancellationTerms): static
    {
        $this->cancellationTerms = $cancellationTerms;
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
}
