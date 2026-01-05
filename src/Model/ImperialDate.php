<?php

namespace App\Model;

class ImperialDate
{
    private ?int $year = null;
    private ?int $day = null; // Day of year (1-365) where 1 = Holiday

    public function __construct(?int $year = null, ?int $day = null)
    {
        $this->year = $year;
        $this->day = $day;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(?int $year): void
    {
        $this->year = $year;
    }

    public function getDay(): ?int
    {
        return $this->day;
    }

    public function setDay(?int $day): void
    {
        $this->day = $day;
    }
}
