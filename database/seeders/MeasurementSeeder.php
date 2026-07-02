<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Measurement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class MeasurementSeeder extends Seeder
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

        $daysTotal = 89;

        for ($daysAgo = $daysTotal; $daysAgo >= 0; $daysAgo--) {
            if (random_int(1, 100) <= 15) {
                continue;
            }

            $date = Carbon::today()->subDays($daysAgo);
            $t = ($daysTotal - $daysAgo) / $daysTotal;

            $noise = random_int(-40, 40) / 100;
            $weight = round(92.0 + ($t * (86.0 - 92.0)) + $noise, 1);

            $noise2 = random_int(-30, 30) / 100;
            $fatPerc = round(30.0 + ($t * (27.0 - 30.0)) + $noise2, 1);

            $noise3 = random_int(-30, 30) / 100;
            $musclePerc = round(30.0 + ($t * (32.0 - 30.0)) + $noise3, 1);

            $bmiValue = round($weight / (1.78 * 1.78), 1);

            if (random_int(1, 100) <= 30) {
                $bedtime = null;
            } elseif (random_int(0, 1) === 0) {
                $bedtime = Carbon::createFromTime(random_int(22, 23), random_int(0, 59))->format('H:i');
            } else {
                $bedtime = Carbon::createFromTime(0, random_int(0, 30))->format('H:i');
            }

            $sleepMinutes = random_int(1, 100) <= 30 ? null : random_int(360, 540);

            Measurement::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'date' => $date,
                ],
                [
                    'weight' => $weight,
                    'fat_perc' => $fatPerc,
                    'muscle_perc' => $musclePerc,
                    'bmi_value' => $bmiValue,
                    'bedtime' => $bedtime,
                    'sleep_minutes' => $sleepMinutes,
                    'notes' => null,
                ]
            );
        }
    }
}
