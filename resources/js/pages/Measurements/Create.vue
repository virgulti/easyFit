<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import MeasurementController from '@/actions/App/Http/Controllers/MeasurementController';
import Heading from '@/components/Heading.vue';
import MeasurementForm from '@/components/MeasurementForm.vue';
import { dashboard } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'easyfit',
                href: dashboard(),
            },
            {
                title: 'New measurement',
                href: MeasurementController.create(),
            },
        ],
    },
});
</script>

<template>
    <Head title="New measurement" />

    <div class="mx-auto w-full max-w-lg flex-1 p-4">
        <Heading
            title="New measurement"
            description="Log today's values: type just the digits, the third one becomes the decimal."
        />

        <MeasurementForm submit-label="Save measurement" />
    </div>
</template>
