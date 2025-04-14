<?php

namespace Tests\Feature\Controllers\Product;

use App\Models\Currency;
use App\Models\PackSize;
use App\Models\Product;
use App\Models\ProductRetailer;
use App\Models\User;
use App\Models\UserProduct;
use App\Models\Retailer;
use App\Models\UserRetailer;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ProductExportControllerTest extends TestCase
{
    public function test_export_csv_returns_valid_csv_output_for_super_user()
    {
        $user = User::factory()->create([
            'role' => 'super_user'
        ]);
        Sanctum::actingAs($user, ['*']);

        $currency = Currency::factory()->create();
        $packSize = PackSize::factory()->create();

        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);
        $product1 = Product::factory()->create([
            'pack_size_id' => $packSize->id,
            'created_at' => Carbon::parse('2024-04-05'),
        ]);
        $product2 = Product::factory()->create([
            'pack_size_id' => $packSize->id,
            'created_at' => Carbon::parse('2024-04-05'),
        ]);

        ProductRetailer::factory()->create([
            'product_id' => $product1->id,
            'retailer_id' => $retailer->id
        ]);
        ProductRetailer::factory()->create([
            'product_id' => $product2->id,
            'retailer_id' => $retailer->id
        ]);

        $payload = [
            'startDate' => '2024-04-01',
            'endDate'   => '2024-04-10',
            'retailers' => []
        ];

        $response = $this->postJson('/api/products/export', $payload);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString("products_2024-04-01_to_2024-04-10.csv", $contentDisposition);

        ob_start();
        $response->sendContent();
        $csvOutput = ob_get_clean();

        $this->assertNotEmpty($csvOutput, 'The CSV output should not be empty.');
        $this->assertStringContainsString('title', $csvOutput);
    }

    public function test_export_csv_generates_default_file_name_if_no_date_input_for_super_user()
    {
        $user = User::factory()->create([
            'role' => 'super_user'
        ]);
        Sanctum::actingAs($user, ['*']);

        $currency = Currency::factory()->create();
        $packSize = PackSize::factory()->create();

        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);
        $product1 = Product::factory()->create([
            'pack_size_id' => $packSize->id,
            'created_at' => Carbon::parse('2024-04-05'),
        ]);
        $product2 = Product::factory()->create([
            'pack_size_id' => $packSize->id,
            'created_at' => Carbon::parse('2024-04-05'),
        ]);

        ProductRetailer::factory()->create([
            'product_id' => $product1->id,
            'retailer_id' => $retailer->id
        ]);
        ProductRetailer::factory()->create([
            'product_id' => $product2->id,
            'retailer_id' => $retailer->id
        ]);

        $payload = [
            'startDate' => null,
            'endDate'   => null,
            'retailers' => []
        ];

        $response = $this->postJson('/api/products/export', $payload);

        $response->assertStatus(200);

        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString("products.csv", $contentDisposition);

        ob_start();
        $response->sendContent();
        $csvOutput = ob_get_clean();

        $this->assertNotEmpty($csvOutput);
    }

    public function test_export_csv_returns_valid_csv_output_filtered_by_retailer_for_super_user()
    {
        $user = User::factory()->create([
            'role' => 'super_user'
        ]);
        Sanctum::actingAs($user, ['*']);

        $currency = Currency::factory()->create();
        $packSize = PackSize::factory()->create();

        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);
        $product1 = Product::factory()->create([
            'pack_size_id' => $packSize->id,
            'created_at' => Carbon::parse('2024-04-05'),
        ]);
        $product2 = Product::factory()->create([
            'pack_size_id' => $packSize->id,
            'created_at' => Carbon::parse('2024-04-05'),
        ]);

        ProductRetailer::factory()->create([
            'product_id' => $product1->id,
            'retailer_id' => $retailer->id
        ]);
        ProductRetailer::factory()->create([
            'product_id' => $product2->id,
            'retailer_id' => $retailer->id
        ]);

        $payload = [
            'startDate' => '2024-04-01',
            'endDate'   => '2024-04-10',
            'retailers' => [$retailer->id]
        ];

        $response = $this->postJson('/api/products/export', $payload);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString("products_2024-04-01_to_2024-04-10.csv", $contentDisposition);

        ob_start();
        $response->sendContent();
        $csvOutput = ob_get_clean();

        $this->assertNotEmpty($csvOutput, 'The CSV output should not be empty.');
        $this->assertStringContainsString('title', $csvOutput);
    }

    public function test_export_csv_returns_valid_csv_output_for_regular_user()
    {
        $user = User::factory()->create([
            'role' => 'regular_user'
        ]);
        Sanctum::actingAs($user, ['*']);

        $currency = Currency::factory()->create();
        $packSize = PackSize::factory()->create();

        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);
        $product1 = Product::factory()->create([
            'pack_size_id' => $packSize->id,
            'created_at' => Carbon::parse('2024-04-05'),
        ]);
        $product2 = Product::factory()->create([
            'pack_size_id' => $packSize->id,
            'created_at' => Carbon::parse('2024-04-05'),
        ]);

        ProductRetailer::factory()->create([
            'product_id' => $product1->id,
            'retailer_id' => $retailer->id
        ]);
        ProductRetailer::factory()->create([
            'product_id' => $product2->id,
            'retailer_id' => $retailer->id
        ]);

        UserProduct::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product1->id
        ]);
        UserProduct::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product2->id
        ]);

        UserRetailer::factory()->create([
            'user_id' => $user->id,
            'retailer_id' => $retailer->id
        ]);

        $payload = [
            'startDate' => '2024-04-01',
            'endDate'   => '2024-04-10',
            'retailers' => []
        ];

        $response = $this->postJson('/api/products/export', $payload);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString("products_2024-04-01_to_2024-04-10.csv", $contentDisposition);

        ob_start();
        $response->sendContent();
        $csvOutput = ob_get_clean();

        $this->assertNotEmpty($csvOutput, 'The CSV output should not be empty.');
        $this->assertStringContainsString('title', $csvOutput);
    }

    public function test_export_csv_generates_default_file_name_if_no_date_input_for_regular_user()
    {
        $user = User::factory()->create([
            'role' => 'regular_user'
        ]);
        Sanctum::actingAs($user, ['*']);

        $currency = Currency::factory()->create();
        $packSize = PackSize::factory()->create();

        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);
        $product1 = Product::factory()->create([
            'pack_size_id' => $packSize->id,
            'created_at' => Carbon::parse('2024-04-05'),
        ]);
        $product2 = Product::factory()->create([
            'pack_size_id' => $packSize->id,
            'created_at' => Carbon::parse('2024-04-05'),
        ]);

        ProductRetailer::factory()->create([
            'product_id' => $product1->id,
            'retailer_id' => $retailer->id
        ]);
        ProductRetailer::factory()->create([
            'product_id' => $product2->id,
            'retailer_id' => $retailer->id
        ]);

        UserProduct::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product1->id
        ]);
        UserProduct::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product2->id
        ]);

        UserRetailer::factory()->create([
            'user_id' => $user->id,
            'retailer_id' => $retailer->id
        ]);

        $payload = [
            'startDate' => null,
            'endDate'   => null,
            'retailers' => []
        ];

        $response = $this->postJson('/api/products/export', $payload);

        $response->assertStatus(200);

        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString("products.csv", $contentDisposition);

        ob_start();
        $response->sendContent();
        $csvOutput = ob_get_clean();

        $this->assertNotEmpty($csvOutput);
    }

    public function test_export_csv_returns_valid_csv_output_filtered_by_retailer_for_regular_user()
    {
        $user = User::factory()->create([
            'role' => 'regular_user'
        ]);
        Sanctum::actingAs($user, ['*']);

        $currency = Currency::factory()->create();
        $packSize = PackSize::factory()->create();

        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);
        $product1 = Product::factory()->create([
            'pack_size_id' => $packSize->id,
            'created_at' => Carbon::parse('2024-04-05'),
        ]);
        $product2 = Product::factory()->create([
            'pack_size_id' => $packSize->id,
            'created_at' => Carbon::parse('2024-04-05'),
        ]);

        ProductRetailer::factory()->create([
            'product_id' => $product1->id,
            'retailer_id' => $retailer->id
        ]);
        ProductRetailer::factory()->create([
            'product_id' => $product2->id,
            'retailer_id' => $retailer->id
        ]);

        UserProduct::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product1->id
        ]);
        UserProduct::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product2->id
        ]);

        UserRetailer::factory()->create([
            'user_id' => $user->id,
            'retailer_id' => $retailer->id
        ]);

        $payload = [
            'startDate' => '2024-04-01',
            'endDate'   => '2024-04-10',
            'retailers' => [$retailer->id]
        ];

        $response = $this->postJson('/api/products/export', $payload);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString("products_2024-04-01_to_2024-04-10.csv", $contentDisposition);

        ob_start();
        $response->sendContent();
        $csvOutput = ob_get_clean();

        $this->assertNotEmpty($csvOutput, 'The CSV output should not be empty.');
        $this->assertStringContainsString('title', $csvOutput);
    }

    public function test_export_csv_returns_empty_csv_output_for_regular_user_when_retailer_is_not_exist_or_accessible()
    {
        $user = User::factory()->create([
            'role' => 'regular_user'
        ]);
        Sanctum::actingAs($user, ['*']);

        $currency = Currency::factory()->create();
        $packSize = PackSize::factory()->create();

        $retailer = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);
        $product1 = Product::factory()->create([
            'pack_size_id' => $packSize->id,
            'created_at' => Carbon::parse('2024-04-05'),
        ]);
        $product2 = Product::factory()->create([
            'pack_size_id' => $packSize->id,
            'created_at' => Carbon::parse('2024-04-05'),
        ]);

        ProductRetailer::factory()->create([
            'product_id' => $product1->id,
            'retailer_id' => $retailer->id
        ]);
        ProductRetailer::factory()->create([
            'product_id' => $product2->id,
            'retailer_id' => $retailer->id
        ]);

        UserProduct::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product1->id
        ]);
        UserProduct::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product2->id
        ]);

        $payload = [
            'startDate' => '2024-04-01',
            'endDate'   => '2024-04-10',
            'retailers' => [$retailer->id]
        ];

        $response = $this->postJson('/api/products/export', $payload);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString("products_2024-04-01_to_2024-04-10.csv", $contentDisposition);

        ob_start();
        $response->sendContent();
        $csvOutput = ob_get_clean();

        $this->assertEmpty($csvOutput, 'The CSV output should not be empty.');
    }

    public function test_export_csv_returns_error_when_unauthorized()
    {
        $payload = [
            'startDate' => '2024-04-01',
            'endDate'   => '2024-04-10',
            'retailers' => []
        ];

        $response = $this->postJson('/api/products/export', $payload);

        $response->assertStatus(401);
        $this->assertEquals('Unauthenticated.', $response->json('message'));
    }
}
