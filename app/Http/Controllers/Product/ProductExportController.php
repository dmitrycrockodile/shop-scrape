<?php 

namespace App\Http\Controllers\Product;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Product\ExportRequest;
use App\Service\CsvExporter;
use App\Service\Product\ProductService;
use Illuminate\Support\Carbon;

class ProductExportController extends BaseController
{
    private CsvExporter $csvExporter;
    private ProductService $productService;

    public function __construct(CsvExporter $csvExporter, ProductService $productService)
    {
        $this->csvExporter = $csvExporter;
        $this->productService = $productService;
    }

    /**
     * Exports the CSV file based on filters (date, retailers).
     * 
     * @param ExportRequest $request The request with start/end dates, retailer ids 
     * 
     * @return StreamedResponse|JsonResponse A streamed CSV file or a JSON response in case of errors.
     */
    public function export(ExportRequest $request) 
    {
        $data = $request->validated();
        $startDate = $data['startDate'] ? Carbon::parse($data['startDate'])->copy()->startOfDay() : null;
        $endDate = $data['endDate'] ? Carbon::parse($data['endDate'])->copy()->endOfDay() : Carbon::today()->endOfDay();
        
        ($startDate && $endDate)
        ? $fileName = "products_{$startDate->format('Y-m-d')}_to_{$endDate->format('Y-m-d')}.csv"
        : $fileName = "products.csv";
        
        $products = $this->productService->getByDataRangeAndRetailers($startDate, $endDate, $data['retailers']);

        return response()->streamDownload(function () use ($products) {
            $output = fopen('php://output', 'w');
            $this->csvExporter->export($output, $products);
            fclose($output);
        }, $fileName, [
            'Content-Type' => 'text/csv',
            'Access-Control-Expose-Headers' => 'Content-Disposition',
        ]);
    }
}