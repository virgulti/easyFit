<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Meal;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMealRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var Meal $meal */
        $meal = $this->route('meal');

        return $this->user()?->can('update', $meal) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Meal $meal */
        $meal = $this->route('meal');

        return [
            'description' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('meals', 'description')->where('user_id', $this->user()->id)->ignore($meal->id),
            ],
            'reference_weight_grams' => ['sometimes', 'required', 'integer', 'min:1', 'max:5000'],
            'calories' => ['sometimes', 'required', 'integer', 'min:0', 'max:10000'],
            'protein_grams' => ['sometimes', 'required', 'numeric', 'min:0', 'max:500'],
            'reference_cost' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:9999.99'],
        ];
    }
}
