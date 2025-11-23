<?php
namespace App\Manager;

use App\Entity\Mortgage;

class MortgageManager
{
    private const SHIP_SHARE_VALUE = 1000000;

    private Mortgage $mortgage;

    public function setMortgage(Mortgage $mortgage)
    {
        $this->mortgage = $mortgage;
    }

    public function calculateShipCost()
    {
        $shipCost = 0.00;
        $shipCost = $this->mortgage->getShip()->getPrice();
        $shipCost = $shipCost - ($this->mortgage->getShipShares() * self::SHIP_SHARE_VALUE);
        if ($this->mortgage->getAdvancePayment()) {
            $shipCost = $shipCost - $this->mortgage->getAdvancePayment();
        }

        if ($this->mortgage->getDiscount()) {
            $discount = $this->mortgage->getShip()->getPrice() * $this->mortgage->getDiscount() / 100;
            $shipCost = $shipCost - $discount;
        }

        return $shipCost;

    }

    public function calculate()
    {
        $shipCost = $this->calculateShipCost();
        $monthlyPayment = 
            $shipCost
            * $this->mortgage->getInterestRate()->getPriceMultiplier()
            / $this->mortgage->getInterestRate()->getDuration()
            / 12
        ;

        $annualPayment = $monthlyPayment * 12;

        $insuranceMonthlyPayment = 0.00;
        $insuranceAnnualPayment = 0.00;
        if ($this->mortgage->getInsurance()) {
            $insuranceMonthlyPayment = $this->calculateInsuranceCost();
            $insuranceAnnualPayment = $insuranceMonthlyPayment * 12;
        }

        $totalMonthlyPayment = $monthlyPayment + $insuranceMonthlyPayment;
        $totalAnnualPayment = $annualPayment + $insuranceAnnualPayment;

        return [
            'ship_cost' => round($shipCost, 2, PHP_ROUND_HALF_DOWN),
            'mortgage_monthly' => round($monthlyPayment, 2, PHP_ROUND_HALF_DOWN),
            'mortgage_annual' => round($annualPayment, 2 , PHP_ROUND_HALF_DOWN),
            'insurance_monthly' => round($insuranceMonthlyPayment, 2 , PHP_ROUND_HALF_DOWN),
            'insurance_annual' => round($insuranceAnnualPayment, 2, PHP_ROUND_HALF_DOWN),
            'total_monthly_payment' => round($totalMonthlyPayment, 2, PHP_ROUND_HALF_DOWN),
            'total_annual_payment' => round($totalAnnualPayment, 2, PHP_ROUND_HALF_DOWN),
        ];
    }

    public function calculateInsuranceCost()
    {
        return $this->mortgage->getShip()->getPrice() 
            / 100
            * $this->mortgage->getInsurance()->getAnnualCost()
            / 12
        ;
    }
}