<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import ProgressChart from '@/components/ProgressChart.vue';
import { dashboard, muscleWeightHistory } from '@/routes';
import type { ChartSeries } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'easyfit',
                href: dashboard(),
            },
            {
                title: 'Muscle weight — full history',
                href: muscleWeightHistory(),
            },
        ],
    },
});

defineProps<{
    muscle_weight_all: ChartSeries;
}>();
</script>

<template>
    <Head title="Muscle weight — full history" />

    <div class="mx-auto flex w-full max-w-6xl flex-1 flex-col space-y-6 p-4">
        <Heading
            title="Muscle weight — full history"
            description="How your muscle weight (kg) has evolved across all logged measurements"
        />

        <ProgressChart
            title="Muscle weight (kg) — full history"
            :series="muscle_weight_all"
            light-color="#16a34a"
            dark-color="#16a34a"
            height-class="h-[70vh]"
            class="flex-1"
        />
    </div>
</template>
