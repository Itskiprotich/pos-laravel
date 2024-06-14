<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use function App\CPU\translate;

class CartController extends Controller
{
    /**
     * @param $id
     * @return JsonResponse
     */
    public function addToCart($id): JsonResponse
    {
        $product = DB::table('products')->where('id', $id)->first();
        $orderDetails = DB::table('order_details')->where('product_id', $id)->first();
        return response()->json([
            'success' => true, 'message' => translate('You Product'), 'product' => $product, 'order_details' => $orderDetails
        ]);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function removeCart($id): JsonResponse
    {
        DB::table('poss')->where('id', $id)->delete();
        return response()->json([
            'success' => true,
            'message' => translate('Cart item removed successfully'),
        ], 200);
    }
}
