<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\Coupon;
use App\CPU\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\DB;
use function App\CPU\translate;

class CouponController extends Controller
{
    public function __construct(
        private Coupon $coupon
    ){}

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function addNew(Request $request): Factory|View|Application
    {
        $queryParam = [];
        $search = $request['search'];
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $coupons = $this->coupon->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('title', 'like', "%{$value}%")
                        ->orWhere('code', 'like', "%{$value}%");
                }
            });

            $queryParam = ['search' => $request['search']];
        }else{
            $coupons = $this->coupon;
        }

        $coupons = $coupons->latest('id')->paginate(Helpers::pagination_limit())->appends($queryParam);
        return view('admin-views.coupon.index', compact('coupons', 'search'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required',
            'coupon_type'=>'required',
            'code' => 'required|unique:coupons',
            'start_date' => 'required',
            'expire_date' => 'required',
            'discount' => 'required',
            'max_discount' => 'required_if:discount_type,percent',
        ]);

        if($request->discount_type == 'percent' && $request->discount > 100)
        {
            Toastr::warning(\App\CPU\translate('discount could not be more than 100 percent'));
            return back();
        }

        if($request->discount_type == 'percent' && $request->max_discount <= 0)
        {
            Toastr::warning(\App\CPU\translate('max discount could not be less than 0'));
            return back();
        }

        DB::table('coupons')->insert([
            'title' => $request->title,
            'code' => $request->code,
            'user_limit' => $request->coupon_type !='default'? 1 : $request->user_limit,
            'coupon_type' => $request->coupon_type,
            'start_date' => $request->start_date,
            'expire_date' => $request->expire_date,
            'min_purchase' => $request->min_purchase != null ? $request->min_purchase : 0,
            'max_discount' => $request->max_discount != null ? $request->max_discount : 0,
            'discount' => $request->discount_type == 'amount' ? $request->discount : $request['discount'],
            'discount_type' => $request->discount_type,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        Toastr::success(translate('Coupon added successfully'));
        return back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function status(Request $request): RedirectResponse
    {
        $coupon = $this->coupon->find($request->id);
        $coupon->status = $request->status;
        $coupon->save();

        Toastr::success(translate('Coupon status updated'));
        return back();
    }

    /**
     * @param $id
     * @return Application|Factory|View
     */
    public function edit($id): Factory|View|Application
    {
        $coupon = $this->coupon->where(['id' => $id])->first();
        return view('admin-views.coupon.edit', compact('coupon'));
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $coupon = $this->coupon->find($id);
        $request->validate([
            'title' => 'required',
            'coupon_type'=>'required',
            'code' => 'required|unique:coupons,code,'.$coupon->id,
            'start_date' => 'required',
            'expire_date' => 'required',
            'discount' => 'required'
        ]);

        if($request->discount_type == 'percent' && $request->discount > 100)
        {
            Toastr::warning(\App\CPU\translate('discount could not be more than 100 percent'));
            return back();
        }

        DB::table('coupons')->where(['id' => $id])->update([
            'title' => $request->title,
            'code' => $request->code,
            'user_limit' => $request->coupon_type !='default'? 1 : $request->user_limit,
            'coupon_type' => $request->coupon_type,
            'start_date' => $request->start_date,
            'expire_date' => $request->expire_date,
            'min_purchase' => $request->min_purchase != null ? $request->min_purchase : 0,
            'max_discount' => $request->max_discount != null ? $request->max_discount : $request->discount,
            'discount' => $request->discount_type == 'amount' ? $request->discount : $request['discount'],
            'discount_type' => $request->discount_type,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        Toastr::success(translate('Coupon updated successfully'));
        return back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function delete(Request $request): RedirectResponse
    {
        $coupon = $this->coupon->find($request->id);
        $coupon->delete();

        Toastr::success(translate('Coupon removed'));
        return back();
    }

}
