<?php

namespace App\Services;

use App\Enums\StatusProduto;
use App\Models\Produto;
use Illuminate\Pagination\LengthAwarePaginator;

class ProdutoService
{
    /**
     * @param  array<string, mixed>  $dados
     */
    public function create(array $dados): Produto
    {
        return Produto::create($dados);
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    public function update(Produto $produto, array $dados): Produto
    {
        $produto->update($dados);

        return $produto;
    }

    public function delete(Produto $produto): void
    {
        $produto->delete();
    }

    public function restore(Produto $produto): Produto
    {
        $produto->restore();

        return $produto;
    }

    public function forceDelete(Produto $produto): void
    {
        $produto->forceDelete();
    }

    public function inativar(Produto $produto): Produto
    {
        $produto->update(['status' => StatusProduto::Inativo]);

        return $produto;
    }

    public function reativar(Produto $produto): Produto
    {
        $produto->update(['status' => StatusProduto::Ativo]);

        return $produto;
    }

    /**
     * @return LengthAwarePaginator<int, Produto>
     */
    public function list(?string $busca, ?StatusProduto $status, bool $excluidos): LengthAwarePaginator
    {
        return Produto::query()
            ->with('fornecedor')
            ->when($excluidos, fn ($query) => $query->onlyTrashed())
            ->when($busca, fn ($query, $busca) => $query->where('nome', 'like', "%{$busca}%"))
            ->when($status, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }
}
