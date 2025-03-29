<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class ManageRetailersRequest extends FormRequest
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
            'retailers' => 'required|array',
            'retailers.*.id' => 'required|integer|exists:retailers,id',
        ];
    }

    public function messages()
    {
        return [
            'retailers.required' => 'Please add at least one retailer ID.',
            'retailers.array' => 'Retailers must be sent as an array.',
            'retailers.*.id.required' => 'Please add at least one retailer ID.',
            'retailers.*.id.integer' => 'Retailer ID must be an integer.',
            'retailers.*.id.exists' => 'There is no retailer with this ID.'
        ];
    }
}
