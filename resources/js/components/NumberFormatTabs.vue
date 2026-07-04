<script setup lang="ts">
import { useNumberFormat } from '@/composables/useNumberFormat';

const { decimalSeparator, updateDecimalSeparator } = useNumberFormat();

const tabs = [
    { value: 'dot', label: 'Point (1234.5)' },
    { value: 'comma', label: 'Comma (1234,5)' },
] as const;
</script>

<template>
    <div
        class="inline-flex gap-1 rounded-lg bg-neutral-100 p-1 dark:bg-neutral-800"
    >
        <button
            v-for="{ value, label } in tabs"
            :key="value"
            @click="updateDecimalSeparator(value)"
            :class="[
                'flex items-center rounded-md px-3.5 py-1.5 transition-colors',
                decimalSeparator === value
                    ? 'bg-white shadow-xs dark:bg-neutral-700 dark:text-neutral-100'
                    : 'text-neutral-500 hover:bg-neutral-200/60 hover:text-black dark:text-neutral-400 dark:hover:bg-neutral-700/60',
            ]"
        >
            <span class="text-sm">{{ label }}</span>
        </button>
    </div>
</template>
