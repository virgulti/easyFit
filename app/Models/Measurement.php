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
     * The user's most recent measurement strictly before this one, or null if this is the
     * first measurement on record.
     */
    public function previous(): ?self
    {
        return static::query()
            ->where('user_id', $this->user_id)
            ->where('date', '<', $this->date)
            ->orderByDesc('date')
            ->first();
    }

    /**
     * Whether this measurement's BMI improved (is lower) compared to the previous
     * measurement of the same user, ordered by date. Null when there is no previous measurement.
     */
    public function improvement(): ?bool
    {
        $previous = $this->previous();

        if ($previous === null) {
            return null;
        }

        return (float) $this->bmi_value < (float) $previous->bmi_value;
    }

    /**
     * Per-metric trend feedback (direction + color) against the previous measurement, for the
     * summary page indicators. Null when there is no previous measurement to compare against;
     * the `sleep_minutes`/`bedtime` entries are individually null when either measurement is
     * missing that optional value.
     *
     * @return array<string, array{direction: 'up'|'down'|'stable', color: 'green'|'red'|'gray'}|null>|null
     */
    public function trends(): ?array
    {
        $previous = $this->previous();

        if ($previous === null) {
            return null;
        }

        return [
            'weight' => self::trendFor((float) $this->weight, (float) $previous->weight, higherIsBetter: false),
            'fat_perc' => self::trendFor((float) $this->fat_perc, (float) $previous->fat_perc, higherIsBetter: false),
            'muscle_perc' => self::trendFor((float) $this->muscle_perc, (float) $previous->muscle_perc, higherIsBetter: true),
            'bmi_value' => self::trendFor((float) $this->bmi_value, (float) $previous->bmi_value, higherIsBetter: false),
            'progress' => self::trendFor($this->progress, $previous->progress, higherIsBetter: true),
            'bmi_progress' => self::trendFor($this->bmiProgress, $previous->bmiProgress, higherIsBetter: true),
            'fat_weight' => self::trendFor($this->fatWeight, $previous->fatWeight, higherIsBetter: false),
            'muscle_weight' => self::trendFor($this->muscleWeight, $previous->muscleWeight, higherIsBetter: true),
            'sleep_minutes' => $this->sleep_minutes !== null && $previous->sleep_minutes !== null
                ? self::trendFor($this->sleep_minutes / 60, $previous->sleep_minutes / 60, higherIsBetter: true)
                : null,
            'bedtime' => $this->bedtime !== null && $previous->bedtime !== null
                ? self::trendFor(self::bedtimeEarliness($this->bedtime), self::bedtimeEarliness($previous->bedtime), higherIsBetter: true)
                : null,
        ];
    }

    /**
     * Classify a metric's change since the previous measurement. A difference under 0.4 (in the
     * metric's own comparison unit — hours for sleep/bedtime, otherwise the value's natural
     * unit) is considered noise and reported as "stable" rather than up/down.
     *
     * @return array{direction: 'up'|'down'|'stable', color: 'green'|'red'|'gray'}
     */
    private static function trendFor(float $current, float $previous, bool $higherIsBetter): array
    {
        $delta = $current - $previous;

        if (abs($delta) < 0.4) {
            return ['direction' => 'stable', 'color' => 'gray'];
        }

        $direction = $delta > 0 ? 'up' : 'down';
        $improved = $higherIsBetter ? $delta > 0 : $delta < 0;

        return ['direction' => $direction, 'color' => $improved ? 'green' : 'red'];
    }

    /**
     * Bedtime as an "earliness" score in hours (higher = earlier), anchored on noon so that
     * times after midnight (e.g. 01:00) still sort as later than evening times (e.g. 21:00).
     */
    private static function bedtimeEarliness(string $time): float
    {
        [$hour, $minute] = array_map('intval', explode(':', $time));
        $minutesSinceNoon = ($hour * 60 + $minute - 720 + 1440) % 1440;

        return -($minutesSinceNoon / 60);
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
