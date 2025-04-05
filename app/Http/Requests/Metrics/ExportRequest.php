<?php

namespace App\Http\Requests\Metrics;

use Illuminate\Foundation\Http\FormRequest;

class ExportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'retailers' => 'nullable|array',
            'retailers.*' => 'nullable|integer|exists:retailers,id'
        ];
    }

    public function messages()
    {
        return [
            'start_date.date' => 'The data per page count must be an integer.',
            'end_date.date' => 'The page number must be an integer.',
            'retailers.*' => 'It seems like this retailer does not exist.'  
        ];
    }
}
