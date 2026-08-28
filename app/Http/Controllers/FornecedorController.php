<?php

namespace App\Http\Controllers;

use App\Concerns\FlashesToastMessages;
use App\Enums\StatusFornecedor;
use App\Http\Requests\StoreFornecedorRequest;
use App\Http\Requests\UpdateFornecedorRequest;
use App\Models\Fornecedor;
use App\Services\FornecedorService;
use Dedoc\Scramble\Attributes\IgnoreResponse;
use Dedoc\Scramble\Attributes\Response as ScrambleResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FornecedorController extends Controller
{
    use FlashesToastMessages;

    /**
     * Lista fornecedores com paginação, filtros por nome/status e contagem de produtos vinculados.
     */
    public function index(Request $request, FornecedorService $service): Response
    {
        $busca = $request->string('busca')->trim()->value() ?: null;
        $status = $request->enum('status', StatusFornecedor::class);
        $excluidos = $request->boolean('excluidos');

        return Inertia::render('Fornecedores', [
            'fornecedores' => $service->list($busca, $status, $excluidos),
            'filtros' => [
                'busca' => $busca,
                'status' => $status?->value,
                'excluidos' => $excluidos,
            ],
        ]);
    }

    /**
     * Cadastra um novo fornecedor.
     */
    #[IgnoreResponse(200)]
    #[ScrambleResponse(201, description: 'Fornecedor cadastrado com sucesso.')]
    public function store(StoreFornecedorRequest $request, FornecedorService $service): RedirectResponse
    {
        $service->create($request->validated());

        $this->flashToast('success', 'Fornecedor cadastrado com sucesso.');

        return back();
    }

    /**
     * Atualiza os dados de um fornecedor.
     */
    public function update(UpdateFornecedorRequest $request, Fornecedor $fornecedor, FornecedorService $service): RedirectResponse
    {
        $service->update($fornecedor, $request->validated());

        $this->flashToast('success', 'Fornecedor atualizado com sucesso.');

        return back();
    }

    /**
     * Exclui um fornecedor (soft delete, reversível).
     */
    public function destroy(Fornecedor $fornecedor, FornecedorService $service): RedirectResponse
    {
        $service->delete($fornecedor);

        $this->flashToast('success', 'Fornecedor excluído com sucesso.');

        return back();
    }

    /**
     * Restaura um fornecedor excluído (soft delete).
     */
    public function restore(Fornecedor $fornecedor, FornecedorService $service): RedirectResponse
    {
        $service->restore($fornecedor);

        $this->flashToast('success', 'Fornecedor restaurado com sucesso.');

        return back();
    }

    /**
     * Exclui um fornecedor definitivamente (irreversível). Recusado se ele ainda tiver produtos vinculados.
     */
    public function forceDestroy(Fornecedor $fornecedor, FornecedorService $service): RedirectResponse
    {
        $service->forceDelete($fornecedor);

        $this->flashToast('success', 'Fornecedor excluído definitivamente com sucesso.');

        return back();
    }

    /**
     * Inativa um fornecedor. Produtos já cadastrados não são afetados, mas nenhum produto
     * novo pode ser vinculado a um fornecedor inativo.
     */
    public function inativar(Fornecedor $fornecedor, FornecedorService $service): RedirectResponse
    {
        $service->inativar($fornecedor);

        $this->flashToast('success', 'Fornecedor inativado com sucesso.');

        return back();
    }

    /**
     * Reativa um fornecedor inativo.
     */
    public function reativar(Fornecedor $fornecedor, FornecedorService $service): RedirectResponse
    {
        $service->reativar($fornecedor);

        $this->flashToast('success', 'Fornecedor reativado com sucesso.');

        return back();
    }
}
