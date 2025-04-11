<?php

namespace App\Http\Requests\ScrapedData;

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
            'startDate' => 'nullable|date',
            'endDate' => 'nullable|date',
            'retailer_ids' => 'nullable|array',
            'retailer_ids.*' => 'nullable|integer|exists:retailers,id',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'nullable|integer|exists:products,id'
        ];
    }

    public function messages()
    {
        return [
            'startDate.date' => 'The data per page count must be an integer.',
            'endDate.date' => 'The page number must be an integer.',
            'retailer_ids.*' => 'It seems like this retailer does not exist.',
            'product_ids.*' => 'It seems like this product does not exist.'  
        ];
    }
}
