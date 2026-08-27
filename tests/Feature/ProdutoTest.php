<?php

namespace Tests\Feature;

use App\Enums\StatusFornecedor;
use App\Enums\StatusProduto;
use App\Models\Fornecedor;
use App\Models\Produto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $this->assertSoftDeleted($produto);
        $this->assertNull(Produto::find($produto->id));
    }
}
