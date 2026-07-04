<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Heading from '@/components/Heading.vue';
import ProgressChart from '@/components/ProgressChart.vue';
import { dashboard, progressHistory } from '@/routes';
import type { ChartSeries } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'easyfit',
                href: dashboard(),
            },
            {
                title: 'Progress — full history',
                href: progressHistory(),
            },
        ],
    },
});

const props = defineProps<{
    progress_last_5_days: ChartSeries;
    progress_last_5_weeks: ChartSeries;
    progress_last_6_months: ChartSeries;
    progress_last_1_year: ChartSeries;
    progress_all: ChartSeries;
}>();

const windows = [
    { value: 'last_5_days', label: '5 days' },
    { value: 'last_5_weeks', label: '5 weeks' },
    { value: 'last_6_months', label: '6 months' },
    { value: 'last_1_year', label: '1 year' },
    { value: 'all', label: 'All' },
] as const;

type Window = (typeof windows)[number]['value'];

const activeWindow = ref<Window>('all');

const seriesByWindow = computed<Record<Window, ChartSeries>>(() => ({
    last_5_days: props.progress_last_5_days,
    last_5_weeks: props.progress_last_5_weeks,
    last_6_months: props.progress_last_6_months,
    last_1_year: props.progress_last_1_year,
    all: props.progress_all,
}));

const activeSeries = computed(() => seriesByWindow.value[activeWindow.value]);

const activeTitle = computed(() => {
    const label = windows.find(
        (window) => window.value === activeWindow.value,
    )?.label;

    return `Progress — ${label}`;
});
</script>

<template>
    <Head title="Progress — full history" />

    <div class="mx-auto flex w-full max-w-6xl flex-1 flex-col space-y-6 p-4">
        <Heading
            title="Progress — full history"
            description="How your progress has evolved across all logged measurements"
        />

        <div
            class="inline-flex flex-wrap gap-1 self-start rounded-lg bg-neutral-100 p-1 dark:bg-neutral-800"
        >
            <button
                v-for="window in windows"
                :key="window.value"
                type="button"
                data-test="progress-history-window"
                @click="activeWindow = window.value"
                :class="[
                    'rounded-md px-3.5 py-1.5 text-sm transition-colors',
                    activeWindow === window.value
                        ? 'bg-white shadow-xs dark:bg-neutral-700 dark:text-neutral-100'
                        : 'text-neutral-500 hover:bg-neutral-200/60 hover:text-black dark:text-neutral-400 dark:hover:bg-neutral-700/60',
                ]"
            >
                {{ window.label }}
            </button>
        </div>

        <ProgressChart
            :title="activeTitle"
            :series="activeSeries"
            light-color="#2563eb"
            dark-color="#3b82f6"
            height-class="h-[70vh]"
            class="flex-1"
        />
    </div>
</template>
