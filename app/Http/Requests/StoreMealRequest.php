<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMealRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'description' => [
                'required',
                'string',
                'max:255',
                Rule::unique('meals', 'description')->where('user_id', $this->user()->id),
            ],
            'reference_weight_grams' => ['required', 'integer', 'min:1', 'max:5000'],
            'calories' => ['required', 'integer', 'min:0', 'max:10000'],
            'protein_grams' => ['required', 'numeric', 'min:0', 'max:500'],
            'reference_cost' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
        ];
    }
}
