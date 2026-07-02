<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\MeasurementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
 * @property-read float $progress
 * @property-read float $bmiProgress
 * @property-read float $fatWeight
 * @property-read float $muscleWeight
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

    /**
     * Progress score: improves as weight approaches (and drops below) the configured max weight.
     */
    /**
     * @return Attribute<float, never>
     */
    protected function progress(): Attribute
    {
        return Attribute::make(
            get: fn (): float => round(
                (config('easyfit.max_weight') - (float) $this->weight) * config('easyfit.con_progress')
                    + config('easyfit.offset_progress'),
                1,
            ),
        );
    }

    /**
     * BMI progress score: improves as BMI approaches (and drops below) the configured max BMI.
     */
    /**
     * @return Attribute<float, never>
     */
    protected function bmiProgress(): Attribute
    {
        return Attribute::make(
            get: fn (): float => round(
                (config('easyfit.max_bmi') - (float) $this->bmi_value) * config('easyfit.con_bmi')
                    + config('easyfit.offset_bmi'),
                1,
            ),
        );
    }

    /**
     * Weight of body fat in kg, derived from weight and fat percentage.
     */
    /**
     * @return Attribute<float, never>
     */
    protected function fatWeight(): Attribute
    {
        return Attribute::make(
            get: fn (): float => round((float) $this->weight * (float) $this->fat_perc / 100, 2),
        );
    }

    /**
     * Weight of muscle mass in kg, derived from weight and muscle percentage.
     */
    /**
     * @return Attribute<float, never>
     */
    protected function muscleWeight(): Attribute
    {
        return Attribute::make(
            get: fn (): float => round((float) $this->weight * (float) $this->muscle_perc / 100, 2),
        );
    }

    /**
     * Whether this measurement's BMI improved (is lower) compared to the previous
     * measurement of the same user, ordered by date. Null when there is no previous measurement.
     */
    public function improvement(): ?bool
    {
        $previous = static::query()
            ->where('user_id', $this->user_id)
            ->where('date', '<', $this->date)
            ->orderByDesc('date')
            ->first();

        if ($previous === null) {
            return null;
        }

        return (float) $this->bmi_value < (float) $previous->bmi_value;
    }

    /**
     * Get the PLAN color band for this measurement's BMI.
     *
     * @return array{label: string, color: string}
     */
    public function bmiBand(): array
    {
        $bmi = (float) $this->bmi_value;

        return match (true) {
            $bmi < 18 => ['label' => 'Underweight', 'color' => 'gray'],
            $bmi < 23 => ['label' => 'Normal', 'color' => 'green'],
            $bmi < 25 => ['label' => 'Normal', 'color' => 'yellow'],
            $bmi < 29 => ['label' => 'Overweight', 'color' => 'dark-yellow'],
            $bmi < 30 => ['label' => 'Overweight', 'color' => 'orange'],
            default => ['label' => 'Obese', 'color' => 'red'],
        };
    }
}
