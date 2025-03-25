<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
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
        ];
    }

    public function messages()
    {
        return [
            'dataPerPage.integer' => 'The data per page count must be an integer',
            'page.integer' => 'The page number must be an integer'
        ];
    }
}
