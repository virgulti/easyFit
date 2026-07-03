<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, CircleCheck, CircleX } from '@lucide/vue';
import { computed } from 'vue';
import MealLogController from '@/actions/App/Http/Controllers/MealLogController';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { mealTypeLabels } from '@/lib/meals';
import { formatDecimal, formatItalianDate } from '@/lib/measurements';
import { dashboard } from '@/routes';
import type { MealLog, MealThresholds, MealTotals } from '@/types';

const props = defineProps<{
    date: string;
    mealLogs: MealLog[];
    totals: MealTotals;
    thresholds: MealThresholds;
}>();

const proteinGoalMet = computed(() =>
    props.thresholds.min_protein_grams === null
        ? null
        : props.totals.protein_grams >= props.thresholds.min_protein_grams,
);

const caloriesGoalMet = computed(() =>
    props.thresholds.max_calories_per_day === null
        ? null
        : props.totals.calories <= props.thresholds.max_calories_per_day,
);

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Pasti del giorno', href: MealLogController.index() },
        ],
    },
});

function shiftDate(days: number): string {
    const date = new Date(`${props.date}T00:00:00Z`);
    date.setUTCDate(date.getUTCDate() + days);

    return date.toISOString().slice(0, 10);
}

const previousDate = computed(() => shiftDate(-1));
const nextDate = computed(() => shiftDate(1));

const isToday = computed(() => {
    const today = new Intl.DateTimeFormat('en-CA').format(new Date());

    return props.date === today;
});
</script>

<template>
    <Head title="Pasti del giorno" />

    <div class="mx-auto w-full max-w-2xl flex-1 space-y-6 p-4">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <Heading
                title="Pasti del giorno"
                :description="formatItalianDate(date)"
            />

            <Button as-child>
                <Link
                    :href="MealLogController.create({ query: { date } })"
                >
                    Registra pasto
                </Link>
            </Button>
        </div>

        <div class="flex items-center justify-between gap-2">
            <Button variant="outline" size="sm" as-child>
                <Link
                    :href="MealLogController.index({ query: { date: previousDate } })"
                >
                    <ChevronLeft class="h-4 w-4" />
                    Giorno prima
                </Link>
            </Button>

            <Button v-if="!isToday" variant="ghost" size="sm" as-child>
                <Link :href="MealLogController.index()">Oggi</Link>
            </Button>

            <Button variant="outline" size="sm" as-child>
                <Link
                    :href="MealLogController.index({ query: { date: nextDate } })"
                >
                    Giorno dopo
                    <ChevronRight class="h-4 w-4" />
                </Link>
            </Button>
        </div>

        <div
            class="grid grid-cols-2 gap-4 rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
        >
            <div>
                <p class="text-sm text-muted-foreground">Calorie totali</p>
                <p class="text-2xl font-semibold">
                    {{ totals.calories }} kcal
                </p>
                <p
                    v-if="thresholds.max_calories_per_day !== null"
                    class="mt-1 flex items-center gap-1 text-sm"
                    :class="
                        caloriesGoalMet
                            ? 'text-green-600 dark:text-green-400'
                            : 'text-red-600 dark:text-red-400'
                    "
                >
                    <CircleCheck v-if="caloriesGoalMet" class="h-4 w-4" />
                    <CircleX v-else class="h-4 w-4" />
                    obiettivo max {{ thresholds.max_calories_per_day }} kcal
                </p>
            </div>
            <div>
                <p class="text-sm text-muted-foreground">Proteine totali</p>
                <p class="text-2xl font-semibold">
                    {{ formatDecimal(totals.protein_grams) }}g
                </p>
                <p
                    v-if="thresholds.min_protein_grams !== null"
                    class="mt-1 flex items-center gap-1 text-sm"
                    :class="
                        proteinGoalMet
                            ? 'text-green-600 dark:text-green-400'
                            : 'text-red-600 dark:text-red-400'
                    "
                >
                    <CircleCheck v-if="proteinGoalMet" class="h-4 w-4" />
                    <CircleX v-else class="h-4 w-4" />
                    obiettivo min {{ thresholds.min_protein_grams }}g
                </p>
            </div>
        </div>

        <div
            v-if="mealLogs.length === 0"
            class="rounded-xl border border-sidebar-border/70 p-8 text-center dark:border-sidebar-border"
        >
            <p class="text-muted-foreground">
                Nessun pasto registrato per questo giorno.
            </p>
        </div>

        <ul v-else class="space-y-3">
            <li
                v-for="mealLog in mealLogs"
                :key="mealLog.id"
                class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <Link
                    :href="MealLogController.edit(mealLog.id)"
                    class="flex items-center justify-between gap-4 p-4"
                >
                    <div>
                        <p class="font-medium">{{ mealLog.description }}</p>
                        <p class="text-sm text-muted-foreground">
                            {{ mealTypeLabels[mealLog.meal_type] }} ·
                            {{ mealLog.weight_grams }}g
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="font-medium">
                            {{ mealLog.calories }} kcal
                        </p>
                        <p class="text-sm text-muted-foreground">
                            {{ formatDecimal(mealLog.protein_grams) }}g
                            proteine
                        </p>
                    </div>
                </Link>
            </li>
        </ul>
    </div>
</template>
