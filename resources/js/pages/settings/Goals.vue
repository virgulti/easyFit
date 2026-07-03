<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import GoalController from '@/actions/App/Http/Controllers/Settings/GoalController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/goals';
import type { Goal } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Goals',
                href: edit(),
            },
        ],
    },
});

const props = defineProps<{
    goal: Goal | null;
}>();
</script>

<template>
    <Head title="Goals" />

    <h1 class="sr-only">Goals</h1>

    <div class="flex flex-col space-y-6">
        <Heading
            variant="small"
            title="Goals"
            description="Set the thresholds you want to track. Leave a field blank to not track that goal."
        />

        <Form
            v-bind="GoalController.update.form()"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="max_fat_percentage">
                    Max body fat (%)
                </Label>
                <Input
                    id="max_fat_percentage"
                    type="number"
                    step="0.1"
                    min="1"
                    max="80"
                    class="mt-1 block w-full"
                    name="max_fat_percentage"
                    :default-value="props.goal?.max_fat_percentage ?? undefined"
                    placeholder="e.g. 19.0"
                />
                <InputError class="mt-2" :message="errors.max_fat_percentage" />
            </div>

            <div class="grid gap-2">
                <Label for="min_protein_grams">
                    Min protein per day (g)
                </Label>
                <Input
                    id="min_protein_grams"
                    type="number"
                    min="0"
                    max="500"
                    class="mt-1 block w-full"
                    name="min_protein_grams"
                    :default-value="props.goal?.min_protein_grams ?? undefined"
                    placeholder="e.g. 160"
                />
                <InputError class="mt-2" :message="errors.min_protein_grams" />
            </div>

            <div class="grid gap-2">
                <Label for="max_calories_per_day">
                    Max calories per day (kcal)
                </Label>
                <Input
                    id="max_calories_per_day"
                    type="number"
                    min="0"
                    max="10000"
                    class="mt-1 block w-full"
                    name="max_calories_per_day"
                    :default-value="props.goal?.max_calories_per_day ?? undefined"
                    placeholder="e.g. 1500"
                />
                <InputError
                    class="mt-2"
                    :message="errors.max_calories_per_day"
                />
            </div>

            <div class="grid gap-2">
                <Label for="max_calories_per_week">
                    Max calories per week (kcal)
                </Label>
                <Input
                    id="max_calories_per_week"
                    type="number"
                    min="0"
                    max="70000"
                    class="mt-1 block w-full"
                    name="max_calories_per_week"
                    :default-value="props.goal?.max_calories_per_week ?? undefined"
                    placeholder="e.g. 10500"
                />
                <InputError
                    class="mt-2"
                    :message="errors.max_calories_per_week"
                />
            </div>

            <div class="flex items-center gap-4">
                <Button :disabled="processing" data-test="update-goal-button">
                    Save
                </Button>
            </div>
        </Form>
    </div>
</template>
