<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\MeasurementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property Carbon $date
 * @property string $weight
 * @property string $fat_perc
 * @property string $muscle_perc
 * @property string $bmi_value
 * @property string|null $bedtime
 * @property int|null $sleep_minutes
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'user_id',
    'date',
    'weight',
    'fat_perc',
    'muscle_perc',
    'bmi_value',
    'bedtime',
    'sleep_minutes',
    'notes',
])]
class Measurement extends Model
{
    /** @use HasFactory<MeasurementFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'weight' => 'decimal:1',
            'fat_perc' => 'decimal:1',
            'muscle_perc' => 'decimal:1',
            'bmi_value' => 'decimal:1',
            'sleep_minutes' => 'integer',
        ];
    }

    /**
     * Get the user that owns the measurement.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
