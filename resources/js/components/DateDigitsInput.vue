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
 * Display text always reads dd/mm/yyyy regardless of the browser/OS locale; the underlying
 * `v-model` stays an ISO `yyyy-mm-dd` string so the backend is unaffected.
 */
const display = ref(displayFromIso(props.modelValue));

function displayFromIso(value: string): string {
    const [year, month, day] = value.split('-');

    if (!year || !month || !day) {
        return '';
    }

    return `${day}/${month}/${year}`;
}

/**
 * Auto-formatting rule: the user types plain digits and slashes are inserted
 * progressively ("0107202" -> "01/07/202"). Input is capped at 8 digits.
 */
function displayFromDigits(digits: string): string {
    if (digits.length <= 2) {
        return digits;
    }

    if (digits.length <= 4) {
        return `${digits.slice(0, 2)}/${digits.slice(2)}`;
    }

    return `${digits.slice(0, 2)}/${digits.slice(2, 4)}/${digits.slice(4)}`;
}

function isoFromDigits(digits: string): string {
    if (digits.length < 8) {
        return '';
    }

    const day = digits.slice(0, 2);
    const month = digits.slice(2, 4);
    const year = digits.slice(4, 8);

    return `${year}-${month}-${day}`;
}

watch(
    () => props.modelValue,
    (value) => {
        const currentDigits = display.value.replace(/\D/g, '');

        if (value !== isoFromDigits(currentDigits)) {
            display.value = displayFromIso(value);
        }
    },
);

function onInput(event: Event): void {
    const target = event.target as HTMLInputElement;
    const digits = target.value.replace(/\D/g, '').slice(0, 8);

    display.value = displayFromDigits(digits);
    target.value = display.value;
    emit('update:modelValue', isoFromDigits(digits));

    if (digits.length === 8) {
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
        placeholder="dd/mm/yyyy"
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
