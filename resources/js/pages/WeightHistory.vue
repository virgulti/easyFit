<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import ProgressChart from '@/components/ProgressChart.vue';
import { dashboard, weightHistory } from '@/routes';
import type { ChartSeries } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'easyfit',
                href: dashboard(),
            },
            {
                title: 'Weight — full history',
                href: weightHistory(),
            },
        ],
    },
});

defineProps<{
    weight_all: ChartSeries;
}>();
</script>

<template>
    <Head title="Weight — full history" />

    <div class="mx-auto flex w-full max-w-6xl flex-1 flex-col space-y-6 p-4">
        <Heading
            title="Weight — full history"
            description="How your weight (kg) has evolved across all logged measurements"
        />

        <ProgressChart
            title="Weight (kg) — full history"
            :series="weight_all"
            light-color="#a21caf"
            dark-color="#c026d3"
            height-class="h-[70vh]"
            class="flex-1"
        />
    </div>
</template>
