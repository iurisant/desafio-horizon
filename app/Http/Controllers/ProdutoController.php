<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProdutoRequest;
use App\Http\Requests\UpdateProdutoRequest;
use App\Models\Produto;
use App\Services\ProdutoService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProdutoController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Produtos', [
            'produtos' => Produto::query()->with('fornecedor')->latest()->paginate(15),
        ]);
    }

    public function store(StoreProdutoRequest $request, ProdutoService $service): RedirectResponse
    {
        $service->create($request->validated());

        return back();
    }

    public function update(UpdateProdutoRequest $request, Produto $produto, ProdutoService $service): RedirectResponse
    {
        $service->update($produto, $request->validated());

        return back();
    }

    public function destroy(Produto $produto, ProdutoService $service): RedirectResponse
    {
        $service->delete($produto);

        return back();
    }

    public function restore(Produto $produto, ProdutoService $service): RedirectResponse
    {
        $service->restore($produto);

        return back();
    }

    public function forceDestroy(Produto $produto, ProdutoService $service): RedirectResponse
    {
        $service->forceDelete($produto);

        return back();
    }
}
