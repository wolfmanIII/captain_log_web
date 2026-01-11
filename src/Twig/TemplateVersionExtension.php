<?php

namespace App\Twig;

use App\Service\TemplateVersionRegistry;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class TemplateVersionExtension extends AbstractExtension
{
    public function __construct(private readonly TemplateVersionRegistry $registry)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('template_version', [$this, 'getVersion']),
        ];
    }

    public function getVersion(string $template, string $default = '—'): string
    {
        return $this->registry->get($template, $default);
    }
}
