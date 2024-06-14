<?php

namespace App\Http\Controllers\Api\V1;

use Box\Spout\Common\Exception\InvalidArgumentException;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Writer\Exception\WriterNotOpenedException;
use Excel;
use App\CPU\Helpers;
use App\Models\Product;
use Barryvdh\DomPDF\PDF;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Rap2hpoutre\FastExcel\FastExcel;
use App\Http\Resources\ProductsResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\CategoryProductsResource;
use Symfony\Component\HttpFoundation\StreamedResponse;
use function App\CPU\translate;

class ProductController extends Controller
{
    public function __construct(
        private product $product,
        private BusinessSetting $businessSetting
    ){}

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkImportData(Request $request): JsonResponse
    {
        try {
            $collections = (new FastExcel)->import($request->file('products_file'));
        } catch (\Exception $exception) {
            return response()->json(['message' => translate('You have uploaded a wrong format file, please upload the right file')]);
        }

        $colKey = ['name','product_code','unit_type','unit_value','brand','category_id','sub_category_id','purchase_price','selling_price','discount_type','discount','tax','quantity', 'supplier_id'];
        foreach ($collections as $k => $collection) {
            foreach ($collection as $key => $value) {
                if ($key!="" && !in_array($key, $colKey)) {
                    return response()->json(['message' => translate('Please upload the correct format file.')]);
                }
            }
        }

        foreach ($collections as $key => $collection) {
            if ($collection['name'] === "") {
                return response()->json(['message' => translate('Please fill row:') . ($key + 2) . translate(' field: name')]);
            } elseif ($collection['product_code'] === "") {
                return response()->json(['message' => translate('Please fill row:') . ($key + 2) . translate(' field: product_code')]);
            } elseif ($collection['unit_type'] === "") {
                return response()->json(['message' => translate('Please fill row:') . ($key + 2) . translate(' field: unit_type')]);
            } elseif ($collection['unit_value'] === "") {
                return response()->json(['message' => translate('Please fill row:') . ($key + 2) . translate(' field: unit value')]);
            } elseif (!is_numeric($collection['unit_value'])) {
                return response()->json(['message' => translate('Unit Value of row ') . ($key + 2) . translate(' must be number')]);
            } elseif ($collection['brand'] === "") {
                return response()->json(['message' => translate('Please fill row:') . ($key + 2) . translate(' field: brand')]);
            } elseif ($collection['category_id'] === "") {
                return response()->json(['message' => translate('Please fill row:') . ($key + 2) . translate(' field: category_id')]);
            } elseif ($collection['sub_category_id'] === "") {
                return response()->json(['message' => translate('Please fill row:') . ($key + 2) . translate(' field: sub_category_id')]);
            } elseif ($collection['purchase_price'] === "") {
                return response()->json(['message' => translate('Please fill row:') . ($key + 2) . translate(' field: purchase price ')]);
            } elseif (!is_numeric($collection['purchase_price'])) {
                return response()->json(['message' => translate('Purchase Price of row ') . ($key + 2) . translate(' must be number')]);
            } elseif ($collection['selling_price'] === "") {
                return response()->json(['message' => translate('Please fill row:') . ($key + 2) . translate(' field: selling_price ')]);
            } elseif (!is_numeric($collection['selling_price'])) {
                return response()->json(['message' => translate('Please fill row:') . ($key + 2) . translate(' field: number ')]);
            } elseif ($collection['discount_type'] === "") {
                return response()->json(['message' => translate('Please fill row:') . ($key + 2) . translate(' field: discount type')]);
            } elseif ($collection['discount'] === "") {
                return response()->json(['message' => translate('Please fill row:') . ($key + 2) . translate(' field: discount ')]);
            } elseif (!is_numeric($collection['discount'])) {
                return response()->json(['message' => translate('Discount of row ') . ($key + 2) . translate(' must be number')]);
            } elseif ($collection['tax'] === "") {
                return response()->json(['message' => translate('Please fill row:') . ($key + 2) . translate(' field: tax ')]);
            } elseif (!is_numeric($collection['tax'])) {
                return response()->json(['message' => translate('Tax of row ') . ($key + 2) . translate(' must be number')]);
            } elseif ($collection['quantity'] === "") {
                return response()->json(['message' => translate('Please fill row:') . ($key + 2) . translate(' field: quantity ')]);
            } elseif (!is_numeric($collection['quantity'])) {
                return response()->json(['message' => translate('Quantity of row ') . ($key + 2) . translate(' must be number ')]);
            } elseif ($collection['supplier_id'] === "") {
                return response()->json(['message' => translate('Please fill row:') . ($key + 2) . translate(' field: supplier_id ')]);
            } elseif (!is_numeric($collection['supplier_id'])) {
                return response()->json(['message' => translate('supplier_id of row ') . ($key + 2) . translate(' must be number')]);
            }

            $product = [
                'discount_type' => $collection['discount_type'],
                'discount' => $collection['discount'],
            ];
            if ($collection['selling_price'] <= Helpers::discount_calculate($product, $collection['selling_price'])) {
                return response()->json(['message' => translate('Discount can not be more or equal to the price in row') . ($key + 2)], 403);
            }
            $product =  $this->product->where('product_code', $collection['product_code'])->first();
            if ($product) {
                return response()->json(['message' => translate('Product code row ') . ($key + 2) . translate(' already exist')], 403);
            }
        }
        $data = [];
        foreach ($collections as $collection) {
            $product =  $this->product->where('product_code', $collection['product_code'])->first();
            if ($product) {
                return response()->json(['message' => translate('Product code already exist')], 403);
            }
            $data[] = [
                'name' => $collection['name'],
                'product_code' => $collection['product_code'],
                'image' => json_encode(['def.png']),
                'unit_type' => $collection['unit_type'],
                'unit_value' => $collection['unit_value'],
                'brand' => $collection['brand'],
                'category_ids' => json_encode([['id' => $collection['category_id'], 'position' => 0], ['id' => $collection['sub_category_id'], 'position' => 1]]),
                'purchase_price' => $collection['purchase_price'],
                'selling_price' => $collection['selling_price'],
                'discount_type' => $collection['discount_type'],
                'discount' => $collection['discount'],
                'tax' => $collection['tax'],
                'quantity' => $collection['quantity'],
                'supplier_id' => $collection['supplier_id'],
            ];
        }
        DB::table('products')->insert($data);
        return response()->json(['code' => 200, 'message' => translate('Products imported successfully')], 200);
    }

    /**
     * @return string|StreamedResponse
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws UnsupportedTypeException
     * @throws WriterNotOpenedException
     */
    public function bulkExportData(): StreamedResponse|string
    {
        $products = $this->product->with('supplier', 'category', 'brand')->latest()->get();
        $format = \APP\CPU\ProductLogic::format_export_products($products);
        return (new FastExcel($format))->download('product_list.xlsx');
    }

    /**
     * @return JsonResponse
     */
    public function downloadExcelSample(): JsonResponse
    {
        $path = asset('public/assets/product_bulk_format.xlsx');
        return response()->json(['product_bulk_file' => $path]);
    }

    public function barcodeGenerate(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'quantity' => 'required',
        ], [
            'id.required' => 'Product ID is required',
            'quantity.required' => 'Barcode quantity is required',
        ]);

        if ($request->limit > 270) {
            return response()->json(['code' => 403, 'message' => translate('You can not generate more than 270 barcodes')]);
        }
        $product = $this->product->where('id', $request->id)->first();
        $quantity = $request->quantity ?? 30;
        $pdf = app()->make(PDF::class);
        $pdf->loadView('admin-views.product.barcode-pdf', compact('product', 'quantity'));
        return $pdf->stream();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function categoryWiseProduct(Request $request): JsonResponse
    {
        $limit = $request['limit'] ?? 10;
        $offset = $request['offset'] ?? 1;
        $stockLimit = $this->businessSetting->where('key', 'stock_limit')->first()->value;
        $categoryWiseProduct = $this->product->with('supplier')->active()
            ->when($request->has('category_id') && $request['category_id'] != 0, function ($query) use ($request) {
                $query->whereJsonContains('category_ids', [['id' => (string) $request['category_id']]]);
            })->latest()->paginate($limit, ['*'], 'page', $offset);
        $categoryWiseProduct = CategoryProductsResource::collection($categoryWiseProduct);
        return response()->json($categoryWiseProduct);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function codeSearch(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_code' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $limit = $request['limit'] ?? 10;
        $offset = $request['offset'] ?? 1;

        $productByCode = $this->product->where('product_code', 'LIKE', '%' . $request->product_code . '%')->latest()->paginate($limit, ['*'], 'page', $offset);
        $products = ProductsResource::collection($productByCode);
        return response()->json($products, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function productSort(Request $request)
    {
        $limit = $request['limit'] ?? 10;
        $offset = $request['offset'] ?? 1;
        $sort = $request['sort'] ? $request['sort'] : 'ASC';
        $sortProducts = $this->product->orderBy('selling_price', $sort)->latest()->paginate($limit, ['*'], 'page', $offset);
        $products = ProductsResource::collection($sortProducts);
        return response()->json($products, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function popularProductSort(Request $request): JsonResponse
    {
        $sort = $request['sort'] ? $request['sort'] : 'ASC';
        $products = $this->product->orderBy('order_count', $sort)->get();
        $products = ProductsResource::collection($products);
        return response()->json($products, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function supplierWiseProduct(Request $request): JsonResponse
    {
        $limit = $request['limit'] ?? 10;
        $offset = $request['offset'] ?? 1;
        $product = $this->product->where('supplier_id', $request->supplier_id)->latest()->paginate($limit, ['*'], 'page', $offset);
        $products = ProductsResource::collection($product);
        $data = [
            'total' => $products->total(),
            'limit' => $limit,
            'offset' => $offset,
            'products' => $products->items(),
        ];
        return response()->json($data, 200);
    }
}
