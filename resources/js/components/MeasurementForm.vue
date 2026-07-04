<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import type { ComponentPublicInstance } from 'vue';
import { computed, ref } from 'vue';
import MeasurementController from '@/actions/App/Http/Controllers/MeasurementController';
import DateDigitsInput from '@/components/DateDigitsInput.vue';
import DecimalDigitsInput from '@/components/DecimalDigitsInput.vue';
import DurationDigitsInput from '@/components/DurationDigitsInput.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    durationToMinutes,
    minutesToDuration,
    todayIsoDate,
} from '@/lib/measurements';
import type { Measurement } from '@/types';

const props = defineProps<{
    measurement?: Measurement | null;
    submitLabel: string;
}>();

const form = useForm({
    date: props.measurement
        ? props.measurement.date.slice(0, 10)
        : todayIsoDate(),
    weight: props.measurement
        ? Number.parseFloat(props.measurement.weight)
        : null,
    fat_perc: props.measurement
        ? Number.parseFloat(props.measurement.fat_perc)
        : null,
    muscle_perc: props.measurement
        ? Number.parseFloat(props.measurement.muscle_perc)
        : null,
    bmi_value: props.measurement
        ? Number.parseFloat(props.measurement.bmi_value)
        : null,
    bedtime: props.measurement?.bedtime
        ? props.measurement.bedtime.slice(0, 5)
        : '',
    sleep_duration: minutesToDuration(props.measurement?.sleep_minutes ?? null),
});

const serverErrors = computed(
    () => form.errors as Partial<Record<string, string>>,
);

type DecimalInput = InstanceType<typeof DecimalDigitsInput>;

const fatInput = ref<DecimalInput | null>(null);
const muscleInput = ref<DecimalInput | null>(null);
const bmiInput = ref<DecimalInput | null>(null);
const bedtimeInput = ref<ComponentPublicInstance | null>(null);

function focusBedtime(): void {
    (bedtimeInput.value?.$el as HTMLInputElement | undefined)?.focus();
}

function submit(): void {
    form.transform((data) => ({
        date: data.date,
        weight: data.weight,
        fat_perc: data.fat_perc,
        muscle_perc: data.muscle_perc,
        bmi_value: data.bmi_value,
        bedtime: data.bedtime === '' ? null : data.bedtime,
        sleep_minutes:
            data.sleep_duration === ''
                ? null
                : durationToMinutes(data.sleep_duration),
    }));

    if (props.measurement) {
        form.submit(MeasurementController.update(props.measurement.id), {
            onSuccess: () => router.visit(MeasurementController.index()),
        });
    } else {
        form.submit(MeasurementController.store());
    }
}
</script>

<template>
    <form class="space-y-6" @submit.prevent="submit">
        <div class="grid gap-2">
            <Label class="text-base" for="date">Date</Label>
            <DateDigitsInput
                id="date"
                v-model="form.date"
                class="h-12 text-lg"
                required
            />
            <InputError :message="form.errors.date" />
        </div>

        <div class="grid gap-2">
            <Label class="text-base" for="weight">Weight (kg)</Label>
            <DecimalDigitsInput
                id="weight"
                v-model="form.weight"
                class="h-12 text-lg"
                placeholder="e.g. 80.5"
                required
                :aria-invalid="form.errors.weight ? true : undefined"
                @complete="fatInput?.focus()"
            />
            <InputError :message="form.errors.weight" />
        </div>

        <div class="grid gap-2">
            <Label class="text-base" for="fat_perc">Body fat (%)</Label>
            <DecimalDigitsInput
                id="fat_perc"
                ref="fatInput"
                v-model="form.fat_perc"
                class="h-12 text-lg"
                placeholder="e.g. 22.5"
                required
                :aria-invalid="form.errors.fat_perc ? true : undefined"
                @complete="muscleInput?.focus()"
            />
            <InputError :message="form.errors.fat_perc" />
        </div>

        <div class="grid gap-2">
            <Label class="text-base" for="muscle_perc">
                Muscle mass (%)
            </Label>
            <DecimalDigitsInput
                id="muscle_perc"
                ref="muscleInput"
                v-model="form.muscle_perc"
                class="h-12 text-lg"
                placeholder="e.g. 41.0"
                required
                :aria-invalid="form.errors.muscle_perc ? true : undefined"
                @complete="bmiInput?.focus()"
            />
            <InputError :message="form.errors.muscle_perc" />
        </div>

        <div class="grid gap-2">
            <Label class="text-base" for="bmi_value">BMI</Label>
            <DecimalDigitsInput
                id="bmi_value"
                ref="bmiInput"
                v-model="form.bmi_value"
                class="h-12 text-lg"
                placeholder="e.g. 24.3"
                required
                :aria-invalid="form.errors.bmi_value ? true : undefined"
                @complete="focusBedtime()"
            />
            <InputError :message="form.errors.bmi_value" />
        </div>

        <div class="grid gap-2">
                <Label class="text-base" for="bedtime">
                    Bedtime (optional)
                </Label>
                <Input
                    id="bedtime"
                    ref="bedtimeInput"
                    v-model="form.bedtime"
                    type="time"
                    class="h-12 text-lg"
                />
                <InputError :message="serverErrors.bedtime" />
        </div>

        <div class="grid gap-2">
                <Label class="text-base" for="sleep_duration">
                    Sleep duration (optional)
                </Label>
                <DurationDigitsInput
                    id="sleep_duration"
                    v-model="form.sleep_duration"
                    class="h-12 text-lg"
                />
                <InputError :message="serverErrors.sleep_minutes" />
        </div>

        <Button
            type="submit"
            size="lg"
            class="h-12 w-full text-base"
            :disabled="form.processing"
            data-test="save-measurement-button"
        >
            {{ form.processing ? 'Saving...' : submitLabel }}
        </Button>
    </form>
</template>
