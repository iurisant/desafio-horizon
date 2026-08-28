<script setup lang="ts">
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';
import '@/lib/chart';
import { useChartColors } from '@/components/dashboard/useChartColors';
import type { ProdutoPorDia } from '@/types';

const props = defineProps<{
    data: ProdutoPorDia[];
}>();

const { palette, mutedForeground } = useChartColors();

function formatarDia(dia: string): string {
    return new Date(`${dia}T00:00:00`).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
    });
}

const chartData = computed(() => ({
    labels: props.data.map((item) => formatarDia(item.dia)),
    datasets: [
        {
            label: 'Produtos criados',
            data: props.data.map((item) => item.total),
            backgroundColor: palette.value[0],
            borderRadius: 4,
            maxBarThickness: 48,
        },
    ],
}));

const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
    },
    scales: {
        x: {
            ticks: { color: mutedForeground.value },
            grid: { display: false },
        },
        y: {
            ticks: { color: mutedForeground.value, precision: 0 },
            beginAtZero: true,
        },
    },
}));
</script>

<template>
    <div
        v-if="data.length === 0"
        class="py-6 text-center text-muted-foreground"
    >
        Nenhum dado no período selecionado.
    </div>
    <div v-else class="h-56">
        <Bar :data="chartData" :options="chartOptions" />
    </div>
</template>
