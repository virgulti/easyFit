<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGoalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Every threshold is optional: a null value means the user hasn't set that goal, and the
     * corresponding comparison is hidden in the UI rather than shown against no target.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'max_fat_percentage' => ['nullable', 'numeric', 'between:1,80'],
            'min_protein_grams' => ['nullable', 'integer', 'between:0,500'],
            'max_calories_per_day' => ['nullable', 'integer', 'between:0,10000'],
            'max_calories_per_week' => ['nullable', 'integer', 'between:0,70000'],
        ];
    }
}
