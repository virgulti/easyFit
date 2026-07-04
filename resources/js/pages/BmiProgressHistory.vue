<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import ProgressChart from '@/components/ProgressChart.vue';
import { bmiProgressHistory, dashboard } from '@/routes';
import type { ChartSeries } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'easyfit',
                href: dashboard(),
            },
            {
                title: 'BMI progress — full history',
                href: bmiProgressHistory(),
            },
        ],
    },
});

defineProps<{
    bmi_progress_all: ChartSeries;
}>();
</script>

<template>
    <Head title="BMI progress — full history" />

    <div class="mx-auto flex w-full max-w-6xl flex-1 flex-col space-y-6 p-4">
        <Heading
            title="BMI progress — full history"
            description="How your BMI progress has evolved across all logged measurements"
        />

        <ProgressChart
            title="BMI progress — full history"
            :series="bmi_progress_all"
            light-color="#0891b2"
            dark-color="#0891b2"
            height-class="h-[70vh]"
            class="flex-1"
        />
    </div>
</template>
