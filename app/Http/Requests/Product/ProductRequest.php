<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'manufacturer_part_number' => 'required|string|max:255',
            'pack_size_id' => 'required|integer|exists:pack_sizes,id',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'image_urls' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Please write the title',
            'title.string' => 'Please write the title',
            'title.max' => 'Title must be less than 255 characters',
            'description.required' => 'Please write the description',
            'description.string' => 'Please write the description',
            'manufacturer_part_number.required' => 'Please add the MPN (Manufacturer Part Number)',
            'manufacturer_part_number.string' => 'MPN (Manufacturer Part Number) must be a string',
            'manufacturer_part_number.max' => 'MPN (Manufacturer Part Number) must be less than 255 characters',
            'pack_size_id.required' => 'Please choose the pack size',
            'pack_size_id.integer' => 'Please choose the pack size',
            'pack_size_id.exists' => 'It seems like this pack size does not exist',
            'images.required' => 'Please upload at least one image.',
            'images.array' => 'Images must be sent as an array.',
            'images.*.required' => 'Each uploaded file must be an image.',
            'images.*.image' => 'Each file must be a valid image.',
            'images.*.mimes' => 'Supported image formats: jpeg, png, jpg, gif.',
            'image_urls.string' => 'Image URLs must be sent as a string.'
        ];
    }
}
