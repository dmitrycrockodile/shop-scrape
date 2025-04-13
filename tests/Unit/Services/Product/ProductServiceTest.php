<?php

namespace Tests\Unit\Services\Product;

use App\Models\Currency;
use App\Models\PackSize;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Retailer;
use App\Models\User;
use App\Service\Product\ProductService;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

class ProductServiceTest extends TestCase
{
    protected ProductService $productService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->productService = new ProductService();
    }

    public function test_store_creates_product_successfully()
    {
        $user = User::factory()->create();
        $packSize = PackSize::factory()->create();

        $data = [
            'manufacturer_part_number' => 'MPN123',
            'pack_size_id' => $packSize->id,
            'title' => 'New Product',
            'description' => 'Some description',
            'images' => [UploadedFile::fake()->image('img.jpg')],
        ];

        $response = $this->productService->store($data, $user);

        $this->assertTrue($response['success']);
        $this->assertDatabaseHas('products', ['manufacturer_part_number' => 'MPN123']);
        $this->assertDatabaseCount('product_images', 1);
    }

    public function test_store_returns_error_for_duplicate()
    {
        $user = User::factory()->create();
        $packSize = PackSize::factory()->create();

        Product::factory()->create([
            'manufacturer_part_number' => 'MPN456',
            'pack_size_id' => $packSize->id,
        ]);

        $data = [
            'manufacturer_part_number' => 'MPN456',
            'pack_size_id' => $packSize->id,
            'title' => 'Duplicate Product',
        ];

        $response = $this->productService->store($data, $user);

        $this->assertFalse($response['success']);
        $this->assertEquals(422, $response['status']);
    }

    public function test_update_changes_data_and_images()
    {
        Storage::fake('public');

        $packSize = PackSize::factory()->create();
        $product = Product::factory()->create([
            'title' => 'Old Title',
            'pack_size_id' => $packSize->id
        ]);

        ProductImage::factory()->count(2)->create(['product_id' => $product->id]);

        $newImages = [UploadedFile::fake()->image('new.jpg')];

        $data = [
            'title' => 'Updated Title',
            'images' => $newImages,
        ];

        $response = $this->productService->update($data, $product);

        $this->assertTrue($response['success']);
        $this->assertDatabaseHas('products', ['title' => 'Updated Title']);
        $this->assertDatabaseCount('product_images', 1);
    }

    public function test_extract_images_combines_files_and_urls()
    {
        $image = UploadedFile::fake()->image('x.jpg');

        $data = [
            'images' => [$image],
            'image_urls' => json_encode(['https://example.com/img.png']),
        ];

        $result = $this->invokePrivateMethod($this->productService, 'extractImages', [&$data]);

        $this->assertCount(2, $result);
    }

    public function test_store_product_images_saves_file_and_record()
    {
        Storage::fake('public');

        $packSize = PackSize::factory()->create();
        $product = Product::factory()->create([
            'pack_size_id' => $packSize->id
        ]);
        $images = [UploadedFile::fake()->image('stored.jpg')];

        $this->invokePrivateMethod($this->productService, 'storeProductImages', [$images, $product]);

        $this->assertDatabaseHas('product_images', [
            'product_id' => $product->id,
            'file_name' => "{$product->title} image"
        ]);

        Storage::disk('public')->assertExists('images/' . $images[0]->hashName());
    }

    public function test_get_by_date_range_and_retailers_returns_correct_products()
    {
        $currency = Currency::factory()->create();
        $packSize = PackSize::factory()->create();

        $user = User::factory()->create([
            'role' => 'super_user'
        ]);
        $this->actingAs($user);

        $retailer1 = Retailer::factory()->create([
            'title' => 'Retailer 1',
            'currency_id' => $currency->id
        ]);
        $retailer2 = Retailer::factory()->create([
            'title' => 'Retailer 2',
            'currency_id' => $currency->id
        ]);
        $retailer3 = Retailer::factory()->create([
            'title' => 'Retailer 3',
            'currency_id' => $currency->id
        ]);

        $user->retailers()->attach([$retailer1->id, $retailer2->id, $retailer3->id]);

        $product1 = Product::factory()->create([
            'title' => 'Product 1',
            'pack_size_id' => $packSize->id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $product2 = Product::factory()->create([
            'title' => 'Product 2',
            'pack_size_id' => $packSize->id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $product3 = Product::factory()->create([
            'title' => 'Product 3',
            'pack_size_id' => $packSize->id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $product4 = Product::factory()->create([
            'title' => 'Product 4',
            'pack_size_id' => $packSize->id,
            'created_at' => Carbon::now()->subDays(12),
            'updated_at' => Carbon::now()->subDays(12),
        ]);

        $product1->retailers()->attach($retailer1->id);
        $product2->retailers()->attach($retailer2->id);
        $product3->retailers()->attach($retailer3->id);
        $product4->retailers()->attach($retailer1->id);

        $startDate = Carbon::now()->subDays(10)->copy()->startOfDay();
        $endDate = Carbon::now()->copy()->endOfDay();

        $result = $this->productService->getByDataRangeAndRetailers(
            $startDate,
            $endDate,
            [
                $retailer1->id,
                $retailer2->id,
                $retailer3->id
            ]
        );

        $this->assertEquals($result[0]['title'], 'Product 1');
        $this->assertEquals($result[1]['title'], 'Product 2');
        $this->assertEquals($result[2]['title'], 'Product 3');
        $this->assertEquals(count($result), 3);
    }

    public function test_get_by_start_date_only_returns_correct_products()
    {
        $currency = Currency::factory()->create();
        $packSize = PackSize::factory()->create();

        $user = User::factory()->create(['role' => 'super_user']);
        $this->actingAs($user);

        $retailer = Retailer::factory()->create(['currency_id' => $currency->id]);
        $user->retailers()->attach($retailer->id);

        $productRecent = Product::factory()->create([
            'title' => 'Recent Product',
            'pack_size_id' => $packSize->id,
            'created_at' => Carbon::now(),
        ]);

        $productOld = Product::factory()->create([
            'title' => 'Old Product',
            'pack_size_id' => $packSize->id,
            'created_at' => Carbon::now()->subDays(15),
        ]);

        $productRecent->retailers()->attach($retailer->id);
        $productOld->retailers()->attach($retailer->id);

        $startDate = Carbon::now()->subDays(10)->startOfDay();

        $result = $this->productService->getByDataRangeAndRetailers(
            $startDate,
            null,
            [$retailer->id]
        );

        $this->assertTrue($result->contains('title', 'Recent Product'));
        $this->assertFalse($result->contains('title', 'Old Product'));
    }

    public function test_get_by_end_date_only_returns_correct_products()
    {
        $currency = Currency::factory()->create();
        $packSize = PackSize::factory()->create();

        $user = User::factory()->create(['role' => 'super_user']);
        $this->actingAs($user);

        $retailer = Retailer::factory()->create(['currency_id' => $currency->id]);
        $user->retailers()->attach($retailer->id);

        $productRecent = Product::factory()->create([
            'title' => 'Recent Product',
            'pack_size_id' => $packSize->id,
            'created_at' => Carbon::now(),
        ]);

        $productOld = Product::factory()->create([
            'title' => 'Old Product',
            'pack_size_id' => $packSize->id,
            'created_at' => Carbon::now()->subDays(15),
        ]);

        $productRecent->retailers()->attach($retailer->id);
        $productOld->retailers()->attach($retailer->id);

        $endDate = Carbon::now()->subDays(10)->endOfDay();

        $result = $this->productService->getByDataRangeAndRetailers(
            null,
            $endDate,
            [$retailer->id]
        );

        $this->assertFalse($result->contains('title', 'Recent Product'));
        $this->assertTrue($result->contains('title', 'Old Product'));
    }

    public function test_store_product_images_with_string_url()
    {
        $packSize = PackSize::factory()->create();
        $product = Product::factory()->create([
            'title' => 'Test Product',
            'pack_size_id' => $packSize->id
        ]);

        $imageUrl = 'https://example.com/image.jpg';

        $this->invokePrivateMethod($this->productService, 'storeProductImages', [[$imageUrl], $product]);

        $this->assertDatabaseHas('product_images', [
            'product_id' => $product->id,
            'file_url' => $imageUrl,
            'file_name' => 'Test Product image',
        ]);
    }
}