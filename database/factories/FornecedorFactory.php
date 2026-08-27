<?php

namespace Database\Factories;

use App\Enums\StatusFornecedor;
use App\Models\Fornecedor;
use App\Rules\Cnpj;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Fornecedor>
 */
class FornecedorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => $this->faker->company(),
            'cnpj' => Cnpj::generate(),
            'email' => $this->faker->unique()->companyEmail(),
            'telefone' => '+55'.$this->faker->numerify('##9########'),
            'status' => StatusFornecedor::Ativo,
        ];
    }

    /**
     * Indicate that the fornecedor is inactive.
     */
    public function inativo(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StatusFornecedor::Inativo,
        ]);
    }
}
