<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * @var array<string, string>
     */
    private array $map = [
        'colazione' => 'breakfast',
        'pranzo' => 'lunch',
        'cena' => 'dinner',
        'spuntino' => 'snack',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->map as $italian => $english) {
            DB::table('meals')->where('meal_type', $italian)->update(['meal_type' => $english]);
            DB::table('meal_logs')->where('meal_type', $italian)->update(['meal_type' => $english]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->map as $italian => $english) {
            DB::table('meals')->where('meal_type', $english)->update(['meal_type' => $italian]);
            DB::table('meal_logs')->where('meal_type', $english)->update(['meal_type' => $italian]);
        }
    }
};
