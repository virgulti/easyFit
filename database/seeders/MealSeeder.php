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
            // Colazione
            ['description' => 'Yogurt greco con miele e noci', 'meal_type' => MealType::Colazione, 'reference_weight_grams' => 150, 'calories' => 180, 'protein_grams' => 15.0],
            ['description' => 'Fette biscottate con marmellata', 'meal_type' => MealType::Colazione, 'reference_weight_grams' => 60, 'calories' => 240, 'protein_grams' => 3.0],
            ['description' => 'Uova strapazzate con pane integrale', 'meal_type' => MealType::Colazione, 'reference_weight_grams' => 200, 'calories' => 320, 'protein_grams' => 18.0],
            ['description' => 'Porridge di avena con frutta', 'meal_type' => MealType::Colazione, 'reference_weight_grams' => 120, 'calories' => 160, 'protein_grams' => 5.0],
            ['description' => 'Pancake con frutti di bosco', 'meal_type' => MealType::Colazione, 'reference_weight_grams' => 150, 'calories' => 320, 'protein_grams' => 8.0],
            ['description' => 'Smoothie proteico alla banana', 'meal_type' => MealType::Colazione, 'reference_weight_grams' => 250, 'calories' => 220, 'protein_grams' => 20.0],

            // Pranzo
            ['description' => 'Pasta al pomodoro', 'meal_type' => MealType::Pranzo, 'reference_weight_grams' => 300, 'calories' => 450, 'protein_grams' => 15.0],
            ['description' => 'Petto di pollo con riso e verdure', 'meal_type' => MealType::Pranzo, 'reference_weight_grams' => 350, 'calories' => 500, 'protein_grams' => 40.0],
            ['description' => 'Insalata di tonno e fagioli', 'meal_type' => MealType::Pranzo, 'reference_weight_grams' => 250, 'calories' => 380, 'protein_grams' => 25.0],
            ['description' => 'Risotto ai funghi', 'meal_type' => MealType::Pranzo, 'reference_weight_grams' => 300, 'calories' => 420, 'protein_grams' => 10.0],
            ['description' => 'Poke bowl con salmone', 'meal_type' => MealType::Pranzo, 'reference_weight_grams' => 350, 'calories' => 480, 'protein_grams' => 28.0],
            ['description' => 'Involtini di tacchino con verdure', 'meal_type' => MealType::Pranzo, 'reference_weight_grams' => 280, 'calories' => 350, 'protein_grams' => 35.0],

            // Cena
            ['description' => 'Salmone al forno con patate', 'meal_type' => MealType::Cena, 'reference_weight_grams' => 300, 'calories' => 450, 'protein_grams' => 35.0],
            ['description' => 'Zuppa di legumi', 'meal_type' => MealType::Cena, 'reference_weight_grams' => 250, 'calories' => 280, 'protein_grams' => 12.0],
            ['description' => 'Bistecca con insalata', 'meal_type' => MealType::Cena, 'reference_weight_grams' => 250, 'calories' => 400, 'protein_grams' => 38.0],
            ['description' => 'Frittata di verdure', 'meal_type' => MealType::Cena, 'reference_weight_grams' => 200, 'calories' => 250, 'protein_grams' => 15.0],
            ['description' => 'Pollo alla griglia con verdure', 'meal_type' => MealType::Cena, 'reference_weight_grams' => 300, 'calories' => 380, 'protein_grams' => 42.0],

            // Spuntino
            ['description' => 'Barretta proteica', 'meal_type' => MealType::Spuntino, 'reference_weight_grams' => 40, 'calories' => 150, 'protein_grams' => 15.0],
            ['description' => 'Frutta secca mista', 'meal_type' => MealType::Spuntino, 'reference_weight_grams' => 50, 'calories' => 280, 'protein_grams' => 6.0],
            ['description' => 'Mela', 'meal_type' => MealType::Spuntino, 'reference_weight_grams' => 150, 'calories' => 80, 'protein_grams' => 1.0],
            ['description' => 'Yogurt magro', 'meal_type' => MealType::Spuntino, 'reference_weight_grams' => 125, 'calories' => 100, 'protein_grams' => 12.0],
            ['description' => 'Crackers integrali', 'meal_type' => MealType::Spuntino, 'reference_weight_grams' => 30, 'calories' => 130, 'protein_grams' => 3.0],
        ];

        foreach ($meals as $mealData) {
            Meal::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'description' => $mealData['description'],
                ],
                [
                    'meal_type' => $mealData['meal_type'],
                    'reference_weight_grams' => $mealData['reference_weight_grams'],
                    'calories' => $mealData['calories'],
                    'protein_grams' => $mealData['protein_grams'],
                ]
            );
        }

        $catalogMeals = Meal::where('user_id', $user->id)->get();

        $unusualMeals = [
            ['description' => 'Aperitivo al bar', 'meal_type' => MealType::Cena, 'weight_grams' => 150, 'calories' => 350, 'protein_grams' => 8.0],
            ['description' => 'Cena fuori con amici', 'meal_type' => MealType::Cena, 'weight_grams' => 300, 'calories' => 500, 'protein_grams' => 20.0],
            ['description' => 'Gelato', 'meal_type' => MealType::Spuntino, 'weight_grams' => 100, 'calories' => 200, 'protein_grams' => 3.0],
            ['description' => 'Pizza a casa', 'meal_type' => MealType::Cena, 'weight_grams' => 250, 'calories' => 450, 'protein_grams' => 15.0],
            ['description' => 'Insalata di mare', 'meal_type' => MealType::Pranzo, 'weight_grams' => 200, 'calories' => 300, 'protein_grams' => 25.0],
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
                            'meal_type' => $meal->meal_type,
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
                            'meal_type' => $meal->meal_type,
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
