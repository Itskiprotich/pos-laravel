@extends('layouts.admin.app')

@section('title',\App\CPU\translate('transection_list'))

@push('css_or_js')

@endpush

@section('content')
<div class="content container-fluid">
        <div class="row align-items-center mb-3">
            <div class="col-sm mb-2 mb-sm-0">
                <h1 class="page-header-title d-flex align-items-center g-2px text-capitalize"><i
                        class="tio-files"></i> {{\App\CPU\translate('transection_list')}}
                    <span class="badge badge-soft-dark ml-2">{{$transections->total()}}</span>
                </h1>
            </div>
        </div>
        <div class="row ">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <div class="card">
                    <form action="{{url()->current()}}" method="GET">
                        <div class="row m-1">
                            <div class="form-group col-12 col-sm-6 col-md-3 col-lg-3">
                                <label class="input-label" for="exampleFormControlInput1">{{\App\CPU\translate('account')}} </label>
                                <select id="account_id" name="account_id" class="form-control js-select2-custom">
                                    <option value="">---{{\App\CPU\translate('select')}}---</option>
                                    @foreach ($accounts as $account)
                                        <option value="{{$account['id']}}" {{ $accId==$account['id']?'selected':''}}>{{$account['account']}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-12 col-sm-6 col-md-3 col-lg-3">
                                <label class="input-label" for="exampleFormControlInput1">{{\App\CPU\translate('type')}} </label>
                                <select id="tran_type" name="tran_type" class="form-control js-select2-custom">
                                    <option value="">---{{\App\CPU\translate('select')}}---</option>
                                    <option value="Expense" {{ $tranType=='Expense'?'selected':''}}>{{\App\CPU\translate('expense')}}</option>
                                    <option value="Transfer" {{ $tranType=='Transfer'?'selected':''}}>{{\App\CPU\translate('transfer')}}</option>
                                    <option value="Income" {{ $tranType=='Income'?'selected':''}}>{{\App\CPU\translate('income')}}</option>
                                    <option value="Payable" {{ $tranType=='Payable'?'selected':''}}>{{\App\CPU\translate('payable')}}</option>
                                    <option value="Receivable" {{ $tranType=='Receivable'?'selected':''}}>{{\App\CPU\translate('receivable')}}</option>
                                </select>
                            </div>
                            <div class="form-group col-12 col-sm-6 col-md-3 col-lg-3">
                                <label class="input-label" for="exampleFormControlInput1">{{\App\CPU\translate('start_date')}} </label>
                                <input id="start_date" type="date" name="from" class="form-control" value="{{ $from }}">
                            </div>
                            <div class="form-group col-12 col-sm-6 col-md-3 col-lg-3">
                                <label class="input-label" for="exampleFormControlInput1">{{\App\CPU\translate('expire_date')}} </label>
                                <input id="end_date" type="date" name="to" class="form-control" value="{{ $to }}">
                            </div>

                            @if ($accId!=null || $tranType!=null || $from!=null || $to!=null)
                                <?php
                                    $chk = 1;
                                ?>
                            @else
                            <?php
                                    $chk = 0;
                                ?>
                            @endif

                            <div class="col-12 ">
                                <div class="row d-flex justify-content-center ">
                                    <button class="btn btn-success col-3 mr-1">{{\App\CPU\translate('filter')}}</button>
                                    <a href="{{ route('admin.account.list-transection') }}" class="btn btn-danger col-3 mr-1">{{\App\CPU\translate('reset')}}</a>
                                    <a href="{{ route('admin.account.transection-export',['account_id'=>$accId,'tran_type'=>$tranType,'from'=>$from,'to'=>$to]) }}" class="btn btn-info col-3" data-toggle="tooltip" data-placement="top" title="{{ $chk==0?\App\CPU\translate('export__data'):''}}">{{\App\CPU\translate('export')}}</a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive datatable-custom">
                        <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                            <tr>
                                <th>{{ \App\CPU\translate('date') }}</th>
                                <th>{{ \App\CPU\translate('account') }}</th>
                                <th>{{\App\CPU\translate('type')}}</th>
                                <th>{{\App\CPU\translate('amount')}}</th>
                                <th >{{\App\CPU\translate('description')}}</th>
                                <th>{{ \App\CPU\translate('debit') }}</th>
                                <th >{{\App\CPU\translate('credit')}}</th>
                                <th >{{\App\CPU\translate('balance')}}</th>
                            </tr>
                            </thead>

                            <tbody>
                                @foreach ($transections as $key=>$transection)
                                    <tr>
                                        <td>{{ $transection->date }}</td>
                                        <td>
                                            @if($transection->account)
                                                {{$transection->account->account}}
                                            @else
                                                <span class="badge badge-danger">{{ \App\CPU\translate('Account Deleted') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($transection->tran_type == 'Expense')
                                                <span class="badge badge-danger">
                                                    {{ $transection->tran_type}} <br>
                                                </span>
                                            @elseif($transection->tran_type == 'Deposit')
                                                <span class="badge badge-info">
                                                    {{ $transection->tran_type}} <br>
                                                </span>
                                            @elseif($transection->tran_type == 'Transfer')
                                                <span class="badge badge-warning">
                                                    {{ $transection->tran_type}} <br>
                                                </span>
                                            @elseif($transection->tran_type == 'Income')
                                                <span class="badge badge-success">
                                                    {{ $transection->tran_type}} <br>
                                                </span>
                                            @elseif($transection->tran_type == 'Payable')
                                                <span class="badge badge-soft-warning">
                                                    {{ $transection->tran_type}} <br>
                                                </span>
                                            @elseif($transection->tran_type == 'Receivable')
                                                <span class="badge badge-soft-success">
                                                    {{ $transection->tran_type}} <br>
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $transection->amount ." ".\App\CPU\Helpers::currency_symbol()}}
                                        </td>
                                        <td>
                                            {{ Str::limit($transection->description,30) }}
                                        </td>
                                        <td>
                                            @if ($transection->debit)
                                                {{ $transection->amount ." ".\App\CPU\Helpers::currency_symbol()}}
                                            @else
                                                 {{ 0 ." ".\App\CPU\Helpers::currency_symbol()}}
                                            @endif
                                        </td>
                                        <td>
                                            @if ($transection->credit)
                                                {{ $transection->amount ." ".\App\CPU\Helpers::currency_symbol()}}
                                            @else
                                                 {{ 0 ." ".\App\CPU\Helpers::currency_symbol()}}
                                            @endif
                                        </td>
                                        <td>
                                            {{ $transection->balance ." ".\App\CPU\Helpers::currency_symbol()}}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="page-area">
                            <table>
                                <tfoot class="border-top">
                                {!! $transections->links() !!}
                                </tfoot>
                            </table>
                        </div>
                        @if(count($transections)==0)
                            <div class="text-center p-4">
                                <img class="mb-3 img-one-tranl" src="{{asset('public/assets/admin')}}/svg/illustrations/sorry.svg" alt="{{\App\CPU\translate('image_description')}}">
                                <p class="mb-0">{{ \App\CPU\translate('No_data_to_show')}}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script src={{asset("public/assets/admin/js/transaction.js")}}></script>
@endpush
