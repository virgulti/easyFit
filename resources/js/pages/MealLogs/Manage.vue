<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import MealLogController from '@/actions/App/Http/Controllers/MealLogController';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { formatCost, mealTypeLabels } from '@/lib/meals';
import { formatDecimal, formatDate } from '@/lib/measurements';
import { dashboard } from '@/routes';
import type { MealLog } from '@/types';

defineProps<{
    mealLogs: MealLog[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Manage meals', href: MealLogController.manage() },
        ],
    },
});
</script>

<template>
    <Head title="Manage meals" />

    <div class="mx-auto w-full max-w-5xl flex-1 space-y-6 p-4">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <Heading
                title="Manage meals"
                description="All logged meals, newest first"
            />

            <div class="flex flex-wrap gap-2">
                <Button variant="outline" as-child>
                    <Link :href="MealLogController.index()">
                        Today's meals
                    </Link>
                </Button>
                <Button as-child>
                    <Link :href="MealLogController.create()">
                        Log meal
                    </Link>
                </Button>
            </div>
        </div>

        <div
            v-if="mealLogs.length === 0"
            class="rounded-xl border border-sidebar-border/70 p-8 text-center dark:border-sidebar-border"
        >
            <p class="text-muted-foreground">
                No meals logged yet.
            </p>
            <Button class="mt-4" as-child>
                <Link :href="MealLogController.create()">
                    Log your first meal
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
                            <th class="px-4 py-3 font-medium">Date</th>
                            <th class="px-4 py-3 font-medium">Meal</th>
                            <th class="px-4 py-3 font-medium">Type</th>
                            <th class="px-4 py-3 font-medium">Weight (g)</th>
                            <th class="px-4 py-3 font-medium">Calories</th>
                            <th class="px-4 py-3 font-medium">Protein (g)</th>
                            <th class="px-4 py-3 font-medium">Cost</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="mealLog in mealLogs"
                            :key="mealLog.id"
                            class="border-b border-sidebar-border/70 last:border-b-0 dark:border-sidebar-border"
                        >
                            <td class="px-4 py-3 whitespace-nowrap">
                                {{ formatDate(mealLog.date) }}
                            </td>
                            <td class="px-4 py-3">
                                {{ mealLog.description }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                {{ mealTypeLabels[mealLog.meal_type] }}
                            </td>
                            <td class="px-4 py-3">{{ mealLog.weight_grams }}</td>
                            <td class="px-4 py-3">{{ mealLog.calories }}</td>
                            <td class="px-4 py-3">
                                {{ formatDecimal(mealLog.protein_grams) }}
                            </td>
                            <td class="px-4 py-3">
                                {{ mealLog.cost !== null ? formatCost(mealLog.cost) : '—' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <Button variant="outline" size="sm" as-child>
                                    <Link :href="MealLogController.edit(mealLog.id)">
                                        Edit
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
                    v-for="mealLog in mealLogs"
                    :key="mealLog.id"
                    class="space-y-3 rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                >
                    <div class="flex items-center justify-between gap-2">
                        <p class="font-medium">{{ mealLog.description }}</p>
                        <p class="text-sm text-muted-foreground">
                            {{ formatDate(mealLog.date) }}
                        </p>
                    </div>

                    <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                        <div>
                            <dt class="text-muted-foreground">Type</dt>
                            <dd>{{ mealTypeLabels[mealLog.meal_type] }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Weight</dt>
                            <dd>{{ mealLog.weight_grams }}g</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Calories</dt>
                            <dd>{{ mealLog.calories }} kcal</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Protein</dt>
                            <dd>{{ formatDecimal(mealLog.protein_grams) }}g</dd>
                        </div>
                        <div v-if="mealLog.cost !== null">
                            <dt class="text-muted-foreground">Cost</dt>
                            <dd>{{ formatCost(mealLog.cost) }}</dd>
                        </div>
                    </dl>

                    <Button variant="outline" size="sm" class="w-full" as-child>
                        <Link :href="MealLogController.edit(mealLog.id)">
                            Edit
                        </Link>
                    </Button>
                </li>
            </ul>
        </template>
    </div>
</template>
