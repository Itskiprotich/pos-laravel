@extends('layouts.admin.app')

@section('title',\App\CPU\translate('customer_list'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{asset('public/assets/admin')}}/css/custom.css"/>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="row align-items-center mb-3">
            <div class="col-sm mb-2 mb-sm-0">
                <h1 class="page-header-title d-flex align-items-center g-2px text-capitalize"><i
                        class="tio-filter-list"></i> {{\App\CPU\translate('customer_list')}}
                    <span class="badge badge-soft-dark ml-2">{{$customers->total()}}</span>
                </h1>
            </div>
        </div>
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <div class="card">
                    <div class="card-header">
                        <div class="row justify-content-between align-items-center flex-grow-1">
                            <div class="col-12 col-sm-7 col-md-6 col-lg-4 col-xl-6 mb-3 mb-sm-0">
                                <form action="{{url()->current()}}" method="GET">
                                    <div class="input-group input-group-merge input-group-flush">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                <i class="tio-search"></i>
                                            </div>
                                        </div>
                                        <input id="datatableSearch_" type="search" name="search" class="form-control"
                                               placeholder="{{\App\CPU\translate('search_by_name_or_phone')}}" aria-label="Search" value="{{ $search }}" required>
                                        <button type="submit" class="btn btn-primary">{{\App\CPU\translate('search')}} </button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-12 col-sm-5">
                                <a href="{{route('admin.customer.add')}}" class="btn btn-primary float-right"><i
                                        class="tio-add-circle"></i> {{\App\CPU\translate('add_new_customer')}}
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive datatable-custom">
                        <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                            <tr>
                                <th>{{\App\CPU\translate('#')}}</th>
                                <th>{{ \App\CPU\translate('image') }}</th>
                                <th>{{\App\CPU\translate('name')}}</th>
                                <th>{{\App\CPU\translate('phone')}}</th>
                                <th>{{ \App\CPU\translate('orders') }}</th>
                                <th class="text-center" >{{ \App\CPU\translate('balance') }}</th>
                                <th>{{\App\CPU\translate('action')}}</th>
                            </tr>
                            </thead>

                            <tbody id="set-rows">
                                @if($customers->currentPage() === 1)
                                <tr>
                                    <td>{{ 1 }}</td>
                                    <td>
                                        <a href="{{ route('admin.customer.view', [$walkingCustomer['id']]) }}">
                                            <img class="img-one-cl" src="{{ $walkingCustomer['image_fullpath'] }}" alt="">
                                        </a>
                                    </td>
                                    <td>
                                        <a class="text-primary" href="{{ route('admin.customer.view', [$walkingCustomer['id']]) }}">
                                            {{ $walkingCustomer->name }}
                                        </a>
                                    </td>
                                    <td>
                                        @if ($walkingCustomer->id != 0)
                                            {{ $walkingCustomer->mobile }}
                                        @else
                                            {{ \App\CPU\translate('no_phone') }}
                                        @endif
                                    </td>
                                    <td>{{ $walkingCustomer->orders->count() }}</td>
                                    <td class="text-center p-5">
                                        @if ($walkingCustomer->id != 0)
                                            <div class="row">
                                                <div class="col-5">
                                                    {{ $walkingCustomer->balance . ' ' . \App\CPU\Helpers::currency_symbol() }}
                                                </div>
                                                <div class="col-5">
                                                    <a class="btn btn-info p-1 badge update-customer-balance" id="{{ $walkingCustomer->id }}" data-id="{{ $walkingCustomer->id }}" type="button" data-toggle="modal" data-target="#update-customer-balance">
                                                        <i class="tio-add-circle"></i>
                                                        {{ \App\CPU\translate('add_balance') }}
                                                    </a>
                                                </div>
                                            </div>
                                        @else
                                            <div class="row">
                                                <div class="col-6">
                                                    {{ \App\CPU\translate('no_balance') }}
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <a class="btn btn-white mr-1" href="{{ route('admin.customer.view', [$walkingCustomer['id']]) }}"><span class="tio-visible"></span></a>
                                    </td>
                                </tr>
                            @endif

                                @foreach($customers as $key => $customer)
                                <tr>
                                    <td>{{ $customers->firstItem() + $key+1 }}</td>
                                    <td>
                                        <a href="{{ route('admin.customer.view', [$customer['id']]) }}">
                                            <img class="img-one-cl" src="{{ $customer['image_fullpath'] }}" alt="">
                                        </a>
                                    </td>
                                    <td>
                                        <a class="text-primary" href="{{ route('admin.customer.view', [$customer['id']]) }}">
                                            {{ $customer->name }}
                                        </a>
                                    </td>
                                    <td>
                                        @if ($customer->id != 0)
                                            {{ $customer->mobile }}
                                        @else
                                            {{ \App\CPU\translate('no_phone') }}
                                        @endif
                                    </td>
                                    <td>{{ $customer->orders->count() }}</td>
                                    <td class="text-center p-5">
                                        <div class="row">
                                            <div class="col-5">
                                                {{ $customer->balance . ' ' . \App\CPU\Helpers::currency_symbol() }}
                                            </div>
                                            <div class="col-5">
                                                <a class="btn btn-info p-1 badge update-customer-balance" id="{{ $customer->id }}" data-id="{{ $customer->id }}" type="button" data-toggle="modal" data-target="#update-customer-balance">
                                                    <i class="tio-add-circle"></i>
                                                    {{ \App\CPU\translate('add_balance') }}
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a class="btn btn-white mr-1" href="{{ route('admin.customer.view', [$customer['id']]) }}"><span class="tio-visible"></span></a>
                                        <a class="btn btn-white mr-1" href="{{ route('admin.customer.edit', [$customer['id']]) }}">
                                            <span class="tio-edit"></span>
                                        </a>
                                        <a class="btn btn-white mr-1 form-alert" href="javascript:" data-id="customer-{{ $customer['id'] }}" data-message="{{ $customer['balance'] < 0 ? \App\CPU\translate('This customer has Payable amount. Current balance is') . ' ' . $customer->balance . ' .' : '' }} {{ \App\CPU\translate('Do you want to delete this customer') }}?"><span class="tio-delete"></span></a>
                                        <form action="{{ route('admin.customer.delete', [$customer['id']]) }}" method="post" id="customer-{{ $customer['id'] }}">
                                            @csrf @method('delete')
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>



                        </table>
                        <div class="page-area">
                            <table>
                                <tfoot class="border-top">
                                {!! $customers->links() !!}
                                </tfoot>
                            </table>
                        </div>
                        @if(count($customers)==0)
                            <div class="text-center p-4">
                                <img class="mb-3 w-one-cl" src="{{asset('public/assets/admin')}}/svg/illustrations/sorry.svg" alt="{{\App\CPU\translate('Image Description')}}">
                                <p class="mb-0">{{ \App\CPU\translate('No_data_to_show')}}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
<div class="modal fade" id="update-customer-balance" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{\App\CPU\translate('update_customer_balance')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{route('admin.customer.update-balance')}}" method="post" class="row">
                    @csrf
                    <input type="hidden" id="customer_id" name="customer_id">
                        <div class="form-group col-12 col-sm-6">
                            <label for="">{{\App\CPU\translate('balance')}}</label>
                            <input type="number" step="0.01" min="0" class="form-control" name="amount" required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{\App\CPU\translate('balance_receive_account')}}</label>
                                <select name="account_id" class="form-control js-select2-custom" required>
                                    <option value="">---{{\App\CPU\translate('select')}}---</option>
                                    @foreach ($accounts as $account)
                                        @if ($account['id']!=2 && $account['id']!=3)
                                            <option value="{{$account['id']}}">{{$account['account']}}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label class="input-label">{{\App\CPU\translate('description')}} </label>
                                <input type="text" name="description" class="form-control" placeholder="{{\App\CPU\translate('description')}}" >
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{\App\CPU\translate('date')}} </label>
                                <input type="date" name="date" class="form-control" required>
                            </div>
                        </div>
                    <div class="form-group col-sm-12">
                        <button class="btn btn-sm btn-primary" type="submit">{{\App\CPU\translate('submit')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script_2')
    <script src={{asset("public/assets/admin/js/global.js")}}></script>
@endpush
