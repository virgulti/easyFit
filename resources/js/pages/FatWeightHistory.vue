<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import ProgressChart from '@/components/ProgressChart.vue';
import { dashboard, fatWeightHistory } from '@/routes';
import type { ChartSeries } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'easyfit',
                href: dashboard(),
            },
            {
                title: 'Fat weight — full history',
                href: fatWeightHistory(),
            },
        ],
    },
});

defineProps<{
    fat_weight_all: ChartSeries;
}>();
</script>

<template>
    <Head title="Fat weight — full history" />

    <div class="mx-auto flex w-full max-w-6xl flex-1 flex-col space-y-6 p-4">
        <Heading
            title="Fat weight — full history"
            description="How your fat weight (kg) has evolved across all logged measurements"
        />

        <ProgressChart
            title="Fat weight (kg) — full history"
            :series="fat_weight_all"
            light-color="#ea580c"
            dark-color="#ea580c"
            height-class="h-[70vh]"
            class="flex-1"
        />
    </div>
</template>
