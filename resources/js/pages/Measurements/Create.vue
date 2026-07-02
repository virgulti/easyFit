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
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Nuova misura',
                href: MeasurementController.create(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Nuova misura" />

    <div class="mx-auto w-full max-w-lg flex-1 p-4">
        <Heading
            title="Nuova misura"
            description="Registra i valori di oggi: digita solo le cifre, la terza diventa il decimale."
        />

        <MeasurementForm submit-label="Salva misura" />
    </div>
</template>
