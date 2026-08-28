<?php

namespace App\Services;

use App\Models\Fornecedor;
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
}
