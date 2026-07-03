<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\MealType;
use App\Models\MealLog;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMealLogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var MealLog $mealLog */
        $mealLog = $this->route('meal_log');

        return $this->user()?->can('update', $mealLog) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * `meal_id` is fixed at creation and cannot be changed. When the entry came from the
     * catalog, description/calories/protein stay derived from the catalog meal (recomputed by
     * the controller if the weight changes); only "pasto inusuale" entries (meal_id null) allow
     * editing those values directly.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var MealLog $mealLog */
        $mealLog = $this->route('meal_log');

        $rules = [
            'date' => ['sometimes', 'required', 'date'],
            'meal_type' => ['sometimes', 'required', Rule::enum(MealType::class)],
            'weight_grams' => ['sometimes', 'required', 'integer', 'min:1', 'max:5000'],
            'cost' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:9999.99'],
        ];

        if ($mealLog->meal_id === null) {
            $rules['description'] = ['sometimes', 'required', 'string', 'max:255'];
            $rules['calories'] = ['sometimes', 'required', 'integer', 'min:0', 'max:10000'];
            $rules['protein_grams'] = ['sometimes', 'required', 'numeric', 'min:0', 'max:500'];
        }

        return $rules;
    }
}
