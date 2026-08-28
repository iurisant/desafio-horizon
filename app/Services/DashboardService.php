<?php

namespace App\Services;

use App\Enums\StatusFornecedor;
use App\Enums\StatusProduto;
use App\Models\Fornecedor;
use App\Models\Produto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class DashboardService
{
    /**
     * Sumariza fornecedores e produtos em cards e séries para os gráficos do dashboard.
     *
     * O período (data_inicio/data_fim sobre created_at) filtra tanto fornecedores quanto
     * produtos. O status filtra apenas métricas de produto: aplicá-lo às contagens de
     * fornecedores por status reduziria os donuts de distribuição a uma única fatia.
     *
     * @return array<string, mixed>
     */
    public function metricas(?StatusProduto $status, ?Carbon $dataInicio, ?Carbon $dataFim): array
    {
        return [
            'cards' => [
                ...$this->contagemFornecedoresPorStatus($dataInicio, $dataFim),
                ...$this->resumoProdutos($status, $dataInicio, $dataFim),
                'produtos_excluidos' => $this->produtosExcluidos($dataInicio, $dataFim),
            ],
            'fornecedoresPorStatus' => $this->distribuicaoPorStatus(Fornecedor::query(), $dataInicio, $dataFim),
            'produtosPorStatus' => $this->distribuicaoPorStatus(Produto::query(), $dataInicio, $dataFim),
            'produtosPorDia' => $this->produtosPorDia($status, $dataInicio, $dataFim),
            'topFornecedores' => $this->topFornecedores($status, $dataInicio, $dataFim),
        ];
    }

    /**
     * @return array{fornecedores_ativos: int, fornecedores_inativos: int}
     */
    private function contagemFornecedoresPorStatus(?Carbon $dataInicio, ?Carbon $dataFim): array
    {
        $linha = Fornecedor::query()
            ->when($dataInicio, fn ($query, $data) => $query->where('created_at', '>=', $data))
            ->when($dataFim, fn ($query, $data) => $query->where('created_at', '<=', $data))
            ->selectRaw("SUM(CASE WHEN status = 'ativo' THEN 1 ELSE 0 END) as ativos")
            ->selectRaw("SUM(CASE WHEN status = 'inativo' THEN 1 ELSE 0 END) as inativos")
            ->first();

        return [
            'fornecedores_ativos' => (int) $linha->ativos,
            'fornecedores_inativos' => (int) $linha->inativos,
        ];
    }

    /**
     * @return array{produtos_total: int, valor_total_produtos: string, preco_medio_produtos: string}
     */
    private function resumoProdutos(?StatusProduto $status, ?Carbon $dataInicio, ?Carbon $dataFim): array
    {
        $linha = Produto::query()
            ->when($status, fn ($query, $status) => $query->where('status', $status))
            ->when($dataInicio, fn ($query, $data) => $query->where('created_at', '>=', $data))
            ->when($dataFim, fn ($query, $data) => $query->where('created_at', '<=', $data))
            ->selectRaw('COUNT(*) as total, COALESCE(SUM(preco), 0) as valor_total, COALESCE(AVG(preco), 0) as preco_medio')
            ->first();

        return [
            'produtos_total' => (int) $linha->total,
            'valor_total_produtos' => number_format((float) $linha->valor_total, 2, '.', ''),
            'preco_medio_produtos' => number_format((float) $linha->preco_medio, 2, '.', ''),
        ];
    }

    private function produtosExcluidos(?Carbon $dataInicio, ?Carbon $dataFim): int
    {
        return Produto::onlyTrashed()
            ->when($dataInicio, fn ($query, $data) => $query->where('created_at', '>=', $data))
            ->when($dataFim, fn ($query, $data) => $query->where('created_at', '<=', $data))
            ->count();
    }

    /**
     * @param  Builder<Fornecedor>|Builder<Produto>  $query
     * @return list<array{status: string, total: int}>
     */
    private function distribuicaoPorStatus($query, ?Carbon $dataInicio, ?Carbon $dataFim): array
    {
        $contagens = $query
            ->when($dataInicio, fn ($query, $data) => $query->where('created_at', '>=', $data))
            ->when($dataFim, fn ($query, $data) => $query->where('created_at', '<=', $data))
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return collect([StatusFornecedor::Ativo->value, StatusFornecedor::Inativo->value])
            ->map(fn ($status) => [
                'status' => $status,
                'total' => (int) ($contagens[$status] ?? 0),
            ])
            ->all();
    }

    /**
     * @return list<array{dia: string, total: int}>
     */
    private function produtosPorDia(?StatusProduto $status, ?Carbon $dataInicio, ?Carbon $dataFim): array
    {
        return Produto::query()
            ->when($status, fn ($query, $status) => $query->where('status', $status))
            ->when($dataInicio, fn ($query, $data) => $query->where('created_at', '>=', $data))
            ->when($dataFim, fn ($query, $data) => $query->where('created_at', '<=', $data))
            ->selectRaw('DATE(created_at) as dia, count(*) as total')
            ->groupBy('dia')
            ->orderBy('dia')
            ->get()
            ->map(fn ($linha) => ['dia' => $linha->dia, 'total' => (int) $linha->total])
            ->all();
    }

    /**
     * @return list<array{id: int, nome: string, produtos_count: int}>
     */
    private function topFornecedores(?StatusProduto $status, ?Carbon $dataInicio, ?Carbon $dataFim): array
    {
        return Fornecedor::query()
            ->withCount(['produtos' => function ($query) use ($status, $dataInicio, $dataFim) {
                $query
                    ->when($status, fn ($query, $status) => $query->where('status', $status))
                    ->when($dataInicio, fn ($query, $data) => $query->where('created_at', '>=', $data))
                    ->when($dataFim, fn ($query, $data) => $query->where('created_at', '<=', $data));
            }])
            ->orderByDesc('produtos_count')
            ->limit(5)
            ->get(['id', 'nome'])
            ->map(fn ($fornecedor) => [
                'id' => $fornecedor->id,
                'nome' => $fornecedor->nome,
                'produtos_count' => $fornecedor->produtos_count,
            ])
            ->all();
    }
}
