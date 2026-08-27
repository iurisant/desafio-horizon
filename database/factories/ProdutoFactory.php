<?php

namespace Database\Factories;

use App\Enums\StatusProduto;
use App\Models\Fornecedor;
use App\Models\Produto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Produto>
 */
class ProdutoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'fornecedor_id' => Fornecedor::factory(),
            'nome' => $this->faker->words(3, true),
            'descricao' => $this->faker->optional()->paragraph(),
            'preco' => $this->faker->randomFloat(2, 1, 1000),
            'codigo_interno' => $this->faker->unique()->bothify('SKU-####??'),
            'status' => StatusProduto::Ativo,
        ];
    }

    /**
     * Indicate that the produto is inactive.
     */
    public function inativo(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StatusProduto::Inativo,
        ]);
    }
}
