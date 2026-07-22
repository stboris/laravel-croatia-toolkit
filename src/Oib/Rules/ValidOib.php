<?php

namespace Stboris\LaravelCroatiaToolkit\Oib\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Stboris\LaravelCroatiaToolkit\Oib\Oib;

class ValidOib implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! Oib::isValid($value)) {
            $fail(trans('croatia-toolkit::validation.oib', ['attribute' => $attribute]));
        }
    }
}
