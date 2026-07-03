<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\MealType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMealLogRequest extends FormRequest
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
     * When `meal_id` is present the entry is registered from the catalog: description,
     * calories and protein are derived server-side from the catalog meal, not trusted from
     * the client. When `meal_id` is absent ("pasto inusuale") those values are required
     * directly from the client, with an optional `save_to_catalog` flag.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'date' => ['required', 'date'],
            'meal_type' => ['required', Rule::enum(MealType::class)],
            'weight_grams' => ['required', 'integer', 'min:1', 'max:5000'],
            'meal_id' => ['nullable', Rule::exists('meals', 'id')->where('user_id', $this->user()->id)],
            'description' => ['required_without:meal_id', 'string', 'max:255'],
            'calories' => ['required_without:meal_id', 'integer', 'min:0', 'max:10000'],
            'protein_grams' => ['required_without:meal_id', 'numeric', 'min:0', 'max:500'],
            'save_to_catalog' => ['sometimes', 'boolean'],
        ];

        if (empty($this->input('meal_id')) && $this->boolean('save_to_catalog')) {
            $rules['description'][] = Rule::unique('meals', 'description')->where('user_id', $this->user()->id);
        }

        return $rules;
    }
}
