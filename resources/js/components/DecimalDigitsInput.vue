<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { ref, watch } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps<{
    modelValue: number | null;
    class?: HTMLAttributes['class'];
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: number | null): void;
    (e: 'complete'): void;
}>();

const input = ref<HTMLInputElement | null>(null);

/**
 * Display text initialized from the saved float (e.g. 80.5 -> "80.5") so that
 * editing an existing value never conflicts with the digit auto-formatting.
 */
const display = ref(displayFromFloat(props.modelValue));

function displayFromFloat(value: number | null): string {
    if (value === null || Number.isNaN(value)) {
        return '';
    }

    return String(value);
}

/**
 * Auto-formatting rule: the user types plain digits and the 3rd digit becomes
 * the decimal ("374" -> "37.4"). Input is capped at 3 digits.
 */
function displayFromDigits(digits: string): string {
    if (digits.length < 3) {
        return digits;
    }

    return `${digits.slice(0, 2)}.${digits.slice(2)}`;
}

function parsedValue(): number | null {
    if (display.value === '') {
        return null;
    }

    const value = Number.parseFloat(display.value);

    return Number.isNaN(value) ? null : value;
}

watch(
    () => props.modelValue,
    (value) => {
        if (value !== parsedValue()) {
            display.value = displayFromFloat(value);
        }
    },
);

function onInput(event: Event): void {
    const target = event.target as HTMLInputElement;
    const digits = target.value.replace(/\D/g, '').slice(0, 3);

    display.value = displayFromDigits(digits);
    target.value = display.value;
    emit('update:modelValue', parsedValue());

    if (digits.length === 3) {
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
