<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import MealController from '@/actions/App/Http/Controllers/MealController';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { formatCost } from '@/lib/meals';
import { formatDecimal } from '@/lib/measurements';
import { dashboard } from '@/routes';
import type { Meal } from '@/types';

defineProps<{
    meals: Meal[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'EasyFit', href: dashboard() },
            { title: 'Meal catalog', href: MealController.index() },
        ],
    },
});
</script>

<template>
    <Head title="Meal catalog" />

    <div class="mx-auto w-full max-w-5xl flex-1 space-y-6 p-4">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <Heading
                title="Meal catalog"
                description="Meals you can quickly pick when logging"
            />

            <Button as-child>
                <Link :href="MealController.create()">
                    Add meal
                </Link>
            </Button>
        </div>

        <div
            v-if="meals.length === 0"
            class="rounded-xl border border-sidebar-border/70 p-8 text-center dark:border-sidebar-border"
        >
            <p class="text-muted-foreground">
                No meals in the catalog yet.
            </p>
            <Button class="mt-4" as-child>
                <Link :href="MealController.create()">
                    Add your first meal
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
                            <th class="px-4 py-3 font-medium">Description</th>
                            <th class="px-4 py-3 font-medium">
                                Ref. weight (g)
                            </th>
                            <th class="px-4 py-3 font-medium">Calories</th>
                            <th class="px-4 py-3 font-medium">Protein (g)</th>
                            <th class="px-4 py-3 font-medium">Cost</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="meal in meals"
                            :key="meal.id"
                            class="border-b border-sidebar-border/70 last:border-b-0 dark:border-sidebar-border"
                        >
                            <td class="px-4 py-3">
                                {{ meal.description }}
                            </td>
                            <td class="px-4 py-3">
                                {{ meal.reference_weight_grams }}
                            </td>
                            <td class="px-4 py-3">{{ meal.calories }}</td>
                            <td class="px-4 py-3">
                                {{ formatDecimal(meal.protein_grams) }}
                            </td>
                            <td class="px-4 py-3">
                                {{
                                    meal.reference_cost !== null
                                        ? formatCost(meal.reference_cost)
                                        : '—'
                                }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <Button variant="outline" size="sm" as-child>
                                    <Link :href="MealController.edit(meal.id)">
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
                    v-for="meal in meals"
                    :key="meal.id"
                    class="space-y-3 rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                >
                    <p class="font-medium">{{ meal.description }}</p>

                    <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                        <div>
                            <dt class="text-muted-foreground">
                                Ref. weight
                            </dt>
                            <dd>{{ meal.reference_weight_grams }}g</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Calories</dt>
                            <dd>{{ meal.calories }} kcal</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Protein</dt>
                            <dd>{{ formatDecimal(meal.protein_grams) }}g</dd>
                        </div>
                        <div v-if="meal.reference_cost !== null">
                            <dt class="text-muted-foreground">Cost</dt>
                            <dd>{{ formatCost(meal.reference_cost) }}</dd>
                        </div>
                    </dl>

                    <Button variant="outline" size="sm" class="w-full" as-child>
                        <Link :href="MealController.edit(meal.id)">
                            Edit
                        </Link>
                    </Button>
                </li>
            </ul>
        </template>
    </div>
</template>
