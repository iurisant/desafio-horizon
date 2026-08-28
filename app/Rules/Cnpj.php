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
     *
     * Accepts both the classic all-numeric CNPJ and the alphanumeric CNPJ
     * format adopted by the Receita Federal: the first 12 characters may be
     * digits (0-9) or uppercase letters (A-Z) and the 2 check digits remain
     * numeric.
     */
    public static function isValid(string $value): bool
    {
        $cnpj = strtoupper((string) preg_replace('/[^0-9A-Za-z]/', '', $value));

        if (preg_match('/^[0-9A-Z]{14}$/', $cnpj) !== 1 || preg_match('/^(.)\1{13}$/', $cnpj) === 1) {
            return false;
        }

        if (! ctype_digit(substr($cnpj, 12, 2))) {
            return false;
        }

        return (int) $cnpj[12] === self::checkDigit($cnpj, 12)
            && (int) $cnpj[13] === self::checkDigit($cnpj, 13);
    }

    /**
     * Generate a structurally valid alphanumeric CNPJ. Intended for tests and factories.
     */
    public static function generate(): string
    {
        $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $base = '';

        for ($i = 0; $i < 12; $i++) {
            $base .= $chars[random_int(0, strlen($chars) - 1)];
        }

        $base .= (string) self::checkDigit($base, 12);
        $base .= (string) self::checkDigit($base, 13);

        return $base;
    }

    /**
     * Calculate a CNPJ check digit from the first $length characters of $cnpj.
     */
    private static function checkDigit(string $cnpj, int $length): int
    {
        $weights = $length === 12
            ? [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]
            : [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        $sum = 0;

        for ($i = 0; $i < $length; $i++) {
            $sum += self::charValue($cnpj[$i]) * $weights[$i];
        }

        $remainder = $sum % 11;

        return $remainder < 2 ? 0 : 11 - $remainder;
    }

    /**
     * Map a CNPJ character (0-9 or uppercase A-Z) to its numeric value for
     * the check digit calculation: '0'-'9' → 0-9, 'A'-'Z' → 17-42 (i.e. the
     * character's ASCII code minus 48, per the Receita Federal spec).
     */
    private static function charValue(string $char): int
    {
        return ord($char) - 48;
    }
}
