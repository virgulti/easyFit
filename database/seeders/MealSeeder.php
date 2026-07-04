<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\MealType;
use App\Models\Meal;
use App\Models\MealLog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class MealSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'demo@easyfit.test'],
            [
                'name' => 'Demo',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        $meals = [
            // Breakfast
            ['description' => 'Greek yogurt with honey and walnuts', 'meal_type' => MealType::Breakfast, 'reference_weight_grams' => 150, 'calories' => 180, 'protein_grams' => 15.0],
            ['description' => 'Toast with jam', 'meal_type' => MealType::Breakfast, 'reference_weight_grams' => 60, 'calories' => 240, 'protein_grams' => 3.0],
            ['description' => 'Scrambled eggs with wholemeal bread', 'meal_type' => MealType::Breakfast, 'reference_weight_grams' => 200, 'calories' => 320, 'protein_grams' => 18.0],
            ['description' => 'Oatmeal porridge with fruit', 'meal_type' => MealType::Breakfast, 'reference_weight_grams' => 120, 'calories' => 160, 'protein_grams' => 5.0],
            ['description' => 'Pancakes with berries', 'meal_type' => MealType::Breakfast, 'reference_weight_grams' => 150, 'calories' => 320, 'protein_grams' => 8.0],
            ['description' => 'Banana protein smoothie', 'meal_type' => MealType::Breakfast, 'reference_weight_grams' => 250, 'calories' => 220, 'protein_grams' => 20.0],

            // Lunch
            ['description' => 'Pasta with tomato sauce', 'meal_type' => MealType::Lunch, 'reference_weight_grams' => 300, 'calories' => 450, 'protein_grams' => 15.0],
            ['description' => 'Chicken breast with rice and vegetables', 'meal_type' => MealType::Lunch, 'reference_weight_grams' => 350, 'calories' => 500, 'protein_grams' => 40.0],
            ['description' => 'Tuna and bean salad', 'meal_type' => MealType::Lunch, 'reference_weight_grams' => 250, 'calories' => 380, 'protein_grams' => 25.0],
            ['description' => 'Mushroom risotto', 'meal_type' => MealType::Lunch, 'reference_weight_grams' => 300, 'calories' => 420, 'protein_grams' => 10.0],
            ['description' => 'Salmon poke bowl', 'meal_type' => MealType::Lunch, 'reference_weight_grams' => 350, 'calories' => 480, 'protein_grams' => 28.0],
            ['description' => 'Turkey rolls with vegetables', 'meal_type' => MealType::Lunch, 'reference_weight_grams' => 280, 'calories' => 350, 'protein_grams' => 35.0],

            // Dinner
            ['description' => 'Baked salmon with potatoes', 'meal_type' => MealType::Dinner, 'reference_weight_grams' => 300, 'calories' => 450, 'protein_grams' => 35.0],
            ['description' => 'Lentil soup', 'meal_type' => MealType::Dinner, 'reference_weight_grams' => 250, 'calories' => 280, 'protein_grams' => 12.0],
            ['description' => 'Steak with salad', 'meal_type' => MealType::Dinner, 'reference_weight_grams' => 250, 'calories' => 400, 'protein_grams' => 38.0],
            ['description' => 'Vegetable frittata', 'meal_type' => MealType::Dinner, 'reference_weight_grams' => 200, 'calories' => 250, 'protein_grams' => 15.0],
            ['description' => 'Grilled chicken with vegetables', 'meal_type' => MealType::Dinner, 'reference_weight_grams' => 300, 'calories' => 380, 'protein_grams' => 42.0],

            // Snack
            ['description' => 'Protein bar', 'meal_type' => MealType::Snack, 'reference_weight_grams' => 40, 'calories' => 150, 'protein_grams' => 15.0],
            ['description' => 'Mixed nuts', 'meal_type' => MealType::Snack, 'reference_weight_grams' => 50, 'calories' => 280, 'protein_grams' => 6.0],
            ['description' => 'Apple', 'meal_type' => MealType::Snack, 'reference_weight_grams' => 150, 'calories' => 80, 'protein_grams' => 1.0],
            ['description' => 'Low-fat yogurt', 'meal_type' => MealType::Snack, 'reference_weight_grams' => 125, 'calories' => 100, 'protein_grams' => 12.0],
            ['description' => 'Wholemeal crackers', 'meal_type' => MealType::Snack, 'reference_weight_grams' => 30, 'calories' => 130, 'protein_grams' => 3.0],
        ];

        // Kept only to pick a plausible meal_type below when generating meal logs from these
        // catalog entries: meal_type belongs to the logging event, not the catalog meal itself.
        $mealTypeByDescription = collect($meals)->pluck('meal_type', 'description');

        foreach ($meals as $mealData) {
            Meal::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'description' => $mealData['description'],
                ],
                [
                    'reference_weight_grams' => $mealData['reference_weight_grams'],
                    'calories' => $mealData['calories'],
                    'protein_grams' => $mealData['protein_grams'],
                ]
            );
        }

        $catalogMeals = Meal::where('user_id', $user->id)->get();

        $unusualMeals = [
            ['description' => 'Drinks at the bar', 'meal_type' => MealType::Dinner, 'weight_grams' => 150, 'calories' => 350, 'protein_grams' => 8.0],
            ['description' => 'Dinner out with friends', 'meal_type' => MealType::Dinner, 'weight_grams' => 300, 'calories' => 500, 'protein_grams' => 20.0],
            ['description' => 'Ice cream', 'meal_type' => MealType::Snack, 'weight_grams' => 100, 'calories' => 200, 'protein_grams' => 3.0],
            ['description' => 'Homemade pizza', 'meal_type' => MealType::Dinner, 'weight_grams' => 250, 'calories' => 450, 'protein_grams' => 15.0],
            ['description' => 'Seafood salad', 'meal_type' => MealType::Lunch, 'weight_grams' => 200, 'calories' => 300, 'protein_grams' => 25.0],
        ];

        $daysTotal = 89;

        for ($daysAgo = $daysTotal; $daysAgo >= 0; $daysAgo--) {
            if (random_int(1, 100) <= 15) {
                continue;
            }

            $date = Carbon::today()->subDays($daysAgo);
            $logsCount = random_int(2, 4);

            for ($i = 0; $i < $logsCount; $i++) {
                if (random_int(1, 100) <= 85) {
                    $meal = $catalogMeals->random();

                    if (random_int(1, 4) === 1) {
                        $weightVariation = random_int(80, 130) / 100;
                        $targetWeightGrams = (int) round($meal->reference_weight_grams * $weightVariation);
                        $scaledValues = $meal->scaledTo($targetWeightGrams);

                        MealLog::create([
                            'user_id' => $user->id,
                            'meal_id' => $meal->id,
                            'description' => $meal->description,
                            'meal_type' => $mealTypeByDescription[$meal->description],
                            'weight_grams' => $targetWeightGrams,
                            'calories' => $scaledValues['calories'],
                            'protein_grams' => $scaledValues['protein_grams'],
                            'date' => $date,
                        ]);
                    } else {
                        MealLog::create([
                            'user_id' => $user->id,
                            'meal_id' => $meal->id,
                            'description' => $meal->description,
                            'meal_type' => $mealTypeByDescription[$meal->description],
                            'weight_grams' => $meal->reference_weight_grams,
                            'calories' => $meal->calories,
                            'protein_grams' => $meal->protein_grams,
                            'date' => $date,
                        ]);
                    }
                } else {
                    $unusualMeal = $unusualMeals[array_rand($unusualMeals)];

                    MealLog::create([
                        'user_id' => $user->id,
                        'meal_id' => null,
                        'description' => $unusualMeal['description'],
                        'meal_type' => $unusualMeal['meal_type'],
                        'weight_grams' => $unusualMeal['weight_grams'],
                        'calories' => $unusualMeal['calories'],
                        'protein_grams' => $unusualMeal['protein_grams'],
                        'date' => $date,
                    ]);
                }
            }
        }
    }
}
