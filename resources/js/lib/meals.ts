import type { Meal, MealType } from '@/types';

export const mealTypeLabels: Record<MealType, string> = {
    colazione: 'Colazione',
    pranzo: 'Pranzo',
    cena: 'Cena',
    spuntino: 'Spuntino',
};

export const mealTypes: MealType[] = ['colazione', 'pranzo', 'cena', 'spuntino'];

/**
 * Scale a catalog meal's calories/protein to a target weight, mirroring the
 * backend's Meal::scaledTo() for a live preview before the server recomputes
 * the authoritative values.
 */
export function scaleMeal(
    meal: Meal,
    targetWeightGrams: number,
): { calories: number; protein_grams: number } {
    const ratio = targetWeightGrams / meal.reference_weight_grams;

    return {
        calories: Math.round(meal.calories * ratio),
        protein_grams: Math.round(Number(meal.protein_grams) * ratio * 10) / 10,
    };
}
