<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import MeasurementController from '@/actions/App/Http/Controllers/MeasurementController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    bmiBandBadgeClasses,
    durationToMinutes,
    formatDecimal,
    formatItalianDate,
    minutesToDuration,
} from '@/lib/measurements';
import { dashboard } from '@/routes';
import type { BmiBand, Measurement } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Riepilogo misurazione',
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
}>();

const mainValues = computed(() => [
    { label: 'Peso', value: `${formatDecimal(props.measurement.weight)} kg` },
    {
        label: 'Massa grassa',
        value: `${formatDecimal(props.measurement.fat_perc)} %`,
    },
    {
        label: 'Massa muscolare',
        value: `${formatDecimal(props.measurement.muscle_perc)} %`,
    },
]);

const derivedValues = computed(() => [
    { label: 'Progress', value: formatDecimal(props.progress) },
    { label: 'Progress BMI', value: formatDecimal(props.bmiProgress) },
    {
        label: 'Massa grassa (kg)',
        value: `${formatDecimal(props.fatWeight, 2)} kg`,
    },
    {
        label: 'Massa muscolare (kg)',
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
    <Head title="Riepilogo misurazione" />

    <div class="mx-auto w-full max-w-3xl flex-1 space-y-8 p-4">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <Heading
                title="Riepilogo misurazione"
                :description="`Valori del ${formatItalianDate(measurement.date)}`"
            />

            <div class="flex flex-wrap gap-2">
                <Button variant="outline" as-child>
                    <Link :href="dashboard()">Torna alla dashboard</Link>
                </Button>
                <Button variant="outline" as-child>
                    <Link :href="MeasurementController.edit(measurement.id)">
                        Modifica
                    </Link>
                </Button>
                <Button as-child>
                    <Link :href="MeasurementController.create()">
                        Nuova misurazione
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
                Miglioramento: il BMI è sceso rispetto alla misurazione
                precedente.
            </p>
        </div>
        <div
            v-else-if="improvement === false"
            class="rounded-xl border border-sidebar-border/70 p-4 text-sm text-muted-foreground dark:border-sidebar-border"
        >
            Nessun miglioramento del BMI rispetto alla misurazione precedente.
        </div>

        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div
                v-for="item in mainValues"
                :key="item.label"
                class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
            >
                <p class="text-sm text-muted-foreground">{{ item.label }}</p>
                <p class="mt-1 text-2xl font-semibold tracking-tight">
                    {{ item.value }}
                </p>
            </div>

            <div
                class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
            >
                <p class="text-sm text-muted-foreground">BMI</p>
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
                <p class="text-sm text-muted-foreground">{{ item.label }}</p>
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
                title="Sonno e riflessioni"
                description="Dati opzionali, modificabili direttamente da qui"
            />

            <form class="mt-4 space-y-6" @submit.prevent="submitOptional">
                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="grid content-start gap-2">
                        <Label for="bedtime">A letto alle</Label>
                        <Input
                            id="bedtime"
                            v-model="form.bedtime"
                            type="time"
                        />
                        <InputError :message="serverErrors.bedtime" />
                    </div>

                    <div class="grid content-start gap-2">
                        <Label for="sleep_duration">Durata sonno (HH:mm)</Label>
                        <Input
                            id="sleep_duration"
                            v-model="form.sleep_duration"
                            type="time"
                        />
                        <InputError :message="serverErrors.sleep_minutes" />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="notes">Riflessioni</Label>
                    <textarea
                        id="notes"
                        v-model="form.notes"
                        rows="4"
                        placeholder="Come è andata oggi?"
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
                        Salva
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
                            Salvato.
                        </p>
                    </Transition>
                </div>
            </form>
        </section>
    </div>
</template>
