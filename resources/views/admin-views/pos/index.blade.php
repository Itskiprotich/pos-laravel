<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <title>{{\App\CPU\translate('Add to cart page')}}</title>
    @php($favIcon=\App\Models\BusinessSetting::where(['key'=>'fav_icon'])->first()->value)
    <link rel="shortcut icon" href="{{asset('storage/app/public/shop').'/' . $favIcon }}">

    <link rel="stylesheet" href="{{asset('public/assets/admin')}}/css/google-fonts.css">
    <link rel="stylesheet" href="{{asset('public/assets/admin')}}/css/vendor.min.css">
    <link rel="stylesheet" href="{{asset('public/assets/admin')}}/vendor/icon-set/style.css">
    <link rel="stylesheet" href="{{asset('public/assets/admin')}}/css/theme.minc619.css?v=1.0">
    @stack('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{asset('public/assets/admin')}}/css/custom.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin')}}/css/pos.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin')}}/css/toastr.css">

    <style>
        .text-decoration{
            text-decoration: line-through;
        }
    </style>
</head>
<body class="footer-offset">
    <div class="direction-toggle">
        <i class="tio-settings"></i>
        <span></span>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div id="loading" class="d-none">
                    <div class="style-i1">
                        <img width="200" src="{{asset('public/assets/admin/img/loader.gif')}}" alt="{{\App\CPU\translate('loader gif')}}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <header id="header"
            class="navbar navbar-expand-lg navbar-fixed navbar-height navbar-flush navbar-container navbar-bordered">
        <div class="navbar-nav-wrap">
            <div class="navbar-brand-wrapper">
                @php($shop_logo=\App\Models\BusinessSetting::where('key','shop_logo')->first()->value)
                <a class="navbar-brand pt-0 pb-0" href="{{route('admin.dashboard')}}" aria-label="Front">
                    <img class="navbar-brand-logo w-i1"
                        src="{{onErrorImage($shop_logo,asset('storage/app/public/shop').'/' . $shop_logo,asset('public/assets/admin/img/160x160/img2.jpg') ,'shop/')}}"
                        alt="{{\App\CPU\translate('Logo')}}">
                </a>
            </div>
            <div class="navbar-nav-wrap-content-right">
                <ul class="navbar-nav align-items-center flex-row">
                    <li class="nav-item d-sm-inline-block">
                        <div class="hs-unfold">
                            <a id="short-cut" class="js-hs-unfold-invoker btn btn-icon btn-ghost-secondary rounded-circle"
                                data-toggle="modal" data-target="#short-cut-keys" title="{{\App\CPU\translate('short_cut_keys')}}">
                                <i class="tio-keyboard"></i>
                            </a>
                        </div>
                    </li>
                    <li class="nav-item d-sm-inline-block">
                        <div class="hs-unfold">
                            <a data-toggle="tooltip" class="js-hs-unfold-invoker btn btn-icon btn-ghost-secondary rounded-circle"
                                href="{{route('admin.pos.orders')}}" target="_blank" title="{{\App\CPU\translate('order_list')}}">
                                <i class="tio-shopping-basket"></i>
                            </a>
                            <div class="tooltip bs-tooltip-top" role="tooltip">
                                <div class="arrow"></div>
                                <div class="tooltip-inner"></div>
                            </div>
                        </div>
                    </li>
                    <li class="nav-item">
                        <div class="hs-unfold">
                            <a class="js-hs-unfold-invoker navbar-dropdown-account-wrapper" href="javascript:;"
                                data-hs-unfold-options='{
                                        "target": "#accountNavbarDropdown",
                                        "type": "css-animation"
                                    }'>
                                <div class="avatar avatar-sm avatar-circle">
                                    <img class="avatar-img"
                                        src="{{auth('admin')->user()->image_fullpath}}"
                                        alt="{{\App\CPU\translate('Image')}}">
                                    <span class="avatar-status avatar-sm-status avatar-status-success"></span>
                                </div>
                            </a>
                            <div id="accountNavbarDropdown"
                                class="w-i2 hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-right navbar-dropdown-menu navbar-dropdown-account">
                                <div class="dropdown-item-text">
                                    <div class="media align-items-center">
                                        <div class="avatar avatar-sm avatar-circle mr-2">
                                            <img class="avatar-img"
                                                src="{{auth('admin')->user()->image_fullpath}}"
                                                alt="{{\App\CPU\translate('Owner image')}}">
                                        </div>
                                        <div class="media-body">
                                            <span class="card-title h5">{{auth('admin')->user()->f_name}}</span>
                                            <span class="card-text">{{auth('admin')->user()->email}}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" id="logoutLink">
                                    <span class="text-truncate pr-2" title="Sign out">{{\App\CPU\translate('sign_out')}}</span>
                                </a>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <main id="content" role="main" class="main pointer-event">
        <section class="section-content pt-5">
            <div class="container-fluid">
                <div class="d-flex flex-wrap">
                    <div class="order--pos-left">
                        <div class="card">
                            <h5 class="p-3 m-0 bg-light">{{\App\CPU\translate('Product_Section')}}</h5>
                            <div class="px-3 py-4">
                                <div class="row gy-1">
                                    <div class="col-sm-6">
                                        <div class="input-group d-flex justify-content-end">
                                            <select name="category" id="category" class="form-control js-select2-custom w-100 category-show"
                                                    title="{{\App\CPU\translate('select category')}}">
                                                <option value="">{{\App\CPU\translate('all_categories')}}</option>
                                                @foreach ($categories as $item)
                                                    <option value="{{ $item['id'] }}" {{$category==$item->id?'selected':''}}>{{ Str::limit($item['name'],15) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <form class="">
                                            <div class="input-group-overlay input-group-merge input-group-custom">
                                                <div class="input-group-prepend">
                                                    <div class="input-group-text">
                                                        <i class="tio-search"></i>
                                                    </div>
                                                </div>
                                                <input id="search" autocomplete="off" type="text" name="search"
                                                    class="form-control search-bar-input"
                                                    placeholder="{{\App\CPU\translate('search_by_code_or_name')}}"
                                                    aria-label="Search here" >
                                                <diV class="pos-search-card w-4 position-absolute z-index-1 w-100">
                                                    <div id="search-box" class="card card-body search-result-box d--none"></div>
                                                </diV>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pt-2" id="items">
                                <div class="pos-item-wrap">
                                    @foreach($products as $product)
                                        @include('admin-views.pos._single_product',['product'=>$product])
                                    @endforeach
                                </div>
                                @if(count($products)==0)
                                    <div class="text-center p-4">
                                        <img class="mb-3 w-i5" src="{{asset('public/assets/admin')}}/svg/illustrations/sorry.svg" alt="{{\App\CPU\translate('Image Description')}}" >
                                        <p class="mb-0">{{ \App\CPU\translate('No_data_to_show')}}</p>
                                    </div>
                                @endif

                                <div class="table-responsive mt-4">
                                    <div class="px-4 d-flex justify-content-lg-end">
                                        {!!$products->withQueryString()->links()!!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @php($customers = \App\Models\Customer::get())
                    <div class="order--pos-right">
                        <div class="card billing-section-wrap">
                            <h5 class="p-3 m-0 bg-light">{{\App\CPU\translate('Billing_Section')}}</h5>
                            <div>
                                <div class="card-body pb-0">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <div class="flex-grow-1">
                                            <select id='customer' name="customer_id"
                                                    class="form-control js-data-example-ajax customer-change">
                                                <option>{{\App\CPU\translate('--select-customer--')}}</option>
                                                <option value="0">{{\App\CPU\translate('walking_customer')}}</option>
                                            </select>
                                        </div>
                                        <div class="">
                                            <button class="w-i6 d-inline-block btn btn-success rounded text-nowrap" id="add_new_customer" type="button" data-toggle="modal" data-target="#add-customer" title="{{\App\CPU\translate('Add Customer')}}">
                                                <i class="tio-add"></i>
                                                {{ \App\CPU\translate('customer')}}
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="input-label text-capitalize" >
                                            {{\App\CPU\translate('current_customer')}} :
                                            <span class="style-i4" id="current_customer"></span>
                                        </label>
                                    </div>

                                    <div class="d-flex gap-2 flex-wrap align-items-center mb-3">
                                        <div class="flex-grow-1">
                                            <select id='cart_id' name="cart_id"
                                                    class=" form-control js-select2-custom cart-change">
                                            </select>
                                        </div>

                                        <div>
                                            <a class="w-i6 d-inline-block btn btn-danger rounded" href="{{route('admin.pos.clear-cart-ids')}}">
                                                {{ \App\CPU\translate('clear_cart')}}
                                            </a>
                                        </div>

                                        <div>
                                            <a class="w-i6 d-inline-block btn btn-success rounded" href="{{route('admin.pos.new-cart-id')}}">
                                                {{ \App\CPU\translate('new_order')}}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="text-center">
                                        <div id="cartloader" class="d-none">
                                            <img width="50" src="{{asset('public/assets/admin/img/loader.gif')}}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="cart">
                                @include('admin-views.pos._cart',['cart_id'=>$cartId])
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <div class="modal fade" id="quick-view" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content" id="quick-view-modal">

                </div>
            </div>
        </div>
        @php($order=\App\Models\Order::find(session('last_order')))
        @if($order)
            @php(session(['last_order'=> false]))
            <div class="modal fade" id="print-invoice" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content modal-content1">
                        <div class="modal-header">
                            <h5 class="modal-title">{{\App\CPU\translate('print_invoice')}}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span class="text-dark" aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body row font-i1">
                            <div class="col-md-12">
                                <div class="text-center">
                                    <input id="print_invoice" type="button" class="mt-2 btn btn-primary non-printable print-div"
                                        data-name="printableArea"
                                        value="Proceed, If thermal printer is ready."/>
                                    <a id="invoice_close" data-route="{{url()->previous()}}"
                                    class="mt-2 btn btn-danger non-printable invoice-close">{{\App\CPU\translate('back')}}</a>
                                </div>
                                <hr class="non-printable">
                            </div>
                            <div class="row m-auto" id="printableArea">
                                @include('admin-views.pos.order.invoice')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </main>

    <script src="{{asset('public/assets/admin')}}/js/vendor.min.js"></script>
    <script src="{{asset('public/assets/admin')}}/js/theme.min.js"></script>
    <script src="{{asset('public/assets/admin')}}/js/sweet_alert.js"></script>
    <script src="{{asset('public/assets/admin')}}/js/toastr.js"></script>
    <script src="{{asset('public/assets/admin')}}/js/pos.js"></script>
{!! Toastr::message() !!}

@if ($errors->any())
    <script>
        "use strict";

        @foreach($errors->all() as $error)
        toastr.error('{{$error}}', Error, {
            CloseButton: true,
            ProgressBar: true
        });
        @endforeach
    </script>
@endif

<script>
    "use strict";

    $(document).on('click', '#logoutLink', function(e) {
        e.preventDefault();

        Swal.fire({
            title: '{{\App\CPU\translate('Do you want to logout')}}?',
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonColor: '#FC6A57',
            cancelButtonColor: '#363636',
            confirmButtonText: `{{\App\CPU\translate('Yes')}}`,
            denyButtonText: `{{\App\CPU\translate('Don\'t Logout')}}'`,
        }).then((result) => {
            if (result.value) {
                window.location.href = '{{route('admin.auth.logout')}}';
            } else {
                Swal.fire('{{\App\CPU\translate('Canceled')}}', '', '{{\App\CPU\translate('info')}}');
            }
        });
    });

    $(document).on('ready', function () {

        $(".print-div").on('click', function(){
            let divName = $(this).data('name');
            printDiv(divName);
        });

        $(".invoice-close").on('click', function(){
            window.location.href = $(this).data('route');
        });

        $('.category-show').on('change', function() {
            set_category_filter($(this).val());
        });

        $('.cart-change').on('change', function() {
            cart_change($(this).val());
        });

        $('.customer-change').on('change', function() {
            customer_change($(this).val());
        });

        $(".single-cart-data").on('click', function(){
            let order_id = $(this).data('id');
            addToCart(order_id);
        });

        $('.js-hs-unfold-invoker').each(function () {
            var unfold = new HSUnfold($(this)).init();
        });

        $('#search').focus();
        $.ajax({
            url: '{{route('admin.pos.get-cart-ids')}}',
            type: 'GET',

            dataType: 'json',
            beforeSend: function () {
                $('#loading').removeClass('d-none');
            },
            success: function (data) {
                var output = '';
                    for(var i=0; i<data.cart_nam.length; i++) {
                        output += `<option value="${data.cart_nam[i]}" ${data.current_user==data.cart_nam[i]?'selected':''}>${data.cart_nam[i]}</option>`;
                    }
                    $('#cart_id').html(output);
                    $('#current_customer').text(data.current_customer);
                    $('#cart').empty().html(data.view);
                    if(data.user_type === 'sc')
                    {
                        console.log('after add');
                        customer_Balance_Append(data.user_id);
                    }
            },
            complete: function () {
                $('#loading').addClass('d-none');
            },
        });
    });

    $(document).on('ready', function(){

        $(".direction-toggle").on("click", function () {
            setDirection(localStorage.getItem("direction"));
        });

        function setDirection(direction) {
            if (direction == "rtl") {
                localStorage.setItem("direction", "ltr");
                $("html").attr('dir', 'ltr');
            $(".direction-toggle").find('span').text('Toggle RTL')
            } else {
                localStorage.setItem("direction", "rtl");
                $("html").attr('dir', 'rtl');
            $(".direction-toggle").find('span').text('Toggle LTR')
            }
        }

        if (localStorage.getItem("direction") == "rtl") {
            $("html").attr('dir', "rtl");
            $(".direction-toggle").find('span').text('Toggle LTR')
        } else {
            $("html").attr('dir', "ltr");
            $(".direction-toggle").find('span').text('Toggle RTL')
        }

    })

    function payment_option(val) {
        if ($(val).val() != 1 && $(val).val() != 0) {
            $("#collected_cash").addClass('d-none');
            $("#returned_amount").addClass('d-none');
            $("#balance").addClass('d-none');
            $("#remaining_balance").addClass('d-none');
            $("#transaction_ref").removeClass('d-none');
            $('#cash_amount').attr('required',false);
            console.log($(val).val());
        } else if ($(val).val() == 1) {
            $("#collected_cash").removeClass('d-none');
            $("#returned_amount").removeClass('d-none');
            $("#transaction_ref").addClass('d-none');
            $("#balance").addClass('d-none');
            $("#remaining_balance").addClass('d-none');
            console.log($(val).val());

        } else if($(val).val() == 0){
            $("#balance").removeClass('d-none');
            $("#remaining_balance").removeClass('d-none');
            $("#collected_cash").addClass('d-none');
            $("#returned_amount").addClass('d-none');
            $("#transaction_ref").addClass('d-none');
            $('#cash_amount').attr('required',false);
            let customerId = $('#customer').val();
            $.ajax({
            url: '{{route('admin.pos.customer-balance')}}',
            type: 'GET',
            data: {
                customer_id: customerId
            },
            dataType: 'json',
            beforeSend: function () {
                $('#loading').removeClass('d-none');
                console.log("loding");
            },
            success: function (data) {
                console.log(data.customer_balance);
                let balance = data.customer_balance;
                let order_total = $('#total_price').text();
                let remain_balance = parseInt(balance) - parseInt(order_total);
                $('#balance_customer').val(balance);
                $('#balance_remain').val(remain_balance);
            },
            complete: function () {
                $('#loading').addClass('d-none');
            },
        });
        }
    }

    function customer_change(val) {
        $.post({
                url: '{{route('admin.pos.remove-coupon')}}',
                data: {
                    _token: '{{csrf_token()}}',
                    user_id:val
                },
                beforeSend: function () {
                    $('#loading').removeClass('d-none');
                },
                success: function (data) {
                    var output = '';
                    for(var i=0; i<data.cart_nam.length; i++) {
                        output += `<option value="${data.cart_nam[i]}" ${data.current_user==data.cart_nam[i]?'selected':''}>${data.cart_nam[i]}</option>`;
                    }
                    $('#cart_id').html(output);
                    $('#current_customer').text(data.current_customer);
                    $('#cart').empty().html(data.view);
                    customer_Balance_Append(val);
                },
                complete: function () {
                    $('#loading').addClass('d-none');
                }
            });
    }

    function cart_change(val)
    {
        let  cart_id = val;
        let url = "{{route('admin.pos.change-cart')}}"+'/?cart_id='+val;
        document.location.href=url;
    }

    function extra_discount()
    {
        let discount = $('#dis_amount').val();
        console.log(discount);
        let type = $('#type_ext_dis').val();
        if(discount)
        {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });
            $.post({
                url: '{{ route('admin.pos.discount') }}',
                data: {
                    _token: '{{csrf_token()}}',
                    discount:discount,
                    type:type,
                },
                beforeSend: function () {
                    $('#loading').removeClass('d-none');
                },
                success: function (data) {
                    if(data.extra_discount==='success')
                    {
                        toastr.success('{{ \App\CPU\translate('extra_discount_added_successfully') }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }else if(data.extra_discount==='empty')
                    {
                        toastr.warning('{{ \App\CPU\translate('your_cart_is_empty') }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });

                    }else{
                        toastr.warning('{{ \App\CPU\translate('this_discount_is_not_applied_for_this_amount') }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }

                    $('.modal-backdrop').addClass('d-none');
                    $('#cart').empty().html(data.view);
                    if(data.user_type === 'sc')
                    {
                        customer_Balance_Append(data.user_id);
                    }
                    $('#search').focus();
                },
                complete: function () {
                    $('.modal-backdrop').addClass('d-none');
                    $(".footer-offset").removeClass("modal-open");
                    $('#loading').addClass('d-none');
                }
            });
        }
    }

    function coupon_discount()
    {
        let  coupon_code = $('#coupon_code').val();
        $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.pos.coupon-discount')}}',
                data: {
                    _token: '{{csrf_token()}}',
                    coupon_code:coupon_code,
                },
                beforeSend: function () {
                    $('#loading').removeClass('d-none');
                },
                success: function (data) {
                    console.log(data);
                    if(data.coupon === 'success')
                    {
                        toastr.success('{{ \App\CPU\translate('coupon_added_successfully') }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }else if(data.coupon === 'amount_low')
                    {
                        toastr.warning('{{ \App\CPU\translate('this_discount_is_not_applied_for_this_amount') }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }else if(data.coupon === 'cart_empty')
                    {
                        toastr.warning('{{ \App\CPU\translate('your_cart_is_empty') }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }
                    else {
                        toastr.warning('{{ \App\CPU\translate('coupon_is_invalid') }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }

                    $('#cart').empty().html(data.view);
                    if(data.user_type === 'sc')
                    {
                        customer_Balance_Append(data.user_id);
                    }
                    $('#search').focus();
                },
                complete: function () {
                    $('.modal-backdrop').addClass('d-none');
                    $(".footer-offset").removeClass("modal-open");
                    $('#loading').addClass('d-none');
                }
            });

    }

    $(document).on('ready', function () {
        @if($order)
            $('#print-invoice').modal('show');
        @endif
    });

    function set_category_filter(id) {
        var nurl = new URL('{!!url()->full()!!}');
        nurl.searchParams.set('category_id', id);
        location.href = nurl;
    }

    $('#search-form').on('submit', function (e) {
        e.preventDefault();
        var keyword = $('#datatableSearch').val();
        var nurl = new URL('{!!url()->full()!!}');
        nurl.searchParams.set('keyword', keyword);
        location.href = nurl;
    });

    function quickView(product_id) {
        $.ajax({
            url: '{{route('admin.pos.quick-view')}}',
            type: 'GET',
            data: {
                product_id: product_id
            },
            dataType: 'json',
            beforeSend: function () {
                $('#loading').removeClass('d-none');
            },
            success: function (data) {
                $('#quick-view').modal('show');
                $('#quick-view-modal').empty().html(data.view);
            },
            complete: function () {
                $('#loading').addClass('d-none');
            },
        });
    }

    function addToCart(form_id) {
        let productId = form_id;
        let productQty = $('#product_qty').val();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });
            $.post({
                url: '{{ route('admin.pos.add-to-cart') }}',
                data: {
                    _token: '{{csrf_token()}}',
                    id:productId,
                    quantity:productQty,
                },
                beforeSend: function () {
                    $('#cartloader').removeClass('d-none');
                },
                success: function (data) {
                    if(data.qty==0)
                    {
                        toastr.warning('{{\App\CPU\translate('product_quantity_end!')}}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }else{
                        toastr.success('{{\App\CPU\translate('item_has_been_added_in_your_cart!')}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                    }

                    $('#cart').empty().html(data.view);
                    if(data.user_type === 'sc')
                    {
                        customer_Balance_Append(data.user_id);
                    }
                    $('#search').val('').focus();
                    $('#search-box').addClass('d-none');
                },
                complete: function () {
                    $('#cartloader').addClass('d-none');

                }
            });

    }

    function removeFromCart(key) {
        $.post('{{ route('admin.pos.remove-from-cart') }}', {_token: '{{ csrf_token() }}', key: key}, function (data) {

                $('#cart').empty().html(data.view);
                if(data.user_type === 'sc')
                {
                    customer_Balance_Append(data.user_id);
                }
                toastr.info('{{\App\CPU\translate('item_has_been_removed_from_cart')}}', {
                    CloseButton: true,
                    ProgressBar: true
                });
            $('#search').focus();

        });
    }

    function emptyCart() {
        Swal.fire({
            title: '{{\App\CPU\translate('Are_you_sure?')}}',
            text: '{{\App\CPU\translate('You_want_to_remove_all_items_from_cart!!')}}',
            type: 'warning',
            showCancelButton: true,
            cancelButtonColor: 'default',
            confirmButtonColor: '#161853',
            cancelButtonText: '{{\App\CPU\translate('No')}}',
            confirmButtonText: '{{\App\CPU\translate('Yes')}}',
            reverseButtons: true
        }).then((result) => {
            if (result.value) {
                $.post('{{ route('admin.pos.emptyCart') }}', {_token: '{{ csrf_token() }}'}, function (data) {
                    $('#cart').empty().html(data.view);
                    $('#search').focus();
                    if(data.user_type === 'sc')
                    {
                        customer_Balance_Append(data.user_id);
                    }
                    toastr.info('{{\App\CPU\translate('Item_has_been_removed_from_cart')}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                });
            }
        })

    }

    function updateCart() {
        $.post('<?php echo e(route('admin.pos.cart_items')); ?>', {_token: '<?php echo e(csrf_token()); ?>'}, function (data) {
            $('#cart').empty().html(data);

        });
    }

    function updateQuantity(id,qty) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });
        $.post({
            url: '{{ route('admin.pos.updateQuantity') }}',
            data: {
                _token: '{{csrf_token()}}',
                key: id,
                quantity: qty,
            },
            beforeSend: function () {
                $('#loading').removeClass('d-none');
            },
            success: function (data) {
                if(data.qty<0)
                {
                    toastr.warning('{{\App\CPU\translate('product_quantity_is_not_enough!')}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
                if(data.upQty==='zeroNegative')
                {
                    toastr.warning('{{\App\CPU\translate('Product_quantity_can_not_be_zero_or_less_than_zero_in_cart!')}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }

                $('#search').focus();
                $('#cart').empty().html(data.view);
                if(data.user_type === 'sc')
                {
                    customer_Balance_Append(data.user_id);
                }
            },
            complete: function () {
                $('#loading').addClass('d-none');
            }
        });



    }

    $('.js-select2-custom').each(function () {
        var select2 = $.HSCore.components.HSSelect2.init($(this));
    });

    $('.js-data-example-ajax').select2({
        ajax: {
            url: '{{route('admin.pos.customers')}}',
            data: function (params) {
                return {
                    q: params.term,
                    page: params.page
                };
            },
            processResults: function (data) {
                return {
                    results: data
                };
            },
            __port: function (params, success, failure) {
                var $request = $.ajax(params);

                $request.then(success);
                $request.fail(failure);

                return $request;
            }
        }
    });

    jQuery(".search-bar-input").on('keyup',function () {
        $(".search-card").removeClass('d-none').show();
        let name = $(".search-bar-input").val();
        if (name.length >0) {
            $('#search-box').removeClass('d-none').show();
            $.get({
                url: '{{route('admin.pos.search-products')}}',
                dataType: 'json',
                data: {
                    name: name
                },
                beforeSend: function () {
                    $('#loading').removeClass('d-none');
                },
                success: function (data) {
                    if (data.count == 0) {
                        $('#search-box').addClass('d-none');
                    }
                    $('.search-result-box').empty().html(data.result);
                },
                complete: function () {
                    $('#loading').addClass('d-none');
                },
            });
        } else {
            $('.search-result-box').empty();
            $('#search-box').addClass('d-none');
        }
    });

    jQuery(".search-bar-input").on('keyup',delay(function () {
        $(".search-card").removeClass('d-none').show();
        let name = $(".search-bar-input").val();
        if (name.length > 0 || isNaN(name)) {
            $.get({
                url: '{{route('admin.pos.search-by-add')}}',
                dataType: 'json',
                data: {
                    name: name
                },
                success: function (data) {
                    if (data.count == 1) {
                        $('#search').attr("disabled", true);
                        addToCart(data.id);
                        $('#search').attr("disabled", false);
                        $('.search-result-box').empty().html(data.result);
                        $('#search').val('');
                        $('#search-box').addClass('d-none');
                    }
                },
            });
        } else {
            $('.search-result-box').empty();
        }
    },1000));

</script>
@stack('script_2')
</body>
</html>
