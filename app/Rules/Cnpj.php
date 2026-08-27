<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Cnpj implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! static::isValid((string) $value)) {
            $fail('O campo :attribute não é um CNPJ válido.');
        }
    }

    /**
     * Determine whether the given value is a structurally valid CNPJ.
     */
    public static function isValid(string $value): bool
    {
        $cnpj = preg_replace('/\D/', '', $value);

        if (strlen($cnpj) !== 14 || preg_match('/^(\d)\1{13}$/', $cnpj) === 1) {
            return false;
        }

        return (int) $cnpj[12] === static::checkDigit($cnpj, 12)
            && (int) $cnpj[13] === static::checkDigit($cnpj, 13);
    }

    /**
     * Generate a structurally valid CNPJ (digits only, random base). Intended for tests and factories.
     */
    public static function generate(): string
    {
        $digits = [];

        for ($i = 0; $i < 12; $i++) {
            $digits[] = random_int(0, 9);
        }

        $digits[] = static::checkDigit(implode('', $digits), 12);
        $digits[] = static::checkDigit(implode('', $digits), 13);

        return implode('', $digits);
    }

    /**
     * @param  array<int, int>|string  $cnpj
     */
    private static function checkDigit(array|string $cnpj, int $length): int
    {
        $weights = $length === 12
            ? [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]
            : [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        $sum = 0;

        for ($i = 0; $i < $length; $i++) {
            $sum += (int) $cnpj[$i] * $weights[$i];
        }

        $remainder = $sum % 11;

        return $remainder < 2 ? 0 : 11 - $remainder;
    }
}
