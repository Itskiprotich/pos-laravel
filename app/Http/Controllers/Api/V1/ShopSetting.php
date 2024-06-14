<?php

namespace App\Http\Controllers\Api\V1;

use App\CPU\Helpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ShopSetting extends Controller
{
    public function __construct(
        private BusinessSetting $businessSetting
    ){}

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getShopData(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'shop_name' => 'required',
            'shop_email' => 'required',
            'shop_phone' => 'required',
            'shop_address' => 'required',
            'pagination_limit' => 'required',
            'footer_text' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $data['shopName'] = DB::table('business_settings')->updateOrInsert(['key' => 'shop_name'], [
            'value' => $request['shop_name']
        ]);

        $data['shop_email'] = DB::table('business_settings')->updateOrInsert(['key' => 'shop_email'], [
            'value' => $request['shop_email']
        ]);

        $data['shop_phone'] = DB::table('business_settings')->updateOrInsert(['key' => 'shop_phone'], [
            'value' => $request['shop_phone']
        ]);

        $data['shop_address'] = DB::table('business_settings')->updateOrInsert(['key' => 'shop_address'], [
            'value' => $request['shop_address']
        ]);

        $data['pagination_limit'] = DB::table('business_settings')->updateOrInsert(['key' => 'pagination_limit'], [
            'value' => $request['pagination_limit']
        ]);

        $data['currency'] = DB::table('business_settings')->updateOrInsert(['key' => 'currency'], [
            'value' => $request['currency']
        ]);

        $data['country'] = DB::table('business_settings')->updateOrInsert(['key' => 'country'], [
            'value' => $request['country']
        ]);

        $data['footer_text'] = DB::table('business_settings')->updateOrInsert(['key' => 'footer_text'], [
            'value' => $request['footer_text']
        ]);

        $currentLogo = $this->businessSetting->where(['key' => 'shop_logo'])->first();
        $data['curr_logo'] = DB::table('business_settings')->updateOrInsert(['key' => 'shop_logo'], [
            'value' => $request->has('shop_logo') ? Helpers::update('shop/', $currentLogo->value, 'png', $request->file('shop_logo')) : $currentLogo->value
        ]);

        $currentFavIcon = $this->businessSetting->where(['key' => 'fav_icon'])->first();
        $data['fav_icon'] = DB::table('business_settings')->updateOrInsert(['key' => 'fav_icon'], [
            'value' => $request->has('fav_icon') ? Helpers::update('shop/', $currentFavIcon->value, 'png', $request->file('fav_icon')) : $currentFavIcon->value
        ]);

        $data['time_zone'] = DB::table('business_settings')->updateOrInsert(['key' => 'time_zone'], [
            'value' => $request['time_zone'],
        ]);

        return response()->json([
            'shopInfo' => $data, 'shopLogo' => $currentLogo, 'favIcon' => $currentFavIcon
        ]);
    }
}
