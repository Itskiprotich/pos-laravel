@extends('layouts.admin.app')

@section('title',\App\CPU\translate('add_new_customer'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin')}}/css/custom.css"/>
@endpush

@section('content')
<div class="content container-fluid">
        <div class="row align-items-center mb-3">
            <div class="col-sm mb-2 mb-sm-0">
                <h1 class="page-header-title d-flex align-items-center g-2px text-capitalize"><i
                        class="tio-add-circle-outlined"></i> {{\App\CPU\translate('add_new_customer')}}
                </h1>
            </div>
        </div>
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12  mb-lg-2">
                <div class="card">
                    <div class="card-body">
                        <form action="{{route('admin.customer.store')}}" method="post" id="product_form"
                            enctype="multipart/form-data">
                            @csrf
                                <input type="hidden"   class="form-control" name="balance"  value= 0>
                                <div class="row pl-2" >
                                    <div class="col-12 col-sm-6">
                                        <div class="form-group">
                                            <label class="input-label" >{{\App\CPU\translate('customer_name')}} <span
                                                    class="input-label-secondary text-danger">*</span></label>
                                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="{{\App\CPU\translate('customer_name')}}" required>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <div class="form-group">
                                            <label class="input-label">{{\App\CPU\translate('mobile_no')}} <span
                                                    class="input-label-secondary text-danger">*</span> </label>
                                            <input type="tel" id="mobile" name="mobile" class="form-control" value="{{ old('mobile') }}"
                                                   pattern="[+0-9]+"
                                                   title="Please enter a valid phone number with only numbers and the plus sign (+)"
                                                   placeholder="{{\App\CPU\translate('mobile_no')}}" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row pl-2" >
                                    <div class="col-12 col-sm-6">
                                        <div class="form-group">
                                            <label class="input-label" >{{\App\CPU\translate('email')}}</label>
                                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="{{\App\CPU\translate('Ex_:_ex@example.com')}}">
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <div class="form-group">
                                            <label class="input-label" >{{\App\CPU\translate('state')}}</label>
                                            <input type="text" name="state" class="form-control" value="{{ old('state') }}" placeholder="{{\App\CPU\translate('state')}}" >
                                        </div>
                                    </div>
                                </div>
                                <div class="row pl-2" >
                                    <div class="col-12 col-sm-6">
                                        <div class="form-group">
                                            <label class="input-label">{{\App\CPU\translate('city')}} </label>
                                            <input type="text" name="city" class="form-control" value="{{ old('city') }}" placeholder="{{\App\CPU\translate('city')}}" >
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <div class="form-group">
                                            <label class="input-label">{{\App\CPU\translate('zip_code')}} </label>
                                            <input type="text" name="zip_code" class="form-control" value="{{ old('zip_code') }}" placeholder="{{\App\CPU\translate('zip_code')}}" >
                                        </div>
                                    </div>
                                </div>
                                <div class="row pl-2" >

                                    <div class="col-12 col-sm-6">
                                        <div class="form-group">
                                            <label class="input-label">{{\App\CPU\translate('address')}} </label>
                                            <input type="text"  name="address" class="form-control" value="{{ old('address') }}" placeholder="{{\App\CPU\translate('address')}}" >
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label>{{\App\CPU\translate('image')}}</label><small> ( {{\App\CPU\translate('ratio_1:1')}}  )( {{\App\CPU\translate('optional')}}  )</small>
                                        <div class="custom-file">
                                            <input type="file" name="image" id="customFileEg1" class="custom-file-input"
                                                accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" >
                                            <label class="custom-file-label" for="customFileEg1">{{\App\CPU\translate('choose')}} {{\App\CPU\translate('file')}}</label>
                                        </div>
                                        <div class="form-group my-4">
                                            <div class="text-center">
                                                <img class="img-one-ci" id="viewer"
                                                    src="{{asset('public/assets/admin/img/400x400/img2.jpg')}}" alt="{{\App\CPU\translate('image')}}"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <button type="submit" class="btn btn-primary">{{\App\CPU\translate('submit')}}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script src={{asset("public/assets/admin/js/global.js")}}></script>
@endpush
