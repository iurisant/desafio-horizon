<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFornecedorRequest;
use App\Http\Requests\UpdateFornecedorRequest;
use App\Models\Fornecedor;
use App\Services\FornecedorService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class FornecedorController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Fornecedores', [
            'fornecedores' => Fornecedor::query()->latest()->paginate(15),
        ]);
    }

    public function store(StoreFornecedorRequest $request, FornecedorService $service): RedirectResponse
    {
        $service->create($request->validated());

        return back();
    }

    public function update(UpdateFornecedorRequest $request, Fornecedor $fornecedor, FornecedorService $service): RedirectResponse
    {
        $service->update($fornecedor, $request->validated());

        return back();
    }

    public function destroy(Fornecedor $fornecedor, FornecedorService $service): RedirectResponse
    {
        $service->delete($fornecedor);

        return back();
    }

    public function restore(Fornecedor $fornecedor, FornecedorService $service): RedirectResponse
    {
        $service->restore($fornecedor);

        return back();
    }

    public function forceDestroy(Fornecedor $fornecedor, FornecedorService $service): RedirectResponse
    {
        $service->forceDelete($fornecedor);

        return back();
    }
}
