<?php

namespace Tests\Feature\Services\Product;

use App\Models\Currency;
use App\Models\PackSize;
use App\Models\Product;
use App\Models\Retailer;
use App\Service\Product\ImportService;
use Tests\TestCase;

class ImportServiceTest extends TestCase
{
    protected $importService;

    public function setUp(): void
    {
        parent::setUp();
        $this->importService = new ImportService();
    }

    public function test_it_imports_products_with_create_and_update_actions_successfully()
    {
        $packSize = PackSize::factory()->create();
        $product = Product::factory()->create([
            'pack_size_id' => $packSize->id
        ]);
        
        $currency = Currency::factory()->create();
        $retailer = Retailer::factory()->create([
            'title' => 'Retailer A',
            'currency_id' => $currency->id
        ]);

        $csvContent = <<<CSV
action,title,description,manufacturer_part_number,pack_size_name,pack_size_weight,pack_size_weight_unit,pack_size_amount,retailer_title,image_urls,product_url,image_name,product_id
create,Test Product,Some description,mpn-1,Small,123,g,5,Retailer A,http://example.com/image1.jpg,http://example.com/product1,image1
update,New Product 2,Some description,mpn-2,Large,1,kg,20,Retailer A,http://example.com/image1.jpg,http://example.com/product1,image1,1
CSV;
        $filePath = $this->createTempCsv($csvContent);

        $this->importService->importProducts($filePath);

        $this->assertDatabaseHas('products', [
            'manufacturer_part_number' => 'mpn-1',
            'title' => 'Test Product',
        ]);

        $this->assertDatabaseHas('products', [
            'manufacturer_part_number' => 'mpn-2',
            'title' => 'New Product 2',
        ]);

        $this->assertDatabaseHas('pack_sizes', [
            'name' => 'Small',
            'weight' => '123',
            'weight_unit' => 'g',
            'amount' => '5',
        ]);
        $this->assertDatabaseHas('pack_sizes', [
            'name' => 'Large',
            'weight' => '1',
            'weight_unit' => 'kg',
            'amount' => '20',
        ]);

        $updatedProduct = Product::where('manufacturer_part_number', 'mpn-2')->first();
        $this->assertNotNull($updatedProduct);
        $createdProduct = Product::where('manufacturer_part_number', 'mpn-1')->first();
        $this->assertNotNull($createdProduct);

        $this->assertDatabaseHas('product_retailers', [
            'product_id' => $createdProduct->id,
            'retailer_id' => $retailer->id,
        ]);

        $this->assertDatabaseHas('product_images', [
            'product_id' => $createdProduct->id,
            'file_url' => 'http://example.com/image1.jpg',
        ]);
    }

    /**
     * Helper to create a temporary CSV file with the given content.
     *
     * @param string $content
     * 
     * @return string Absolute file path.
     */
    protected function createTempCsv(string $content): string
    {
        $path = tempnam(sys_get_temp_dir(), 'csv_');
        file_put_contents($path, $content);
        return $path;
    }
}
