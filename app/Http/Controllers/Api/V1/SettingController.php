<?php

namespace App\Http\Controllers\Api\V1;

use App\CPU\Helpers;
use App\Models\AdminRole;
use App\Models\Currency;
use Illuminate\Http\Request;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use function App\CPU\translate;
class SettingController extends Controller
{
    public function __construct(
        private Currency $currency,
        private BusinessSetting $businessSetting
    ){}

    public function updateShop(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shop_name' => 'required',
            'shop_email' => 'required',
            'shop_phone' => 'required',
            'shop_address' => 'required',
            'pagination_limit' => 'required',
            'footer_text' => 'required',
            'stock_limit' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
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

        DB::table('business_settings')->updateOrInsert(['key' => 'stock_limit'], [
            'value' => $request['stock_limit']
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'shop_address'], [
            'value' => $request['shop_address']
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'pagination_limit'], [
            'value' => $request['pagination_limit']
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'currency'], [
            'value' => $request['currency']
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'currency_symbol'], [
            'value' => $request['currency_symbol']
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'country'], [
            'value' => $request['country']
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'footer_text'], [
            'value' => $request['footer_text']
        ]);

        $currentLogo = $this->businessSetting->where(['key' => 'shop_logo'])->first();
        DB::table('business_settings')->updateOrInsert(['key' => 'shop_logo'], [
            'value' => $request->has('shop_logo') ? Helpers::update('shop/', $currentLogo->value, 'png', $request->file('shop_logo')) : $currentLogo->value
        ]);

        $currentFavIcon = $this->businessSetting->where(['key' => 'fav_icon'])->first();
        DB::table('business_settings')->updateOrInsert(['key' => 'fav_icon'], [
            'value' => $request->has('fav_icon') ? Helpers::update('shop/', $currentFavIcon->value, 'png', $request->file('fav_icon')) : $currentFavIcon->value
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'time_zone'], [
            'value' => $request['time_zone'],
        ]);
        DB::table('business_settings')->updateOrInsert(['key' => 'vat_reg_no'], [
            'value' => $request['vat_reg_no'],
        ]);
        return response()->json([
            'success' => true,
            'message' => translate('Shop updated successfully'),
        ], 200);
    }
    public function configuration()
    {
        $key = [
            'shop_logo',
            'pagination_limit',
            'currency',
            'shop_name',
            'shop_address',
            'shop_phone',
            'shop_email',
            'footer_text',
            'app_minimum_version_ios',
            'country',
            'stock_limit',
            'time_zone',
            'vat_reg_no',
            'fav_icon'
        ];
        $configKeyValueArray =  array_column($this->businessSetting->whereIn('key', $key)->get()->toArray(), 'value', 'key');
        return response()->json([
            'business_info' => $configKeyValueArray,
            'currency_symbol' => $this->currency->where(['currency_code' => Helpers::currency_code()])->first()->currency_symbol,
            'base_urls' => [
                'category_image_url' => asset('storage/app/public/category'),
                'brand_image_url' => asset('storage/app/public/brand'),
                'product_image_url' => asset('storage/app/public/product'),
                'supplier_image_url' => asset('storage/app/public/supplier'),
                'shop_image_url' => asset('storage/app/public/shop'),
                'admin_image_url' => asset('storage/app/public/admin'),
                'customer_image_url' => asset('storage/app/public/customer'),
            ],
            'time_zone' => TIME_ZONE,
            'role' => MODULE_PERMISSION,
        ], 200);
    }
}
