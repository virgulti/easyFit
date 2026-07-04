<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import MealController from '@/actions/App/Http/Controllers/MealController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { mealTypeLabels, mealTypes } from '@/lib/meals';
import { dashboard } from '@/routes';
import type { Meal, MealType } from '@/types';

const props = defineProps<{
    meal: Meal;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'easyfit', href: dashboard() },
            { title: 'Meal catalog', href: MealController.index() },
            { title: 'Edit meal', href: '#' },
        ],
    },
});

const form = useForm({
    description: props.meal.description,
    meal_type: props.meal.meal_type as MealType,
    reference_weight_grams: props.meal.reference_weight_grams,
    calories: props.meal.calories,
    protein_grams: Number.parseFloat(props.meal.protein_grams),
    reference_cost:
        props.meal.reference_cost === null
            ? (undefined as number | undefined)
            : Number.parseFloat(props.meal.reference_cost),
});

const isDeleting = ref(false);

function submit(): void {
    form.submit(MealController.update(props.meal.id), {
        onSuccess: () => router.visit(MealController.index()),
    });
}

function destroy(): void {
    isDeleting.value = true;
    router.delete(MealController.destroy.url(props.meal.id), {
        onError: () => {
            isDeleting.value = false;
        },
    });
}
</script>

<template>
    <Head title="Edit meal" />

    <div class="mx-auto w-full max-w-lg flex-1 p-4">
        <div class="mb-8 flex items-start justify-between gap-4">
            <Heading
                title="Edit meal"
                :description="meal.description"
            />

            <Dialog>
                <DialogTrigger as-child>
                    <Button
                        type="button"
                        variant="ghost"
                        class="text-destructive hover:bg-destructive/10 hover:text-destructive"
                    >
                        Delete
                    </Button>
                </DialogTrigger>

                <DialogContent>
                    <DialogTitle>Delete meal</DialogTitle>
                    <DialogDescription>
                        Do you want to remove "{{ meal.description }}" from
                        the catalog? Meals already logged from it are not
                        affected. This can't be undone.
                    </DialogDescription>
                    <DialogFooter class="gap-2">
                        <DialogClose as-child>
                            <Button variant="secondary">Cancel</Button>
                        </DialogClose>
                        <Button
                            variant="destructive"
                            :disabled="isDeleting"
                            @click="destroy"
                        >
                            {{ isDeleting ? 'Deleting...' : 'Delete' }}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>

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
                    required
                />
                <InputError :message="form.errors.description" />
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
                {{ form.processing ? 'Saving...' : 'Save changes' }}
            </Button>
        </form>
    </div>
</template>
