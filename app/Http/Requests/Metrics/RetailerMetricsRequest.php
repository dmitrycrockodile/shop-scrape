<?php

namespace App\Http\Requests\Metrics;

use Illuminate\Foundation\Http\FormRequest;

class RetailerMetricsRequest extends FormRequest
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
            'dataPerPage' => 'nullable|integer',
            'page' => 'nullable|integer',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'nullable|integer|exists:products,id',
            'manufacturer_part_numbers' => 'nullable|array',
            'manufacturer_part_numbers.*' => 'nullable|string|exists:products,manufacturer_part_number',
            'retailers' => 'nullable|array',
            'retailers.*' => 'nullable|integer|exists:retailers,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ];
    }

    public function messages()
    {
        return [
            'dataPerPage.integer' => 'The data per page count must be an integer',
            'page.integer' => 'The page number must be an integer',
            'product_ids.array' => 'Product IDs must be sent as an array.',
            'product_ids.*.integer' => 'Product ID must be an integer',
            'product_ids.*.exists' => 'It seems like this product does not exist',
            'manufacturer_part_numbers.array' => 'MPNs must be sent as an array.',
            'manufacturer_part_numbers.*.string' => 'MPN must be a string',
            'manufacturer_part_numbers.*.exists' => 'It seems like product with this MPN does not exist',
            'retailers.array' => 'Retailer IDs must be sent as an array.',
            'retailers.*.integer' => 'Retailer ID must be an integer',
            'retailers.*.exists' => 'It seems like this retailer does not exist',
            'start_date.date' => 'Start date value must be a valid date',
            'end_date.date' => 'End date value must be a valid date'
        ];
    }
}
