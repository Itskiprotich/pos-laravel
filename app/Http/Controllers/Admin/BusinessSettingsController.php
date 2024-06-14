<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\DB;
use App\CPU\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use function App\CPU\translate;

class BusinessSettingsController extends Controller
{
    public function __construct(
        private BusinessSetting $businessSetting
    ){}

    /**
     * @return Application|Factory|View
     */
    public function shopIndex(): View|Factory|Application
    {
        return view('admin-views.business-settings.shop-index');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function shopSetup(Request $request): RedirectResponse
    {
        $request->validate([
            'shop_logo' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'fav_icon' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'name.required' => translate('Name is required'),
        ]);

        if ($request->pagination_limit == 0) {
            Toastr::warning(translate('pagination_limit_is_required'));
            return back();
        }

        DB::table('business_settings')->updateOrInsert(['key' => 'shop_name'], [
            'value' => $request['shop_name']
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'shop_email'], [
            'value' => $request['shop_email']
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'shop_phone'], [
            'value' => $request['shop_phone']
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'shop_address'], [
            'value' => $request['shop_address']
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'pagination_limit'], [
            'value' => $request['pagination_limit']
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'stock_limit'], [
            'value' => $request['stock_limit']
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'currency'], [
            'value' => $request['currency']
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'country'], [
            'value' => $request['country']
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'footer_text'], [
            'value' => $request['footer_text']
        ]);

        $currLogo = $this->businessSetting->where(['key' => 'shop_logo'])->first();

        DB::table('business_settings')->updateOrInsert(['key' => 'shop_logo'], [
            'value' => $request->has('shop_logo') ? Helpers::update('shop/', $currLogo->value, 'png', $request->file('shop_logo')) : $currLogo->value
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'time_zone'], [
            'value' => $request['time_zone'],
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'vat_reg_no'], [
            'value' => $request['vat_reg_no'],
        ]);

        $currentFavIcon = $this->businessSetting->where(['key' => 'fav_icon'])->first();

        DB::table('business_settings')->updateOrInsert(['key' => 'fav_icon'], [
            'value' => $request->has('fav_icon') ? Helpers::update('shop/', $currentFavIcon->value, 'png', $request->file('fav_icon')) : $currentFavIcon->value
        ]);

        Toastr::success(translate('Settings updated'));
        return back();
    }

    /**
     * @return Application|Factory|View
     */
    public function shortcutKey(): View|Factory|Application
    {
        return view('admin-views.business-settings.shortcut-key-index');
    }
}
