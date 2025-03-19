<?php

namespace App\Http\Requests\PackSize;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('pack_sizes')
                    ->where('weight', $this->weight)
                    ->where('weight_unit', $this->weight_unit)
                    ->where('amount', $this->amount)
            ],
            'weight' => 'required|numeric|min:0',
            'weight_unit' => 'required|string|in:kg,g,l,ml|max:10',
            'amount' => 'required|integer',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Please write the pack size name',
            'name.string' => 'Pack size name must be a string',
            'name.max' => 'Pack size name must be less than 255 characters',
            'name.unique' => 'A pack size with the same name, weight, weight unit and amount already exists.',
            'weight.required' => 'Please write the pack size weight',
            'weight.numeric' => 'Pack size weight must be a number',
            'weight.min' => 'Pack size weight must be a positive number',
            'weight_unit.required' => 'Please add the weight unit',
            'weight_unit.string' => 'Weight unit must be a string',
            'weight_unit.in' => 'The weight unit must be one of the following: "kg", "g", "l", "ml"',
            'weight_unit.max' => 'The weight unit value length must be less than 10 characters',
            'amount.required' => 'Please choose the amount',
            'amount.integer' => 'Amount must be a valid integer',
        ];
   }
}
