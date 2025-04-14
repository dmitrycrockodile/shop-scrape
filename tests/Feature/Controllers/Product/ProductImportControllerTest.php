<?php

namespace Tests\Feature\Controllers\Product;

use App\Models\Currency;
use App\Models\Retailer;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductImportControllerTest extends TestCase 
{
    public function test_csv_import_for_create_and_update_return_error_when_retailer_is_not_exist()
    {
        $user = User::factory()->create([
            'role' => 'super_user'
        ]);
        Sanctum::actingAs($user, ['*']);

        $csvContent = <<<CSV
            action,title,description,manufacturer_part_number,pack_size_name,pack_size_weight,pack_size_weight_unit,pack_size_amount,retailer_title,image_urls,product_url,image_name,product_id
            create,Laptop,High-performance laptop,lpsts-2287,Cola 6-pack,205,kg,6,TEST,https://example.com/images/laptop1.jpg|https://example.com/images/laptop2.jpg|https://example.com/images/laptop3.jpg,https://testURLrel.com,laptop image,
            update,Smartphone,Latest smartphone,jhx-83449,Beer 4 pack,2,l,4,TEST,https://example.com/images/smartphone1.jpg|https://example.com/images/smartphone2.jpg,https://testURLrel.ua,smartphone image,2
        CSV;
        $file = UploadedFile::fake()->createWithContent('products.csv', $csvContent);

        $response = $this->postJson('/api/products/upload-csv', [
            'file' => $file,
        ]);

        $response->assertStatus(400);
        $response->assertJsonFragment([
            'message' => 'Error processing CSV: The following retailers do not exist. Please create them first: TEST',
        ]);
    }

    public function test_csv_imports_successfuly_with_create_and_update_for_super_user()
    {
        $user = User::factory()->create([
            'role' => 'super_user'
        ]);
        Sanctum::actingAs($user, ['*']);

        $currency = Currency::factory()->create();
        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id,
            'title' => 'TEST'
        ]);

        $csvContent = <<<CSV
            action,title,description,manufacturer_part_number,pack_size_name,pack_size_weight,pack_size_weight_unit,pack_size_amount,retailer_title,image_urls,product_url,image_name,product_id
            create,Laptop,High-performance laptop,lpsts-2287,Cola 6-pack,205,kg,6,TEST,https://example.com/images/laptop1.jpg|https://example.com/images/laptop2.jpg|https://example.com/images/laptop3.jpg,https://testURLrel.com,laptop image,
            update,Smartphone,Latest smartphone,jhx-83449,Beer 4 pack,2,l,4,TEST,https://example.com/images/smartphone1.jpg|https://example.com/images/smartphone2.jpg,https://testURLrel.ua,smartphone image,2
        CSV;
        $file = UploadedFile::fake()->createWithContent('products.csv', $csvContent);

        $response = $this->postJson('/api/products/upload-csv', [
            'file' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'message' => 'products imported successfully.',
        ]);
    }

    public function test_csv_imports_successfuly_with_create_and_update_for_regular_user()
    {
        $user = User::factory()->create([
            'role' => 'regular_user'
        ]);
        Sanctum::actingAs($user, ['*']);

        $currency = Currency::factory()->create();
        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id,
            'title' => 'TEST'
        ]);

        $csvContent = <<<CSV
            action,title,description,manufacturer_part_number,pack_size_name,pack_size_weight,pack_size_weight_unit,pack_size_amount,retailer_title,image_urls,product_url,image_name,product_id
            create,Laptop,High-performance laptop,lpsts-2287,Cola 6-pack,205,kg,6,TEST,https://example.com/images/laptop1.jpg|https://example.com/images/laptop2.jpg|https://example.com/images/laptop3.jpg,https://testURLrel.com,laptop image,
            update,Smartphone,Latest smartphone,jhx-83449,Beer 4 pack,2,l,4,TEST,https://example.com/images/smartphone1.jpg|https://example.com/images/smartphone2.jpg,https://testURLrel.ua,smartphone image,2
        CSV;
        $file = UploadedFile::fake()->createWithContent('products.csv', $csvContent);

        $response = $this->postJson('/api/products/upload-csv', [
            'file' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'message' => 'products imported successfully.',
        ]);
    }

    public function test_csv_import_for_create_and_update_return_error_when_file_is_not_provided()
    {
        $user = User::factory()->create([
            'role' => 'super_user'
        ]);
        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson('/api/products/upload-csv', []);

        $response->assertStatus(400);
        $response->assertJsonFragment([
            'message' => 'Please select a CSV file to upload.',
        ]);
    }

    public function test_csv_import_returns_error_when_unauthorized()
    {
        $csvContent = <<<CSV
            action,title,description,manufacturer_part_number,pack_size_name,pack_size_weight,pack_size_weight_unit,pack_size_amount,retailer_title,image_urls,product_url,image_name,product_id
            create,Laptop,High-performance laptop,lpsts-2287,Cola 6-pack,205,kg,6,TEST,https://example.com/images/laptop1.jpg|https://example.com/images/laptop2.jpg|https://example.com/images/laptop3.jpg,https://testURLrel.com,laptop image,
            update,Smartphone,Latest smartphone,jhx-83449,Beer 4 pack,2,l,4,TEST,https://example.com/images/smartphone1.jpg|https://example.com/images/smartphone2.jpg,https://testURLrel.ua,smartphone image,2
        CSV;
        $file = UploadedFile::fake()->createWithContent('products.csv', $csvContent);

        $response = $this->postJson('/api/products/upload-csv', [
            'file' => $file,
        ]);

        $response->assertStatus(401);
        $this->assertEquals('Unauthenticated.', $response->json('message'));
    }
}