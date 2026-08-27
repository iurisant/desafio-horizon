<?php

namespace App\Services;

use App\Models\Produto;

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
}
