<?php

namespace Tests\Unit\Services\Product;

use App\Exceptions\CsvImportException;
use App\Models\Currency;
use App\Models\PackSize;
use App\Models\Product;
use App\Models\Retailer;
use App\Service\Product\ImportService;
use Tests\TestCase;

class ImportServiceTest extends TestCase
{
    private ImportService $importService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importService = new ImportService();
    }

    public function test_it_bulk_stores_data()
    {
        $packSize = PackSize::factory()->create();
        $currency = Currency::factory()->create();
        
        $retailer1 = Retailer::factory()->create([
            'title' => 'Retailer 1',
            'currency_id' => $currency->id
        ]);
        $retailer2 = Retailer::factory()->create([
            'title' => 'Retailer 2',
            'currency_id' => $currency->id
        ]);

        $products = [
            [
                'title' => 'Product 1',
                'description' => 'product descr',
                'manufacturer_part_number' => 'mpn-111',
                'pack_size_id' => $packSize->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Product 2',
                'description' => 'product descr',
                'manufacturer_part_number' => 'mpn-222',
                'pack_size_id' => $packSize->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        
        $rawData = [
            [
                'title' => 'Product 1',
                'description' => 'product descr',
                'manufacturer_part_number' => 'mpn-111',
                'pack_size_id' => $packSize->id,
                'created_at' => now(),
                'updated_at' => now(),
                'image_urls' => 'http://localhost:8080/products',
                'file_name' => 'product 1 image',
                'product_url' => 'http://localhost:8080/products',
                'retailer_title' => $retailer1->title,
            ],
            [
                'title' => 'Product 2',
                'description' => 'product descr',
                'manufacturer_part_number' => 'mpn-222',
                'pack_size_id' => $packSize->id,
                'created_at' => now(),
                'updated_at' => now(),
                'image_urls' => 'http://localhost:8080/products',
                'file_name' => 'product 2 image',
                'product_url' => 'http://localhost:8080/products',
                'retailer_title' => $retailer2->title,
            ],
        ];

        $existingRetailers = [
            $retailer1->title => $retailer1->id,
            $retailer2->title => $retailer2->id,
        ];

        $result = $this->invokePrivateMethod($this->importService, 'bulkStore', [$products, $rawData, $existingRetailers]);

        $this->assertEquals(2, $result);

        $this->assertDatabaseHas('products', ['title' => 'Product 1']);
        $this->assertDatabaseHas('products', ['title' => 'Product 2']);

        $this->assertDatabaseHas('product_images', [
            'product_id' => 1, 
            'file_url' => 'http://localhost:8080/products', 
            'file_name' => 'product 1 image'
        ]);
        $this->assertDatabaseHas('product_images', [
            'product_id' => 2, 
            'file_url' => 'http://localhost:8080/products', 
            'file_name' => 'product 2 image'
        ]);

        $this->assertDatabaseHas('product_retailers', [
            'product_id' => 1, 
            'retailer_id' => $retailer1->id,
            'product_url' => 'http://localhost:8080/products', 
        ]);
        $this->assertDatabaseHas('product_retailers', [
            'product_id' => 2, 
            'retailer_id' => $retailer2->id,
            'product_url' => 'http://localhost:8080/products', 
        ]);
    }

    public function test_it_bulk_updates_data()
    {
        $packSize = PackSize::factory()->create();
        $currency = Currency::factory()->create();
        
        $retailer1 = Retailer::factory()->create([
            'title' => 'Retailer 1',
            'currency_id' => $currency->id
        ]);
        $retailer2 = Retailer::factory()->create([
            'title' => 'Retailer 2',
            'currency_id' => $currency->id
        ]);

        $product1 = Product::factory()->create([
            'title' => 'Product 1',
            'pack_size_id' => $packSize->id
        ]);
        $product2 = Product::factory()->create([
            'title' => 'Product 2',
            'pack_size_id' => $packSize->id
        ]);

        $products = [
            [
                'id' => $product1->id,
                'title' => 'New Product 1',
                'description' => 'product descr',
                'manufacturer_part_number' => 'mpn-111',
                'pack_size_id' => $packSize->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $product2->id,
                'title' => 'New Product 2',
                'description' => 'product descr',
                'manufacturer_part_number' => 'mpn-222',
                'pack_size_id' => $packSize->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        
        $rawData = [
            [
                'product_id' => $product1->id,
                'title' => 'Product 1',
                'description' => 'product descr',
                'manufacturer_part_number' => 'mpn-111',
                'pack_size_id' => $packSize->id,
                'created_at' => now(),
                'updated_at' => now(),
                'image_urls' => 'http://localhost:8080/products',
                'file_name' => 'product 1 image',
                'product_url' => 'http://localhost:8080/products',
                'retailer_title' => $retailer1->title,
            ],
            [
                'product_id' => $product2->id,
                'title' => 'Product 2',
                'description' => 'product descr',
                'manufacturer_part_number' => 'mpn-222',
                'pack_size_id' => $packSize->id,
                'created_at' => now(),
                'updated_at' => now(),
                'image_urls' => 'http://localhost:8080/products',
                'file_name' => 'product 2 image',
                'product_url' => 'http://localhost:8080/products',
                'retailer_title' => $retailer2->title,
            ],
        ];

        $existingRetailers = [
            $retailer1->title => $retailer1->id,
            $retailer2->title => $retailer2->id,
        ];

        $result = $this->invokePrivateMethod($this->importService, 'bulkUpdate', [$products, $rawData, $existingRetailers]);

        $this->assertEquals(2, $result);

        $this->assertDatabaseHas('products', ['id' => $product1->id, 'title' => 'New Product 1']);
        $this->assertDatabaseHas('products', ['id' => $product2->id, 'title' => 'New Product 2']);

        $this->assertDatabaseHas('product_images', ['product_id' => $product1->id]);
        $this->assertDatabaseHas('product_images', ['product_id' => $product2->id]);

        $this->assertDatabaseHas('product_retailers', ['product_id' => $product1->id, 'retailer_id' => $retailer1->id]);
        $this->assertDatabaseHas('product_retailers', ['product_id' => $product2->id, 'retailer_id' => $retailer2->id]);
    }

    public function test_it_prepares_product_data()
    {
        $csvPath = storage_path('app/test_import.csv');
        file_put_contents($csvPath, "title,description,manufacturer_part_number,pack_size_name,pack_size_weight,pack_size_weight_unit,pack_size_amount,action,image_urls\n");
        file_put_contents($csvPath, "Product 1,Description 1,12345,Size 1,100,g,10,create,http://example.com/image1.jpg\n", FILE_APPEND);

        $records = [
            [
                'title' => 'Product 1',
                'description' => 'Description 1',
                'manufacturer_part_number' => '12345',
                'pack_size_name' => 'Size 1',
                'pack_size_weight' => 100,
                'pack_size_weight_unit' => 'g',
                'pack_size_amount' => 10,
                'action' => 'create',
                'image_urls' => 'http://example.com/image1.jpg|http://example.com/image2.jpg',
                'file_name' => 'image1.jpg',
                'product_url' => 'http://example.com/product1',
                'retailer_title' => 'Retailer 1',
            ],
        ];

        $packSizes = [
            [
                'id' => 1,
                'name' => 'Size 1',
                'weight' => 100,
                'weight_unit' => 'g',
                'amount' => 10
            ],
        ];

        $result = $this->invokePrivateMethod($this->importService, 'prepareProductData', [$records, $packSizes]);

        $this->assertCount(1, $result[0]);
        $this->assertArrayHasKey('title', $result[0][0]);
        $this->assertArrayHasKey('description', $result[0][0]);
        $this->assertArrayHasKey('manufacturer_part_number', $result[0][0]);
    }

    public function test_it_builds_product_lookup_map()
    {
        $packSize = PackSize::factory()->create();

        $rawProductData = [
            [
                'manufacturer_part_number' => '12345',
                'pack_size_id' => $packSize->id
            ],
        ];

        Product::factory()->create([
            'manufacturer_part_number' => '12345',
            'pack_size_id' => $packSize->id
        ]);

        $result = $this->invokePrivateMethod($this->importService, 'buildProductLookupMap', [$rawProductData]);

        $this->assertArrayHasKey('12345-1', $result);
        $this->assertEquals(1, $result['12345-1']);
    }

    public function test_it_prepares_related_data()
    {
        $rawProductData = [
            [
                'manufacturer_part_number' => '12345',
                'pack_size_id' => 1,
                'image_urls' => 'http://example.com/image1.jpg|http://example.com/image2.jpg',
                'file_name' => 'image1.jpg',
                'product_url' => 'http://example.com/product1',
                'retailer_title' => 'Retailer 1',
            ]
        ];

        $existingRetailers = [
            'Retailer 1' => 1
        ];

        $productMap = [
            '12345-1' => 1
        ];

        $result = $this->invokePrivateMethod($this->importService, 'prepareRelatedData', [$rawProductData, $existingRetailers, $productMap]);

        $this->assertCount(2, $result[0]);
        $this->assertCount(1, $result[1]);

        $this->assertArrayHasKey('product_id', $result[0][0]);
        $this->assertArrayHasKey('file_url', $result[0][0]);
        $this->assertArrayHasKey('file_name', $result[0][0]);
        $this->assertArrayHasKey('retailer_id', $result[1][0]);
    }

    public function test_it_finds_and_inserts_missing_pack_sizes()
    {
        $records = [
            [
                'pack_size_name' => 'Small Pack',
                'pack_size_weight' => 200,
                'pack_size_weight_unit' => 'g',
                'pack_size_amount' => 5
            ],
            [
                'pack_size_name' => 'Large Pack',
                'pack_size_weight' => 1000,
                'pack_size_weight_unit' => 'g',
                'pack_size_amount' => 10
            ],
        ];

        $existingPackSizes = [
            ['name' => 'Small Pack', 'weight' => 200, 'weight_unit' => 'g', 'amount' => 5, 'id' => 1]
        ];

        $result = $this->invokePrivateMethod($this->importService, 'findAndInsertMissingPackSizes', [$records, $existingPackSizes]);
            
        $this->assertEquals('Large Pack', $result[0]['name']);
        $this->assertEquals(1000.0, $result[0]['weight']);
        $this->assertEquals('g', $result[0]['weight_unit']);
        $this->assertEquals(10, $result[0]['amount']);
        $this->assertCount(1, $result);
    }

    public function test_it_throws_exception_for_empty_csv()
    {
        $emptyCsv = '';
        $filePath = $this->createTempCsv($emptyCsv);

        $this->assertFileExists($filePath); 
        $this->expectException(CsvImportException::class);
        $this->expectExceptionMessage('Error processing CSV: CSV file is empty.');
        $this->importService->importProducts($filePath);


    }

    public function test_it_throws_exception_for_missing_retailers()
    {
        $csvContent = <<<CSV
            action,title,description,manufacturer_part_number,pack_size_name,pack_size_weight,pack_size_weight_unit,pack_size_amount,retailer_title,image_urls,product_url,image_name,product_id
            create,Test Product,Some product description,TEST-1,Small,123,g,5,Retailer NotFound,http://example.com/image1.jpg,http://example.com/product1,image1,
        CSV;
        $filePath = $this->createTempCsv($csvContent);

        $this->assertFileExists($filePath); 
        $this->expectException(CsvImportException::class);
        $this->expectExceptionMessage('The following retailers do not exist');

        $this->importService->importProducts($filePath);
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
