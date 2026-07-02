<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Models\Measurement;
use Closure;

trait MeasurementValidationRules
{
    /**
     * Get a validation rule closure that fails when the authenticated user already has a
     * measurement for the given date.
     *
     * The `date` column is stored with a full datetime component (Laravel's default date-cast
     * storage format), so a plain string comparison would never match; `whereDate()` normalizes
     * the stored value before comparing.
     */
    protected function uniqueMeasurementDateRule(?Measurement $ignoring = null): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($ignoring): void {
            $exists = Measurement::query()
                ->where('user_id', $this->user()->id)
                ->whereDate('date', $value)
                ->when($ignoring, fn ($query) => $query->whereKeyNot($ignoring))
                ->exists();

            if ($exists) {
                $fail('A measurement already exists for this date.');
            }
        };
    }
}
