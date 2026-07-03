<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MealType;
use Database\Factories\MealLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $meal_id
 * @property string $description
 * @property MealType $meal_type
 * @property int $weight_grams
 * @property int $calories
 * @property string $protein_grams
 * @property Carbon $date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'user_id',
    'meal_id',
    'description',
    'meal_type',
    'weight_grams',
    'calories',
    'protein_grams',
    'date',
])]
class MealLog extends Model
{
    /** @use HasFactory<MealLogFactory> */
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
            'weight_grams' => 'integer',
            'calories' => 'integer',
            'protein_grams' => 'decimal:1',
            'date' => 'date',
        ];
    }

    /**
     * Get the user that owns the registered meal.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the catalog meal this log was created from, if any.
     *
     * @return BelongsTo<Meal, $this>
     */
    public function meal(): BelongsTo
    {
        return $this->belongsTo(Meal::class);
    }
}
