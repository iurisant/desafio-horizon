<?php

namespace App\Services;

use App\Models\Fornecedor;

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
}
