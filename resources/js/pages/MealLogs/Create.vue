<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import MealLogController from '@/actions/App/Http/Controllers/MealLogController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatCost, mealTypeLabels, mealTypes, scaleMeal } from '@/lib/meals';
import { formatDecimal } from '@/lib/measurements';
import { dashboard } from '@/routes';
import type { Meal, MealType } from '@/types';

const props = defineProps<{
    meals: Meal[];
    date: string;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Log meal', href: MealLogController.create() },
        ],
    },
});

const mode = ref<'catalog' | 'unusual'>('catalog');
const filterText = ref('');
const selectedMeal = ref<Meal | null>(null);

const filteredMeals = computed(() => {
    const query = filterText.value.trim().toLowerCase();

    if (query === '') {
        return props.meals;
    }

    return props.meals.filter((meal) =>
        meal.description.toLowerCase().includes(query),
    );
});

const form = useForm({
    meal_id: null as number | null,
    meal_type: 'lunch' as MealType,
    weight_grams: undefined as number | undefined,
    description: '',
    calories: undefined as number | undefined,
    protein_grams: undefined as number | undefined,
    cost: undefined as number | undefined,
    date: props.date,
    save_to_catalog: false,
});

const scaledPreview = computed(() => {
    if (selectedMeal.value === null || !form.weight_grams) {
        return null;
    }

    return scaleMeal(selectedMeal.value, form.weight_grams);
});

const showCostInput = computed(
    () =>
        mode.value === 'unusual' ||
        selectedMeal.value === null ||
        selectedMeal.value.reference_cost === null,
);

const canSubmit = computed(() => {
    if (mode.value === 'catalog') {
        return selectedMeal.value !== null && !!form.weight_grams;
    }

    return (
        form.description.trim() !== '' &&
        form.calories !== undefined &&
        form.protein_grams !== undefined &&
        !!form.weight_grams
    );
});

function selectMeal(meal: Meal): void {
    selectedMeal.value = meal;
    form.meal_id = meal.id;
    form.meal_type = meal.meal_type;
    form.weight_grams = meal.reference_weight_grams;
    form.cost = undefined;
}

function switchMode(next: 'catalog' | 'unusual'): void {
    mode.value = next;
    selectedMeal.value = null;
    form.meal_id = null;
    form.weight_grams = undefined;
    form.description = '';
    form.calories = undefined;
    form.protein_grams = undefined;
    form.cost = undefined;
    form.save_to_catalog = false;
}

function submit(): void {
    form.transform((data) =>
        mode.value === 'catalog'
            ? {
                  meal_id: data.meal_id,
                  meal_type: data.meal_type,
                  weight_grams: data.weight_grams,
                  cost: data.cost,
                  date: data.date,
              }
            : {
                  meal_type: data.meal_type,
                  weight_grams: data.weight_grams,
                  description: data.description,
                  calories: data.calories,
                  protein_grams: data.protein_grams,
                  cost: data.cost,
                  date: data.date,
                  save_to_catalog: data.save_to_catalog,
              },
    );

    form.submit(MealLogController.store());
}
</script>

<template>
    <Head title="Log meal" />

    <div class="mx-auto w-full max-w-lg flex-1 p-4">
        <Heading
            title="Log meal"
            description="Pick a meal from the catalog or log an unusual meal"
        />

        <div class="mb-6 grid grid-cols-2 gap-2">
            <Button
                type="button"
                :variant="mode === 'catalog' ? 'default' : 'outline'"
                @click="switchMode('catalog')"
            >
                From catalog
            </Button>
            <Button
                type="button"
                :variant="mode === 'unusual' ? 'default' : 'outline'"
                @click="switchMode('unusual')"
            >
                Unusual meal
            </Button>
        </div>

        <form class="space-y-6" @submit.prevent="submit">
            <div class="grid gap-2">
                <Label class="text-base" for="date">Date</Label>
                <Input
                    id="date"
                    v-model="form.date"
                    type="date"
                    class="h-12 text-lg"
                    required
                />
                <InputError :message="form.errors.date" />
            </div>

            <div class="grid gap-2">
                <Label class="text-base">Meal type</Label>
                <div class="grid grid-cols-4 gap-2">
                    <Button
                        v-for="type in mealTypes"
                        :key="type"
                        type="button"
                        size="sm"
                        :variant="form.meal_type === type ? 'default' : 'outline'"
                        @click="form.meal_type = type"
                    >
                        {{ mealTypeLabels[type] }}
                    </Button>
                </div>
                <InputError :message="form.errors.meal_type" />
            </div>

            <template v-if="mode === 'catalog'">
                <div class="grid gap-2">
                    <Label class="text-base" for="meal-filter">
                        Search meals
                    </Label>
                    <Input
                        id="meal-filter"
                        v-model="filterText"
                        type="text"
                        class="h-12 text-lg"
                        placeholder="e.g. chicken, yogurt..."
                    />
                </div>

                <div
                    class="max-h-72 space-y-1 overflow-y-auto rounded-xl border border-sidebar-border/70 p-2 dark:border-sidebar-border"
                >
                    <p
                        v-if="filteredMeals.length === 0"
                        class="p-4 text-center text-sm text-muted-foreground"
                    >
                        No meals found.
                    </p>
                    <button
                        v-for="meal in filteredMeals"
                        :key="meal.id"
                        type="button"
                        class="w-full rounded-lg border p-3 text-left transition-colors"
                        :class="
                            selectedMeal?.id === meal.id
                                ? 'border-primary bg-primary/10'
                                : 'border-transparent hover:bg-muted'
                        "
                        @click="selectMeal(meal)"
                    >
                        <p class="font-medium">{{ meal.description }}</p>
                        <p class="text-sm text-muted-foreground">
                            {{ mealTypeLabels[meal.meal_type] }} ·
                            {{ meal.reference_weight_grams }}g ·
                            {{ meal.calories }} kcal ·
                            {{ formatDecimal(meal.protein_grams) }}g protein
                        </p>
                    </button>
                </div>
                <InputError :message="form.errors.meal_id" />

                <div v-if="selectedMeal" class="grid gap-2">
                    <Label class="text-base" for="weight_grams">
                        Weight (g)
                    </Label>
                    <Input
                        id="weight_grams"
                        v-model.number="form.weight_grams"
                        type="number"
                        min="1"
                        max="5000"
                        class="h-12 text-lg"
                        required
                    />
                    <InputError :message="form.errors.weight_grams" />

                    <p
                        v-if="scaledPreview"
                        class="text-sm text-muted-foreground"
                    >
                        {{ scaledPreview.calories }} kcal ·
                        {{ formatDecimal(scaledPreview.protein_grams) }}g
                        protein
                        <template v-if="scaledPreview.cost !== null">
                            · {{ formatCost(scaledPreview.cost) }}
                        </template>
                    </p>
                </div>

                <div v-if="showCostInput" class="grid gap-2">
                    <Label class="text-base" for="cost">Cost (€, optional)</Label>
                    <Input
                        id="cost"
                        v-model.number="form.cost"
                        type="number"
                        min="0"
                        max="9999.99"
                        step="0.01"
                        class="h-12 text-lg"
                        placeholder="e.g. 8.50"
                    />
                    <InputError :message="form.errors.cost" />
                </div>
            </template>

            <template v-else>
                <div class="grid gap-2">
                    <Label class="text-base" for="description">
                        Description
                    </Label>
                    <Input
                        id="description"
                        v-model="form.description"
                        type="text"
                        class="h-12 text-lg"
                        placeholder="e.g. Dinner out with friends"
                        required
                    />
                    <InputError :message="form.errors.description" />
                </div>

                <div class="grid gap-2">
                    <Label class="text-base" for="weight_grams_unusual">
                        Weight (g)
                    </Label>
                    <Input
                        id="weight_grams_unusual"
                        v-model.number="form.weight_grams"
                        type="number"
                        min="1"
                        max="5000"
                        class="h-12 text-lg"
                        required
                    />
                    <InputError :message="form.errors.weight_grams" />
                </div>

                <div class="grid gap-2">
                    <Label class="text-base" for="calories">
                        Calories (kcal)
                    </Label>
                    <Input
                        id="calories"
                        v-model.number="form.calories"
                        type="number"
                        min="0"
                        max="10000"
                        class="h-12 text-lg"
                        required
                    />
                    <InputError :message="form.errors.calories" />
                </div>

                <div class="grid gap-2">
                    <Label class="text-base" for="protein_grams">
                        Protein (g)
                    </Label>
                    <Input
                        id="protein_grams"
                        v-model.number="form.protein_grams"
                        type="number"
                        min="0"
                        max="500"
                        step="0.1"
                        class="h-12 text-lg"
                        required
                    />
                    <InputError :message="form.errors.protein_grams" />
                </div>

                <div class="grid gap-2">
                    <Label class="text-base" for="cost">Cost (€, optional)</Label>
                    <Input
                        id="cost"
                        v-model.number="form.cost"
                        type="number"
                        min="0"
                        max="9999.99"
                        step="0.01"
                        class="h-12 text-lg"
                        placeholder="e.g. 8.50"
                    />
                    <InputError :message="form.errors.cost" />
                </div>

                <Label class="flex items-center gap-3 text-base">
                    <Checkbox v-model="form.save_to_catalog" />
                    Save to catalog for next time
                </Label>
            </template>

            <Button
                type="submit"
                size="lg"
                class="h-12 w-full text-base"
                :disabled="form.processing || !canSubmit"
            >
                {{ form.processing ? 'Saving...' : 'Log meal' }}
            </Button>
        </form>
    </div>
</template>
