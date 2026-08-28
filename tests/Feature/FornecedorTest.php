<?php

namespace Tests\Feature;

use App\Enums\StatusFornecedor;
use App\Models\Fornecedor;
use App\Models\Produto;
use App\Models\User;
use App\Rules\Cnpj;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FornecedorTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'nome' => 'Fornecedor Teste',
            'cnpj' => Cnpj::generate(),
            'email' => 'contato@fornecedor-teste.com',
            'telefone' => '+5511987654321',
            'status' => StatusFornecedor::Ativo->value,
        ], $overrides);
    }

    public function test_fornecedores_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('fornecedores'));

        $response->assertOk();
    }

    public function test_fornecedor_can_be_created_with_valid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('fornecedores.store'), $this->validPayload());

        $response->assertSessionHasNoErrors();
        $response->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Fornecedor cadastrado com sucesso.']);

        $this->assertDatabaseCount('fornecedores', 1);
        $this->assertSame(StatusFornecedor::Ativo, Fornecedor::first()->status);
    }

    public function test_nome_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('fornecedores.store'), $this->validPayload(['nome' => '']));

        $response->assertSessionHasErrors('nome');
    }

    public function test_nome_must_have_at_least_3_characters(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('fornecedores.store'), $this->validPayload(['nome' => 'Ab']));

        $response->assertSessionHasErrors('nome');
    }

    public function test_nome_cannot_exceed_150_characters(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('fornecedores.store'), $this->validPayload(['nome' => str_repeat('a', 151)]));

        $response->assertSessionHasErrors('nome');
    }

    public function test_cnpj_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('fornecedores.store'), $this->validPayload(['cnpj' => '']));

        $response->assertSessionHasErrors('cnpj');
    }

    public function test_cnpj_must_be_a_valid_cnpj(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('fornecedores.store'), $this->validPayload(['cnpj' => '11111111111111']));

        $response->assertSessionHasErrors('cnpj');
    }

    public function test_cnpj_must_be_unique(): void
    {
        $user = User::factory()->create();
        $existing = Fornecedor::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('fornecedores.store'), $this->validPayload(['cnpj' => $existing->cnpj]));

        $response->assertSessionHasErrors('cnpj');
    }

    public function test_email_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('fornecedores.store'), $this->validPayload(['email' => '']));

        $response->assertSessionHasErrors('email');
    }

    public function test_email_must_be_a_valid_email(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('fornecedores.store'), $this->validPayload(['email' => 'not-an-email']));

        $response->assertSessionHasErrors('email');
    }

    public function test_email_must_be_unique(): void
    {
        $user = User::factory()->create();
        $existing = Fornecedor::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('fornecedores.store'), $this->validPayload(['email' => $existing->email]));

        $response->assertSessionHasErrors('email');
    }

    public function test_telefone_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('fornecedores.store'), $this->validPayload(['telefone' => '']));

        $response->assertSessionHasErrors('telefone');
    }

    public function test_telefone_must_include_ddi_and_ddd(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('fornecedores.store'), $this->validPayload(['telefone' => '987654321']));

        $response->assertSessionHasErrors('telefone');
    }

    public function test_status_is_restricted_to_ativo_or_inativo(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('fornecedores.store'), $this->validPayload(['status' => 'pendente']));

        $response->assertSessionHasErrors('status');
    }

    public function test_fornecedor_can_be_updated_ignoring_its_own_unique_values(): void
    {
        $user = User::factory()->create();
        $fornecedor = Fornecedor::factory()->create();

        $response = $this->actingAs($user)->put(route('fornecedores.update', $fornecedor), $this->validPayload([
            'cnpj' => $fornecedor->cnpj,
            'email' => $fornecedor->email,
            'nome' => 'Novo Nome',
            'status' => StatusFornecedor::Inativo->value,
        ]));

        $response->assertSessionHasNoErrors();
        $response->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Fornecedor atualizado com sucesso.']);

        $fornecedor->refresh();
        $this->assertSame('Novo Nome', $fornecedor->nome);
        $this->assertSame(StatusFornecedor::Inativo, $fornecedor->status);
    }

    public function test_fornecedor_is_soft_deleted(): void
    {
        $user = User::factory()->create();
        $fornecedor = Fornecedor::factory()->create();

        $response = $this->actingAs($user)->delete(route('fornecedores.destroy', $fornecedor));

        $response->assertSessionHasNoErrors();
        $response->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Fornecedor excluído com sucesso.']);
        $this->assertSoftDeleted($fornecedor);
        $this->assertNull(Fornecedor::find($fornecedor->id));
    }

    public function test_cnpj_and_email_remain_reserved_while_fornecedor_is_soft_deleted(): void
    {
        $user = User::factory()->create();
        $fornecedor = Fornecedor::factory()->create();
        $fornecedor->delete();

        $response = $this->actingAs($user)->post(route('fornecedores.store'), $this->validPayload([
            'cnpj' => $fornecedor->cnpj,
            'email' => $fornecedor->email,
        ]));

        $response->assertSessionHasErrors(['cnpj', 'email']);
    }

    public function test_fornecedor_can_be_restored(): void
    {
        $user = User::factory()->create();
        $fornecedor = Fornecedor::factory()->create();
        $fornecedor->delete();

        $response = $this->actingAs($user)->patch(route('fornecedores.restaurar', $fornecedor));

        $response->assertSessionHasNoErrors();
        $response->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Fornecedor restaurado com sucesso.']);
        $this->assertNotNull(Fornecedor::find($fornecedor->id));
    }

    public function test_fornecedor_without_produtos_can_be_permanently_deleted(): void
    {
        $user = User::factory()->create();
        $fornecedor = Fornecedor::factory()->create();

        $response = $this->actingAs($user)->delete(route('fornecedores.excluir-permanente', $fornecedor));

        $response->assertSessionHasNoErrors();
        $response->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Fornecedor excluído definitivamente com sucesso.']);
        $this->assertDatabaseCount('fornecedores', 0);
    }

    public function test_fornecedor_with_produtos_cannot_be_permanently_deleted(): void
    {
        $user = User::factory()->create();
        $fornecedor = Fornecedor::factory()->create();
        Produto::factory()->create(['fornecedor_id' => $fornecedor->id]);

        $response = $this->actingAs($user)->delete(route('fornecedores.excluir-permanente', $fornecedor));

        $response->assertSessionHasErrors([
            'fornecedor' => 'Não é possível excluir definitivamente um fornecedor com produtos vinculados.',
        ]);
        $this->assertDatabaseCount('fornecedores', 1);
    }

    public function test_fornecedor_with_soft_deleted_produtos_cannot_be_permanently_deleted(): void
    {
        $user = User::factory()->create();
        $fornecedor = Fornecedor::factory()->create();
        $produto = Produto::factory()->create(['fornecedor_id' => $fornecedor->id]);
        $produto->delete();

        $response = $this->actingAs($user)->delete(route('fornecedores.excluir-permanente', $fornecedor));

        $response->assertSessionHasErrors([
            'fornecedor' => 'Não é possível excluir definitivamente um fornecedor com produtos vinculados.',
        ]);
        $this->assertDatabaseCount('fornecedores', 1);
    }

    public function test_fornecedor_can_be_inativado(): void
    {
        $user = User::factory()->create();
        $fornecedor = Fornecedor::factory()->create(['status' => StatusFornecedor::Ativo]);

        $response = $this->actingAs($user)->patch(route('fornecedores.inativar', $fornecedor));

        $response->assertSessionHasNoErrors();
        $response->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Fornecedor inativado com sucesso.']);
        $this->assertSame(StatusFornecedor::Inativo, $fornecedor->fresh()->status);
    }

    public function test_fornecedor_can_be_reativado(): void
    {
        $user = User::factory()->create();
        $fornecedor = Fornecedor::factory()->create(['status' => StatusFornecedor::Inativo]);

        $response = $this->actingAs($user)->patch(route('fornecedores.reativar', $fornecedor));

        $response->assertSessionHasNoErrors();
        $response->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Fornecedor reativado com sucesso.']);
        $this->assertSame(StatusFornecedor::Ativo, $fornecedor->fresh()->status);
    }

    public function test_inativar_and_reativar_are_not_allowed_on_soft_deleted_fornecedor(): void
    {
        $user = User::factory()->create();
        $fornecedor = Fornecedor::factory()->create();
        $fornecedor->delete();

        $this->actingAs($user)->patch(route('fornecedores.inativar', $fornecedor))->assertNotFound();
        $this->actingAs($user)->patch(route('fornecedores.reativar', $fornecedor))->assertNotFound();
    }

    public function test_busca_filters_fornecedores_by_nome(): void
    {
        $user = User::factory()->create();
        $correspondente = Fornecedor::factory()->create(['nome' => 'Distribuidora Horizonte']);
        Fornecedor::factory()->create(['nome' => 'Comércio Aurora']);

        $response = $this->actingAs($user)->get(route('fornecedores', ['busca' => 'Horizonte']));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Fornecedores')
            ->has('fornecedores.data', 1)
            ->where('fornecedores.data.0.id', $correspondente->id)
            ->where('filtros.busca', 'Horizonte')
        );
    }

    public function test_status_filtro_filters_fornecedores(): void
    {
        $user = User::factory()->create();
        $ativo = Fornecedor::factory()->create(['status' => StatusFornecedor::Ativo]);
        Fornecedor::factory()->create(['status' => StatusFornecedor::Inativo]);

        $response = $this->actingAs($user)->get(route('fornecedores', ['status' => 'ativo']));

        $response->assertInertia(fn (Assert $page) => $page
            ->has('fornecedores.data', 1)
            ->where('fornecedores.data.0.id', $ativo->id)
        );
    }

    public function test_excluidos_filtro_shows_only_soft_deleted_fornecedores(): void
    {
        $user = User::factory()->create();
        $ativo = Fornecedor::factory()->create();
        $excluido = Fornecedor::factory()->create();
        $excluido->delete();

        $listaNormal = $this->actingAs($user)->get(route('fornecedores'));
        $listaNormal->assertInertia(fn (Assert $page) => $page
            ->has('fornecedores.data', 1)
            ->where('fornecedores.data.0.id', $ativo->id)
        );

        $listaExcluidos = $this->actingAs($user)->get(route('fornecedores', ['excluidos' => 1]));
        $listaExcluidos->assertInertia(fn (Assert $page) => $page
            ->has('fornecedores.data', 1)
            ->where('fornecedores.data.0.id', $excluido->id)
        );
    }

    public function test_fornecedores_listing_exposes_produtos_count_including_soft_deleted(): void
    {
        $user = User::factory()->create();
        $fornecedor = Fornecedor::factory()->create();
        Produto::factory()->create(['fornecedor_id' => $fornecedor->id]);
        $produtoExcluido = Produto::factory()->create(['fornecedor_id' => $fornecedor->id]);
        $produtoExcluido->delete();

        $response = $this->actingAs($user)->get(route('fornecedores'));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('fornecedores.data.0.produtos_count', 2)
        );
    }
}
