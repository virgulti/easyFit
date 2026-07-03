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
                title: 'Progress — full history',
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
    <Head title="Progress — full history" />

    <div class="mx-auto flex w-full max-w-6xl flex-1 flex-col space-y-6 p-4">
        <Heading
            title="Progress — full history"
            description="How your progress has evolved across all logged measurements"
        />

        <ProgressChart
            title="Progress — full history"
            :series="progress_all"
            light-color="#2563eb"
            dark-color="#3b82f6"
            height-class="h-[70vh]"
            class="flex-1"
        />
    </div>
</template>
