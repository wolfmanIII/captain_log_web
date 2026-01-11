<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_PROPERTY)]
class ImperialDateComplete extends Constraint
{
    public string $message = 'Log the full Imperial stamp (day and year).';
    public bool $required = false;

    public function __construct(?array $options = null, ?bool $required = null, ?string $message = null)
    {
        parent::__construct($options ?? []);

        if ($required !== null) {
            $this->required = $required;
        }

        if ($message !== null) {
            $this->message = $message;
        }
    }

    public function validatedBy(): string
    {
        return static::class.'Validator';
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
