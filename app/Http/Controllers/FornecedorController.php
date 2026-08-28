<?php

namespace App\Http\Controllers;

use App\Concerns\FlashesToastMessages;
use App\Enums\StatusFornecedor;
use App\Http\Requests\StoreFornecedorRequest;
use App\Http\Requests\UpdateFornecedorRequest;
use App\Models\Fornecedor;
use App\Services\FornecedorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FornecedorController extends Controller
{
    use FlashesToastMessages;

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

    public function store(StoreFornecedorRequest $request, FornecedorService $service): RedirectResponse
    {
        $service->create($request->validated());

        $this->flashToast('success', 'Fornecedor cadastrado com sucesso.');

        return back();
    }

    public function update(UpdateFornecedorRequest $request, Fornecedor $fornecedor, FornecedorService $service): RedirectResponse
    {
        $service->update($fornecedor, $request->validated());

        $this->flashToast('success', 'Fornecedor atualizado com sucesso.');

        return back();
    }

    public function destroy(Fornecedor $fornecedor, FornecedorService $service): RedirectResponse
    {
        $service->delete($fornecedor);

        $this->flashToast('success', 'Fornecedor excluído com sucesso.');

        return back();
    }

    public function restore(Fornecedor $fornecedor, FornecedorService $service): RedirectResponse
    {
        $service->restore($fornecedor);

        $this->flashToast('success', 'Fornecedor restaurado com sucesso.');

        return back();
    }

    public function forceDestroy(Fornecedor $fornecedor, FornecedorService $service): RedirectResponse
    {
        $service->forceDelete($fornecedor);

        $this->flashToast('success', 'Fornecedor excluído definitivamente com sucesso.');

        return back();
    }

    public function inativar(Fornecedor $fornecedor, FornecedorService $service): RedirectResponse
    {
        $service->inativar($fornecedor);

        $this->flashToast('success', 'Fornecedor inativado com sucesso.');

        return back();
    }

    public function reativar(Fornecedor $fornecedor, FornecedorService $service): RedirectResponse
    {
        $service->reativar($fornecedor);

        $this->flashToast('success', 'Fornecedor reativado com sucesso.');

        return back();
    }
}
