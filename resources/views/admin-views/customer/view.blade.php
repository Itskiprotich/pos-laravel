@extends('layouts.admin.app')

@section('title',\App\CPU\translate('customer_details'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin')}}/css/custom.css"/>
@endpush

@section('content')
<div class="content container-fluid">
        <div class="d-print-none pb-2">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <div class="page-header">
                        <div class="js-nav-scroller hs-nav-scroller-horizontal">
                            <ul class="nav nav-tabs page-header-tabs">
                                <li class="nav-item">
                                    <a class="nav-link active" href="{{ route('admin.customer.view',[$customer['id']]) }}">{{\App\CPU\translate('order_list')}}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link " href="{{ route('admin.customer.transaction-list',[$customer['id']]) }}">{{\App\CPU\translate('transaction_list')}}</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="d-sm-flex align-items-sm-center">
                        <h4 class="page-header-title">{{\App\CPU\translate('customer')}} {{\App\CPU\translate('id')}}#{{$customer['id']}}</h4>
                        <span class="ml-2 ml-sm-3">
                        <i class="tio-date-range"></i> {{\App\CPU\translate('joined_at')}} : {{date('d M Y H:i:s',strtotime($customer['created_at']))}}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <div class="card">
                    <div class="card-header">
                        <div class="row justify-content-between align-items-center flex-grow-1">
                            <div class="col-12 col-sm-4 col-md-4 col-lg-4">
                                <h3>{{\App\CPU\translate('order_table')}}
                                    <span class="badge badge-soft-dark ml-2">{{$orders->total()}}</span>
                                </h3>
                            </div>
                            <div class="col-12 col-sm-8 col-md-4 col-lg-6">
                                <form action="{{url()->current()}}" method="GET">
                                    <div class="input-group input-group-merge input-group-flush">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                <i class="tio-search"></i>
                                            </div>
                                        </div>
                                        <input id="datatableSearch_" type="search" name="search" class="form-control"
                                                placeholder="{{\App\CPU\translate('search_by_order_id')}}" aria-label="Search" value="{{ $search }}" required>
                                        <button type="submit" class="btn btn-primary">{{\App\CPU\translate('search')}} </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive datatable-custom">
                        <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                            <tr>
                                <th>{{\App\CPU\translate('#')}}</th>
                                <th class="text-center">{{\App\CPU\translate('order')}} {{\App\CPU\translate('id')}}</th>
                                <th>{{\App\CPU\translate('total')}}</th>
                                <th>{{\App\CPU\translate('action')}}</th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach($orders as $key=>$order)
                                <tr>
                                    <td>{{$orders->firstItem()+$key}}</td>
                                    <td class="table-column-pl-0 text-center">
                                        <a href="#">{{$order['id']}}</a>
                                    </td>
                                    <td>{{$order->order_amount + $order->total_tax - $order->extra_discount - $order->coupon_discount_amount." ".\App\CPU\Helpers::currency_symbol()}}</td>
                                    <td>
                                        <button class="btn btn-sm btn-white print-invoice" target="_blank" type="button"
                                        data-id="{{$order->id}}"><i
                                        class="tio-download"></i> {{\App\CPU\translate('invoice')}}</button>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        <div class="card-footer">
                        {!! $orders->links() !!}
                        </div>
                        @if(count($orders)==0)
                            <div class="text-center p-4">
                                <img class="mb-3 w-one-carsi" src="{{asset('public/assets/admin')}}/svg/illustrations/sorry.svg" alt="{{\App\CPU\translate('Image Description')}}">
                                <p class="mb-0">{{ \App\CPU\translate('No_data_to_show')}}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-header-title">{{\App\CPU\translate('customer')}}</h4>
                    </div>
                    @if($customer)
                        <div class="card-body">
                            <div class="media align-items-center" href="javascript:">
                                <div class="avatar avatar-circle mr-3">
                                    <img
                                        class="avatar-img"
                                        src="{{$customer['image_fullpath']}}"
                                        alt="{{\App\CPU\translate('Image Description')}}">
                                </div>
                                <div class="media-body">
                                <span
                                    class="text-body text-hover-primary">{{$customer['name']}}</span>
                                </div>
                            </div>

                            <hr>

                            <div class="media align-items-center" href="javascript:">
                                <div class="icon icon-soft-info icon-circle mr-3">
                                    <i class="tio-shopping-basket-outlined"></i>
                                </div>
                                <div class="media-body">
                                    <span class="text-body text-hover-primary">{{ $orders->total() }} {{\App\CPU\translate('orders')}}</span>
                                </div>
                            </div>
                            <div class="media align-items-center mt-1" href="javascript:">
                                <div class="icon icon-soft-info icon-circle mr-3">
                                    <i class="tio-money"></i>
                                </div>
                                <div class="media-body">
                                    <span class="text-body text-hover-primary">{{$customer->balance. ' ' . \App\CPU\Helpers::currency_symbol() }}</span>
                                </div>
                            </div>

                            <hr>

                            @if ($customer->id!=0)
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5>{{\App\CPU\translate('contact_info')}}</h5>
                                </div>
                                <ul class="list-unstyled list-unstyled-py-2">
                                    <li>
                                        <i class="tio-android-phone-vs mr-2"></i>
                                        {{$customer['mobile']}}
                                    </li>
                                    @if ($customer['email'])
                                        <li>
                                            <i class="tio-online mr-2"></i>
                                            {{$customer['email']}}
                                        </li>
                                    @endif
                                </ul>
                                <hr>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5>{{\App\CPU\translate('addresses')}}</h5>
                                </div>
                                <ul class="list-unstyled list-unstyled-py-2">
                                    <li>{{\App\CPU\translate('state')}}: {{$customer['state']}}</li>
                                    <li>{{\App\CPU\translate('city')}}: {{$customer['city']}}</li>
                                    <li>{{\App\CPU\translate('zip_code')}}: {{$customer['zip_code']}}</li>
                                    <li>{{\App\CPU\translate('address')}}: {{$customer['address']}}</li>
                                </ul>
                            @endif
                        </div>
                   @endif
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="print-invoice" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{\App\CPU\translate('print')}} {{\App\CPU\translate('invoice')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body row font-one-cv">
                    <div class="col-md-12">
                        <div class="text-center">
                            <input type="button" class="btn btn-primary non-printable print-div" data-name="printableArea"
                                value="{{\App\CPU\translate('Proceed, If thermal printer is ready')}}."/>
                            <a href="{{url()->previous()}}" class="btn btn-danger non-printable">{{\App\CPU\translate('Back')}}</a>
                        </div>
                        <hr class="non-printable">
                    </div>
                    <div class="row m-auto" id="printableArea">
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script>
        "use strict";

        $(".print-invoice").on('click', function(){
            let order_id = $(this).data('id');
            print_invoice(order_id);
        });

        function print_invoice(order_id) {
            $.get({
                url: '{{url('/')}}/admin/pos/invoice/'+order_id,
                dataType: 'json',
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    $('#print-invoice').modal('show');
                    $('#printableArea').empty().html(data.view);
                },
                complete: function () {
                    $('#loading').hide();
                },
            });
        }
    </script>
    <script src={{asset("public/assets/admin/js/global.js")}}></script>
@endpush
