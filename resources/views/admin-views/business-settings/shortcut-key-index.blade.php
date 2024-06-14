@extends('layouts.admin.app')

@section('title',\App\CPU\translate('Settings'))

@section('content')
    <div class="content container-fluid">
        <div class="row">
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <h3>{{\App\CPU\translate('short_cut_key_list')}}</h3>
                    </div>
                    <div class="card-body">
                        <span>{{\App\CPU\translate('to_click_order')}} : {{\App\CPU\translate('alt')}} + {{\App\CPU\translate('O')}}</span><br>
                        <span>{{\App\CPU\translate('to_click_payment_submit')}} : {{\App\CPU\translate('alt')}} + {{\App\CPU\translate('S')}}</span><br>
                        <span>{{\App\CPU\translate('to_click_cancel_cart_item_all')}} : {{\App\CPU\translate('alt')}} + {{\App\CPU\translate('C')}}</span><br>
                        <span>{{\App\CPU\translate('to_click_add_new_customer')}} : {{\App\CPU\translate('alt')}} + {{\App\CPU\translate('A')}}</span> <br>
                        <span>{{\App\CPU\translate('to_click_add_new_customer_form')}} : {{\App\CPU\translate('alt')}} + {{\App\CPU\translate('N')}}</span><br>
                        <span>{{\App\CPU\translate('to_click_short_cut_keys')}} : {{\App\CPU\translate('alt')}} + {{\App\CPU\translate('K')}}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
