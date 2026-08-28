<?php

namespace Database\Seeders;

use App\Enums\StatusProduto;
use App\Models\Fornecedor;
use App\Models\Produto;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     *
     * Popula o banco com Fornecedores e Produtos fictícios para permitir
     * a avaliação manual das telas sem precisar cadastrar dados na mão.
     */
    public function run(): void
    {
        Fornecedor::factory()
            ->count(12)
            ->create()
            ->each(function (Fornecedor $fornecedor): void {
                Produto::factory()
                    ->count(random_int(2, 6))
                    ->for($fornecedor)
                    ->create();
            });

        Fornecedor::factory()
            ->inativo()
            ->count(3)
            ->create()
            ->each(function (Fornecedor $fornecedor): void {
                Produto::factory()
                    ->count(random_int(1, 3))
                    ->for($fornecedor)
                    ->create();
            });

        // Alguns produtos inativos e alguns excluídos (soft delete), para exercitar
        // filtros, lixeira e ações condicionais na interface.
        Produto::query()->inRandomOrder()->limit(8)->get()->each(
            fn (Produto $produto) => $produto->update(['status' => StatusProduto::Inativo])
        );

        Produto::query()->inRandomOrder()->limit(4)->get()->each(
            fn (Produto $produto) => $produto->delete()
        );
    }
}
