<?php

namespace App\Http\Requests\Retailer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddProductsRequest extends FormRequest
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
        $retailerId = $this->route('retailer')->id;

        return [
            'products' => 'required|array',
            'products.*.id' => [
                'required',
                'integer',
                'exists:products,id',
                Rule::unique('product_retailers', 'product_id')->where(function ($query) use ($retailerId) {
                    $query->where('retailer_id', $retailerId); 
                })
            ],
            'products.*.url' => 'required|string|url|max:255'
        ];
    }

    public function messages()
    {
        return [
            'products.required' => 'Please add at least one product.',
            'products.array' => 'Products must be sent as an array.',
            'products.*.id.required' => 'Please add at least one product.',
            'products.*.id.integer' => 'Please add at least one product.',
            'products.*.id.exists' => 'There is no product with this ID.',
            'products.*.id.unique' => 'The retailer has this product already.',
            'products.*.url.required' => 'Please add the link to the product on your website',
            'products.*.url.string' => 'Url of the product must be a string',
            'products.*.url.url' => 'Url of the product must be valid',
            'products.*.url.max' => 'Url of the product must be less than 255 characters',
        ];
    }
}
