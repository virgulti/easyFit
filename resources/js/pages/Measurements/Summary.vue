<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import MeasurementController from '@/actions/App/Http/Controllers/MeasurementController';
import DurationDigitsInput from '@/components/DurationDigitsInput.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import TrendIndicator from '@/components/TrendIndicator.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    bmiBandBadgeClasses,
    durationToMinutes,
    formatDecimal,
    formatDate,
    minutesToDuration,
} from '@/lib/measurements';
import { dashboard } from '@/routes';
import type { BmiBand, Measurement, Trend } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'EasyFit',
                href: dashboard(),
            },
            {
                title: 'Measurement summary',
                href: '#',
            },
        ],
    },
});

const props = defineProps<{
    measurement: Measurement;
    progress: number;
    bmiProgress: number;
    fatWeight: number;
    muscleWeight: number;
    improvement: boolean | null;
    bmiBand: BmiBand;
    trends: Record<string, Trend> | null;
}>();

function trendOf(key: string): Trend {
    return props.trends?.[key] ?? null;
}

const mainValues = computed(() => [
    {
        key: 'weight',
        label: 'Weight',
        value: `${formatDecimal(props.measurement.weight)} kg`,
    },
    {
        key: 'fat_perc',
        label: 'Body fat',
        value: `${formatDecimal(props.measurement.fat_perc)} %`,
    },
    {
        key: 'muscle_perc',
        label: 'Muscle mass',
        value: `${formatDecimal(props.measurement.muscle_perc)} %`,
    },
]);

const derivedValues = computed(() => [
    {
        key: 'progress',
        label: 'Progress',
        value: formatDecimal(props.progress),
    },
    {
        key: 'bmi_progress',
        label: 'BMI progress',
        value: formatDecimal(props.bmiProgress),
    },
    {
        key: 'fat_weight',
        label: 'Fat weight (kg)',
        value: `${formatDecimal(props.fatWeight, 2)} kg`,
    },
    {
        key: 'muscle_weight',
        label: 'Muscle weight (kg)',
        value: `${formatDecimal(props.muscleWeight, 2)} kg`,
    },
]);

const form = useForm({
    bedtime: props.measurement.bedtime
        ? props.measurement.bedtime.slice(0, 5)
        : '',
    sleep_duration: minutesToDuration(props.measurement.sleep_minutes),
    notes: props.measurement.notes ?? '',
});

const serverErrors = computed(
    () => form.errors as Partial<Record<string, string>>,
);

function submitOptional(): void {
    form.transform((data) => ({
        bedtime: data.bedtime === '' ? null : data.bedtime,
        sleep_minutes:
            data.sleep_duration === ''
                ? null
                : durationToMinutes(data.sleep_duration),
        notes: data.notes === '' ? null : data.notes,
    }));

    form.submit(MeasurementController.update(props.measurement.id), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Measurement summary" />

    <div class="mx-auto w-full max-w-3xl flex-1 space-y-8 p-4">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <Heading
                title="Measurement summary"
                :description="`Values from ${formatDate(measurement.date)}`"
            />

            <div class="flex flex-wrap gap-2">
                <Button variant="outline" as-child>
                    <Link :href="dashboard()">Back to dashboard</Link>
                </Button>
                <Button variant="outline" as-child>
                    <Link :href="MeasurementController.edit(measurement.id)">
                        Edit
                    </Link>
                </Button>
                <Button as-child>
                    <Link :href="MeasurementController.create()">
                        New measurement
                    </Link>
                </Button>
            </div>
        </div>

        <div
            v-if="improvement === true"
            data-test="improvement-marker"
            class="flex items-center gap-3 rounded-xl border border-green-300 bg-green-50 p-4 text-green-800 dark:border-green-800 dark:bg-green-950/40 dark:text-green-300"
        >
            <span class="text-xl" aria-hidden="true">&#9650;</span>
            <p class="text-sm font-medium">
                Improvement: BMI is down compared to the previous
                measurement.
            </p>
        </div>
        <div
            v-else-if="improvement === false"
            class="rounded-xl border border-sidebar-border/70 p-4 text-sm text-muted-foreground dark:border-sidebar-border"
        >
            No BMI improvement compared to the previous measurement.
        </div>

        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div
                v-for="item in mainValues"
                :key="item.label"
                class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
            >
                <p
                    class="flex items-center gap-1 text-sm text-muted-foreground"
                >
                    {{ item.label }}
                    <TrendIndicator :trend="trendOf(item.key)" />
                </p>
                <p class="mt-1 text-2xl font-semibold tracking-tight">
                    {{ item.value }}
                </p>
            </div>

            <div
                class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
            >
                <p
                    class="flex items-center gap-1 text-sm text-muted-foreground"
                >
                    BMI
                    <TrendIndicator :trend="trendOf('bmi_value')" />
                </p>
                <div class="mt-1 flex items-center gap-2">
                    <p class="text-2xl font-semibold tracking-tight">
                        {{ formatDecimal(measurement.bmi_value) }}
                    </p>
                    <Badge
                        data-test="bmi-band-badge"
                        class="border-transparent"
                        :class="bmiBandBadgeClasses[bmiBand.color]"
                    >
                        {{ bmiBand.label }}
                    </Badge>
                </div>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div
                v-for="item in derivedValues"
                :key="item.label"
                class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
            >
                <p
                    class="flex items-center gap-1 text-sm text-muted-foreground"
                >
                    {{ item.label }}
                    <TrendIndicator :trend="trendOf(item.key)" />
                </p>
                <p class="mt-1 text-2xl font-semibold tracking-tight">
                    {{ item.value }}
                </p>
            </div>
        </section>

        <section
            class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
        >
            <Heading
                variant="small"
                title="Sleep and reflections"
                description="Optional data, editable right from here"
            />

            <form class="mt-4 space-y-6" @submit.prevent="submitOptional">
                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="grid content-start gap-2">
                        <Label for="bedtime" class="flex items-center gap-1">
                            Bedtime
                            <TrendIndicator :trend="trendOf('bedtime')" />
                        </Label>
                        <Input
                            id="bedtime"
                            v-model="form.bedtime"
                            type="time"
                        />
                        <InputError :message="serverErrors.bedtime" />
                    </div>

                    <div class="grid content-start gap-2">
                        <Label
                            for="sleep_duration"
                            class="flex items-center gap-1"
                        >
                            Sleep duration
                            <TrendIndicator :trend="trendOf('sleep_minutes')" />
                        </Label>
                        <DurationDigitsInput
                            id="sleep_duration"
                            v-model="form.sleep_duration"
                        />
                        <InputError :message="serverErrors.sleep_minutes" />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="notes">Reflections</Label>
                    <textarea
                        id="notes"
                        v-model="form.notes"
                        rows="4"
                        placeholder="How did today go?"
                        class="w-full min-w-0 rounded-md border border-input bg-transparent px-3 py-2 text-base shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm dark:bg-input/30"
                    ></textarea>
                    <InputError :message="serverErrors.notes" />
                </div>

                <div class="flex items-center gap-4">
                    <Button
                        type="submit"
                        :disabled="form.processing"
                        data-test="save-optional-button"
                    >
                        Save
                    </Button>
                    <Transition
                        enter-active-class="transition ease-in-out"
                        enter-from-class="opacity-0"
                        leave-active-class="transition ease-in-out"
                        leave-to-class="opacity-0"
                    >
                        <p
                            v-if="form.recentlySuccessful"
                            class="text-sm text-muted-foreground"
                        >
                            Saved.
                        </p>
                    </Transition>
                </div>
            </form>
        </section>
    </div>
</template>
