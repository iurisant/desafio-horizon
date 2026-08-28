<script setup lang="ts">
import { computed } from 'vue';
import { Doughnut } from 'vue-chartjs';
import '@/lib/chart';
import { useChartColors } from '@/components/dashboard/useChartColors';
import type { ContagemPorStatus } from '@/types';

const props = defineProps<{
    data: ContagemPorStatus[];
}>();

const { palette, mutedForeground } = useChartColors();

const total = computed(() =>
    props.data.reduce((soma, item) => soma + item.total, 0),
);

const rotulos: Record<ContagemPorStatus['status'], string> = {
    ativo: 'Ativos',
    inativo: 'Inativos',
};

const chartData = computed(() => ({
    labels: props.data.map((item) => rotulos[item.status]),
    datasets: [
        {
            data: props.data.map((item) => item.total),
            backgroundColor: [palette.value[0], palette.value[1]],
            borderWidth: 0,
        },
    ],
}));

const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom' as const,
            labels: {
                color: mutedForeground.value,
            },
        },
    },
}));
</script>

<template>
    <div v-if="total === 0" class="py-6 text-center text-muted-foreground">
        Nenhum dado no período selecionado.
    </div>
    <div v-else class="h-56">
        <Doughnut :data="chartData" :options="chartOptions" />
    </div>
</template>
