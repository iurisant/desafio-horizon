<?php

namespace App\Http\Controllers;

use App\Enums\StatusProduto;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Sumariza fornecedores e produtos em cards e gráficos, filtráveis por status e período.
     */
    public function index(Request $request, DashboardService $service): Response
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::in(['ativo', 'inativo'])],
            'data_inicio' => ['nullable', 'date_format:Y-m-d'],
            'data_fim' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:data_inicio'],
        ]);

        $status = isset($validated['status']) ? StatusProduto::from($validated['status']) : null;
        $dataInicio = isset($validated['data_inicio']) ? Carbon::parse($validated['data_inicio'])->startOfDay() : null;
        $dataFim = isset($validated['data_fim']) ? Carbon::parse($validated['data_fim'])->endOfDay() : null;

        return Inertia::render('Dashboard', [
            'metricas' => $service->metricas($status, $dataInicio, $dataFim),
            'filtros' => [
                'status' => $status?->value,
                'data_inicio' => $validated['data_inicio'] ?? null,
                'data_fim' => $validated['data_fim'] ?? null,
            ],
        ]);
    }
}
