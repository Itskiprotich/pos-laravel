<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\Product;
use App\CPU\Helpers;
use Brian2694\Toastr\Facades\Toastr;

class StocklimitController extends Controller
{
    public function __construct(
        private Product $product
    ){}

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function stockLimit(Request $request): Factory|View|Application
    {
        $stockLimit = Helpers::get_business_settings('stock_limit');
        $queryParam = [];
        $search = $request['search'];
        $sortOrderQty= $request['sort_orderQty'];
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $query = $this->product->where('quantity','<',$stockLimit)->
                where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('name', 'like', "%{$value}%")
                            ->orWhere('product_code', 'like', "%{$value}%");
                    }
            });
            $queryParam = ['search' => $request['search']];
        } else {
            $query = $this->product->when($request->sort_orderQty=='quantity_asc', function($q) use ($request){
                return $q->orderBy('quantity', 'asc');
            })
            ->when($request->sort_orderQty=='quantity_desc', function($q) use ($request){
                return $q->orderBy('quantity', 'desc');
            })
            ->when($request->sort_orderQty=='order_asc', function($q) use ($request){
                return $q->orderBy('order_count', 'asc');
            })
            ->when($request->sort_orderQty=='order_desc', function($q) use ($request){
                return $q->orderBy('order_count', 'desc');
            })
            ->when($request->sort_orderQty=='default', function($q) use ($request){
                return $q->orderBy('id');
            })->where('quantity','<',$stockLimit);
        }

        $products = $query->latest()->paginate(Helpers::pagination_limit())->appends($queryParam);

        return view('admin-views.stock.list',compact('products','search','sortOrderQty'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateQuantity(Request $request): RedirectResponse
    {
        $product = $this->product->find($request->id);
        $totalQuantity = $product->quantity + $request->quantity;
        if($totalQuantity >= 0)
        {
            $product->quantity = $product->quantity + $request->quantity;
            $product->save();

            Toastr::success(\App\CPU\translate('product_quantity_updated_successfully!'));
        }else{

            Toastr::warning(\App\CPU\translate('product_quantity_can_not_be_less_than_0_!'));
        }

        return back();
    }
}
