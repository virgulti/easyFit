<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\GoalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $max_fat_percentage
 * @property int|null $min_protein_grams
 * @property int|null $max_calories_per_day
 * @property int|null $max_calories_per_week
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'user_id',
    'max_fat_percentage',
    'min_protein_grams',
    'max_calories_per_day',
    'max_calories_per_week',
])]
class Goal extends Model
{
    /** @use HasFactory<GoalFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'max_fat_percentage' => 'decimal:1',
            'min_protein_grams' => 'integer',
            'max_calories_per_day' => 'integer',
            'max_calories_per_week' => 'integer',
        ];
    }

    /**
     * Get the user that owns the goal.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
