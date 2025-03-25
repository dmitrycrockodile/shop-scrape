<?php

namespace App\Http\Requests\ScrapedData;

use Illuminate\Foundation\Http\FormRequest;

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
            'mpn' => 'required|string|exists:products,manufacturer_part_number',
            'product_retailer_id' => 'required|integer|exists:product_retailers,id',
            'scraping_session_id' => 'required|integer|exists:scraping_sessions,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock_count' => 'required|integer|min:0',
            'avg_rating' => 'required|numeric|min:0',
            'images' => 'nullable|array',
            'images.*.url' => 'required|image|mimes:jpeg,png,jpg,gif',
            'images.*.name' => 'required|string|max:255',
            'images.*.position' => 'required|integer|min:0',
            'ratings' => 'required|array',
            'ratings.one_star' => 'required|integer|min:0',
            'ratings.two_stars' => 'required|integer|min:0',
            'ratings.three_stars' => 'required|integer|min:0',
            'ratings.four_stars' => 'required|integer|min:0',
            'ratings.five_stars' => 'required|integer|min:0',
        ];
    }

    public function messages()
    {
        return [
            'product_retailer_id.required' => 'Please add the product-retailer ID',
            'product_retailer_id.integer' => 'Product-retailer ID must be an integer',
            'product_retailer_id.exists' => 'No retailers with provided ID contains products with provided ID',
            'title.required' => 'Please add the title',
            'title.string' => 'Title must be a valid string',
            'title.max' => 'Title must be less than 255 characters',
            'description.required' => 'Please add the description',
            'description.string' => 'Description must be a valid string',
            'price.required' => 'Please add the price',
            'price.numeric' => 'Price must be a number',
            'price.min' => 'Price must be a positive number',
            'stock_count.required' => 'Please add the stock count',
            'stock_count.integer' => 'Stock count must be a number',
            'stock_count.min' => 'Stock count must be a positive number',
            'avg_rating.required' => 'Please add the average rating',
            'avg_rating.numeric' => 'Average rating must be a number',
            'avg_rating.min' => 'Average rating must be a positive number',
            'scraping_session_id.required' => 'Please add the scraping session ID',
            'scraping_session_id.integer' => 'Scraping session ID must be an integer',
            'scraping_session_id.exists' => 'There is no scraping session with provided ID',
            'images.required' => 'Please upload at least one image.',
            'images.array' => 'Images must be sent as an array.',
            'images.*.url.required' => 'Please upload an image',
            'images.*.url.image' => 'Each file must be a valid image.',
            'images.*.url.mimes' => 'Supported image formats: jpeg, png, jpg, gif.',
            'images.*.name.required' => 'Image name is required.',
            'images.*.name.string' => 'Image name must be a valid string',
            'images.*.name.max' => 'Image name must be less than 255 characters',
            'images.*.position.required' => 'Image position is required.',
            'images.*.position.integer' => 'Image position must be a number',
            'images.*.position.min' => 'Image position must be a positive number',
            'ratings.required' => 'Please upload the ratings.',
            'ratings.array' => 'Ratings must be sent as an array.',
            'ratings.one_star.required' => 'One stars count is required',
            'ratings.one_star.integer' => 'One stars count must be a valid integer',
            'ratings.one_star.min' => 'One stars count must be positive number',
            'ratings.two_stars.required' => 'Two stars count is required',
            'ratings.two_stars.integer' => 'Two stars count must be a valid integer',
            'ratings.two_stars.min' => 'Two stars count must be positive number',
            'ratings.three_stars.required' => 'Three stars count is required',
            'ratings.three_stars.integer' => 'Three stars count must be a valid integer',
            'ratings.three_stars.min' => 'Three stars count must be positive number',
            'ratings.four_stars.required' => 'Four stars count is required',
            'ratings.four_stars.integer' => 'Four stars count must be a valid integer',
            'ratings.four_stars.min' => 'Four stars count must be positive number',
            'ratings.five_stars.required' => 'Five stars count is required',
            'ratings.five_stars.integer' => 'Five stars count must be a valid integer',
            'ratings.five_stars.min' => 'Five stars count must be positive number'
        ];
    }
}
