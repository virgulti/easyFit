<?php

declare(strict_types=1);

use App\Models\Measurement;
use App\Models\User;

test('a measurement can be created via the factory and persisted', function () {
    $user = User::factory()->create();

    $measurement = Measurement::factory()->for($user)->create();

    expect(Measurement::count())->toBe(1);

    $this->assertDatabaseHas('measurements', [
        'id' => $measurement->id,
        'user_id' => $user->id,
    ]);
});

test('easyfit config exposes the plan constants', function () {
    expect(config('easyfit.max_weight'))->toBe(85)
        ->and(config('easyfit.max_bmi'))->toBe(30)
        ->and(config('easyfit.con_progress'))->toBe(1.5)
        ->and(config('easyfit.con_bmi'))->toBe(4.5)
        ->and(config('easyfit.offset_progress'))->toBe(70)
        ->and(config('easyfit.offset_bmi'))->toBe(18);
});
