<script setup lang="ts">
import type { ChartOptions, TooltipItem } from 'chart.js';
import {
    CategoryScale,
    Chart as ChartJS,
    Filler,
    LinearScale,
    LineElement,
    PointElement,
    Tooltip,
} from 'chart.js';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Line } from 'vue-chartjs';
import { formatDecimal, formatDate } from '@/lib/measurements';
import type { ChartSeries } from '@/types';

// chart.js v4 is tree-shakeable: without explicit registration the canvas
// stays empty.
ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Tooltip,
    Filler,
);

const props = withDefaults(
    defineProps<{
        title: string;
        series: ChartSeries;
        lightColor?: string;
        darkColor?: string;
        heightClass?: string;
        goalValue?: number | null;
    }>(),
    {
        lightColor: '#2563eb',
        darkColor: '#3b82f6',
        heightClass: 'h-56',
        goalValue: null,
    },
);

const isDark = ref(
    typeof document !== 'undefined' &&
        document.documentElement.classList.contains('dark'),
);

let observer: MutationObserver | null = null;

onMounted(() => {
    observer = new MutationObserver(() => {
        isDark.value = document.documentElement.classList.contains('dark');
    });
    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class'],
    });
});

onUnmounted(() => observer?.disconnect());

const color = computed(() =>
    isDark.value ? props.darkColor : props.lightColor,
);

function withAlpha(hex: string, alpha: number): string {
    const red = Number.parseInt(hex.slice(1, 3), 16);
    const green = Number.parseInt(hex.slice(3, 5), 16);
    const blue = Number.parseInt(hex.slice(5, 7), 16);

    return `rgba(${red}, ${green}, ${blue}, ${alpha})`;
}

/**
 * Short tick label for an ISO date ("2026-07-02" -> "2 Jul").
 */
function shortLabel(isoDate: string): string {
    return new Intl.DateTimeFormat('en-GB', {
        day: 'numeric',
        month: 'short',
        timeZone: 'UTC',
    }).format(new Date(isoDate));
}

const chartData = computed(() => ({
    labels: props.series.labels.map(shortLabel),
    datasets: [
        {
            label: props.title,
            data: props.series.values,
            borderColor: color.value,
            backgroundColor: withAlpha(color.value, 0.12),
            pointBackgroundColor: color.value,
            borderWidth: 2,
            pointRadius: 2,
            pointHoverRadius: 5,
            pointHitRadius: 12,
            tension: 0.3,
            fill: true,
        },
        ...(props.goalValue !== null
            ? [
                  {
                      label: 'Goal',
                      data: props.series.labels.map(() => props.goalValue),
                      borderColor: isDark.value ? '#f59e0b' : '#b45309',
                      borderWidth: 1.5,
                      borderDash: [6, 6],
                      pointRadius: 0,
                      pointHitRadius: 0,
                      fill: false,
                      tension: 0,
                  },
              ]
            : []),
    ],
}));

const chartOptions = computed<ChartOptions<'line'>>(() => {
    const tickColor = isDark.value ? '#9ca3af' : '#6b7280';
    const gridColor = isDark.value
        ? 'rgba(255, 255, 255, 0.08)'
        : 'rgba(0, 0, 0, 0.06)';

    return {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            tooltip: {
                displayColors: false,
                callbacks: {
                    title: (items: TooltipItem<'line'>[]) =>
                        formatDate(
                            props.series.labels[items[0].dataIndex],
                        ),
                    label: (item: TooltipItem<'line'>) => {
                        if (item.parsed.y === null) {
                            return '';
                        }

                        const value = formatDecimal(item.parsed.y);

                        return chartData.value.datasets.length > 1
                            ? `${item.dataset.label}: ${value}`
                            : value;
                    },
                },
            },
        },
        scales: {
            x: {
                ticks: {
                    color: tickColor,
                    maxTicksLimit: 8,
                    maxRotation: 0,
                },
                grid: { display: false },
            },
            y: {
                ticks: {
                    color: tickColor,
                    callback: (value) => formatDecimal(Number(value)),
                },
                grid: { color: gridColor },
                border: { display: false },
            },
        },
    };
});
</script>

<template>
    <section
        class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
    >
        <h2 class="text-sm font-medium text-muted-foreground">{{ title }}</h2>

        <div
            v-if="series.values.length === 0"
            :class="heightClass"
            class="flex items-center justify-center px-4 text-center text-sm text-muted-foreground"
        >
            No data to show yet: log a measurement to see the chart.
        </div>
        <div v-else :class="heightClass" class="mt-2">
            <Line :data="chartData" :options="chartOptions" />
        </div>
    </section>
</template>
