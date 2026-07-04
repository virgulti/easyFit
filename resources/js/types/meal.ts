export type MealType = 'breakfast' | 'lunch' | 'dinner' | 'snack';

export type Meal = {
    id: number;
    description: string;
    reference_weight_grams: number;
    calories: number;
    protein_grams: string;
    reference_cost: string | null;
};

export type MealLog = {
    id: number;
    meal_id: number | null;
    description: string;
    meal_type: MealType;
    weight_grams: number;
    calories: number;
    protein_grams: string;
    cost: string | null;
    date: string;
};

export type MealTotals = {
    calories: number;
    protein_grams: number;
    cost: number | null;
};

export type MealThresholds = {
    min_protein_grams: number | null;
    max_calories_per_day: number | null;
};
