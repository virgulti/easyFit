<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Concerns\MeasurementValidationRules;
use App\Models\Measurement;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMeasurementRequest extends FormRequest
{
    use MeasurementValidationRules;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var Measurement $measurement */
        $measurement = $this->route('measurement');

        return $this->user()?->can('update', $measurement) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date' => [
                'sometimes',
                'required',
                'date',
                $this->uniqueMeasurementDateRule($this->route('measurement')),
            ],
            'weight' => ['sometimes', 'required', 'numeric', 'between:30,300'],
            'fat_perc' => ['sometimes', 'required', 'numeric', 'between:1,80'],
            'muscle_perc' => ['sometimes', 'required', 'numeric', 'between:1,80'],
            'bmi_value' => ['sometimes', 'required', 'numeric', 'between:10,60'],
            'bedtime' => ['nullable', 'date_format:H:i'],
            'sleep_minutes' => ['nullable', 'integer', 'between:0,1440'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
