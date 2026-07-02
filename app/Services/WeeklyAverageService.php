<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

final class WeeklyAverageService
{
    /**
     * @return array<int, array{
     *     week_start: string,
     *     week_end: string,
     *     days_count: int,
     *     weight: float,
     *     fat_perc: float,
     *     muscle_perc: float,
     *     bmi_value: float,
     *     progress: float,
     *     bmi_progress: float,
     *     fat_weight: float,
     *     muscle_weight: float,
     * }>
     */
    public function averagesForUser(User $user): array
    {
        $measurements = $user->measurements;

        $groupedMeasurements = $measurements->groupBy(function ($measurement) {
            return $measurement->date->startOfWeek(CarbonInterface::MONDAY)->toDateString();
        });

        $averages = $groupedMeasurements->map(function (Collection $group, string $weekStart) {
            $weekEnd = Carbon::parse($weekStart)->addDays(6)->toDateString();

            return [
                'week_start' => $weekStart,
                'week_end' => $weekEnd,
                'days_count' => $group->count(),
                'weight' => round($group->avg(fn ($m) => (float) $m->weight), 2),
                'fat_perc' => round($group->avg(fn ($m) => (float) $m->fat_perc), 2),
                'muscle_perc' => round($group->avg(fn ($m) => (float) $m->muscle_perc), 2),
                'bmi_value' => round($group->avg(fn ($m) => (float) $m->bmi_value), 2),
                'progress' => round($group->avg(fn ($m) => $m->progress), 2),
                'bmi_progress' => round($group->avg(fn ($m) => $m->bmiProgress), 2),
                'fat_weight' => round($group->avg(fn ($m) => $m->fatWeight), 2),
                'muscle_weight' => round($group->avg(fn ($m) => $m->muscleWeight), 2),
            ];
        })->sortBy('week_start')->values()->all();

        return $averages;
    }
}
