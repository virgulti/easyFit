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
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Peso — storico completo',
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
    <Head title="Peso — storico completo" />

    <div class="mx-auto flex w-full max-w-6xl flex-1 flex-col space-y-6 p-4">
        <Heading
            title="Peso — storico completo"
            description="L'andamento del tuo peso (kg) su tutte le misurazioni registrate"
        />

        <ProgressChart
            title="Peso (kg) — storico completo"
            :series="weight_all"
            light-color="#a21caf"
            dark-color="#c026d3"
            height-class="h-[70vh]"
            class="flex-1"
        />
    </div>
</template>
