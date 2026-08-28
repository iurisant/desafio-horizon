<script setup lang="ts">
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';
import '@/lib/chart';
import { useChartColors } from '@/components/dashboard/useChartColors';
import type { TopFornecedor } from '@/types';

const props = defineProps<{
    data: TopFornecedor[];
}>();

const { palette, mutedForeground } = useChartColors();

const chartData = computed(() => ({
    labels: props.data.map((item) => item.nome),
    datasets: [
        {
            label: 'Produtos vinculados',
            data: props.data.map((item) => item.produtos_count),
            backgroundColor: palette.value[0],
            borderRadius: 4,
        },
    ],
}));

const chartOptions = computed(() => ({
    indexAxis: 'y' as const,
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
    },
    scales: {
        x: {
            ticks: { color: mutedForeground.value, precision: 0 },
            beginAtZero: true,
        },
        y: {
            ticks: { color: mutedForeground.value },
            grid: { display: false },
        },
    },
}));

const semDados = computed(() =>
    props.data.every((item) => item.produtos_count === 0),
);
</script>

<template>
    <div
        v-if="data.length === 0 || semDados"
        class="py-6 text-center text-muted-foreground"
    >
        Nenhum dado no período selecionado.
    </div>
    <div v-else class="h-56">
        <Bar :data="chartData" :options="chartOptions" />
    </div>
</template>
