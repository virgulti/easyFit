<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import MealController from '@/actions/App/Http/Controllers/MealController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'easyfit', href: dashboard() },
            { title: 'Meal catalog', href: MealController.index() },
            { title: 'Add meal', href: MealController.create() },
        ],
    },
});

const form = useForm({
    description: '',
    reference_weight_grams: undefined as number | undefined,
    calories: undefined as number | undefined,
    protein_grams: undefined as number | undefined,
    reference_cost: undefined as number | undefined,
});

function submit(): void {
    form.submit(MealController.store());
}
</script>

<template>
    <Head title="Add meal" />

    <div class="mx-auto w-full max-w-lg flex-1 p-4">
        <Heading
            title="Add meal"
            description="Add a reusable meal to your catalog"
        />

        <form class="space-y-6" @submit.prevent="submit">
            <div class="grid gap-2">
                <Label class="text-base" for="description">
                    Description
                </Label>
                <Input
                    id="description"
                    v-model="form.description"
                    type="text"
                    class="h-12 text-lg"
                    placeholder="e.g. Chicken breast with rice and vegetables"
                    required
                />
                <InputError :message="form.errors.description" />
            </div>

            <div class="grid gap-2">
                <Label class="text-base" for="reference_weight_grams">
                    Reference weight (g)
                </Label>
                <Input
                    id="reference_weight_grams"
                    v-model.number="form.reference_weight_grams"
                    type="number"
                    min="1"
                    max="5000"
                    class="h-12 text-lg"
                    required
                />
                <InputError :message="form.errors.reference_weight_grams" />
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
                <Label class="text-base" for="reference_cost">
                    Cost (€, optional)
                </Label>
                <Input
                    id="reference_cost"
                    v-model.number="form.reference_cost"
                    type="number"
                    min="0"
                    max="9999.99"
                    step="0.01"
                    class="h-12 text-lg"
                    placeholder="e.g. 8.50"
                />
                <InputError :message="form.errors.reference_cost" />
            </div>

            <Button
                type="submit"
                size="lg"
                class="h-12 w-full text-base"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Saving...' : 'Add meal' }}
            </Button>
        </form>
    </div>
</template>
