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
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Storico misurazioni',
                href: MeasurementController.index(),
            },
            {
                title: 'Modifica misurazione',
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
    <Head title="Modifica misurazione" />

    <div class="mx-auto w-full max-w-lg flex-1 p-4">
        <Heading
            title="Modifica misurazione"
            :description="`Aggiorna i valori registrati: digita solo le cifre, la terza diventa il decimale.`"
        />

        <MeasurementForm
            :measurement="props.measurement"
            submit-label="Salva modifiche"
        />
    </div>
</template>
