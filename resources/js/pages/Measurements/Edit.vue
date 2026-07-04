<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import MeasurementController from '@/actions/App/Http/Controllers/MeasurementController';
import Heading from '@/components/Heading.vue';
import MeasurementForm from '@/components/MeasurementForm.vue';
import { dashboard } from '@/routes';
import type { Measurement } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'EasyFit',
                href: dashboard(),
            },
            {
                title: 'Measurement history',
                href: MeasurementController.index(),
            },
            {
                title: 'Edit measurement',
                href: '#',
            },
        ],
    },
});

const props = defineProps<{
    measurement: Measurement;
}>();
</script>

<template>
    <Head title="Edit measurement" />

    <div class="mx-auto w-full max-w-lg flex-1 p-4">
        <Heading
            title="Edit measurement"
            :description="`Update the logged values: type just the digits, the third one becomes the decimal.`"
        />

        <MeasurementForm
            :measurement="props.measurement"
            submit-label="Save changes"
        />
    </div>
</template>
