<?php

namespace Stboris\LaravelCroatiaToolkit\Iban\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Stboris\LaravelCroatiaToolkit\Iban\Iban;

class ValidIban implements ValidationRule
{
    public function __construct(protected bool $croatianOnly = false) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail(trans('croatia-toolkit::validation.iban', ['attribute' => $attribute]));

            return;
        }

        $valid = $this->croatianOnly ? Iban::isValidCroatian($value) : Iban::isValid($value);

        if (! $valid) {
            $fail(trans('croatia-toolkit::validation.iban', ['attribute' => $attribute]));
        }
    }
}
