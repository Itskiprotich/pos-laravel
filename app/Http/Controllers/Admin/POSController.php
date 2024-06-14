<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\Coupon;
use App\Models\Transection;
use App\Models\Account;
use App\Models\OrderDetail;
use App\Models\Customer;
use App\CPU\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use function App\CPU\translate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class POSController extends Controller
{
    public function __construct(
        private Category $category,
        private Product $product,
        private Order $order,
        private Coupon $coupon,
        private Transection $transection,
        private Account $account,
        private OrderDetail $orderDetails,
        private Customer $customer
    ){}

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function index(Request $request): Factory|View|Application
    {
        $category = $request->query('category_id', 0);
        $keyword = $request->query('search', false);
        $key = explode(' ', $keyword);
        $categories = $this->category->where('status', 1)->where('position', 0)->latest()->get();

        $products = $this->product->where('quantity', '>', 0)->active()
            ->when($request->has('category_id') && $request['category_id'] != 0, function ($query) use ($request) {
                $query->whereJsonContains('category_ids', [['id' => (string)$request['category_id']]]);
            })->latest()->paginate(Helpers::pagination_limit());

        $cartId = 'wc-' . rand(10, 1000);

        if (!session()->has('current_user')) {
            session()->put('current_user', $cartId);
        }
        if (strpos(session('current_user'), 'wc')) {
            $userId = 0;
        } else {
            $userId = explode('-', session('current_user'))[1];
        }

        if (!session()->has('cart_name')) {
            if (!in_array($cartId, session('cart_name') ?? [])) {
                session()->push('cart_name', $cartId);
            }
        }

        return view('admin-views.pos.index', compact('categories', 'products', 'cartId', 'category', 'userId'));
    }

    /**
     * @return RedirectResponse
     */
    public function clearCartIds(): RedirectResponse
    {
        session()->forget('cart_name');
        session()->forget(session('current_user'));
        session()->forget('current_user');

        return redirect()->route('admin.pos.index');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function quickView(Request $request): JsonResponse
    {
        $product = $this->product->findOrFail($request->product_id);

        return response()->json([
            'success' => 1,
            'view' => view('admin-views.pos._quick-view-data', compact('product'))->render(),
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function addToCart(Request $request): JsonResponse
    {
        $cartId = session('current_user');
        $userId = 0;
        $userType = 'wc';
        if (Str::contains(session('current_user'), 'sc')) {
            $userId = explode('-', session('current_user'))[1];
            $userType = 'sc';
        }
        $product = $this->product->find($request->id);
        $cart = session($cartId);
        if (session()->has($cartId) && count($cart) > 0) {
            foreach ($cart as $key => $cartItem) {
                if (is_array($cartItem) && $cartItem['id'] == $request['id']) {
                    $qty = $product->quantity - $cartItem['quantity'];
                    if ($qty == 0) {
                        return response()->json([
                            'qty' => $qty,
                            'user_type' => $userType,
                            'user_id' => $userId,
                            'view' => view('admin-views.pos._cart', compact('cartId'))->render()
                        ]);
                    }
                }
            }
        }

        $data = array();
        $data['id'] = $product->id;
        $cartKeeper = [];
        $itemExist = 0;
        if (session()->has($cartId) && count($cart) > 0) {
            foreach ($cart as $key => $cartItem) {
                if (is_array($cartItem) && $cartItem['id'] == $request['id']) {
                    $cartItem['quantity'] += 1;
                    $itemExist = 1;
                }
                array_push($cartKeeper, $cartItem);
            }
        }
        session()->put($cartId, $cartKeeper);

        if ($itemExist == 0) {
            $data['quantity'] = $request['quantity'];
            $data['price'] = $product->selling_price;
            $data['name'] = $product->name;
            $data['discount'] = Helpers::discount_calculate($product, $product->selling_price);
            $data['image'] = $product->image;
            $data['tax'] = Helpers::tax_calculate($product, $product->selling_price);
            if ($request->session()->has($cartId)) {
                $keeper = [];
                foreach (session($cartId) as $item) {
                    array_push($keeper, $item);
                }
                $keeper[] = $data;
                $request->session()->put($cartId, $keeper);
            } else {
                $request->session()->put($cartId, [$data]);
            }
        }

        return response()->json([
            'user_type' => $userType,
            'user_id' => $userId,
            'view' => view('admin-views.pos._cart', compact('cartId'))->render()
        ]);
    }

    /**
     * @return Application|Factory|View
     */
    public function cartItems(): Factory|View|Application
    {
        return view('admin-views.pos._cart');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function emptyCart(Request $request): JsonResponse
    {
        $cartId = session('current_user');
        $userId = 0;
        $userType = 'wc';
        if (Str::contains(session('current_user'), 'sc')) {
            $userId = explode('-', session('current_user'))[1];
            $userType = 'sc';
        }
        session()->forget($cartId);
        return response()->json([
            'user_type' => $userType,
            'user_id' => $userId,
            'view' => view('admin-views.pos._cart', compact('cartId'))->render()
        ], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateQuantity(Request $request): JsonResponse
    {
        $cartId = session('current_user');
        $userId = 0;
        $userType = 'wc';
        if (Str::contains(session('current_user'), 'sc')) {
            $userId = explode('-', session('current_user'))[1];
            $userType = 'sc';
        }
        if ($request->quantity > 0) {

            $product = $this->product->find($request->key);
            $cart = session($cartId);
            $keeper = [];
            foreach ($cart as $item) {
                if (is_array($item)) {
                    if ($item['id'] == $request->key) {
                        $qty = $product->quantity - $request->quantity;
                        if ($qty < 0) {
                            return response()->json([
                                'qty' => $qty,
                                'user_type' => $userType,
                                'user_id' => $userId,
                                'view' => view('admin-views.pos._cart', compact('cartId'))->render()
                            ]);
                        }
                        $item['quantity'] = $request->quantity;
                    }
                    $keeper[] = $item;
                }
            }
            session()->put($cartId, $keeper);

            return response()->json([
                'user_type' => $userType,
                'user_id' => $userId,
                'view' => view('admin-views.pos._cart', compact('cartId'))->render()
            ], 200);
        } else {
            return response()->json([
                'upQty' => 'zeroNegative',
                'user_type' => $userType,
                'user_id' => $userId,
                'view' => view('admin-views.pos._cart', compact('cartId'))->render()
            ]);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeFromCart(Request $request): JsonResponse
    {
        $cartId = session('current_user');
        $userId = 0;
        $userType = 'wc';
        if (Str::contains(session('current_user'), 'sc')) {
            $userId = explode('-', session('current_user'))[1];
            $userType = 'sc';
        }
        $cart = session($cartId);
        $cartKeeper = [];
        if (session()->has($cartId) && count($cart) > 0) {
            foreach ($cart as $cartItem) {
                if (is_array($cartItem) && $cartItem['id'] != $request['key']) {
                    array_push($cartKeeper, $cartItem);
                }
            }
        }
        session()->put($cartId, $cartKeeper);

        return response()->json([
            'user_type' => $userType,
            'user_id' => $userId,
            'view' => view('admin-views.pos._cart', compact('cartId'))->render()
        ], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateDiscount(Request $request): JsonResponse
    {
        $cartId = session('current_user');
        $userId = 0;
        $userType = 'wc';
        if (Str::contains(session('current_user'), 'sc')) {
            $userId = explode('-', session('current_user'))[1];
            $userType = 'sc';
        }
        $cart = session($cartId, collect([]));
        if ($cart != null) {
            $totalProductPrice = 0;
            $productDiscount = 0;
            $productTax = 0;
            $extDiscount = 0;
            $couponDiscount = $cart['coupon_discount'] ?? 0;
            foreach ($cart as $ct) {
                if (is_array($ct)) {
                    $totalProductPrice += $ct['price'] * $ct['quantity'];
                    $productDiscount += $ct['discount'] * $ct['quantity'];
                    $productTax += $ct['tax'] * $ct['quantity'];
                }
            }
            $priceDiscount = 0;
            if ($request->type == 'percent') {
                $priceDiscount = ($totalProductPrice / 100) * $request->discount;
            } else {
                $priceDiscount = $request->discount;
            }
            $extDiscount = $priceDiscount;
            $total = $totalProductPrice - $productDiscount + $productTax - $couponDiscount - $extDiscount;

            if ($total < 0) {
                return response()->json([
                    'extra_discount' => "amount_low",
                    'user_type' => $userType,
                    'user_id' => $userId,
                    'view' => view('admin-views.pos._cart', compact('cartId'))->render()
                ]);
            } else {
                $cart['ext_discount'] = $request->discount;
                $cart['ext_discount_type'] = $request->type;
                session()->put($cartId, $cart);

                return response()->json([
                    'extra_discount' => "success",
                    'user_type' => $userType,
                    'user_id' => $userId,
                    'view' => view('admin-views.pos._cart', compact('cartId'))->render()
                ]);
            }
        } else {
            return response()->json([
                'extra_discount' => "empty",
                'user_type' => $userType,
                'user_id' => $userId,
                'view' => view('admin-views.pos._cart', compact('cartId'))->render()
            ]);
        }
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateTax(Request $request): RedirectResponse
    {
        $cart = $request->session()->get('cart', collect([]));
        $cart['tax'] = $request->tax;
        $request->session()->put('cart', $cart);

        return back();
    }

    /**
     * @param $cart
     * @param $price
     * @return float|int
     */
    public function extraDisCalculate($cart, $price): float|int
    {

        if ($cart['ext_discount_type'] == 'percent') {
            $priceDiscount = ($price / 100) * $cart['ext_discount'];
        } else {
            $priceDiscount = $cart['ext_discount'];
        }

        return $priceDiscount;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function couponDiscount(Request $request): JsonResponse
    {
        $cartId = session('current_user');
        $userId = 0;
        $userType = 'wc';
        if (Str::contains(session('current_user'), 'sc')) {
            $userId = explode('-', session('current_user'))[1];
            $userType = 'sc';
        }

        if ($userId != 0) {
            $couponLimit = $this->order->where('user_id', $userId)
                ->where('coupon_code', $request['coupon_code'])->count();

            $coupon = $this->coupon->where(['code' => $request['coupon_code']])
                ->where('user_limit', '>', $couponLimit)
                ->where('status', '=', 1)
                ->whereDate('start_date', '<=', now())
                ->whereDate('expire_date', '>=', now())->first();
        } else {
            $coupon = $this->coupon->where(['code' => $request['coupon_code']])
                ->where('status', '=', 1)
                ->where('coupon_type', '=', 'default')
                ->whereDate('start_date', '<=', now())
                ->whereDate('expire_date', '>=', now())->first();
        }

        $carts = session($cartId);
        $totalProductPrice = 0;
        $productDiscount = 0;
        $productTax = 0;
        $extDiscount = 0;

        if ($coupon != null) {
            if ($carts != null) {
                foreach ($carts as $cart) {
                    if (is_array($cart)) {
                        $totalProductPrice += $cart['price'] * $cart['quantity'];
                        $productDiscount += $cart['discount'] * $cart['quantity'];
                        $productTax += $cart['tax'] * $cart['quantity'];
                    }
                }
                if ($totalProductPrice >= $coupon['min_purchase']) {
                    if ($coupon['discount_type'] == 'percent') {

                        $discount = (($totalProductPrice / 100) * $coupon['discount']) > $coupon['max_discount'] ? $coupon['max_discount'] : (($totalProductPrice / 100) * $coupon['discount']);
                    } else {
                        $discount = $coupon['discount'];
                    }
                    if (isset($carts['ext_discount_type'])) {
                        $extDiscount = $this->extraDisCalculate($carts, $totalProductPrice);
                    }
                    $total = $totalProductPrice - $productDiscount + $productTax - $discount - $extDiscount;
                    if ($total < 0) {
                        return response()->json([
                            'coupon' => "amount_low",
                            'user_type' => $userType,
                            'user_id' => $userId,
                            'view' => view('admin-views.pos._cart', compact('cartId'))->render()
                        ]);
                    }

                    $cart = session($cartId, collect([]));
                    $cart['coupon_code'] = $request['coupon_code'];
                    $cart['coupon_discount'] = $discount;
                    $cart['coupon_title'] = $coupon->title;
                    $request->session()->put($cartId, $cart);

                    return response()->json([
                        'coupon' => 'success',
                        'user_type' => $userType,
                        'user_id' => $userId,
                        'view' => view('admin-views.pos._cart', compact('cartId'))->render()
                    ]);
                }
            } else {
                return response()->json([
                    'coupon' => 'cart_empty',
                    'user_type' => $userType,
                    'user_id' => $userId,
                    'view' => view('admin-views.pos._cart', compact('cartId'))->render()
                ]);
            }

            return response()->json([
                'coupon' => 'coupon_invalid',
                'user_type' => $userType,
                'user_id' => $userId,
                'view' => view('admin-views.pos._cart', compact('cartId'))->render()
            ]);
        }

        return response()->json([
            'coupon' => 'coupon_invalid',
            'user_type' => $userType,
            'user_id' => $userId,
            'view' => view('admin-views.pos._cart', compact('cartId'))->render()
        ]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function placeOrder(Request $request): RedirectResponse
    {
        $cartId = session('current_user');
        $userId = 0;
        $userType = 'wc';
        if (Str::contains(session('current_user'), 'sc')) {
            $userId = explode('-', session('current_user'))[1];
            $userType = 'sc';
        }
        if (session($cartId)) {
            if (count(session($cartId)) < 1) {
                Toastr::error(translate('cart_empty_warning'));
                return back();
            }
        } else {
            Toastr::error(translate('cart_empty_warning'));
            return back();
        }

        $cart = session($cartId);
        $coupon_code = 0;
        $productPrice = 0;
        $orderDetails = [];
        $productDiscount = 0;
        $productTax = 0;
        $extDiscount = 0;
        $couponDiscount = $cart['coupon_discount'] ?? 0;

        $order_id = 100000 + $this->order->all()->count() + 1;
        if ($this->order->find($order_id)) {
            $order_id = $this->order->orderBy('id', 'DESC')->first()->id + 1;
        }

        $order = $this->order;
        $order->id = $order_id;

        $order->user_id = $userId;
        $order->coupon_code = $cart['coupon_code'] ?? null;
        $order->coupon_discount_title = $cart['coupon_title'] ?? null;
        $order->payment_id = $request->type;
        $order->transaction_reference = $request->transaction_reference ?? null;

        $order->created_at = now();
        $order->updated_at = now();

        foreach ($cart as $c) {
            if (is_array($c)) {
                $product = $this->product->find($c['id']);
                if ($product) {
                    $price = $c['price'];
                    $orD = [
                        'product_id' => $c['id'],
                        'product_details' => $product,
                        'quantity' => $c['quantity'],
                        'price' => $product->selling_price,
                        'tax_amount' => Helpers::tax_calculate($product, $product->selling_price),
                        'discount_on_product' => Helpers::discount_calculate($product, $product->selling_price),
                        'discount_type' => 'discount_on_product',
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                    $productPrice += $price * $c['quantity'];
                    $productDiscount += $c['discount'] * $c['quantity'];
                    $productTax += $c['tax'] * $c['quantity'];
                    $orderDetails[] = $orD;

                    $product->quantity = $product->quantity - $c['quantity'];
                    $product->order_count++;
                    $product->save();
                }
            }
        }
        $totalPrice = $productPrice - $productDiscount;

        if (isset($cart['ext_discount_type'])) {
            $extDiscount = $this->extraDisCalculate($cart, $productPrice);
            $order->extra_discount = $extDiscount;
        }

        $totalTaxAmount = $productTax;
        try {
            $order->total_tax = $totalTaxAmount;
            $order->order_amount = $totalPrice;

            $order->coupon_discount_amount = $couponDiscount;
            $order->collected_cash = $request->collected_cash ? $request->collected_cash : $totalPrice + $totalTaxAmount - $extDiscount - $couponDiscount;
            $order->save();

            $customer = $this->customer->where('id', $userId)->first();
            if ($userId != 0 && $request->type == 0) {
                $grandTotal = $totalPrice + $totalTaxAmount - $extDiscount - $couponDiscount;

                if ($request->remaining_balance >= 0) {
                    $payableAccount = Account::find(2);
                    $payableTransaction = new Transection;
                    $payableTransaction->tran_type = 'Payable';
                    $payableTransaction->account_id = $payableAccount->id;
                    $payableTransaction->amount = $grandTotal;
                    $payableTransaction->description = 'POS order';
                    $payableTransaction->debit = 1;
                    $payableTransaction->credit = 0;
                    $payableTransaction->balance = $payableAccount->balance - $grandTotal;
                    $payableTransaction->date = date("Y/m/d");
                    $payableTransaction->customer_id = $customer->id;
                    $payableTransaction->order_id = $order_id;
                    $payableTransaction->save();

                    $payableAccount->total_out = $payableAccount->total_out + $grandTotal;
                    $payableAccount->balance = $payableAccount->balance - $grandTotal;
                    $payableAccount->save();
                } else {

                    if ($customer->balance > 0) {
                        $payableAccount = Account::find(2);
                        $payableTransaction = new Transection;
                        $payableTransaction->tran_type = 'Payable';
                        $payableTransaction->account_id = $payableAccount->id;
                        $payableTransaction->amount = $customer->balance;
                        $payableTransaction->description = 'POS order';
                        $payableTransaction->debit = 1;
                        $payableTransaction->credit = 0;
                        $payableTransaction->balance = $payableAccount->balance - $customer->balance;
                        $payableTransaction->date = date("Y/m/d");
                        $payableTransaction->customer_id = $customer->id;
                        $payableTransaction->order_id = $order_id;
                        $payableTransaction->save();

                        $payableAccount->total_out = $payableAccount->total_out + $customer->balance;
                        $payableAccount->balance = $payableAccount->balance - $customer->balance;
                        $payableAccount->save();

                        $receivableAccount = Account::find(3);
                        $receivableTransaction = new Transection;
                        $receivableTransaction->tran_type = 'Receivable';
                        $receivableTransaction->account_id = $receivableAccount->id;
                        $receivableTransaction->amount = -$request->remaining_balance;
                        $receivableTransaction->description = 'POS order';
                        $receivableTransaction->debit = 0;
                        $receivableTransaction->credit = 1;
                        $receivableTransaction->balance = $receivableAccount->balance - $request->remaining_balance;
                        $receivableTransaction->date = date("Y/m/d");
                        $receivableTransaction->customer_id = $customer->id;
                        $receivableTransaction->order_id = $order_id;
                        $receivableTransaction->save();

                        $receivableAccount->total_in = $receivableAccount->total_in - $request->remaining_balance;
                        $receivableAccount->balance = $receivableAccount->balance - $request->remaining_balance;
                        $receivableAccount->save();
                    } else {

                        $receivableAccount = Account::find(3);
                        $receivableTransaction = new Transection;
                        $receivableTransaction->tran_type = 'Receivable';
                        $receivableTransaction->account_id = $receivableAccount->id;
                        $receivableTransaction->amount = $grandTotal;
                        $receivableTransaction->description = 'POS order';
                        $receivableTransaction->debit = 0;
                        $receivableTransaction->credit = 1;
                        $receivableTransaction->balance = $receivableAccount->balance + $grandTotal;
                        $receivableTransaction->date = date("Y/m/d");
                        $receivableTransaction->customer_id = $customer->id;
                        $receivableTransaction->order_id = $order_id;
                        $receivableTransaction->save();

                        $receivableAccount->total_in = $receivableAccount->total_in + $grandTotal;
                        $receivableAccount->balance = $receivableAccount->balance + $grandTotal;
                        $receivableAccount->save();
                    }
                }

                $customer->balance = $request->remaining_balance;
                $customer->save();
            }

            //transaction start
            if ($request->type != 0) {
                $account = Account::find($request->type);
                $transection = new Transection;
                $transection->tran_type = 'Income';
                $transection->account_id = $request->type;
                $transection->amount = $totalPrice + $totalTaxAmount - $extDiscount - $couponDiscount;
                $transection->description = 'POS order';
                $transection->debit = 0;
                $transection->credit = 1;
                $transection->balance = $account->balance + $totalPrice + $totalTaxAmount - $extDiscount - $couponDiscount;
                $transection->date = date("Y/m/d");
                $transection->customer_id = $customer->id;
                $transection->order_id = $order_id;
                $transection->save();
                //transaction end

                //account
                $account->balance = $account->balance + $totalPrice + $totalTaxAmount - $extDiscount - $couponDiscount;
                $account->total_in = $account->total_in + $totalPrice + $totalTaxAmount - $extDiscount - $couponDiscount;
                $account->save();
            }

            foreach ($orderDetails as $key => $item) {
                $orderDetails[$key]['order_id'] = $order->id;
            }

            $this->orderDetails->insert($orderDetails);

            session()->forget($cartId);
            session(['last_order' => $order->id]);

            Toastr::success(translate('order_placed_successfully'));
            return back();
        } catch (\Exception $e) {

            Toastr::warning(translate('failed_to_place_order'));
            return back();
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function searchProduct(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required',
        ], [
            'name.required' => translate('Product name is required'),
        ]);

        $key = explode(' ', $request['name']);
        $products = $this->product->where('quantity', '>', 0)->active()->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->where('name', 'like', "%{$value}%");
            }
        })->orWhere(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->where('product_code', 'like', "%{$value}%");
            }
        })->paginate(6);

        $countP = $products->count();

        return response()->json([
            'result' => view('admin-views.pos._search-result', compact('products'))->render(),
            'count' => $countP
        ]);
    }


    /**
     * @param Request $request
     * @return JsonResponse|void
     */
    public function searchByAddProduct(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ], [
            'name.required' => translate('Product name is required'),
        ]);

        if (is_numeric($request['name'])) {
            $products = $this->product->where('quantity', '>', 0)->active()->where('product_code', $request['name'])->paginate(6);
        } else {
            $products = $this->product->where('quantity', '>', 0)->active()->where('name', $request['name'])->paginate(6);
        }

        $countP = $products->count();
        if ($countP > 0) {
            return response()->json([
                'count' => $countP,
                'id' => $products[0]->id,
            ]);
        }
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function orderList(Request $request): Factory|View|Application
    {
        $queryParam = [];
        $search = $request['search'];
        if ($request->has('search')) {
            $orders = $this->order->latest()->where('id', 'like', "%{$search}%")->paginate(Helpers::pagination_limit())->appends($search);
        } else {
            $orders = $this->order->latest()->paginate(Helpers::pagination_limit())->appends($search);
        }

        return view('admin-views.pos.order.list', compact('orders', 'search'));
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function generateInvoice($id)
    {
        $order = $this->order->where('id', $id)->with(['details'])->first();
        //return $order;
        return response()->json([
            'success' => 1,
            'view' => view('admin-views.pos.order.invoice', compact('order'))->render(),
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCustomers(Request $request): JsonResponse
    {
        $key = explode(' ', $request['q']);
        $data = DB::table('customers')
            ->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('name', 'like', "%{$value}%")
                        ->orWhere('mobile', 'like', "%{$value}%");
                }
            })->limit(6)
            ->get([DB::raw('id, IF(id <> "0",CONCAT(name,  " (", mobile ,")"), name) as text')]);

        return response()->json($data);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function customerBalance(Request $request): JsonResponse
    {
        $customerBalance = $this->customer->where('id', $request->customer_id)->first()->balance;
        return response()->json([
            'customer_balance' => $customerBalance
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeCoupon(Request $request): JsonResponse
    {
        $cartId = ($request->user_id != 0 ? 'sc-' . $request->user_id : 'wc-' . rand(10, 1000));
        if (!in_array($cartId, session('cart_name') ?? [])) {
            session()->push('cart_name', $cartId);
        }

        $cart = session(session('current_user'));

        $cartKeeper = [];
        if (session()->has(session('current_user')) && count($cart) > 0) {
            foreach ($cart as $cartItem) {

                array_push($cartKeeper, $cartItem);
            }
        }
        if (session('current_user') != $cartId) {
            $tempCartName = [];
            foreach (session('cart_name') as $cart_name) {
                if ($cart_name != session('current_user')) {
                    $tempCartName[] = $cart_name;
                }
            }
            session()->put('cart_name', $tempCartName);
        }
        session()->put('cart_name', $tempCartName);
        session()->forget(session('current_user'));
        session()->put($cartId, $cartKeeper);
        session()->put('current_user', $cartId);
        $userId = explode('-', session('current_user'))[1];
        $currentCustomer = '';
        if (explode('-', session('current_user'))[0] == 'wc') {
            $currentCustomer = 'Walking Customer';
        } else {
            $current = $this->customer->where('id', $userId)->first();
            $currentCustomer = $current->name . ' (' . $current->mobile . ')';
        }

        return response()->json([
            'cart_nam' => session('cart_name'),
            'current_user' => session('current_user'),
            'current_customer' => $currentCustomer,
            'view' => view('admin-views.pos._cart', compact('cartId'))->render()
        ]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function changeCart(Request $request): RedirectResponse
    {

        session()->put('current_user', $request->cart_id);

        return redirect()->route('admin.pos.index');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function newCartId(Request $request): RedirectResponse
    {
        $cartId = 'wc-' . rand(10, 1000);
        session()->put('current_user', $cartId);
        if (!in_array($cartId, session('cart_name') ?? [])) {
            session()->push('cart_name', $cartId);
        }

        return redirect()->route('admin.pos.index');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCartIds(Request $request): JsonResponse
    {
        $cartId = session('current_user');
        $userId = 0;
        $userType = 'wc';
        if (Str::contains(session('current_user'), 'sc')) {
            $userId = explode('-', session('current_user'))[1];
            $userType = 'sc';
        }
        $cart = session($cartId);
        $cartKeeper = [];
        if (session()->has($cartId) && count($cart) > 0) {
            foreach ($cart as $cartItem) {
                $cartKeeper[] = $cartItem;
            }
        }
        session()->put(session('current_user'), $cartKeeper);
        $userId = explode('-', session('current_user'))[1];
        $currentCustomer = '';
        if (explode('-', session('current_user'))[0] == 'wc') {
            $currentCustomer = 'Walking Customer';
        } else {
            $current = $this->customer->where('id', $userId)->first();
            $currentCustomer = $current->name . ' (' . $current->mobile . ')';
        }
        return response()->json([
            'current_user' => session('current_user'),
            'cart_nam' => session('cart_name') ?? '',
            'current_customer' => $currentCustomer,
            'user_type' => $userType,
            'user_id' => $userId,
            'view' => view('admin-views.pos._cart', compact('cartId'))->render()
        ]);
    }
}
