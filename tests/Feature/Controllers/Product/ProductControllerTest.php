<?php

namespace Tests\Feature\Controllers\Product;

use App\Models\Currency;
use App\Models\PackSize;
use App\Models\Product;
use App\Models\Retailer;
use App\Models\User;
use App\Models\UserProduct;
use Illuminate\Http\Response;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{   
    public function test_index_returns_products_for_super_user()
    {
        $user = User::factory()->create([
            'role' => 'super_user'
        ]);

        $packSize = PackSize::factory()->create();
        Product::factory()->count(15)->create([
            'pack_size_id' => $packSize->id
        ]);

        Sanctum::actingAs($user, ['*']);
    
        $payload = [
            'dataPerPage' => 10,
            'page' => 1,
        ];

        $response = $this->postJson('/api/products', $payload);

        $response->assertStatus(Response::HTTP_OK);    
        $response->assertJsonStructure([
            'success',
            'message',
            'data',
            'meta' => [
                'current_page',
                'per_page',
                'last_page',
                'total',
                'links',
            ],
        ]);

        $this->assertCount(10, $response->json('data'));
    }

    public function test_index_returns_products_for_regular_user()
    {
        $user = User::factory()->create([
            'role' => 'regular_user'
        ]);
        $packSize = PackSize::factory()->create();

        $product1 = Product::factory()->create([
            'pack_size_id' => $packSize->id
        ]);
        $product2 = Product::factory()->create([
            'pack_size_id' => $packSize->id
        ]);
        UserProduct::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product1->id
        ]);
        UserProduct::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product2->id
        ]);

        Sanctum::actingAs($user, ['*']);

        $payload = [
            'dataPerPage' => 10,
            'page' => 1,
        ];

        $response = $this->postJson('/api/products', $payload);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'success',
            'message',
            'data',
            'meta' => [
                'current_page',
                'per_page',
                'last_page',
                'total',
                'links',
            ],
        ]);

    
        $data = $response->json('data');
        foreach ($data as $product) {    
            $this->assertNotEmpty($product['id']);
        }
    }

    public function test_index_returns_error_when_unauthorized()
    {
        $response = $this->postJson('/api/products');

        $response->assertStatus(401);
        $this->assertEquals('Unauthenticated.', $response->json('message'));
    }

    public function test_get_retailers_returns_correct_retailers_for_super_user()
    {
        $user = User::factory()->create([
            'role' => 'super_user'
        ]);
        $packSize = PackSize::factory()->create();
        $currency = Currency::factory()->create();

        $product = Product::factory()->create([
            'pack_size_id' => $packSize->id
        ]);

        $retailers = Retailer::factory()->count(3)->create([
            'currency_id' => $currency->id
        ]);
    
        $product->retailers()->attach($retailers->pluck('id')->toArray());    
        $user->retailers()->attach($retailers->pluck('id')->take(2)->toArray());

        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson("/api/products/{$product->id}/retailers");

        $response->assertStatus(Response::HTTP_OK);    
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'id',
                    'title',
                ]
            ]
        ]);
    }

    public function test_get_retailers_returns_correct_retailers_for_regular_user()
    {
        $user = User::factory()->create([
            'role' => 'regular_user'
        ]);
        $packSize = PackSize::factory()->create();
        $currency = Currency::factory()->create();

        $product = Product::factory()->create([
            'pack_size_id' => $packSize->id
        ]);

        $retailers = Retailer::factory()->count(3)->create([
            'currency_id' => $currency->id
        ]);
    
        $product->retailers()->attach($retailers->pluck('id')->toArray());    
        $user->retailers()->attach($retailers->pluck('id')->take(2)->toArray());

        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson("/api/products/{$product->id}/retailers");

        $response->assertStatus(Response::HTTP_OK);    
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'id',
                    'title',
                ]
            ]
        ]);
    }

    public function test_get_retailers_returns_error_when_unauthorized()
    {
        $packSize = PackSize::factory()->create();

        $product = Product::factory()->create([
            'pack_size_id' => $packSize->id
        ]);

        $response = $this->getJson("/api/products/{$product->id}/retailers");

        $response->assertStatus(401);
        $this->assertEquals('Unauthenticated.', $response->json('message'));
    }

    public function test_store_creates_product_successfully_for_super_user()
    {
        $packSize = PackSize::factory()->create();
        $user = User::factory()->create([
            'role' => 'super_user'
        ]);

        Sanctum::actingAs($user, ['*']);

        $payload = [
            'title' => 'Test Product',
            'description' => 'Test product description',
            'manufacturer_part_number' => 'mpn-111',
            'pack_size_id' => $packSize->id,
        ];

        $response = $this->postJson('/api/products/store', $payload);

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonFragment([
            'id' => 1,
            'title' => 'Test Product',
            'description' => 'Test product description',
            'manufacturer_part_number' => 'mpn-111',
        ]);
    }

    public function test_store_creates_product_successfully_for_regular_user()
    {
        $packSize = PackSize::factory()->create();
        $user = User::factory()->create([
            'role' => 'regular_user'
        ]);

        Sanctum::actingAs($user, ['*']);

        $payload = [
            'title' => 'Test Product',
            'description' => 'Test product description',
            'manufacturer_part_number' => 'mpn-111',
            'pack_size_id' => $packSize->id,
        ];

        $response = $this->postJson('/api/products/store', $payload);

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonFragment([
            'id' => 1,
            'title' => 'Test Product',
            'description' => 'Test product description',
            'manufacturer_part_number' => 'mpn-111',
        ]);
    }

    public function test_store_returns_error_when_unauthorized()
    {
        $packSize = PackSize::factory()->create();

        $payload = [
            'title' => 'Test Product',
            'description' => 'Test product description',
            'manufacturer_part_number' => 'mpn-111',
            'pack_size_id' => $packSize->id,
        ];

        $response = $this->postJson('/api/products/store', $payload);

        $response->assertStatus(401);
        $this->assertEquals('Unauthenticated.', $response->json('message'));
    }

    public function test_update_product_successfully_for_super_user()
    {
        $user = User::factory()->create(['role' => 'super_user']);
        Sanctum::actingAs($user, ['*']);

        $packSize = PackSize::factory()->create();
        $product = Product::factory()->create([
            'title' => 'Old Title',
            'description' => 'Old description',
            'manufacturer_part_number' => 'mpn-111',
            'pack_size_id' => $packSize->id
        ]);

        $payload = [
            'title' => 'New Title',
            'description' => 'New description',
            'manufacturer_part_number' => 'mpn-222',
            'pack_size_id' => $packSize->id
        ];

        $response = $this->putJson("/api/products/{$product->id}", $payload);
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            'title' => 'New Title',
            'manufacturer_part_number' => 'mpn-222',
        ]);
    }

    public function test_update_product_successfully_for_regular_user()
    {
        $user = User::factory()->create(['role' => 'regular_user']);
        Sanctum::actingAs($user, ['*']);

        $packSize = PackSize::factory()->create();
        $product = Product::factory()->create([
            'title' => 'Old Title',
            'description' => 'Old description',
            'manufacturer_part_number' => 'mpn-111',
            'pack_size_id' => $packSize->id
        ]);

        UserProduct::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id
        ]);

        $payload = [
            'title' => 'New Title',
            'description' => 'New description',
            'manufacturer_part_number' => 'mpn-222',
            'pack_size_id' => $packSize->id
        ];

        $response = $this->putJson("/api/products/{$product->id}", $payload);
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            'title' => 'New Title',
            'manufacturer_part_number' => 'mpn-222',
        ]);
    }

    public function test_update_other_user_product_is_forbidden_for_regular_user()
    {
        $user1 = User::factory()->create(['role' => 'regular_user']);
        $user2 = User::factory()->create(['role' => 'regular_user']);
        Sanctum::actingAs($user1, ['*']);

        $packSize = PackSize::factory()->create();
        $product = Product::factory()->create([
            'title' => 'Old Title',
            'description' => 'Old description',
            'manufacturer_part_number' => 'mpn-111',
            'pack_size_id' => $packSize->id
        ]);

        UserProduct::factory()->create([
            'user_id' => $user2->id,
            'product_id' => $product->id
        ]);

        $payload = [
            'title' => 'New Title',
            'description' => 'New description',
            'manufacturer_part_number' => 'mpn-222',
            'pack_size_id' => $packSize->id
        ];

        $response = $this->putJson("/api/products/{$product->id}", $payload);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $response->assertJsonFragment([
            'message' => 'This action is unauthorized.',
        ]);
    }

    public function test_update_returns_error_when_unauthorized()
    {
        $packSize = PackSize::factory()->create();
        $product = Product::factory()->create([
            'title' => 'Old Title',
            'description' => 'Old description',
            'manufacturer_part_number' => 'mpn-111',
            'pack_size_id' => $packSize->id
        ]);

        $payload = [
            'title' => 'New Title',
            'description' => 'New description',
            'manufacturer_part_number' => 'mpn-222',
            'pack_size_id' => $packSize->id
        ];

        $response = $this->putJson("/api/products/{$product->id}", $payload);

        $response->assertStatus(401);
        $this->assertEquals('Unauthenticated.', $response->json('message'));
    }

    public function test_destroy_deletes_product_successfully_for_super_user()
    {
        $user = User::factory()->create([
            'role' => 'super_user'
        ]);

        Sanctum::actingAs($user, ['*']);

        $packSize = PackSize::factory()->create();
        $product = Product::factory()->create([
            'pack_size_id' => $packSize->id
        ]);
    
        $response = $this->deleteJson("/api/products/{$product->id}");
        
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            'success' => true,
            'message' => 'product deleted successfully!',
        ]);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_delete_returns_error_when_unauthorized()
    {
        $packSize = PackSize::factory()->create();
        $product = Product::factory()->create([
            'pack_size_id' => $packSize->id
        ]);
    
        $response = $this->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(401);
        $this->assertEquals('Unauthenticated.', $response->json('message'));
    }
}