<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TelefoneComDdiDdd implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * Expects the international format (e.g. +5511987654321): a leading "+",
     * the DDI (1 to 3 digits), the DDD (2 digits) and the local number (7 to 9 digits).
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $normalized = preg_replace('/[\s()-]/', '', (string) $value);

        if (! preg_match('/^\+\d{10,14}$/', $normalized)) {
            $fail('O campo :attribute deve estar no formato internacional, com DDI e DDD (ex: +5511987654321).');
        }
    }
}
