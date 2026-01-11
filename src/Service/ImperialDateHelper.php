<?php

namespace App\Service;

use App\Model\ImperialDate;

class ImperialDateHelper
{
    public function parseFilter(string $value, bool $isEnd): ?int
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (str_contains($value, '/')) {
            [$day, $year] = array_map('trim', explode('/', $value, 2));
            if (!ctype_digit($day) || !ctype_digit($year)) {
                return null;
            }

            return $this->toKey((int) $day, (int) $year);
        }

        if (!ctype_digit($value)) {
            return null;
        }

        $year = (int) $value;
        $day = $isEnd ? 999 : 1;

        return $this->toKey($day, $year);
    }

    public function parseInput(string $value): ?ImperialDate
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (str_contains($value, '/')) {
            [$day, $year] = array_map('trim', explode('/', $value, 2));
            if (!ctype_digit($day) || !ctype_digit($year)) {
                return null;
            }

            return new ImperialDate((int) $year, (int) $day);
        }

        if (!ctype_digit($value)) {
            return null;
        }

        return new ImperialDate((int) $value, 1);
    }

    public function toKey(?int $day, ?int $year): ?int
    {
        if ($day === null || $year === null) {
            return null;
        }

        return $year * 1000 + $day;
    }

    public function format(?int $day, ?int $year): ?string
    {
        if ($day === null || $year === null) {
            return null;
        }

        return sprintf('%03d/%d', $day, $year);
    }
}
