export type MealType = 'colazione' | 'pranzo' | 'cena' | 'spuntino';

export type Meal = {
    id: number;
    description: string;
    meal_type: MealType;
    reference_weight_grams: number;
    calories: number;
    protein_grams: string;
};

export type MealLog = {
    id: number;
    meal_id: number | null;
    description: string;
    meal_type: MealType;
    weight_grams: number;
    calories: number;
    protein_grams: string;
    date: string;
};

export type MealTotals = {
    calories: number;
    protein_grams: number;
};

export type MealThresholds = {
    min_protein_grams: number | null;
    max_calories_per_day: number | null;
};
