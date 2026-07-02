<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Concerns\MeasurementValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMeasurementRequest extends FormRequest
{
    use MeasurementValidationRules;

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
            'date' => ['required', 'date', $this->uniqueMeasurementDateRule()],
            'weight' => ['required', 'numeric', 'between:30,300'],
            'fat_perc' => ['required', 'numeric', 'between:1,80'],
            'muscle_perc' => ['required', 'numeric', 'between:1,80'],
            'bmi_value' => ['required', 'numeric', 'between:10,60'],
            'bedtime' => ['nullable', 'date_format:H:i'],
            'sleep_minutes' => ['nullable', 'integer', 'between:0,1440'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
