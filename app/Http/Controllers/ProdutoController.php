<?php

namespace App\Http\Controllers;

use App\Concerns\FlashesToastMessages;
use App\Enums\StatusProduto;
use App\Http\Requests\StoreProdutoRequest;
use App\Http\Requests\UpdateProdutoRequest;
use App\Models\Produto;
use App\Services\FornecedorService;
use App\Services\ProdutoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProdutoController extends Controller
{
    use FlashesToastMessages;

    /**
     * Lista produtos com paginação, filtros por nome/status e a lista de empresas aptas a receber vínculo.
     */
    public function index(Request $request, ProdutoService $produtoService, FornecedorService $fornecedorService): Response
    {
        $busca = $request->string('busca')->trim()->value() ?: null;
        $status = $request->enum('status', StatusProduto::class);
        $excluidos = $request->boolean('excluidos');

        return Inertia::render('Produtos', [
            'produtos' => $produtoService->list($busca, $status, $excluidos),
            'filtros' => [
                'busca' => $busca,
                'status' => $status?->value,
                'excluidos' => $excluidos,
            ],
            'fornecedoresElegiveis' => $fornecedorService->listarElegiveis(),
        ]);
    }

    /**
     * Cadastra um novo produto vinculado a um fornecedor ativo.
     */
    public function store(StoreProdutoRequest $request, ProdutoService $service): RedirectResponse
    {
        $service->create($request->validated());

        $this->flashToast('success', 'Produto cadastrado com sucesso.');

        return back();
    }

    /**
     * Atualiza os dados de um produto.
     */
    public function update(UpdateProdutoRequest $request, Produto $produto, ProdutoService $service): RedirectResponse
    {
        $service->update($produto, $request->validated());

        $this->flashToast('success', 'Produto atualizado com sucesso.');

        return back();
    }

    /**
     * Exclui um produto (soft delete, reversível).
     */
    public function destroy(Produto $produto, ProdutoService $service): RedirectResponse
    {
        $service->delete($produto);

        $this->flashToast('success', 'Produto excluído com sucesso.');

        return back();
    }

    /**
     * Restaura um produto excluído (soft delete).
     */
    public function restore(Produto $produto, ProdutoService $service): RedirectResponse
    {
        $service->restore($produto);

        $this->flashToast('success', 'Produto restaurado com sucesso.');

        return back();
    }

    /**
     * Exclui um produto definitivamente (irreversível).
     */
    public function forceDestroy(Produto $produto, ProdutoService $service): RedirectResponse
    {
        $service->forceDelete($produto);

        $this->flashToast('success', 'Produto excluído definitivamente com sucesso.');

        return back();
    }

    /**
     * Inativa um produto.
     */
    public function inativar(Produto $produto, ProdutoService $service): RedirectResponse
    {
        $service->inativar($produto);

        $this->flashToast('success', 'Produto inativado com sucesso.');

        return back();
    }

    /**
     * Reativa um produto inativo.
     */
    public function reativar(Produto $produto, ProdutoService $service): RedirectResponse
    {
        $service->reativar($produto);

        $this->flashToast('success', 'Produto reativado com sucesso.');

        return back();
    }
}
