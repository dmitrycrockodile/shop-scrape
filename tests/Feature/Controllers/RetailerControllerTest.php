<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use App\Models\Retailer;
use App\Models\Product;
use App\Models\Currency;
use App\Models\PackSize;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RetailerControllerTest extends TestCase
{
    public function test_super_user_can_view_all_retailers()
    {
        $user = User::factory()->create([
            'role' => 'super_user'
        ]);
        Sanctum::actingAs($user);

        $currency = Currency::factory()->create();
        Retailer::factory()->count(3)->create([
            'currency_id' => $currency->id
        ]);

        $response = $this->getJson('/api/retailers');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => ['id', 'title', 'url', 'currency']
                ]
            ]);
    }

    public function test_regular_user_cannot_view_all_retailers()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $currency = Currency::factory()->create();
        Retailer::factory()->count(3)->create([
            'currency_id' => $currency->id
        ]);

        $response = $this->getJson('/api/retailers');

        $response->assertStatus(403);
        $this->assertEquals('This action is unauthorized.', $response->json('message'));
    }

    public function test_super_user_can_view_retailer_products()
    {
        $user = User::factory()->create([
            'role' => 'super_user'
        ]);
        Sanctum::actingAs($user);

        $currency = Currency::factory()->create();
        $packSize = PackSize::factory()->create();

        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);
        $products = Product::factory()->count(5)->create([
            'pack_size_id' => $packSize->id
        ]);

        $retailer->products()->attach($products);

        $response = $this->getJson("/api/retailers/{$retailer->id}/products?dataPerPage=5&page=1");

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => ['id', 'title']
                ],
                'meta' => ['current_page', 'per_page', 'last_page', 'total', 'links']
            ])
            ->assertJsonCount(5, 'data');
    }

    public function test_regular_user_can_view_his_retailer_products()
    {
        $user = User::factory()->create([
            'role' => 'regular_user'
        ]);
        Sanctum::actingAs($user);

        $currency = Currency::factory()->create();
        $packSize = PackSize::factory()->create();

        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);
        $products = Product::factory()->count(5)->create([
            'pack_size_id' => $packSize->id
        ]);

        $user->retailers()->attach($retailer);
        $user->products()->attach($products);
        $retailer->products()->attach($products);

        $response = $this->getJson("/api/retailers/{$retailer->id}/products?dataPerPage=5&page=1");

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => ['id', 'title']
                ],
                'meta' => ['current_page', 'per_page', 'last_page', 'total', 'links']
            ])
            ->assertJsonCount(5, 'data');
    }

    public function test_regular_user_cannot_view_all_retailers_retailers()
    {
        $user = User::factory()->create([
            'role' => 'regular_user'
        ]);
        Sanctum::actingAs($user);

        $currency = Currency::factory()->create();
        $packSize = PackSize::factory()->create();

        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);
        $products = Product::factory()->count(5)->create([
            'pack_size_id' => $packSize->id
        ]);

        $retailer->products()->attach($products);

        $response = $this->getJson("/api/retailers/{$retailer->id}/products?dataPerPage=5&page=1");

        $response->assertStatus(403);
        $this->assertEquals('This action is unauthorized.', $response->json('message'));
    }

    public function test_super_user_can_add_products_to_retailer()
    {
        $user = User::factory()->create([
            'role' => 'super_user'
        ]);
        Sanctum::actingAs($user);

        $currency = Currency::factory()->create();
        $packSize = PackSize::factory()->create();
        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);
        $product1 = Product::factory()->create([
            'pack_size_id' => $packSize->id
        ]);
        $product2 = Product::factory()->create([
            'pack_size_id' => $packSize->id
        ]);

        $payload = [
            'products' => [
                [
                    'id' => $product1->id,
                    'url' => 'http://test_url'
                ],
                [
                    'id' => $product2->id,
                    'url' => 'http://test_url'
                ],
            ]
        ];

        $response = $this->postJson("/api/retailers/{$retailer->id}/products", $payload);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertCount(2, $retailer->products()->get());
    }

    public function test_regular_user_can_add_products_to_his_retailer()
    {
        $user = User::factory()->create([
            'role' => 'regular_user'
        ]);
        Sanctum::actingAs($user);

        $currency = Currency::factory()->create();
        $packSize = PackSize::factory()->create();
        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);
        $product1 = Product::factory()->create([
            'pack_size_id' => $packSize->id
        ]);
        $product2 = Product::factory()->create([
            'pack_size_id' => $packSize->id
        ]);

        $user->retailers()->attach($retailer);
        $user->products()->attach([$product1, $product2]);

        $payload = [
            'products' => [
                [
                    'id' => $product1->id,
                    'url' => 'http://test_url'
                ],
                [
                    'id' => $product2->id,
                    'url' => 'http://test_url'
                ],
            ]
        ];

        $response = $this->postJson("/api/retailers/{$retailer->id}/products", $payload);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertCount(2, $retailer->products()->get());
    }

    public function test_regular_user_cannot_add_products_to_his_retailer()
    {
        $user = User::factory()->create([
            'role' => 'regular_user'
        ]);
        Sanctum::actingAs($user);

        $currency = Currency::factory()->create();
        $packSize = PackSize::factory()->create();
        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);
        $product1 = Product::factory()->create([
            'pack_size_id' => $packSize->id
        ]);
        $product2 = Product::factory()->create([
            'pack_size_id' => $packSize->id
        ]);

        $payload = [
            'products' => [
                [
                    'id' => $product1->id,
                    'url' => 'http://test_url'
                ],
                [
                    'id' => $product2->id,
                    'url' => 'http://test_url'
                ],
            ]
        ];

        $response = $this->postJson("/api/retailers/{$retailer->id}/products", $payload);

        $response->assertStatus(403);
        $this->assertEquals('This action is unauthorized.', $response->json('message'));
    }

    public function test_super_user_can_create_retailers()
    {
        $user = User::factory()->create([
            'role' => 'super_user'
        ]);
        Sanctum::actingAs($user);

        $currency = Currency::factory()->create();

        $payload = [
            'title' => 'New Retailer',
            'url' => 'http://retailer.test',
            'currency_id' => $currency->id
        ];

        $response = $this->postJson('/api/retailers', $payload);

        $response->assertCreated()
            ->assertJsonFragment(['title' => 'New Retailer']);

        $this->assertDatabaseHas('retailers', ['title' => 'New Retailer']);
    }

    public function test_regular_user_cannot_create_retailer()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $currency = Currency::factory()->create();

        $payload = [
            'title' => 'New Retailer',
            'url' => 'http://retailer.test',
            'currency_id' => $currency->id
        ];

        $response = $this->postJson('/api/retailers', $payload);

        $response->assertStatus(403);
        $this->assertEquals('This action is unauthorized.', $response->json('message'));
    }

    public function test_super_user_can_update_retailers()
    {
        $user = User::factory()->create([
            'role' => 'super_user'
        ]);
        Sanctum::actingAs($user);

        $currency = Currency::factory()->create();
        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);

        $payload = [
            'title' => 'New Retailer',
            'url' => 'http://retailer.test',
            'currency_id' => $currency->id
        ];

        $response = $this->putJson("/api/retailers/{$retailer->id}", $payload);

        $response->assertOk()
            ->assertJsonFragment(['title' => 'New Retailer']);

        $this->assertDatabaseHas('retailers', ['title' => 'New Retailer']);
    }

    public function test_regular_user_cannot_update_retailer()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $currency = Currency::factory()->create();
        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);

        $payload = [
            'title' => 'New Retailer',
            'url' => 'http://retailer.test',
            'currency_id' => $currency->id
        ];

        $response = $this->putJson("/api/retailers/{$retailer->id}", $payload);

        $response->assertStatus(403);
        $this->assertEquals('This action is unauthorized.', $response->json('message'));
    }

    public function test_super_user_can_delete_retailers()
    {
        $user = User::factory()->create([
            'role' => 'super_user'
        ]);
        Sanctum::actingAs($user);

        $currency = Currency::factory()->create();
        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);

        $response = $this->deleteJson("/api/retailers/{$retailer->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('retailers', ['id' => $retailer->id]);
    }

    public function test_regular_user_cannot_delete_retailer()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $currency = Currency::factory()->create();
        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);

        $response = $this->deleteJson("/api/retailers/{$retailer->id}");

        $response->assertStatus(403);
        $this->assertEquals('This action is unauthorized.', $response->json('message'));
    }
}