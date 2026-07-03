<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import MealLogController from '@/actions/App/Http/Controllers/MealLogController';
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
import { mealTypeLabels, mealTypes, scaleMeal } from '@/lib/meals';
import { formatDecimal } from '@/lib/measurements';
import { dashboard } from '@/routes';
import type { Meal, MealLog, MealType } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Pasti del giorno', href: MealLogController.index() },
            { title: 'Modifica pasto', href: '#' },
        ],
    },
});

const props = defineProps<{
    mealLog: MealLog;
    meal: Meal | null;
}>();

const form = useForm({
    date: props.mealLog.date.slice(0, 10),
    meal_type: props.mealLog.meal_type as MealType,
    weight_grams: props.mealLog.weight_grams,
    description: props.mealLog.description,
    calories: props.mealLog.calories,
    protein_grams: Number.parseFloat(props.mealLog.protein_grams),
    cost:
        props.mealLog.cost === null
            ? (undefined as number | undefined)
            : Number.parseFloat(props.mealLog.cost),
});

const isDeleting = ref(false);

const scaledPreview = computed(() => {
    if (props.meal === null) {
        return null;
    }

    return scaleMeal(props.meal, form.weight_grams);
});

function submit(): void {
    if (props.meal !== null) {
        form.transform((data) => ({
            date: data.date,
            meal_type: data.meal_type,
            weight_grams: data.weight_grams,
            cost: data.cost ?? null,
        })).submit(MealLogController.update(props.mealLog.id), {
            onSuccess: () =>
                router.visit(
                    MealLogController.index({ query: { date: form.date } }),
                ),
        });
    } else {
        form.transform((data) => ({
            date: data.date,
            meal_type: data.meal_type,
            weight_grams: data.weight_grams,
            description: data.description,
            calories: data.calories,
            protein_grams: data.protein_grams,
            cost: data.cost ?? null,
        })).submit(MealLogController.update(props.mealLog.id), {
            onSuccess: () =>
                router.visit(
                    MealLogController.index({ query: { date: form.date } }),
                ),
        });
    }
}

function destroy(): void {
    isDeleting.value = true;
    router.delete(MealLogController.destroy.url(props.mealLog.id), {
        onError: () => {
            isDeleting.value = false;
        },
    });
}
</script>

<template>
    <Head title="Modifica pasto" />

    <div class="mx-auto w-full max-w-lg flex-1 p-4">
        <div class="mb-8 flex items-start justify-between gap-4">
            <Heading
                title="Modifica pasto"
                :description="mealLog.description"
            />

            <Dialog>
                <DialogTrigger as-child>
                    <Button
                        type="button"
                        variant="ghost"
                        class="text-destructive hover:bg-destructive/10 hover:text-destructive"
                    >
                        Elimina
                    </Button>
                </DialogTrigger>

                <DialogContent>
                    <DialogTitle>Elimina pasto</DialogTitle>
                    <DialogDescription>
                        Vuoi eliminare "{{ mealLog.description }}" dal
                        registro? L'operazione non è reversibile.
                    </DialogDescription>
                    <DialogFooter class="gap-2">
                        <DialogClose as-child>
                            <Button variant="secondary">Annulla</Button>
                        </DialogClose>
                        <Button
                            variant="destructive"
                            :disabled="isDeleting"
                            @click="destroy"
                        >
                            {{ isDeleting ? 'Eliminazione...' : 'Elimina' }}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>

        <form class="space-y-6" @submit.prevent="submit">
            <div class="grid gap-2">
                <Label class="text-base" for="date">Data</Label>
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
                <Label class="text-base">Tipo pasto</Label>
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

            <div v-if="meal !== null" class="grid gap-2">
                <Label class="text-base">Descrizione</Label>
                <p class="text-lg">{{ mealLog.description }}</p>
            </div>
            <div v-else class="grid gap-2">
                <Label class="text-base" for="description">
                    Descrizione
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
                <Label class="text-base" for="weight_grams">Peso (g)</Label>
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
                    {{ formatDecimal(scaledPreview.protein_grams) }}g proteine
                </p>
            </div>

            <div class="grid gap-2">
                <Label class="text-base" for="cost">Costo (€, opzionale)</Label>
                <Input
                    id="cost"
                    v-model.number="form.cost"
                    type="number"
                    min="0"
                    max="9999.99"
                    step="0.01"
                    class="h-12 text-lg"
                    placeholder="es. 8,50"
                />
                <InputError :message="form.errors.cost" />
            </div>

            <template v-if="meal === null">
                <div class="grid gap-2">
                    <Label class="text-base" for="calories">
                        Calorie (kcal)
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
                        Proteine (g)
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
            </template>

            <Button
                type="submit"
                size="lg"
                class="h-12 w-full text-base"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Salvataggio...' : 'Salva modifiche' }}
            </Button>
        </form>
    </div>
</template>
