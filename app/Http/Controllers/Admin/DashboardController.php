<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Transection;
use App\CPU\Helpers;
use Carbon\Carbon;
use App\Models\Account;
use App\Models\Product;
use Illuminate\Pagination\Paginator;

class DashboardController extends Controller
{
    public function __construct(
        private Transection $transection,
        private Account $account,
        private Product $product
    ){}

    /**
     * @return Application|Factory|View
     */
    public function dashboard(): Factory|View|Application
    {
        $totalPayableDebit = $this->transection->where('tran_type','Payable')->where('debit',1)->sum('amount');
        $totalPayableCredit = $this->transection->where('tran_type','Payable')->where('credit',1)->sum('amount');
        $totalPayable = $totalPayableCredit - $totalPayableDebit;

        $totalReceivableDebit = $this->transection->where('tran_type','Receivable')->where('debit',1)->sum('amount');
        $totalReceivableCredit = $this->transection->where('tran_type','Receivable')->where('credit',1)->sum('amount');
        $totalReceivable = $totalReceivableCredit - $totalReceivableDebit;

        $account = [
            'totalIncome' => $this->transection->where('tran_type','Income')->sum('amount'),
            'totalExpense' => $this->transection->where('tran_type','Expense')->sum('amount'),
            'totalPayable' => $totalPayable,
            'totalReceivable' => $totalReceivable,
        ];
        $monthlyIncome=[];
        for ($i=1;$i<=12;$i++){
            $from = Carbon::create(null, $i, 1)->startOfMonth();
            $to = Carbon::create(null, $i, 1)->endOfMonth();
            $monthlyIncome[$i] = $this->transection->where(['tran_type'=>'Income'])->whereBetween('date', [$from, $to])->sum('amount');
        }
        $monthlyExpense=[];
        for ($i=1;$i<=12;$i++){
            $from = Carbon::create(null, $i, 1)->startOfMonth();
            $to = Carbon::create(null, $i, 1)->endOfMonth();
            $monthlyExpense[$i] = $this->transection->where(['tran_type'=>'Expense'])->whereBetween('date', [$from, $to])->sum('amount');
        }

        $month = date('t');
        $totalDay = Carbon::now()->daysInMonth;

        $lastMonthIncome=[];
        for($i=1;$i<=$totalDay;$i++){
            $day = date('Y-m-'.$i);
            $lastMonthIncome[$i] = $this->transection->where(['tran_type'=>'Income'])->where('date', $day)->sum('amount');
        }

        $lastMonthExpense=[];
        for($i=1;$i<=$totalDay;$i++){
            $day = date('Y-m-'.$i);
            $lastMonthExpense[$i] = $this->transection->where(['tran_type'=>'Expense'])->where('date', $day)->sum('amount');
        }

        $stockLimit = Helpers::get_business_settings('stock_limit');
        $products = $this->product->where('quantity','<',$stockLimit)->orderBy('quantity')->take(5)->get();
        $accounts = $this->account->take(5)->get();

        return view('admin-views.dashboard',compact('account','monthlyIncome','monthlyExpense','accounts','products','lastMonthIncome','lastMonthExpense','month','totalDay'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function accountStats(Request $request): JsonResponse
    {
        if($request->statistics_type=='overall')
        {
            $totalPayableDebit = $this->transection->where('tran_type','Payable')->where('debit',1)->sum('amount');
            $totalPayableCredit = $this->transection->where('tran_type','Payable')->where('credit',1)->sum('amount');
            $totalPayable = $totalPayableCredit - $totalPayableDebit;

            $totalReceivableDebit = $this->transection->where('tran_type','Receivable')->where('debit',1)->sum('amount');
            $totalReceivableCredit = $this->transection->where('tran_type','Receivable')->where('credit',1)->sum('amount');
            $totalReceivable = $totalReceivableCredit - $totalReceivableDebit;

            $account = [
                'totalIncome' => $this->transection->where('tran_type','Income')->sum('amount'),
                'totalExpense' => $this->transection->where('tran_type','Expense')->sum('amount'),
                'totalPayable' => $totalPayable,
                'totalReceivable' => $totalReceivable,
            ];
        }elseif ($request->statistics_type=='today') {

            $totalPayableDebit = $this->transection->where('tran_type','Payable')->whereDate('date', '=', Carbon::now()->toDateString())->where('debit',1)->sum('amount');
            $totalPayableCredit = $this->transection->where('tran_type','Payable')->whereDate('date', '=', Carbon::now()->toDateString())->where('credit',1)->sum('amount');
            $totalPayable = $totalPayableCredit - $totalPayableDebit;

            $totalReceivableDebit = $this->transection->where('tran_type','Receivable')->whereDate('date', '=', Carbon::now()->toDateString())->where('debit',1)->sum('amount');
            $totalReceivableCredit = $this->transection->where('tran_type','Receivable')->whereDate('date', '=', Carbon::now()->toDateString())->where('credit',1)->sum('amount');
            $totalReceivable = $totalReceivableCredit - $totalReceivableDebit;

            $account = [
                'totalIncome' => $this->transection->where('tran_type','Income')->whereDate('date', '=', Carbon::now()->toDateString())->sum('amount'),
                'totalExpense' => $this->transection->where('tran_type','Expense')->whereDate('date', '=', Carbon::now()->toDateString())->sum('amount'),
                'totalPayable' => $totalPayable,
                'totalReceivable' => $totalReceivable,
            ];
        }elseif ($request->statistics_type=='month') {

            $totalPayableDebit = $this->transection->where('tran_type','Payable')->whereMonth('date', '=', Carbon::today())->where('debit',1)->sum('amount');
            $totalPayableCredit = $this->transection->where('tran_type','Payable')->whereMonth('date', '=', Carbon::today())->where('credit',1)->sum('amount');
            $totalPayable = $totalPayableCredit - $totalPayableDebit;

            $totalReceivableDebit = $this->transection->where('tran_type','Receivable')->whereMonth('date', '=', Carbon::today())->where('debit',1)->sum('amount');
            $totalReceivableCredit = $this->transection->where('tran_type','Receivable')->whereMonth('date', '=', Carbon::today())->where('credit',1)->sum('amount');
            $totalReceivable = $totalReceivableCredit - $totalReceivableDebit;

            $account = [
                'totalIncome' => $this->transection->where('tran_type','Income')->whereMonth('date', '=', Carbon::today())->sum('amount'),
                'totalExpense' => $this->transection->where('tran_type','Expense')->whereMonth('date', '=', Carbon::today())->sum('amount'),
                'totalPayable' => $totalPayable,
                'totalReceivable' => $totalReceivable,
            ];
        }
        return response()->json([
            'view'=> view('admin-views.partials._dashboard-balance-stats',compact('account'))->render()
        ],200);
    }

}
