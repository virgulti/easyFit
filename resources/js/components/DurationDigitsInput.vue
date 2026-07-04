<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { ref, watch } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps<{
    modelValue: string;
    class?: HTMLAttributes['class'];
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
    (e: 'complete'): void;
}>();

const input = ref<HTMLInputElement | null>(null);

/**
 * Display text always reads "07h 32m" (no am/pm, this is a duration, not a clock time); the
 * underlying `v-model` stays an "HH:mm" string so durationToMinutes/minutesToDuration and the
 * backend payload are unaffected.
 */
const display = ref(displayFromHhMm(props.modelValue));

function displayFromHhMm(value: string): string {
    const match = /^(\d{2}):(\d{2})$/.exec(value);

    if (match === null) {
        return '';
    }

    return `${match[1]}h ${match[2]}m`;
}

/**
 * Auto-formatting rule: the user types plain digits, first 2 are hours and the
 * next 2 are minutes ("073" -> "07h 3m" -> "0732" -> "07h 32m"). Capped at 4 digits.
 */
function displayFromDigits(digits: string): string {
    if (digits.length < 2) {
        return digits;
    }

    if (digits.length === 2) {
        return `${digits}h`;
    }

    return `${digits.slice(0, 2)}h ${digits.slice(2)}m`;
}

function hhMmFromDigits(digits: string): string {
    if (digits.length < 4) {
        return '';
    }

    return `${digits.slice(0, 2)}:${digits.slice(2, 4)}`;
}

watch(
    () => props.modelValue,
    (value) => {
        const currentDigits = display.value.replace(/\D/g, '');

        if (value !== hhMmFromDigits(currentDigits)) {
            display.value = displayFromHhMm(value);
        }
    },
);

function onInput(event: Event): void {
    const target = event.target as HTMLInputElement;
    const digits = target.value.replace(/\D/g, '').slice(0, 4);

    display.value = displayFromDigits(digits);
    target.value = display.value;
    emit('update:modelValue', hhMmFromDigits(digits));

    if (digits.length === 4) {
        emit('complete');
    }
}

function onEnter(event: KeyboardEvent): void {
    event.preventDefault();
    emit('complete');
}

defineExpose({
    focus: (): void => input.value?.focus(),
});
</script>

<template>
    <input
        ref="input"
        type="text"
        inputmode="numeric"
        autocomplete="off"
        placeholder="00h 00m"
        data-slot="input"
        :value="display"
        :class="
            cn(
                'w-full min-w-0 rounded-md border border-input bg-transparent px-3 py-1 shadow-xs transition-[color,box-shadow] outline-none selection:bg-primary selection:text-primary-foreground placeholder:text-muted-foreground disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 dark:bg-input/30',
                'focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50',
                'aria-invalid:border-destructive aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40',
                props.class,
            )
        "
        @input="onInput"
        @keydown.enter="onEnter"
    />
</template>
