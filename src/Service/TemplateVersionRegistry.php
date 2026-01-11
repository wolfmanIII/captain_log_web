<?php

namespace App\Service;

final class TemplateVersionRegistry
{
    /** @var array<string, string> */
    private array $versions;

    public function __construct(string $projectDir)
    {
        $path = $projectDir . '/config/template_versions.php';
        $versions = is_file($path) ? require $path : [];

        if (!is_array($versions)) {
            $versions = [];
        }

        $this->versions = $versions;
    }

    public function get(string $template, string $default = '—'): string
    {
        return $this->versions[$template] ?? $default;
    }

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        return $this->versions;
    }
}
