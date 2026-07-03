<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import ProgressChart from '@/components/ProgressChart.vue';
import { dashboard, progressHistory } from '@/routes';
import type { ChartSeries } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Progress — storico completo',
                href: progressHistory(),
            },
        ],
    },
});

defineProps<{
    progress_all: ChartSeries;
}>();
</script>

<template>
    <Head title="Progress — storico completo" />

    <div class="mx-auto flex w-full max-w-6xl flex-1 flex-col space-y-6 p-4">
        <Heading
            title="Progress — storico completo"
            description="L'andamento del tuo progress su tutte le misurazioni registrate"
        />

        <ProgressChart
            title="Progress — storico completo"
            :series="progress_all"
            light-color="#2563eb"
            dark-color="#3b82f6"
            height-class="h-[70vh]"
            class="flex-1"
        />
    </div>
</template>
