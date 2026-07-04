<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const env = computed(() => usePage().props.appEnv);
</script>

<template>
    <div
        v-if="env !== 'production'"
        data-test="environment-badge"
        class="pointer-events-none fixed bottom-2 left-2 z-50 rounded-full bg-amber-500 px-2.5 py-1 text-xs font-semibold tracking-wide text-white uppercase shadow-lg dark:bg-amber-600"
    >
        {{ env }}
    </div>
</template>
