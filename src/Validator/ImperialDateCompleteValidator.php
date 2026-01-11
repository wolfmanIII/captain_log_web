<?php

namespace App\Validator;

use App\Model\ImperialDate;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ImperialDateCompleteValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ImperialDateComplete) {
            return;
        }

        if ($value === null) {
            if ($constraint->required) {
                $this->context->buildViolation($constraint->message)->addViolation();
            }
            return;
        }

        if (!$value instanceof ImperialDate) {
            return;
        }

        $day = $value->getDay();
        $year = $value->getYear();

        if ($day === null && $year === null) {
            if ($constraint->required) {
                $this->context->buildViolation($constraint->message)->addViolation();
            }
            return;
        }

        if ($day === null || $year === null) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
