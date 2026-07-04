<script setup lang="ts">
import { Deferred, Head, Link } from '@inertiajs/vue3';
import MeasurementController from '@/actions/App/Http/Controllers/MeasurementController';
import ChartSkeleton from '@/components/ChartSkeleton.vue';
import Heading from '@/components/Heading.vue';
import ProgressChart from '@/components/ProgressChart.vue';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import type { ChartSeries } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'easyfit',
                href: dashboard(),
            },
        ],
    },
});

defineProps<{
    progress_last_5_days: ChartSeries;
    progress_last_5_weeks: ChartSeries;
    progress_last_6_months: ChartSeries;
    progress_all?: ChartSeries;
    weight_all?: ChartSeries;
    fat_weight_all?: ChartSeries;
    muscle_weight_all?: ChartSeries;
    bmi_progress_all?: ChartSeries;
}>();

/**
 * Per-metric line colors (light/dark), validated for lightness, chroma and
 * contrast against the app surfaces in both modes.
 */
const seriesColors = {
    progress: { light: '#2563eb', dark: '#3b82f6' },
    weight: { light: '#a21caf', dark: '#c026d3' },
    fatWeight: { light: '#ea580c', dark: '#ea580c' },
    muscleWeight: { light: '#16a34a', dark: '#16a34a' },
    bmiProgress: { light: '#0891b2', dark: '#0891b2' },
} as const;
</script>

<template>
    <Head title="Dashboard" />

    <div class="mx-auto w-full max-w-6xl flex-1 space-y-6 p-4">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <Heading
                title="Dashboard"
                description="Your progress, day by day"
            />

            <div class="flex flex-wrap gap-2">
                <Button
                    size="lg"
                    class="text-base font-semibold"
                    as-child
                    data-test="new-measurement-button"
                >
                    <Link :href="MeasurementController.create()">
                        New measurement
                    </Link>
                </Button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <ProgressChart
                title="Progress — last 5 days"
                :series="progress_last_5_days"
                :light-color="seriesColors.progress.light"
                :dark-color="seriesColors.progress.dark"
            />

            <ProgressChart
                title="Progress — last 5 weeks (weekly average)"
                :series="progress_last_5_weeks"
                :light-color="seriesColors.progress.light"
                :dark-color="seriesColors.progress.dark"
            />

            <ProgressChart
                title="Progress — last 6 months (weekly average)"
                :series="progress_last_6_months"
                :light-color="seriesColors.progress.light"
                :dark-color="seriesColors.progress.dark"
            />

            <Deferred data="progress_all">
                <template #fallback>
                    <ChartSkeleton />
                </template>

                <ProgressChart
                    title="Progress — full history"
                    :series="progress_all!"
                    :light-color="seriesColors.progress.light"
                    :dark-color="seriesColors.progress.dark"
                />
            </Deferred>

            <Deferred data="weight_all">
                <template #fallback>
                    <ChartSkeleton />
                </template>

                <ProgressChart
                    title="Weight (kg) — full history"
                    :series="weight_all!"
                    :light-color="seriesColors.weight.light"
                    :dark-color="seriesColors.weight.dark"
                />
            </Deferred>

            <Deferred data="fat_weight_all">
                <template #fallback>
                    <ChartSkeleton />
                </template>

                <ProgressChart
                    title="Fat weight (kg) — full history"
                    :series="fat_weight_all!"
                    :light-color="seriesColors.fatWeight.light"
                    :dark-color="seriesColors.fatWeight.dark"
                />
            </Deferred>

            <Deferred data="muscle_weight_all">
                <template #fallback>
                    <ChartSkeleton />
                </template>

                <ProgressChart
                    title="Muscle weight (kg) — full history"
                    :series="muscle_weight_all!"
                    :light-color="seriesColors.muscleWeight.light"
                    :dark-color="seriesColors.muscleWeight.dark"
                />
            </Deferred>

            <Deferred data="bmi_progress_all">
                <template #fallback>
                    <ChartSkeleton />
                </template>

                <ProgressChart
                    title="BMI progress — full history"
                    :series="bmi_progress_all!"
                    :light-color="seriesColors.bmiProgress.light"
                    :dark-color="seriesColors.bmiProgress.dark"
                />
            </Deferred>
        </div>
    </div>
</template>
