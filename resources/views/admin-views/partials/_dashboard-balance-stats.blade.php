<div class="col-sm-12 col-lg-4 mb-3 mt-3 mb-lg-5">
    <a class="card card-hover-shadow h-100 color-one" href="#">
        <div class="card-body">
            <div class="flex-between align-items-center mb-1">
                <div>
                    <h6 class="card-subtitle text-white">{{\App\CPU\translate('total_revenue')}}</h6>
                    <span class="card-title h2 text-white">
                        {{ $account['totalIncome']-$account['totalExpense'] ." ".\App\CPU\Helpers::currency_symbol()}}
                    </span>
                </div>
                <div class="mt-2">
                    <i class="tio-money-vs text-white font-one"></i>
                </div>
            </div>
        </div>
    </a>
</div>
<div class="col-sm-6 col-lg-4 mb-3 mt-3 mb-lg-5">
    <a class="card card-hover-shadow h-100 color-two" href="#">
        <div class="card-body">
            <div class="flex-between align-items-center mb-1">
                <div>
                    <h6 class="card-subtitle text-white">{{\App\CPU\translate('total_Income')}}</h6>
                    <span class="card-title h2 text-white">
                        {{ $account['totalIncome'] ." ".\App\CPU\Helpers::currency_symbol()}}
                    </span>
                </div>
                <div class="mt-2">
                    <i class="tio-money-vs text-white font-one"></i>
                </div>
            </div>
        </div>
    </a>
</div>
<div class="col-sm-6 col-lg-4 mb-3 mt-3 mb-lg-5">
    <a class="card card-hover-shadow h-100 color-three" href="#">
        <div class="card-body">
            <div class="flex-between align-items-center mb-1">
                <div>
                    <h6 class="card-subtitle text-white">{{\App\CPU\translate('total_Expense')}}</h6>
                    <span class="card-title h2 text-white">
                        {{ $account['totalExpense'] ." ".\App\CPU\Helpers::currency_symbol()}}
                    </span>
                </div>
                <div class="mt-2">
                    <i class="tio-money-vs font-one text-white"></i>
                </div>
            </div>
        </div>
    </a>
</div>

<div class="col-sm-6 col-lg-6 mb-3 mt-3 mb-lg-5">
    <a class="card card-hover-shadow h-100 color-four" href="#">
        <div class="card-body">
            <div class="flex-between align-items-center mb-1">
                <div>
                    <h6 class="card-subtitle text-white">{{\App\CPU\translate('account_payable')}}</h6>
                    <span class="card-title h2 text-white">
                        {{ $account['totalPayable'] ." ".\App\CPU\Helpers::currency_symbol()}}
                    </span>
                </div>
                <div class="mt-2">
                    <i class="tio-money-vs text-white font-one"></i>
                </div>
            </div>
        </div>
    </a>
</div>
<div class="col-sm-6 col-lg-6 mb-3 mt-3 mb-lg-5">
    <a class="card card-hover-shadow h-100 color-five" href="#">
        <div class="card-body">
            <div class="flex-between align-items-center mb-1">
                <div>
                    <h6 class="card-subtitle text-white">{{\App\CPU\translate('account_receivable')}}</h6>
                    <span class="card-title h2 text-white">
                        {{ $account['totalReceivable'] ." ".\App\CPU\Helpers::currency_symbol()}}
                    </span>
                </div>
                <div class="mt-2">
                    <i class="tio-money-vs text-white font-one"></i>
                </div>
            </div>
        </div>
    </a>
</div>
