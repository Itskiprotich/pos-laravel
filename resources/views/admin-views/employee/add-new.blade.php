@extends('layouts.admin.app')
@section('title',\App\CPU\translate('Employee_Add'))
@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div class="content container-fluid">
    <div class="row align-items-center mb-3">
        <div class="col-sm mb-2 mb-sm-0">
            <h1 class="page-header-title d-flex align-items-center g-2px text-capitalize"><i
                    class="tio-add-circle-outlined"></i>{{ \App\CPU\translate('Add_New_Employee') }}
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card p-3">
                <form action="{{route('admin.employee.add-new')}}" method="post"  class="js-validate" enctype="multipart/form-data">
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
                        <div class="card-body pb-0">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="row g-3">
                                        <div class="col-sm-6">
                                            <div class="form-group mb-0">
                                                <label class="form-label" for="fname">{{\App\CPU\translate('first_name')}}</label>
                                                <input type="text" name="f_name" class="form-control h--45px" id="fname"
                                                       placeholder="{{ \App\CPU\translate('Ex:_John') }}" value="{{old('f_name')}}" required>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group mb-0">
                                                <label class="form-label" for="lname">{{\App\CPU\translate('last_name')}}</label>
                                                <input type="text" name="l_name" class="form-control h--45px" id="lname" value="{{old('l_name')}}"
                                                       placeholder="{{ \App\CPU\translate('Ex:_Doe') }}" value="{{old('name')}}">
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group mb-0">
                                                <label class="form-label" for="role_id">{{\App\CPU\translate('Role')}}</label>
                                                <select class="w-100 form-control h--45px js-select2-custom" name="role_id" id="role_id" required>
                                                    <option value="" selected disabled>{{\App\CPU\translate('select_Role')}}</option>
                                                    @foreach($roles as $role)
                                                        <option value="{{$role->id}}">{{$role->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="form-label" for="phone">{{\App\CPU\translate('phone')}}</label>
                                                <input type="tel" name="phone" value="{{old('phone')}}" class="form-control h--45px" id="phone"
                                                       pattern="[+0-9]+"
                                                       title="Please enter a valid phone number with only numbers and the plus sign (+)"
                                                       placeholder="{{ \App\CPU\translate('Ex:_+8801******') }}" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
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
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label" for="email">{{\App\CPU\translate('email')}}</label>
                                    <input type="email" name="email" value="{{old('email')}}" class="form-control h--45px" id="email"
                                           placeholder="{{ \App\CPU\translate('Ex:_ex@gmail.com') }}" required>
                                </div>
                                <div class="col-md-4">
                                    <div class="js-form-message form-group">
                                        <label class="input-label" for="signupSrPassword">{{\App\CPU\translate('password')}}</label>
                                        <div class="input-group input-group-merge">
                                            <input type="password" class="js-toggle-password form-control h--45px" name="password" id="signupSrPassword" required
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
                                            <input type="password" class="js-toggle-password form-control h--45px" name="password_confirmation" required
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
                    <div class="btn--container justify-content-end my-2">
                        <button type="submit" class="btn btn-primary">{{\App\CPU\translate('submit')}}</button>
                    </div>
                </form>

            </div>
        </div>

        <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2 my-3">
            <div class="card">
                <div class="card-header">
                    <div class="w-100">
                        <div class="row">
                            <div class="col-12 col-sm-4 col-md-6">
                                <h5 class="card-header-title">
                                    <span>{{\App\CPU\translate('employee_table')}}</span>
                                    <span class="badge badge-soft-dark ml-2">{{$employees->total()}}</span>
                                </h5>

                            </div>
                            <div class="col-12 col-sm-8 col-md-6 mt-1 mt-sm-0">
                                <form action="{{url()->current()}}" method="GET">
                                    <div class="input-group input-group-merge input-group-flush">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                <i class="tio-search"></i>
                                            </div>
                                        </div>
                                        <input id="datatableSearch_" type="search" name="search" class="form-control"
                                               placeholder="{{\App\CPU\translate('search_by_name_or_email_or_phone')}}" aria-label="Search" value="{{ $search }}" required>
                                        <button type="submit" class="btn btn-primary">{{\App\CPU\translate('search')}} </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive ">
                    <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table mb-4">
                        <thead class="thead-light">
                        <tr>
                            <th>{{ \App\CPU\translate('sl') }}</th>
                            <th>{{\App\CPU\translate('Employee Name')}}</th>
                            <th>{{\App\CPU\translate('phone')}}</th>
                            <th>{{\App\CPU\translate('email')}}</th>
                            <th class="text-center w-120px">{{\App\CPU\translate('action')}}</th>
                        </tr>
                        </thead>
                        <tbody id="set-rows">
                        @foreach($employees as $key=>$employee)
                            <tr>
                                <th scope="row">{{$key+$employees->firstItem()}}</th>
                                <td class="text-capitalize">{{$employee['f_name']}} {{$employee['l_name']}}</td>
                                <td>{{$employee['phone']}}</td>
                                <td >
                                    {{$employee['email']}}
                                </td>
                                <td class="text-center w-50px">
                                    @if (auth('admin')->id()  != $employee['id'])
                                        <div class="btn--container">
                                            <a class="btn btn-sm btn--primary btn-outline-primary action-btn"
                                               href="{{route('admin.employee.edit',[$employee['id']])}}" title="{{\App\CPU\translate('edit_Employee')}}"><i class="tio-edit"></i>
                                            </a>
                                            <a class="btn btn-sm btn--danger btn-outline-danger action-btn" href="javascript:"
                                               onclick="form_alert('employee-{{$employee['id']}}','{{\App\CPU\translate('Want_to_delete_this_employee_?')}}')" title="{{\App\CPU\translate('delete_Employee')}}"><i class="tio-delete-outlined"></i>
                                            </a>
                                        </div>
                                        <form action="{{route('admin.employee.delete',[$employee['id']])}}"
                                              method="post" id="employee-{{$employee['id']}}">
                                            @csrf @method('delete')
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    @if(count($employees) === 0)
                        <div class="text-center p-4">
                            <img class="mb-3 w-one-cati"
                                 src="{{ asset('public/assets/admin') }}/svg/illustrations/sorry.svg"
                                 alt="Image Description">
                            <p class="mb-0">{{ \App\CPU\translate('No_data_to_show') }}</p>
                        </div>
                    @endif
                </div>
            </div>
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
