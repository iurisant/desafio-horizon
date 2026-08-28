<?php

namespace Tests\Feature;

use App\Enums\StatusFornecedor;
use App\Enums\StatusProduto;
use App\Models\Fornecedor;
use App\Models\Produto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_dashboard_retorna_metricas_corretas_sem_filtros(): void
    {
        $user = User::factory()->create();

        $fornecedorComProdutos = Fornecedor::factory()->create(['status' => StatusFornecedor::Ativo]);
        Fornecedor::factory()->create(['status' => StatusFornecedor::Inativo]);

        Produto::factory()->for($fornecedorComProdutos)->create(['status' => StatusProduto::Ativo, 'preco' => 100]);
        Produto::factory()->for($fornecedorComProdutos)->create(['status' => StatusProduto::Ativo, 'preco' => 50]);
        Produto::factory()->for($fornecedorComProdutos)->create(['status' => StatusProduto::Inativo, 'preco' => 30]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('metricas.cards.fornecedores_ativos', 1)
            ->where('metricas.cards.fornecedores_inativos', 1)
            ->where('metricas.cards.produtos_total', 3)
            ->where('metricas.cards.produtos_excluidos', 0)
            ->where('metricas.cards.valor_total_produtos', '180.00')
            ->where('metricas.topFornecedores.0.id', $fornecedorComProdutos->id)
            ->where('metricas.topFornecedores.0.produtos_count', 3)
        );
    }

    public function test_dashboard_filtra_por_status_afeta_apenas_metricas_de_produto(): void
    {
        $user = User::factory()->create();
        $fornecedor = Fornecedor::factory()->create();

        Produto::factory()->for($fornecedor)->create(['status' => StatusProduto::Ativo, 'preco' => 100]);
        Produto::factory()->for($fornecedor)->create(['status' => StatusProduto::Inativo, 'preco' => 999]);

        $response = $this->actingAs($user)->get(route('dashboard', ['status' => 'ativo']));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('metricas.cards.produtos_total', 1)
            ->where('metricas.cards.valor_total_produtos', '100.00')
            ->where('metricas.cards.preco_medio_produtos', '100.00')
        );
    }

    public function test_dashboard_filtra_por_periodo(): void
    {
        $user = User::factory()->create();

        $fornecedorAntigo = Fornecedor::factory()->create(['created_at' => '2025-01-10']);
        Produto::factory()->for($fornecedorAntigo)->create(['created_at' => '2025-01-10', 'preco' => 10]);

        $fornecedorRecente = Fornecedor::factory()->create(['created_at' => '2026-06-15']);
        Produto::factory()->for($fornecedorRecente)->create(['created_at' => '2026-06-15', 'preco' => 20]);

        $response = $this->actingAs($user)->get(route('dashboard', [
            'data_inicio' => '2026-01-01',
            'data_fim' => '2026-12-31',
        ]));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('metricas.cards.fornecedores_ativos', 1)
            ->where('metricas.cards.produtos_total', 1)
            ->where('metricas.cards.valor_total_produtos', '20.00')
        );
    }

    public function test_data_fim_anterior_a_data_inicio_e_invalida(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard', [
            'data_inicio' => '2026-06-01',
            'data_fim' => '2026-01-01',
        ]));

        $response->assertSessionHasErrors('data_fim');
    }

    public function test_produtos_excluidos_sao_contados_na_lixeira_mas_nao_nos_demais_cards(): void
    {
        $user = User::factory()->create();
        $fornecedor = Fornecedor::factory()->create();

        Produto::factory()->for($fornecedor)->create(['status' => StatusProduto::Ativo]);
        $excluido = Produto::factory()->for($fornecedor)->create(['status' => StatusProduto::Ativo]);
        $excluido->delete();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('metricas.cards.produtos_total', 1)
            ->where('metricas.cards.produtos_excluidos', 1)
        );
    }
}
