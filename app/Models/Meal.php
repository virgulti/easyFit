<?php

declare(strict_types=1);

namespace App\Models;

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
 * @property int $reference_weight_grams
 * @property int $calories
 * @property string $protein_grams
 * @property string|null $reference_cost
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'user_id',
    'description',
    'reference_weight_grams',
    'calories',
    'protein_grams',
    'reference_cost',
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
            'reference_weight_grams' => 'integer',
            'calories' => 'integer',
            'protein_grams' => 'decimal:1',
            'reference_cost' => 'decimal:2',
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
     * Scale this meal's calories, protein and cost proportionally to a target weight, for when
     * a meal is registered with a different weight than its catalog reference_weight_grams. Cost
     * is null when the catalog meal has no reference_cost.
     *
     * @return array{calories: int, protein_grams: float, cost: float|null}
     */
    public function scaledTo(int $targetWeightGrams): array
    {
        $ratio = $targetWeightGrams / $this->reference_weight_grams;

        return [
            'calories' => (int) round($this->calories * $ratio),
            'protein_grams' => round((float) $this->protein_grams * $ratio, 1),
            'cost' => $this->reference_cost === null ? null : round((float) $this->reference_cost * $ratio, 2),
        ];
    }
}
