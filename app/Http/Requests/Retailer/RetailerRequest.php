<?php

namespace App\Http\Requests\Retailer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RetailerRequest extends FormRequest
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
          'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('retailers', 'title')->ignore($this->route('retailer')),
            ],
            'url' => 'required|string|url|max:255',
            'currency_id' => 'required|integer|exists:currencies,id',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif'
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Please write the title',
            'title.string' => 'Title must be a string',
            'title.unique' => 'Retailer with this title already exist',
            'title.max' => 'Title must be less than 255 characters',
            'url.required' => 'Please write the url to the retailer website',
            'url.string' => 'Url of the retailer website must be a string',
            'url.url' => 'Url of the retailer website must be valid',
            'url.max' => 'Url must be less than 255 characters',
            'currency_id.required' => 'Please choose the currency',
            'currency_id.integer' => 'Please choose the currency',
            'currency_id.exists' => 'It seems like this currency does not exist',
            'logo.image' => 'Logo file must be a valid image.',
            'logo.mimes' => 'Supported image formats: jpeg, png, jpg, gif.',
        ];
    }
}
