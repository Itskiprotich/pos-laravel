@extends('layouts.admin.app')

@section('title',\App\CPU\translate('coupon_update'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h1 class="page-header-title d-flex align-items-center g-2px text-capitalize">
                <i class="tio-edit"></i>
                <span>{{\App\CPU\translate('coupon_update')}}</span>
            </h1>
        </div>
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <div class="card">
                    <div class="card-body">
                        <form action="{{route('admin.coupon.update',[$coupon['id']])}}" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-12 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{\App\CPU\translate('title')}}</label>
                                        <input type="text" name="title" value="{{$coupon['title']}}" class="form-control"
                                            placeholder="{{\App\CPU\translate('new_coupon')}}" required>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="form-group">
                                        <div class="d-flex justify-content-between">
                                            <label class="input-label" for="exampleFormControlInput1">{{\App\CPU\translate('coupon_code')}}</label>
                                            <a href="javascript:void(0)" class="float-right c1 fz-12 generate-code-link">{{\App\CPU\translate('generate_code')}}</a>
                                        </div>
                                        <input type="text" name="code" class="form-control" value="{{$coupon['code']}}" id="code"
                                            placeholder="{{\Illuminate\Support\Str::random(8)}}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{\App\CPU\translate('coupon_type')}}</label>
                                        <select name="coupon_type" class="form-control coupon-type-change">
                                            <option value="default" {{$coupon['coupon_type']=='default'?'selected':''}}>
                                                {{\App\CPU\translate('default')}}
                                            </option>
                                            <option value="first_order" {{$coupon['coupon_type']=='first_order'?'selected':''}}>
                                                {{\App\CPU\translate('first')}} {{\App\CPU\translate('order')}}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6 {{$coupon['coupon_type']=='first_order'?'d-none':'d-block'}}" id="limit-for-user">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{\App\CPU\translate('limit_for_same_user')}}</label>
                                        <input min="1" type="number" name="user_limit" value="{{$coupon['user_limit']}}" class="form-control"
                                            placeholder="{{\App\CPU\translate('EX:_10')}}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="">{{\App\CPU\translate('start')}} {{\App\CPU\translate('date')}}</label>
                                        <input id="start_date" type="text" name="start_date" class="js-flatpickr form-control flatpickr-custom" placeholder="{{\App\CPU\translate('select_dates')}}" value="{{date('Y/m/d',strtotime($coupon['start_date']))}}"
                                            data-hs-flatpickr-options='{
                                            "dateFormat": "Y/m/d"
                                        }'>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="">{{\App\CPU\translate('expire')}} {{\App\CPU\translate('date')}}</label>
                                        <input id="expire_date" type="text" name="expire_date" class="js-flatpickr form-control flatpickr-custom check-date" placeholder="{{\App\CPU\translate('select_dates')}}" value="{{date('Y/m/d',strtotime($coupon['expire_date']))}}"
                                            data-hs-flatpickr-options='{
                                            "dateFormat": "Y/m/d"
                                        }'>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{\App\CPU\translate('min')}} {{\App\CPU\translate('purchase')}}</label>
                                        <input type="number" name="min_purchase" step="0.01" value="{{$coupon['min_purchase']}}"
                                            min="0" max="100000" class="form-control"
                                            placeholder="{{\App\CPU\translate('100')}}">
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6 {{$coupon['discount_type']=='amount'?'d-none':'d-block'}}" id="max_discount">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{\App\CPU\translate('max')}} {{\App\CPU\translate('discount')}}</label>
                                        <input type="number" min="0" max="1000000" step="0.01"
                                            value="{{$coupon['max_discount']}}" name="max_discount" class="form-control">
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{\App\CPU\translate('discount')}}</label>
                                        <input type="number" min="1" max="10000" step="0.01" value="{{$coupon['discount']}}"
                                            name="discount" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{\App\CPU\translate('discount')}} {{\App\CPU\translate('type')}}</label>
                                        <select name="discount_type" class="form-control" onchange="discount_amount(this.value)">
                                            <option value="amount" {{$coupon['discount_type']=='amount'?'selected':''}}>{{\App\CPU\translate('amount')}}
                                            </option>
                                            <option value="percent" {{$coupon['discount_type']=='percent'?'selected':''}}>
                                                {{\App\CPU\translate('percent')}}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">{{\App\CPU\translate('update')}}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script src={{asset("public/assets/admin/js/coupon.js")}}></script>
    <script>
        "use strict";

        $('.generate-code-link').on('click', function() {
            generateCode();
        });

        function  generateCode(){
            let code = Math.random().toString(36).substring(2,12);
            $('#code').val(code)
        }
    </script>
@endpush
