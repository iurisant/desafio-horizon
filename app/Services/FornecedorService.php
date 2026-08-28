<?php

namespace App\Services;

use App\Enums\StatusFornecedor;
use App\Models\Fornecedor;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class FornecedorService
{
    /**
     * @param  array<string, mixed>  $dados
     */
    public function create(array $dados): Fornecedor
    {
        return Fornecedor::create($dados);
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    public function update(Fornecedor $fornecedor, array $dados): Fornecedor
    {
        $fornecedor->update($dados);

        return $fornecedor;
    }

    public function delete(Fornecedor $fornecedor): void
    {
        $fornecedor->delete();
    }

    public function restore(Fornecedor $fornecedor): Fornecedor
    {
        $fornecedor->restore();

        return $fornecedor;
    }

    /**
     * Permanently delete a fornecedor. Refused when it still has produtos linked to it,
     * even soft-deleted ones, since the foreign key would otherwise reject the delete.
     */
    public function forceDelete(Fornecedor $fornecedor): void
    {
        if ($fornecedor->produtos()->withTrashed()->exists()) {
            throw ValidationException::withMessages([
                'fornecedor' => 'Não é possível excluir definitivamente um fornecedor com produtos vinculados.',
            ]);
        }

        $fornecedor->forceDelete();
    }

    public function inativar(Fornecedor $fornecedor): Fornecedor
    {
        $fornecedor->update(['status' => StatusFornecedor::Inativo]);

        return $fornecedor;
    }

    public function reativar(Fornecedor $fornecedor): Fornecedor
    {
        $fornecedor->update(['status' => StatusFornecedor::Ativo]);

        return $fornecedor;
    }

    /**
     * @return LengthAwarePaginator<int, Fornecedor>
     */
    public function list(?string $busca, ?StatusFornecedor $status, bool $excluidos): LengthAwarePaginator
    {
        return Fornecedor::query()
            ->withCount(['produtos as produtos_count' => fn ($query) => $query->withTrashed()])
            ->when($excluidos, fn ($query) => $query->onlyTrashed())
            ->when($busca, fn ($query, $busca) => $query->where('nome', 'like', "%{$busca}%"))
            ->when($status, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }

    /**
     * Fornecedores aptos a receber vínculo com um produto (ativos e não excluídos).
     *
     * @return Collection<int, Fornecedor>
     */
    public function listarElegiveis(): Collection
    {
        return Fornecedor::query()
            ->where('status', StatusFornecedor::Ativo)
            ->orderBy('nome')
            ->get(['id', 'nome']);
    }
}
