import type { Meal, MealType } from '@/types';

export const mealTypeLabels: Record<MealType, string> = {
    breakfast: 'Breakfast',
    lunch: 'Lunch',
    dinner: 'Dinner',
    snack: 'Snack',
};

export const mealTypes: MealType[] = ['breakfast', 'lunch', 'dinner', 'snack'];

/**
 * Scale a catalog meal's calories/protein/cost to a target weight, mirroring the
 * backend's Meal::scaledTo() for a live preview before the server recomputes
 * the authoritative values. Cost is null when the catalog meal has no
 * reference_cost.
 */
export function scaleMeal(
    meal: Meal,
    targetWeightGrams: number,
): { calories: number; protein_grams: number; cost: number | null } {
    const ratio = targetWeightGrams / meal.reference_weight_grams;

    return {
        calories: Math.round(meal.calories * ratio),
        protein_grams: Math.round(Number(meal.protein_grams) * ratio * 10) / 10,
        cost:
            meal.reference_cost === null
                ? null
                : Math.round(Number(meal.reference_cost) * ratio * 100) / 100,
    };
}

/**
 * Format a cost value in euros ("8.5" -> "€8.50").
 */
export function formatCost(value: string | number): string {
    return `€${Number(value).toFixed(2)}`;
}
