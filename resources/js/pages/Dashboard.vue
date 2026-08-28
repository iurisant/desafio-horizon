<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    CircleDollarSign,
    Package,
    Receipt,
    Trash2,
    UserCheck,
    UserX,
} from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import DashboardController from '@/actions/App/Http/Controllers/DashboardController';
import ProdutosPorDiaChart from '@/components/dashboard/ProdutosPorDiaChart.vue';
import StatusDonutChart from '@/components/dashboard/StatusDonutChart.vue';
import TopFornecedoresChart from '@/components/dashboard/TopFornecedoresChart.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Skeleton } from '@/components/ui/skeleton';
import { formatarPreco } from '@/lib/format';
import { dashboard } from '@/routes';
import type { DashboardFiltros, DashboardMetricas } from '@/types';

const props = defineProps<{
    metricas: DashboardMetricas;
    filtros: DashboardFiltros;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

const status = ref(props.filtros.status ?? 'todos');
const dataInicio = ref(props.filtros.data_inicio ?? '');
const dataFim = ref(props.filtros.data_fim ?? '');
const loading = ref(false);

function reload() {
    router.get(
        DashboardController.index.url(),
        {
            status: status.value !== 'todos' ? status.value : undefined,
            data_inicio: dataInicio.value || undefined,
            data_fim: dataFim.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['metricas', 'filtros'],
            onStart: () => {
                loading.value = true;
            },
            onFinish: () => {
                loading.value = false;
            },
        },
    );
}

watch([status, dataInicio, dataFim], reload);

const cards = computed(() => [
    {
        titulo: 'Fornecedores ativos',
        valor: props.metricas.cards.fornecedores_ativos,
        icone: UserCheck,
    },
    {
        titulo: 'Fornecedores inativos',
        valor: props.metricas.cards.fornecedores_inativos,
        icone: UserX,
    },
    {
        titulo: 'Produtos',
        valor: props.metricas.cards.produtos_total,
        icone: Package,
    },
    {
        titulo: 'Produtos excluídos',
        valor: props.metricas.cards.produtos_excluidos,
        icone: Trash2,
    },
    {
        titulo: 'Valor total em produtos',
        valor: formatarPreco(props.metricas.cards.valor_total_produtos),
        icone: CircleDollarSign,
    },
    {
        titulo: 'Preço médio dos produtos',
        valor: formatarPreco(props.metricas.cards.preco_medio_produtos),
        icone: Receipt,
    },
]);
</script>

<template>
    <Head title="Dashboard" />

    <div
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
        <div class="flex items-center justify-between gap-4">
            <h1 class="text-xl font-semibold">Dashboard</h1>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <Select v-model="status">
                <SelectTrigger class="w-full sm:w-44">
                    <SelectValue placeholder="Status" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="todos">Todos os status</SelectItem>
                    <SelectItem value="ativo">Ativo</SelectItem>
                    <SelectItem value="inativo">Inativo</SelectItem>
                </SelectContent>
            </Select>

            <div class="flex items-center gap-2">
                <label class="text-sm text-muted-foreground" for="data_inicio"
                    >De</label
                >
                <Input
                    id="data_inicio"
                    v-model="dataInicio"
                    type="date"
                    class="w-full sm:w-40"
                />
            </div>

            <div class="flex items-center gap-2">
                <label class="text-sm text-muted-foreground" for="data_fim"
                    >Até</label
                >
                <Input
                    id="data_fim"
                    v-model="dataFim"
                    type="date"
                    class="w-full sm:w-40"
                />
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Card v-for="card in cards" :key="card.titulo">
                <CardHeader
                    class="flex flex-row items-center justify-between gap-4 space-y-0"
                >
                    <CardTitle
                        class="text-sm font-medium text-muted-foreground"
                    >
                        {{ card.titulo }}
                    </CardTitle>
                    <component
                        :is="card.icone"
                        class="size-4 text-muted-foreground"
                    />
                </CardHeader>
                <CardContent>
                    <Skeleton v-if="loading" class="h-8 w-24" />
                    <div v-else class="text-2xl font-semibold">
                        {{ card.valor }}
                    </div>
                </CardContent>
            </Card>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle>Fornecedores por status</CardTitle>
                </CardHeader>
                <CardContent>
                    <Skeleton v-if="loading" class="h-56 w-full" />
                    <StatusDonutChart
                        v-else
                        :data="metricas.fornecedoresPorStatus"
                    />
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Produtos por status</CardTitle>
                </CardHeader>
                <CardContent>
                    <Skeleton v-if="loading" class="h-56 w-full" />
                    <StatusDonutChart
                        v-else
                        :data="metricas.produtosPorStatus"
                    />
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Produtos criados ao longo do tempo</CardTitle>
                </CardHeader>
                <CardContent>
                    <Skeleton v-if="loading" class="h-56 w-full" />
                    <ProdutosPorDiaChart
                        v-else
                        :data="metricas.produtosPorDia"
                    />
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Top fornecedores por produtos</CardTitle>
                </CardHeader>
                <CardContent>
                    <Skeleton v-if="loading" class="h-56 w-full" />
                    <TopFornecedoresChart
                        v-else
                        :data="metricas.topFornecedores"
                    />
                </CardContent>
            </Card>
        </div>
    </div>
</template>
