<?php

namespace App\Http\Controllers\Api\V1;

use Carbon\Carbon;
use App\CPU\Helpers;
use App\Models\Product;
use App\Models\Transection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\StockLimitedProductsResource;

class DashboardController extends Controller
{
    public function __construct(
        private Transection $transection,
        private Product     $product
    )
    {
    }

    /**
     * @param Request $request
     * @return JsonResponse|void
     */
    public function getIndex(Request $request)
    {
        if ($request->statistics_type == 'overall') {
            $totalPayableDebit = $this->transection->where('tran_type', 'Payable')->where('debit', 1)->sum('amount');
            $totalPaybleCredit = $this->transection->where('tran_type', 'Payable')->where('credit', 1)->sum('amount');
            $totalPayable = $totalPaybleCredit - $totalPayableDebit;

            $totalReceivableDebit = $this->transection->where('tran_type', 'Receivable')->where('debit', 1)->sum('amount');
            $totalReceivableCredit = $this->transection->where('tran_type', 'Receivable')->where('credit', 1)->sum('amount');
            $totalReceivable = $totalReceivableCredit - $totalReceivableDebit;

            $revenueSummary = [
                'totalIncome' => $this->transection->where('tran_type', 'Income')->sum('amount'),
                'totalExpense' => $this->transection->where('tran_type', 'Expense')->sum('amount'),
                'totalPayable' => $totalPayable,
                'totalReceivable' => $totalReceivable,
            ];
            return response()->json([
                'revenueSummary' => $revenueSummary
            ], 200);
        } elseif ($request->statistics_type == 'today') {

            $totalPayableDebit = $this->transection->where('tran_type', 'Payable')->whereDate('date', '=', Carbon::now()->toDateString())->where('debit', 1)->sum('amount');
            $totalPaybleCredit = $this->transection->where('tran_type', 'Payable')->whereDate('date', '=', Carbon::now()->toDateString())->where('credit', 1)->sum('amount');
            $totalPayable = $totalPaybleCredit - $totalPayableDebit;

            $totalReceivableDebit = $this->transection->where('tran_type', 'Receivable')->whereDate('date', '=', Carbon::now()->toDateString())->where('debit', 1)->sum('amount');
            $totalReceivableCredit = $this->transection->where('tran_type', 'Receivable')->whereDate('date', '=', Carbon::now()->toDateString())->where('credit', 1)->sum('amount');
            $totalReceivable = $totalReceivableCredit - $totalReceivableDebit;

            $revenueSummary = [
                'totalIncome' => $this->transection->where('tran_type', 'Income')->whereDate('date', '=', Carbon::now()->toDateString())->sum('amount'),
                'totalExpense' => $this->transection->where('tran_type', 'Expense')->whereDate('date', '=', Carbon::now()->toDateString())->sum('amount'),
                'totalPayable' => $totalPayable,
                'totalReceivable' => $totalReceivable,
            ];
            return response()->json([
                'revenueSummary' => $revenueSummary
            ], 200);
        } elseif ($request->statistics_type == 'month') {

            $totalPayableDebit = $this->transection->where('tran_type', 'Payable')->whereMonth('date', '=', Carbon::today())->where('debit', 1)->sum('amount');
            $totalPaybleCredit = $this->transection->where('tran_type', 'Payable')->whereMonth('date', '=', Carbon::today())->where('credit', 1)->sum('amount');
            $totalPayable = $totalPaybleCredit - $totalPayableDebit;

            $totalReceivableDebit = $this->transection->where('tran_type', 'Receivable')->whereMonth('date', '=', Carbon::today())->where('debit', 1)->sum('amount');
            $totalReceivableCredit = $this->transection->where('tran_type', 'Receivable')->whereMonth('date', '=', Carbon::today())->where('credit', 1)->sum('amount');
            $totalReceivable = $totalReceivableCredit - $totalReceivableDebit;

            $revenueSummary = [
                'totalIncome' => $this->transection->where('tran_type', 'Income')->whereMonth('date', '=', Carbon::today())->sum('amount'),
                'totalExpense' => $this->transection->where('tran_type', 'Expense')->whereMonth('date', '=', Carbon::today())->sum('amount'),
                'totalPayable' => $totalPayable,
                'totalReceivable' => $totalReceivable,
            ];
            return response()->json([
                'revenueSummary' => $revenueSummary
            ], 200);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function productLimitedStockList(Request $request): JsonResponse
    {
        $limit = $request['limit'] ?? 10;
        $offset = $request['offset'] ?? 1;

        $stockLimit = Helpers::get_business_settings('stock_limit');
        $stockLimitedProduct = $this->product->with('unit', 'supplier')->where('quantity', '<', $stockLimit)->orderBy('quantity')->latest()->paginate($limit, ['*'], 'page', $offset);
        $stockLimitedProducts = StockLimitedProductsResource::collection($stockLimitedProduct);

        return response()->json([
            'total' => $stockLimitedProducts->total(),
            'offset' => $offset,
            'limit' => $limit,
            'stock_limited_products' => $stockLimitedProducts->items(),
        ], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function quantityIncrease(Request $request): JsonResponse
    {
        $product = $this->product->find($request->id);
        $totalQuantity = $product->quantity + $request->quantity;
        if($totalQuantity >= 0)
        {
            $product->quantity = $product->quantity + $request->quantity;
            $product->save();
            return response()->json(['message' => 'Product quantity updated successfully'], 200);
        }else{
            return response()->json(['message' => 'product quantity can not be less than 0!'], 403);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse|void
     */
    public function getFilter(Request $request)
    {
        if ($request->statistics_type == 'overall') {
            $totalPayableDebit = $this->transection->where('tran_type', 'Payable')->where('debit', 1)->sum('amount');
            $totalPaybleCredit = $this->transection->where('tran_type', 'Payable')->where('credit', 1)->sum('amount');
            $totalPayable = $totalPaybleCredit - $totalPayableDebit;

            $totalReceivableDebit = $this->transection->where('tran_type', 'Receivable')->where('debit', 1)->sum('amount');
            $totalReceivableCredit = $this->transection->where('tran_type', 'Receivable')->where('credit', 1)->sum('amount');
            $totalReceivable = $totalReceivableCredit - $totalReceivableDebit;
            $account = [
                'total_income' => $this->transection->where('tran_type', 'Income')->sum('amount'),
                'total_expense' => $this->transection->where('tran_type', 'Expense')->sum('amount'),
                'total_payable' => $totalPayable,
                'total_receivable' => $totalReceivable,
            ];
            return response()->json([
                'success' => true,
                'message' => "Overall Statistics",
                'data' => $account
            ], 200);
        } elseif ($request->statistics_type == 'today') {
            $totalPayableDebit = $this->transection->where('tran_type', 'Payable')->whereDay('date', '=', Carbon::today())->where('debit', 1)->sum('amount');
            $totalPaybleCredit = $this->transection->where('tran_type', 'Payable')->whereDay('date', '=', Carbon::today())->where('credit', 1)->sum('amount');
            $totalPayable = $totalPaybleCredit - $totalPayableDebit;

            $totalReceivableDebit = $this->transection->where('tran_type', 'Receivable')->whereDay('date', '=', Carbon::today())->where('debit', 1)->sum('amount');
            $totalReceivableCredit = $this->transection->where('tran_type', 'Receivable')->whereDay('date', '=', Carbon::today())->where('credit', 1)->sum('amount');
            $totalReceivable = $totalReceivableCredit - $totalReceivableDebit;

            $account = [
                'total_income' => $this->transection->where('tran_type', 'Income')->whereDay('date', '=', Carbon::today())->sum('amount'),
                'total_expense' => $this->transection->where('tran_type', 'Expense')->whereDay('date', '=', Carbon::today())->sum('amount'),
                'total_payable' => $totalPayable,
                'total_receivable' => $totalReceivable,
            ];
            return response()->json([
                'success' => true,
                'message' => "Today Statistics",
                'data' => $account
            ], 200);
        } elseif ($request->statistics_type == 'month') {

            $totalPayableDebit = $this->transection->where('tran_type', 'Payable')->whereMonth('date', '=', Carbon::today())->where('debit', 1)->sum('amount');
            $totalPaybleCredit = $this->transection->where('tran_type', 'Payable')->whereMonth('date', '=', Carbon::today())->where('credit', 1)->sum('amount');
            $totalPayable = $totalPaybleCredit - $totalPayableDebit;

            $totalReceivableDebit = $this->transection->where('tran_type', 'Receivable')->whereMonth('date', '=', Carbon::today())->where('debit', 1)->sum('amount');
            $totalReceivableCredit = $this->transection->where('tran_type', 'Receivable')->whereMonth('date', '=', Carbon::today())->where('credit', 1)->sum('amount');
            $totalReceivable = $totalReceivableCredit - $totalReceivableDebit;

            $account = [
                'total_income' => $this->transection->where('tran_type', 'Income')->whereMonth('date', '=', Carbon::today())->sum('amount'),
                'total_expense' => $this->transection->where('tran_type', 'Expense')->whereMonth('date', '=', Carbon::today())->sum('amount'),
                'total_payable' => $totalPayable,
                'total_receivable' => $totalReceivable,
            ];
            return response()->json([
                'success' => true,
                'message' => "Monthly Statistics",
                'data' => $account
            ], 200);
        }
    }

    /**
     * @return JsonResponse
     */
    public function incomeRevenue(): JsonResponse
    {
        $yearWiseExpense = Transection::selectRaw("sum(`amount`) as 'total_amount', YEAR(`date`) as 'year', MONTH(`date`) as 'month'")->where(['tran_type' => 'Expense'])
            ->groupBy('month')
            ->orderBy('year')
            ->get();

        $yearWiseIncome = Transection::selectRaw("sum(`amount`) as 'total_amount', YEAR(`date`) as 'year', MONTH(`date`) as 'month'")->where(['tran_type' => 'Income'])
            ->groupBy('month')
            ->orderBy('year')
            ->get();

        return response()->json([
            'year_wise_expense' => $yearWiseExpense,
            'year_wise_income' => $yearWiseIncome
        ], 200);
    }
}
