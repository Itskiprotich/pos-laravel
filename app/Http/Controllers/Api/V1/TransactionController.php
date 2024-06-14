<?php

namespace App\Http\Controllers\Api\V1;

use App\CPU\Helpers;
use App\Models\Account;
use App\Models\Transection;
use Box\Spout\Common\Exception\InvalidArgumentException;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Writer\Exception\WriterNotOpenedException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use function App\CPU\translate;

class TransactionController extends Controller
{
    public function __construct(
        private Account $account,
        private Transection $transection,
    ){}

    public function getIndex(Request $request)
    {
        $transactions = $this->transection->latest()->paginate($request['limit'], ['*'], 'page', $request['offset']);
        $data = [
            'total' => $transactions->total(),
            'limit' => $request['limit'],
            'offset' => $request['offset'],
            'transactions' => $transactions
        ];
        return response()->json($data, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @param Transection $transaction
     * @return JsonResponse
     */
    public function storeExpenses(Request $request, Transection $transaction): JsonResponse
    {
        $request->validate([
            'account_id' => 'required',
            'description' => 'required',
            'amount' => 'required|min:1',
        ]);
        try {
            $account = $this->account->find($request->account_id);
            if ($account->balance < $request->amount) {
                return response()->json(['success' => false, 'message' => translate('You do not have sufficient balance')], 400);
            }
            $transaction->tran_type = "Expense";
            $transaction->account_id = $request->account_id;
            $transaction->amount = $request->amount;
            $transaction->description = $request->description;
            $transaction->debit = 0;
            $transaction->credit = 0;
            $transaction->date = $request->date;
            $transaction->save();

            $account->total_out = $account->total_out + $request->amount;
            $account->balance = $account->balance - $request->amount;
            $account->save();

            return response()->json([
                'success' => true,
                'message' => translate('Expenses saved successfully'),
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => translate('Expenses not saved')
            ], 403);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function fundTransfer(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'account_from_id' => 'required',
            'account_to_id' => 'required',
            'description' => 'required',
            'amount' => 'required|min:1',
            'date' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $accountFrom = Account::find($request->account_from_id);
        if ($accountFrom->balance < $request->amount) {
            return response()->json([
                'message' => translate('You have not sufficient balance'),
            ], 203);
        }
        $transaction = new Transection();
        $transaction->tran_type = 'Transfer';
        $transaction->account_id = $request->account_from_id;
        $transaction->amount = $request->amount;
        $transaction->description = $request->description;
        $transaction->debit = 1;
        $transaction->credit = 0;
        $transaction->balance = $accountFrom->balance - $request->amount;
        $transaction->date = $request->date;
        $transaction->save();

        $accountFrom->total_out = $accountFrom->total_out + $request->amount;
        $accountFrom->balance = $accountFrom->balance - $request->amount;
        $accountFrom->save();

        $accountTo = Account::find($request->account_to_id);
        $transaction = new Transection();
        $transaction->tran_type = 'Transfer';
        $transaction->account_id = $request->account_to_id;
        $transaction->amount = $request->amount;
        $transaction->description = $request->description;
        $transaction->debit = 0;
        $transaction->credit = 1;
        $transaction->balance = $accountTo->balance + $request->amount;
        $transaction->date = $request->date;
        $transaction->save();

        $accountTo->total_in = $accountTo->total_in + $request->amount;
        $accountTo->balance = $accountTo->balance + $request->amount;
        $accountTo->save();

        return response()->json([
            'message' => translate('New Deposit Added successfully'),
        ], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function transactionFilter(Request $request): JsonResponse
    {
        $limit = $request['limit'] ?? 10;
        $offset = $request['offset'] ?? 1;
        $transactions = $this->transection->when($request->has('account_id'), function ($query) use ($request) {
            $query->where('account_id', $request->account_id);
        })->when($request->has('tran_type'), function ($query) use ($request) {
            $query->where('tran_type', $request->tran_type);
        })->when($request->has('from') && $request->has('to'), function ($query) use ($request) {
            $query->whereBetween('date', [$request->from . ' 00:00:00', $request->to . ' 23:59:59']);
        })->latest()->paginate($request['limit'], ['*'], 'page', $request['offset']);
        $data = [
            'total' => $transactions->total(),
            'limit' => $limit,
            'offset' => $offset,
            'transfers' => $transactions->items()
        ];
        return response()->json($data, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function transferAccounts(Request $request): JsonResponse
    {

        $limit = $request['limit'] ?? 10;
        $offset = $request['offset'] ?? 1;
        if (isset($request->customer_balance)) {
            $accounts = $this->account->orderBy('id')->where('id', '!=', 2)->where('id', '!=', 3)->paginate($request['limit'], ['*'], 'page', $request['offset']);
            $data = [
                'limit' => $limit,
                'offset' => $offset,
                'accounts' => $accounts->items(),
                'customer_balance' => [
                    'id' => 0,
                    'account' => 'Customer Balance'
                ]
            ];
        } else {
            $accounts = $this->account->orderBy('id')->where('id', '!=', 2)->where('id', '!=', 3)->latest()->paginate($request['limit'], ['*'], 'page', $request['offset']);
            $data = [
                'total' => $accounts->total(),
                'limit' => $limit,
                'offset' => $offset,
                'accounts' => $accounts->items(),
            ];
        }
        return response()->json($data, 200);
    }

    /**
     * @param Request $request
     * @return string|StreamedResponse
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws UnsupportedTypeException
     * @throws WriterNotOpenedException
     */
    public function transferListExport(Request $request): StreamedResponse|string
    {
        if ($request->account_id) {
            $transactions = $this->transection->where('account_id', $request->account_id)->latest()->get();
        } elseif ($request->transaction_type) {
            $transactions = $this->transection->where('tran_type', $request->transaction_type)->latest()->get();
        } elseif ($request->from && $request->to) {
            $transactions = $this->transection->whereBetween('date', [$request->from . ' 00:00:00', $request->to . ' 23:59:59'])->latest()->get();
        } else {
            $transactions = $this->transection->where('tran_type', 'Transfer')->get();
        }
        return (new FastExcel($transactions))->download('transactions_list.xlsx');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function transactionTypes(Request $request): JsonResponse
    {
        $types = $this->transection->select('id', 'tran_type')->groupBy('tran_type')->get();
        $data = [
            'types' => $types
        ];
        return response()->json($data, 200);
    }
}
