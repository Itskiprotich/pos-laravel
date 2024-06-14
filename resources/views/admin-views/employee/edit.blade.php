@extends('layouts.admin.app')
@section('title',\App\CPU\translate('Employee_edit'))
@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div class="content container-fluid">

    <div class="row align-items-center mb-3">
        <div class="col-sm mb-2 mb-sm-0">
            <h1 class="page-header-title text-capitalize"><i
                    class="tio-edit"></i> {{\App\CPU\translate('update_employee')}}
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 card py-3">
            <form action="{{route('admin.employee.update',[$employee['id']])}}" method="post"  class="js-validate"  enctype="multipart/form-data">
                @csrf
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title">
                            <span class="card-header-icon">
                                <i class="tio-user"></i>
                            </span>
                            <span>
                                {{ \App\CPU\translate('General_Information') }}
                            </span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <div class="form-group mb-0">
                                            <label class="form-label " for="fname">{{\App\CPU\translate('first_name')}}</label>
                                            <input type="text" name="f_name" class="form-control h--45px" id="fname"
                                                    placeholder="{{ \App\CPU\translate('Ex:_John') }} " value="{{$employee['f_name']}}" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group mb-0">
                                            <label class="form-label " for="lname">{{\App\CPU\translate('last_name')}}</label>
                                            <input type="text" name="l_name" class="form-control h--45px" id="lname"
                                                    placeholder="{{ \App\CPU\translate('Ex:_Doe') }} " value="{{$employee['l_name']}}">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group mb-0">
                                            <label class="form-label " for="role_id">{{\App\CPU\translate('Role')}}</label>
                                            <select class="form-control w-100 h--45px js-select2-custom" name="role_id" id="role_id" required>
                                                    <option value="" selected disabled>{{\App\CPU\translate('select_Role')}}</option>
                                                    @foreach($roles as $role)
                                                        <option value="{{$role->id}}" {{$role['id']==$employee['role_id']?'selected':''}}>{{$role->name}}</option>
                                                    @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label " for="phone">{{\App\CPU\translate('phone')}}</label>
                                        <input type="tel" name="phone" value="{{$employee['phone']}}" class="form-control h--45px" id="phone"
                                               pattern="[+0-9]+"
                                               title="Please enter a valid phone number with only numbers and the plus sign (+)"
                                               placeholder="{{ \App\CPU\translate('Ex:_+88017********') }} " required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label>{{\App\CPU\translate('image')}}</label><small> ( {{\App\CPU\translate('ratio_1:1')}} )( {{\App\CPU\translate('optional')}} )</small>
                                <div class="custom-file">
                                    <input type="file" name="image" id="customFileEg1" class="custom-file-input"
                                           accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" >
                                    <label class="custom-file-label" for="customFileEg1">{{\App\CPU\translate('choose_file')}}</label>
                                </div>
                                <div class="form-group my-4">
                                    <div class="text-center">
                                        <img class="img-one-cusu" id="viewer"
                                             src="{{onErrorImage($employee['image'],asset('storage/app/public/admin').'/' . $employee['image'],asset('public/assets/admin/img/160x160/img1.jpg') ,'admin/')}}"
                                             alt="{{ \App\CPU\translate('Employee thumbnail')}}"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title">
                            <span class="card-header-icon">
                                <i class="tio-user"></i>
                            </span>
                            <span>
                                {{\App\CPU\translate('account_info')}}
                            </span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label " for="email">{{\App\CPU\translate('email')}}</label>
                                    <input type="email" name="email" value="{{$employee['email']}}" class="form-control h--45px" id="email"
                                        placeholder="{{ \App\CPU\translate('Ex:_ex@gmail.com') }} " required>
                                </div>
                                <div class="col-md-4">
                                    <div class="js-form-message form-group">
                                        <label class="input-label" for="signupSrPassword">{{\App\CPU\translate('password')}}</label>
                                        <div class="input-group input-group-merge">
                                            <input type="password" class="js-toggle-password form-control h--45px" name="password" id="signupSrPassword"
                                                   placeholder="{{\App\CPU\translate('password_length_8+')}}" aria-label="8+ characters required"
                                                   data-msg="Your password is invalid. Please try again."
                                                   data-hs-toggle-password-options='{"target": ".js-toggle-password-target-1",
                                                                                   "defaultClass": "tio-hidden-outlined",
                                                                                   "showClass": "tio-visible-outlined",
                                                                                    "classChangeTarget": ".js-toggle-password-show-icon-1"}'>
                                            <div class="js-toggle-password-target-1 input-group-append">
                                                <a class="input-group-text" href="javascript:;">
                                                    <i class="js-toggle-password-show-icon-1 tio-visible-outlined"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="js-form-message form-group">
                                        <label class="input-label" for="signupSrConfirmPassword">{{\App\CPU\translate('confirm_password')}}</label>
                                        <div class="input-group input-group-merge">
                                            <input type="password" class="js-toggle-password form-control h--45px" name="password_confirmation"
                                                   id="signupSrConfirmPassword" placeholder="{{\App\CPU\translate('password_length_8+')}}"
                                                   aria-label="8+ characters required" data-msg="Password does not match the confirm password."
                                                   data-hs-toggle-password-options='{"target": ".js-toggle-password-target-2",
                                                                                   "defaultClass": "tio-hidden-outlined",
                                                                                   "showClass": "tio-visible-outlined",
                                                                                   "classChangeTarget": ".js-toggle-password-show-icon-2"}'>
                                            <div class="js-toggle-password-target-2 input-group-append">
                                                <a class="input-group-text" href="javascript:;">
                                                    <i class="js-toggle-password-show-icon-2 tio-visible-outlined"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="btn--container justify-content-end my-2">
                    <button type="submit" class="btn btn-primary">{{\App\CPU\translate('update')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script_2')
    <script src={{asset("public/assets/admin/js/global.js")}}></script>

    <script>
        "use strict";

        $('.js-toggle-password').each(function() {
            new HSTogglePassword(this).init()
        });
    </script>
@endpush
