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

class ProdutoTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'fornecedor_id' => Fornecedor::factory()->create()->id,
            'nome' => 'Produto Teste',
            'descricao' => 'Descrição do produto de teste.',
            'preco' => '150.00',
            'codigo_interno' => 'SKU-0001',
            'status' => StatusProduto::Ativo->value,
        ], $overrides);
    }

    public function test_produtos_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('produtos'));

        $response->assertOk();
    }

    public function test_produto_can_be_created_with_valid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('produtos.store'), $this->validPayload());

        $response->assertSessionHasNoErrors();
        $response->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Produto cadastrado com sucesso.']);
        $this->assertDatabaseCount('produtos', 1);
        $this->assertSame(StatusProduto::Ativo, Produto::first()->status);
    }

    public function test_fornecedor_id_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('produtos.store'), $this->validPayload(['fornecedor_id' => '']));

        $response->assertSessionHasErrors('fornecedor_id');
    }

    public function test_fornecedor_id_must_exist(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('produtos.store'), $this->validPayload(['fornecedor_id' => 999999]));

        $response->assertSessionHasErrors('fornecedor_id');
    }

    public function test_fornecedor_must_be_active(): void
    {
        $user = User::factory()->create();
        $fornecedor = Fornecedor::factory()->create(['status' => StatusFornecedor::Inativo]);

        $response = $this->actingAs($user)
            ->post(route('produtos.store'), $this->validPayload(['fornecedor_id' => $fornecedor->id]));

        $response->assertSessionHasErrors('fornecedor_id');
    }

    public function test_fornecedor_must_not_be_soft_deleted(): void
    {
        $user = User::factory()->create();
        $fornecedor = Fornecedor::factory()->create();
        $fornecedor->delete();

        $response = $this->actingAs($user)
            ->post(route('produtos.store'), $this->validPayload(['fornecedor_id' => $fornecedor->id]));

        $response->assertSessionHasErrors('fornecedor_id');
    }

    public function test_nome_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('produtos.store'), $this->validPayload(['nome' => '']));

        $response->assertSessionHasErrors('nome');
    }

    public function test_nome_must_have_at_least_3_characters(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('produtos.store'), $this->validPayload(['nome' => 'Ab']));

        $response->assertSessionHasErrors('nome');
    }

    public function test_nome_cannot_exceed_150_characters(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('produtos.store'), $this->validPayload(['nome' => str_repeat('a', 151)]));

        $response->assertSessionHasErrors('nome');
    }

    public function test_descricao_is_optional(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('produtos.store'), $this->validPayload(['descricao' => null]));

        $response->assertSessionHasNoErrors();
    }

    public function test_descricao_cannot_exceed_2000_characters(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('produtos.store'), $this->validPayload(['descricao' => str_repeat('a', 2001)]));

        $response->assertSessionHasErrors('descricao');
    }

    public function test_preco_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('produtos.store'), $this->validPayload(['preco' => '']));

        $response->assertSessionHasErrors('preco');
    }

    public function test_preco_must_be_greater_than_zero(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('produtos.store'), $this->validPayload(['preco' => '0.00']));

        $response->assertSessionHasErrors('preco');
    }

    public function test_preco_must_have_exactly_two_decimal_places(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('produtos.store'), $this->validPayload(['preco' => '150.1']));

        $response->assertSessionHasErrors('preco');
    }

    public function test_codigo_interno_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('produtos.store'), $this->validPayload(['codigo_interno' => '']));

        $response->assertSessionHasErrors('codigo_interno');
    }

    public function test_codigo_interno_must_be_unique_within_the_same_fornecedor(): void
    {
        $user = User::factory()->create();
        $existing = Produto::factory()->create(['codigo_interno' => 'SKU-0001']);

        $response = $this->actingAs($user)->post(route('produtos.store'), $this->validPayload([
            'fornecedor_id' => $existing->fornecedor_id,
            'codigo_interno' => 'SKU-0001',
        ]));

        $response->assertSessionHasErrors('codigo_interno');
    }

    public function test_codigo_interno_can_repeat_across_different_fornecedores(): void
    {
        $user = User::factory()->create();
        Produto::factory()->create(['codigo_interno' => 'SKU-0001']);

        $response = $this->actingAs($user)->post(route('produtos.store'), $this->validPayload([
            'fornecedor_id' => Fornecedor::factory()->create()->id,
            'codigo_interno' => 'SKU-0001',
        ]));

        $response->assertSessionHasNoErrors();
    }

    public function test_status_is_restricted_to_ativo_or_inativo(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('produtos.store'), $this->validPayload(['status' => 'pendente']));

        $response->assertSessionHasErrors('status');
    }

    public function test_produto_can_be_updated(): void
    {
        $user = User::factory()->create();
        $produto = Produto::factory()->create();

        $response = $this->actingAs($user)->put(route('produtos.update', $produto), $this->validPayload([
            'fornecedor_id' => $produto->fornecedor_id,
            'codigo_interno' => $produto->codigo_interno,
            'nome' => 'Produto Atualizado',
            'status' => StatusProduto::Inativo->value,
        ]));

        $response->assertSessionHasNoErrors();
        $response->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Produto atualizado com sucesso.']);

        $produto->refresh();
        $this->assertSame('Produto Atualizado', $produto->nome);
        $this->assertSame(StatusProduto::Inativo, $produto->status);
    }

    public function test_produto_is_soft_deleted(): void
    {
        $user = User::factory()->create();
        $produto = Produto::factory()->create();

        $response = $this->actingAs($user)->delete(route('produtos.destroy', $produto));

        $response->assertSessionHasNoErrors();
        $response->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Produto excluído com sucesso.']);
        $this->assertSoftDeleted($produto);
        $this->assertNull(Produto::find($produto->id));
    }

    public function test_produto_can_be_restored(): void
    {
        $user = User::factory()->create();
        $produto = Produto::factory()->create();
        $produto->delete();

        $response = $this->actingAs($user)->patch(route('produtos.restaurar', $produto));

        $response->assertSessionHasNoErrors();
        $response->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Produto restaurado com sucesso.']);
        $this->assertNotNull(Produto::find($produto->id));
    }

    public function test_produto_can_be_permanently_deleted(): void
    {
        $user = User::factory()->create();
        $produto = Produto::factory()->create();

        $response = $this->actingAs($user)->delete(route('produtos.excluir-permanente', $produto));

        $response->assertSessionHasNoErrors();
        $response->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Produto excluído definitivamente com sucesso.']);
        $this->assertDatabaseCount('produtos', 0);
    }

    public function test_soft_deleted_produto_can_be_permanently_deleted(): void
    {
        $user = User::factory()->create();
        $produto = Produto::factory()->create();
        $produto->delete();

        $response = $this->actingAs($user)->delete(route('produtos.excluir-permanente', $produto));

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseCount('produtos', 0);
    }

    public function test_produto_can_be_inativado(): void
    {
        $user = User::factory()->create();
        $produto = Produto::factory()->create(['status' => StatusProduto::Ativo]);

        $response = $this->actingAs($user)->patch(route('produtos.inativar', $produto));

        $response->assertSessionHasNoErrors();
        $response->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Produto inativado com sucesso.']);
        $this->assertSame(StatusProduto::Inativo, $produto->fresh()->status);
    }

    public function test_produto_can_be_reativado(): void
    {
        $user = User::factory()->create();
        $produto = Produto::factory()->create(['status' => StatusProduto::Inativo]);

        $response = $this->actingAs($user)->patch(route('produtos.reativar', $produto));

        $response->assertSessionHasNoErrors();
        $response->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Produto reativado com sucesso.']);
        $this->assertSame(StatusProduto::Ativo, $produto->fresh()->status);
    }

    public function test_inativar_and_reativar_are_not_allowed_on_soft_deleted_produto(): void
    {
        $user = User::factory()->create();
        $produto = Produto::factory()->create();
        $produto->delete();

        $this->actingAs($user)->patch(route('produtos.inativar', $produto))->assertNotFound();
        $this->actingAs($user)->patch(route('produtos.reativar', $produto))->assertNotFound();
    }

    public function test_busca_filters_produtos_by_nome(): void
    {
        $user = User::factory()->create();
        $correspondente = Produto::factory()->create(['nome' => 'Cadeira Ergonômica']);
        Produto::factory()->create(['nome' => 'Mesa de Escritório']);

        $response = $this->actingAs($user)->get(route('produtos', ['busca' => 'Ergonômica']));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Produtos')
            ->has('produtos.data', 1)
            ->where('produtos.data.0.id', $correspondente->id)
            ->where('filtros.busca', 'Ergonômica')
        );
    }

    public function test_status_filtro_filters_produtos(): void
    {
        $user = User::factory()->create();
        $ativo = Produto::factory()->create(['status' => StatusProduto::Ativo]);
        Produto::factory()->create(['status' => StatusProduto::Inativo]);

        $response = $this->actingAs($user)->get(route('produtos', ['status' => 'ativo']));

        $response->assertInertia(fn (Assert $page) => $page
            ->has('produtos.data', 1)
            ->where('produtos.data.0.id', $ativo->id)
        );
    }

    public function test_excluidos_filtro_shows_only_soft_deleted_produtos(): void
    {
        $user = User::factory()->create();
        $ativo = Produto::factory()->create();
        $excluido = Produto::factory()->create();
        $excluido->delete();

        $listaNormal = $this->actingAs($user)->get(route('produtos'));
        $listaNormal->assertInertia(fn (Assert $page) => $page
            ->has('produtos.data', 1)
            ->where('produtos.data.0.id', $ativo->id)
        );

        $listaExcluidos = $this->actingAs($user)->get(route('produtos', ['excluidos' => 1]));
        $listaExcluidos->assertInertia(fn (Assert $page) => $page
            ->has('produtos.data', 1)
            ->where('produtos.data.0.id', $excluido->id)
        );
    }

    public function test_produtos_listing_exposes_only_active_fornecedores_as_elegiveis(): void
    {
        $user = User::factory()->create();
        $ativo = Fornecedor::factory()->create(['status' => StatusFornecedor::Ativo]);
        Fornecedor::factory()->create(['status' => StatusFornecedor::Inativo]);
        $excluido = Fornecedor::factory()->create();
        $excluido->delete();

        $response = $this->actingAs($user)->get(route('produtos'));

        $response->assertInertia(fn (Assert $page) => $page
            ->has('fornecedoresElegiveis', 1)
            ->where('fornecedoresElegiveis.0.id', $ativo->id)
        );
    }
}
