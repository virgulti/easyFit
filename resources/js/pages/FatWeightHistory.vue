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
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Massa grassa — storico completo',
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
    <Head title="Massa grassa — storico completo" />

    <div class="mx-auto flex w-full max-w-6xl flex-1 flex-col space-y-6 p-4">
        <Heading
            title="Massa grassa — storico completo"
            description="L'andamento della tua massa grassa (kg) su tutte le misurazioni registrate"
        />

        <ProgressChart
            title="Massa grassa (kg) — storico completo"
            :series="fat_weight_all"
            light-color="#ea580c"
            dark-color="#ea580c"
            height-class="h-[70vh]"
            class="flex-1"
        />
    </div>
</template>
