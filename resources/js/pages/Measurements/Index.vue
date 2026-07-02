<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import MeasurementController from '@/actions/App/Http/Controllers/MeasurementController';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    bmiBandBadgeClasses,
    bmiBandFor,
    formatDecimal,
    formatItalianDate,
    minutesToDuration,
    progressFor,
} from '@/lib/measurements';
import { dashboard } from '@/routes';
import type { BmiBand, Measurement } from '@/types';

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
        ],
    },
});

const props = defineProps<{
    measurements: Measurement[];
}>();

type Row = {
    measurement: Measurement;
    band: BmiBand;
    progress: string;
    improvement: boolean | null;
    sleep: string;
};

/**
 * Measurements arrive newest-first, so the previous (older) measurement of a
 * row is the next entry in the list; the improvement marker mirrors the
 * backend rule (BMI lower than the previous measurement).
 */
const rows = computed<Row[]>(() =>
    props.measurements.map((measurement, index) => {
        const older = props.measurements[index + 1];

        return {
            measurement,
            band: bmiBandFor(Number.parseFloat(measurement.bmi_value)),
            progress: formatDecimal(
                progressFor(Number.parseFloat(measurement.weight)),
            ),
            improvement: older
                ? Number.parseFloat(measurement.bmi_value) <
                  Number.parseFloat(older.bmi_value)
                : null,
            sleep: sleepInfo(measurement),
        };
    }),
);

function sleepInfo(measurement: Measurement): string {
    const parts: string[] = [];

    if (measurement.bedtime) {
        parts.push(`a letto ${measurement.bedtime.slice(0, 5)}`);
    }

    if (measurement.sleep_minutes !== null) {
        parts.push(`sonno ${minutesToDuration(measurement.sleep_minutes)}`);
    }

    return parts.join(' · ');
}
</script>

<template>
    <Head title="Storico misurazioni" />

    <div class="mx-auto w-full max-w-5xl flex-1 space-y-6 p-4">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <Heading
                title="Storico misurazioni"
                description="Tutte le misurazioni, dalla più recente"
            />

            <div class="flex flex-wrap gap-2">
                <Button variant="outline" as-child>
                    <Link :href="dashboard()">Torna alla dashboard</Link>
                </Button>
                <Button as-child>
                    <Link :href="MeasurementController.create()">
                        Nuova misurazione
                    </Link>
                </Button>
            </div>
        </div>

        <div
            v-if="rows.length === 0"
            class="rounded-xl border border-sidebar-border/70 p-8 text-center dark:border-sidebar-border"
        >
            <p class="text-muted-foreground">
                Nessuna misurazione registrata finora.
            </p>
            <Button class="mt-4" as-child>
                <Link :href="MeasurementController.create()">
                    Registra la prima misurazione
                </Link>
            </Button>
        </div>

        <template v-else>
            <!-- Desktop table -->
            <div
                class="hidden overflow-x-auto rounded-xl border border-sidebar-border/70 md:block dark:border-sidebar-border"
            >
                <table class="w-full text-sm">
                    <thead>
                        <tr
                            class="border-b border-sidebar-border/70 text-left text-muted-foreground dark:border-sidebar-border"
                        >
                            <th class="px-4 py-3 font-medium">Data</th>
                            <th class="px-4 py-3 font-medium">Peso (kg)</th>
                            <th class="px-4 py-3 font-medium">Grassa (%)</th>
                            <th class="px-4 py-3 font-medium">Muscoli (%)</th>
                            <th class="px-4 py-3 font-medium">BMI</th>
                            <th class="px-4 py-3 font-medium">Progress</th>
                            <th class="px-4 py-3 font-medium">Sonno</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="row in rows"
                            :key="row.measurement.id"
                            class="border-b border-sidebar-border/70 last:border-b-0 dark:border-sidebar-border"
                        >
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="flex items-center gap-2">
                                    {{
                                        formatItalianDate(row.measurement.date)
                                    }}
                                    <span
                                        v-if="row.improvement === true"
                                        class="text-green-600 dark:text-green-400"
                                        title="BMI migliorato rispetto alla misurazione precedente"
                                        aria-label="Miglioramento"
                                    >
                                        &#9650;
                                    </span>
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                {{ formatDecimal(row.measurement.weight) }}
                            </td>
                            <td class="px-4 py-3">
                                {{ formatDecimal(row.measurement.fat_perc) }}
                            </td>
                            <td class="px-4 py-3">
                                {{ formatDecimal(row.measurement.muscle_perc) }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="flex items-center gap-2">
                                    {{
                                        formatDecimal(row.measurement.bmi_value)
                                    }}
                                    <Badge
                                        class="border-transparent"
                                        :class="
                                            bmiBandBadgeClasses[row.band.color]
                                        "
                                    >
                                        {{ row.band.label }}
                                    </Badge>
                                </span>
                            </td>
                            <td class="px-4 py-3">{{ row.progress }}</td>
                            <td
                                class="px-4 py-3 whitespace-nowrap text-muted-foreground"
                            >
                                {{ row.sleep || '—' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <Button variant="outline" size="sm" as-child>
                                    <Link
                                        :href="
                                            MeasurementController.edit(
                                                row.measurement.id,
                                            )
                                        "
                                    >
                                        Modifica
                                    </Link>
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile cards -->
            <ul class="space-y-4 md:hidden">
                <li
                    v-for="row in rows"
                    :key="row.measurement.id"
                    class="space-y-3 rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                >
                    <div class="flex items-center justify-between gap-2">
                        <p class="flex items-center gap-2 font-medium">
                            {{ formatItalianDate(row.measurement.date) }}
                            <span
                                v-if="row.improvement === true"
                                class="text-green-600 dark:text-green-400"
                                title="BMI migliorato rispetto alla misurazione precedente"
                                aria-label="Miglioramento"
                            >
                                &#9650;
                            </span>
                        </p>
                        <Badge
                            class="border-transparent"
                            :class="bmiBandBadgeClasses[row.band.color]"
                        >
                            {{ row.band.label }}
                        </Badge>
                    </div>

                    <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                        <div>
                            <dt class="text-muted-foreground">Peso</dt>
                            <dd>
                                {{ formatDecimal(row.measurement.weight) }} kg
                            </dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">BMI</dt>
                            <dd>
                                {{ formatDecimal(row.measurement.bmi_value) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Massa grassa</dt>
                            <dd>
                                {{ formatDecimal(row.measurement.fat_perc) }} %
                            </dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">
                                Massa muscolare
                            </dt>
                            <dd>
                                {{ formatDecimal(row.measurement.muscle_perc) }}
                                %
                            </dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Progress</dt>
                            <dd>{{ row.progress }}</dd>
                        </div>
                        <div v-if="row.sleep">
                            <dt class="text-muted-foreground">Sonno</dt>
                            <dd>{{ row.sleep }}</dd>
                        </div>
                    </dl>

                    <Button variant="outline" size="sm" class="w-full" as-child>
                        <Link
                            :href="
                                MeasurementController.edit(row.measurement.id)
                            "
                        >
                            Modifica
                        </Link>
                    </Button>
                </li>
            </ul>
        </template>
    </div>
</template>
