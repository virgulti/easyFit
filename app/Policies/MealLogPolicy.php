<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MealLog;
use App\Models\User;

class MealLogPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MealLog $mealLog): bool
    {
        return $user->id === $mealLog->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MealLog $mealLog): bool
    {
        return $user->id === $mealLog->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MealLog $mealLog): bool
    {
        return $user->id === $mealLog->user_id;
    }
}
