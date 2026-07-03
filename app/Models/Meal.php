<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MealType;
use Database\Factories\MealFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $description
 * @property MealType $meal_type
 * @property int $reference_weight_grams
 * @property int $calories
 * @property string $protein_grams
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'user_id',
    'description',
    'meal_type',
    'reference_weight_grams',
    'calories',
    'protein_grams',
])]
class Meal extends Model
{
    /** @use HasFactory<MealFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'meal_type' => MealType::class,
            'reference_weight_grams' => 'integer',
            'calories' => 'integer',
            'protein_grams' => 'decimal:1',
        ];
    }

    /**
     * Get the user that owns the meal.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scale this meal's calories and protein proportionally to a target weight, for when a
     * meal is registered with a different weight than its catalog reference_weight_grams.
     *
     * @return array{calories: int, protein_grams: float}
     */
    public function scaledTo(int $targetWeightGrams): array
    {
        $ratio = $targetWeightGrams / $this->reference_weight_grams;

        return [
            'calories' => (int) round($this->calories * $ratio),
            'protein_grams' => round((float) $this->protein_grams * $ratio, 1),
        ];
    }
}
