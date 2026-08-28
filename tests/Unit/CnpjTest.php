<?php

namespace Tests\Unit;

use App\Rules\Cnpj;
use PHPUnit\Framework\TestCase;

class CnpjTest extends TestCase
{
    /**
     * Official worked example from the Receita Federal / SERPRO alphanumeric
     * CNPJ specification (result: 12.ABC.345/01DE-35).
     */
    public function test_accepts_the_official_alphanumeric_example(): void
    {
        $this->assertTrue(Cnpj::isValid('12.ABC.345/01DE-35'));
        $this->assertTrue(Cnpj::isValid('12ABC34501DE35'));
        $this->assertTrue(Cnpj::isValid('12abc34501de35'));
    }

    public function test_accepts_a_classic_all_numeric_cnpj(): void
    {
        $this->assertTrue(Cnpj::isValid($this->validNumericCnpj()));
    }

    public function test_rejects_a_tampered_check_digit(): void
    {
        $cnpj = $this->validNumericCnpj();
        $lastDigit = (int) substr($cnpj, -1);
        $tampered = substr($cnpj, 0, -1).(($lastDigit + 1) % 10);

        $this->assertFalse(Cnpj::isValid($tampered));
    }

    public function test_rejects_non_numeric_check_digits(): void
    {
        $this->assertFalse(Cnpj::isValid('12ABC34501DEAB'));
    }

    public function test_rejects_repeated_characters(): void
    {
        $this->assertFalse(Cnpj::isValid('11111111111111'));
        $this->assertFalse(Cnpj::isValid('AAAAAAAAAAAAAA'));
    }

    public function test_rejects_wrong_length(): void
    {
        $this->assertFalse(Cnpj::isValid('12ABC34501DE3'));
        $this->assertFalse(Cnpj::isValid('12ABC34501DE355'));
    }

    public function test_generate_always_produces_a_structurally_valid_cnpj(): void
    {
        for ($i = 0; $i < 50; $i++) {
            $cnpj = Cnpj::generate();

            $this->assertMatchesRegularExpression('/^[0-9A-Z]{12}\d{2}$/', $cnpj);
            $this->assertTrue(Cnpj::isValid($cnpj));
        }
    }

    /**
     * Build a structurally valid, purely numeric CNPJ using the classic
     * (pre-alphanumeric) mod-11 algorithm, independently from App\Rules\Cnpj,
     * to confirm legacy CNPJs are still accepted.
     */
    private function validNumericCnpj(): string
    {
        $base = '';

        for ($i = 0; $i < 12; $i++) {
            $base .= random_int(0, 9);
        }

        $base .= $this->classicCheckDigit($base, 12);
        $base .= $this->classicCheckDigit($base, 13);

        return $base;
    }

    private function classicCheckDigit(string $cnpj, int $length): int
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
