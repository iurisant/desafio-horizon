import { computed } from 'vue';
import { useAppearance } from '@/composables/useAppearance';

const CHART_VARS = [
    '--chart-1',
    '--chart-2',
    '--chart-3',
    '--chart-4',
    '--chart-5',
] as const;

/**
 * Lê as cores de gráfico definidas em resources/css/app.css (--chart-1..5) direto do
 * DOM, já que o Canvas 2D do Chart.js não entende var(). Reavalia quando o tema muda,
 * pois os valores das variáveis diferem entre claro e escuro.
 */
export function useChartColors() {
    const { resolvedAppearance } = useAppearance();

    const palette = computed<string[]>(() => {
        // Depender do tema resolvido força a releitura das CSS vars após a troca.
        void resolvedAppearance.value;

        if (typeof window === 'undefined') {
            return [];
        }

        const styles = getComputedStyle(document.documentElement);

        return CHART_VARS.map((variavel) =>
            styles.getPropertyValue(variavel).trim(),
        );
    });

    const mutedForeground = computed<string>(() => {
        void resolvedAppearance.value;

        if (typeof window === 'undefined') {
            return '#71717a';
        }

        return getComputedStyle(document.documentElement)
            .getPropertyValue('--muted-foreground')
            .trim();
    });

    return { palette, mutedForeground };
}
