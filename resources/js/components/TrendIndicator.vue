<script setup lang="ts">
import { EqualApproximately, TrendingDown, TrendingUp } from '@lucide/vue';
import type { Trend } from '@/types';

defineProps<{
    trend: Trend;
}>();

const icons = {
    up: TrendingUp,
    down: TrendingDown,
    stable: EqualApproximately,
} as const;

const colorClasses: Record<'green' | 'red' | 'gray', string> = {
    green: 'text-green-600 dark:text-green-400',
    red: 'text-red-600 dark:text-red-400',
    gray: 'text-gray-400 dark:text-gray-500',
};
</script>

<template>
    <component
        v-if="trend"
        :is="icons[trend.direction]"
        class="h-3.5 w-3.5"
        :class="colorClasses[trend.color]"
        aria-hidden="true"
    />
</template>
