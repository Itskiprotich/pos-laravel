@extends('layouts.admin.app')

@section('title', \App\CPU\translate('create_role'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ asset('public/assets/admin') }}/css/custom.css" />

    <style>
        .check--item-wrapper {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
            margin: 30px -5px -30px -10px;
        }
        .check-item {
            width: 50%;
            max-width: 248px;
            padding: 0 5px 30px;
        }
        .form--check {
            padding-inline-start: 30px!important;
            cursor: pointer;
            margin-bottom: 0;
            position: relative;
        }
        .form-check-input {
            cursor: pointer;
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="">
            <div class="row align-items-center mb-3">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title d-flex align-items-center g-2px text-capitalize">
                        <i class="tio-add-circle-outlined"></i>
                        <span>{{ \App\CPU\translate('create_role') }}</span>
                    </h1>
                </div>
            </div>
        </div>
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('admin.custom-role.create') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                    <div class="form-group">
                                        <label for="">{{ \App\CPU\translate('role_name') }}</label>
                                        <input type="text" name="name" class="form-control"
                                               placeholder="{{ \App\CPU\translate('add_role_name') }}">
                                        <input name="position" value="0" class="d-none">
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex">
                                <h5 class="input-label m-0 text-capitalize">{{ \App\CPU\translate('module_permission')}} : </h5>
                                <div class="check-item pb-0 w-auto">
                                    <div class="form-group form-check form--check m-0 ml-2">
                                        <input type="checkbox" name="modules[]" value="account" class="form-check-input"
                                               id="select-all">
                                        <label class="form-check-label ml-2" for="select-all">{{ \App\CPU\translate('Select_All') }}</label>
                                    </div>
                                </div>
                            </div>
                            <div class="check--item-wrapper">
                                @foreach($modules as $module)
                                    <div class="check-item">
                                        <div class="form-group form-check form--check">
                                            <input type="checkbox" name="modules[]" value="{{ $module }}" class="form-check-input" id="{{ $module }}">
                                            <label class="form-check-label ml-2 ml-sm-3  text-dark" for="{{ $module }}">{{ ucwords(str_replace('_', ' ', $module)) }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="pt-4">
                                <button type="submit" class="btn btn-primary">{{ \App\CPU\translate('submit') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <div class="card">
                    <div class="card-header">
                        <div class="w-100">
                            <div class="row">
                                <div class="col-12 col-sm-4 col-md-6 col-lg-7 col-xl-8">
                                    <h5>{{ \App\CPU\translate('role_table') }}
                                        <span class="badge badge-soft-dark">{{$roles->total()}}</span>
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive ">
                        <table
                            class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                            <tr>
                                <th scope="col" class="w-50px">{{\App\CPU\translate('sl') }}</th>
                                <th scope="col" class="w-50px">{{\App\CPU\translate('Employee_Role_List') }}</th>
                                <th scope="col" class="w-200px">{{\App\CPU\translate('modules')}}</th>
                                <th scope="col" class="w-50px">{{\App\CPU\translate('status')}}</th>
                                <th scope="col" class="text-center w-50px">{{\App\CPU\translate('action')}}</th>
                            </tr>
                            </thead>
                            <tbody  id="set-rows">
                            @foreach($roles as $k=>$role)
                                <tr>
                                    <td scope="row">{{$k+$roles->firstItem()}}</td>
                                    <td>{{Str::limit($role['name'],25,'...')}}</td>
                                    <td class="text-capitalize">
                                        <div class="max-w450 text-wrap">
                                            @if($role['modules']!=null)
                                                @foreach((array)json_decode($role['modules']) as $key=>$m)
                                                <span class="badge badge-soft-success"> {{str_replace('_',' ',$m)}}</span>
                                                @endforeach
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <label class="toggle-switch toggle-switch-sm">
                                            <input type="checkbox" class="toggle-switch-input change-status"
                                                   data-route="{{ route('admin.custom-role.status', [$role['id'], $role->status ? 0 : 1]) }}"
                                                   class="toggle-switch-input" {{ $role->status ? 'checked' : '' }}>
                                            <span class="toggle-switch-label">
                                                    <span class="toggle-switch-indicator"></span>
                                                </span>
                                        </label>
                                    </td>
                                    <td class="text-center w-50px">
                                        <div class="btn--container justify-content-center">
                                            <a class="btn btn--primary btn-outline-primary action-btn"
                                               href="{{route('admin.custom-role.edit',[$role['id']])}}" title="{{\App\CPU\translate('edit_role')}}"><i class="tio-edit"></i>
                                            </a>
                                            <a class="btn btn--danger btn-outline-danger action-btn" href="javascript:"
                                               onclick="form_alert('role-{{$role['id']}}','{{\App\CPU\translate('Want_to_delete_this_role_?')}}')" title="{{\App\CPU\translate('delete_role')}}"><i class="tio-delete-outlined"></i>
                                            </a>
                                        </div>
                                        <form action="{{route('admin.custom-role.delete',[$role['id']])}}"
                                              method="post" id="role-{{$role['id']}}">
                                            @csrf @method('delete')
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        <hr>
                        <table>
                            <tfoot>
                            </tfoot>
                        </table>
                        @if(count($roles) === 0)
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
    <script src={{ asset('public/assets/admin/js/global.js') }}></script>

    <script>
        $('#select-all').on('change', function(){
            if(this.checked === true) {
                $('.check--item-wrapper .check-item .form-check-input').attr('checked', true)
            } else {
                $('.check--item-wrapper .check-item .form-check-input').attr('checked', false)
            }
        })

        $('.check--item-wrapper .check-item .form-check-input').on('change', function(){
            if(this.checked === true) {
                $(this).attr('checked', true)
            } else {
                $(this).attr('checked', false)
            }
        })
    </script>
@endpush
