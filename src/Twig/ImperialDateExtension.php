<?php

namespace App\Twig;

use App\Service\ImperialDateHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ImperialDateExtension extends AbstractExtension
{
    public function __construct(private readonly ImperialDateHelper $helper)
    {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('imperial_date', [$this, 'formatImperialDate']),
        ];
    }

    public function formatImperialDate(?int $day, ?int $year = null): ?string
    {
        return $this->helper->format($day, $year);
    }
}
