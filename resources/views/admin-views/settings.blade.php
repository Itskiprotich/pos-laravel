@extends('layouts.admin.app')

@section('title',\App\CPU\translate('profile_settings'))

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">{{\App\CPU\translate('settings')}}</h1>
                </div>
                <div class="col-sm-auto">
                    <a class="btn btn-primary" href="{{route('admin.dashboard')}}">
                        <i class="tio-home mr-1"></i> {{\App\CPU\translate('dashboard')}}
                    </a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3">
                <div class="navbar-vertical navbar-expand-lg mb-3 mb-lg-5">
                    <button type="button" class="navbar-toggler btn btn-block btn-white mb-3"
                            aria-label="Toggle navigation" aria-expanded="false" aria-controls="navbarVerticalNavMenu"
                            data-toggle="collapse" data-target="#navbarVerticalNavMenu">
                            <span class="d-flex justify-content-between align-items-center">
                              <span class="h5 mb-0">{{\App\CPU\translate('admin_settings')}} </span>

                              <span class="navbar-toggle-default">
                                <i class="tio-menu-hamburger"></i>
                              </span>

                              <span class="navbar-toggle-toggled">
                                <i class="tio-clear"></i>
                              </span>
                            </span>
                    </button>
                    <div id="navbarVerticalNavMenu" class="collapse navbar-collapse">
                        <ul id="navbarSettings"
                            class="js-sticky-block js-scrollspy navbar-nav navbar-nav-lg nav-tabs card card-navbar-nav">
                            <li class="nav-item">
                                <a class="text-black-50 nav-link active" href="javascript:" id="generalSection">
                                    <i class="tio-user-outlined nav-icon"></i> {{\App\CPU\translate('basic_information')}}
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="text-black-50 nav-link" href="javascript:" id="passwordSection">
                                    <i class="tio-lock-outlined nav-icon"></i> {{\App\CPU\translate('password')}}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <form action="{{env('APP_MODE')!='demo'?route('admin.settings'):'javascript:'}}" method="post" enctype="multipart/form-data" id="admin-settings-form">
                @csrf
                    <div class="card mb-3 mb-lg-5" id="generalDiv">
                        <div class="profile-cover">
                            <div class="profile-cover-img-wrapper"></div>
                        </div>
                        <label
                            class="avatar avatar-xxl avatar-circle avatar-border-lg avatar-uploader profile-cover-avatar"
                            for="avatarUploader">
                            <img id="viewer"
                                 class="avatar-img"
                                 src="{{auth('admin')->user()->image_fullpath}}"
                                 alt="{{\App\CPU\translate('Image')}}">

                            <input type="file" name="image" class="js-file-attach avatar-uploader-input"
                                   id="customFileEg1"
                                   accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                            <label class="avatar-uploader-trigger" for="customFileEg1">
                                <i class="tio-edit avatar-uploader-icon shadow-soft"></i>
                            </label>
                        </label>
                    </div>

                    <div class="card mb-3 mb-lg-5">
                        <div class="card-header">
                            <h2 class="card-title d-flex align-items-center g-2px h4"><i class="tio-info"></i> {{\App\CPU\translate('basic_information')}}</h2>
                        </div>

                        <div class="card-body">
                            <div class="row form-group">
                                <label for="firstNameLabel" class="col-sm-3 col-form-label input-label">{{\App\CPU\translate('full_name')}} <i
                                        class="tio-help-outlined text-body ml-1" data-toggle="tooltip"
                                        data-placement="top"
                                        title="{{\App\CPU\translate('Display name')}}"></i></label>

                                <div class="col-sm-9">
                                    <div class="input-group input-group-sm-down-break">
                                        <input type="text" class="form-control" name="f_name" id="firstNameLabel"
                                               placeholder="{{\App\CPU\translate('Your first name')}}" aria-label="{{\App\CPU\translate('Your first name')}}"
                                               value="{{auth('admin')->user()->f_name}}">
                                        <input type="text" class="form-control" name="l_name" id="lastNameLabel"
                                               placeholder="{{\App\CPU\translate('Your last name')}}" aria-label="{{\App\CPU\translate('Your last name')}}"
                                               value="{{auth('admin')->user()->l_name}}">
                                    </div>
                                </div>
                            </div>
                            <div class="row form-group">
                                <label for="phoneLabel" class="col-sm-3 col-form-label input-label">{{\App\CPU\translate('phone')}} <span
                                        class="input-label-secondary">({{\App\CPU\translate('optional')}})</span></label>
                                <div class="col-sm-9">
                                    <input type="text" class="js-masked-input form-control" name="phone" id="phoneLabel"
                                           placeholder="{{\App\CPU\translate('+x(xxx)xxx-xx-xx')}}" aria-label="{{\App\CPU\translate('+(xxx)xx-xxx-xxxxx')}}"
                                           value="{{auth('admin')->user()->phone}}"
                                           data-hs-mask-options='{
                                           "template": "+(880)00-000-00000"
                                         }'>
                                </div>
                            </div>
                            <div class="row form-group">
                                <label for="newEmailLabel" class="col-sm-3 col-form-label input-label">{{\App\CPU\translate('email')}}</label>
                                <div class="col-sm-9">
                                    <input type="email" class="form-control" name="email" id="newEmailLabel"
                                           value="{{auth('admin')->user()->email}}"
                                           placeholder="{{\App\CPU\translate('enter_new_email_address')}}" aria-label="{{\App\CPU\translate('enter_new_email_address')}}">
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="button" id="saveChangesBtn" class="btn btn-primary">{{\App\CPU\translate('save_changes')}}</button>
                            </div>
                        </div>
                    </div>
                </form>
                <div id="passwordDiv" class="card mb-3 mb-lg-5">
                    <div class="card-header">
                        <h4 class="card-title">{{\App\CPU\translate('change_your_password')}}</h4>
                    </div>

                    <div class="card-body">
                        <form id="changePasswordForm" action="{{env('APP_MODE')!='demo'?route('admin.settings-password'):'javascript:'}}" method="post"
                              enctype="multipart/form-data">
                        @csrf
                            <div class="row form-group">
                                <label for="newPassword" class="col-sm-3 col-form-label input-label">{{\App\CPU\translate('new_password')}}</label>

                                <div class="col-sm-9">
                                    <input type="password" class="js-pwstrength form-control" name="password"
                                           id="newPassword" placeholder="Enter new password"
                                           aria-label="{{\App\CPU\translate('enter_new_password')}}"
                                           data-hs-pwstrength-options='{
                                           "ui": {
                                             "container": "#changePasswordForm",
                                             "viewports": {
                                               "progress": "#passwordStrengthProgress",
                                               "verdict": "#passwordStrengthVerdict"
                                             }
                                           }
                                         }' required>

                                    <p id="passwordStrengthVerdict" class="form-text mb-2"></p>

                                    <div id="passwordStrengthProgress"></div>
                                </div>
                            </div>
                            <div class="row form-group">
                                <label for="confirmNewPasswordLabel" class="col-sm-3 col-form-label input-label">{{\App\CPU\translate('confirm_password')}}
                                    </label>

                                <div class="col-sm-9">
                                    <div class="mb-3">
                                        <input type="password" class="form-control" name="confirm_password"
                                               id="confirmNewPasswordLabel" placeholder="{{\App\CPU\translate('confirm_your_new_password')}}"
                                               aria-label="{{\App\CPU\translate('confirm_your_new_password')}}" required>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="button" id="saveChangesPasswordBtn" class="btn btn-primary">{{\App\CPU\translate('save_changes')}} </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div id="stickyBlockEndPoint"></div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script src={{asset("public/assets/admin/js/settings.js")}}></script>
    <script src={{asset("public/assets/admin/js/global.js")}}></script>


    <script>
        "use strict";

        $('#saveChangesBtn').on('click', function() {
            if ("{{env('APP_MODE')}}" !== 'demo') {
                form_alert('admin-settings-form', 'Want to update info?');
            } else {
                call_demo();
            }
        });

        $('#saveChangesPasswordBtn').on('click', function() {
            if ("{{env('APP_MODE')}}" !== 'demo') {
                form_alert('changePasswordForm', 'Want to update password?');
            } else {
                call_demo();
            }
        });
    </script>

@endpush
