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
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Massa muscolare — storico completo',
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
    <Head title="Massa muscolare — storico completo" />

    <div class="mx-auto flex w-full max-w-6xl flex-1 flex-col space-y-6 p-4">
        <Heading
            title="Massa muscolare — storico completo"
            description="L'andamento della tua massa muscolare (kg) su tutte le misurazioni registrate"
        />

        <ProgressChart
            title="Massa muscolare (kg) — storico completo"
            :series="muscle_weight_all"
            light-color="#16a34a"
            dark-color="#16a34a"
            height-class="h-[70vh]"
            class="flex-1"
        />
    </div>
</template>
