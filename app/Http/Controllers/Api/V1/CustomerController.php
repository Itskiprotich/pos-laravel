<?php

namespace App\Http\Controllers\Api\V1;

use App\CPU\Helpers;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Transection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use function App\CPU\translate;

class CustomerController extends Controller
{
    public function __construct(
        private Account     $account,
        private Customer    $customer,
        private Transection $transection
    )
    {
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getIndex(Request $request): JsonResponse
    {
        $limit = $request['limit'] ?? 10;
        $offset = $request['offset'] ?? 1;
        $customers = $this->customer->withCount('orders')->orderBy('id', 'DESC')->paginate($limit, ['*'], 'page', $offset);
        $data = [
            'total' => $customers->total(),
            'limit' => $limit,
            'offset' => $offset,
            'customers' => $customers->items(),
        ];
        return response()->json($data, 200);
    }

    /**
     * @param Request $request
     * @param Customer $customer
     * @return JsonResponse
     */
    public function postStore(Request $request, Customer $customer): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'mobile' => 'required|unique:customers',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        if (!empty($request->file('image'))) {
            $imageName = Helpers::upload('customer/', 'png', $request->file('image'));
        } else {
            $imageName = 'def.png';
        }
        $customer->name = $request->name;
        $customer->mobile = $request->mobile;
        $customer->email = $request->email;
        $customer->image = $imageName;
        $customer->state = $request->state;
        $customer->city = $request->city;
        $customer->zip_code = $request->zip_code;
        $customer->address = $request->address;
        $customer->balance = $request->balance;
        $customer->save();

        return response()->json([
            'success' => true,
            'message' => translate('Customer saved successfully'),
        ], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDetails(Request $request): JsonResponse
    {
        try {
            $customerDetails = $this->customer->findOrFail($request->id);
            return response()->json([
                'message' => translate('Customer details'),
                'data' => $customerDetails,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Invalid id: customer not found',
            ], 422);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function postUpdate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'mobile' => 'required|unique:customers,mobile,' . $request->id,
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $customer = $this->customer->where('id', $request->id)->first();
        $customer->name = $request->name;
        $customer->mobile = $request->mobile;
        $customer->email = $request->email;
        $customer->image = $request->has('image') ? Helpers::update('customer/', $customer->image, 'png', $request->file('image')) : $customer->image;
        $customer->state = $request->state;
        $customer->city = $request->city;
        $customer->zip_code = $request->zip_code;
        $customer->address = $request->address;
        $customer->balance = $request->balance;
        $customer->update();
        return response()->json([
            'message' => translate('Customer updated successfully'),
        ], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        try {
            $customer = $this->customer->where('id', '!=', '0')->find($request->id);
            Helpers::delete('customer/' . $customer['image']);
            $customer->delete();
            return response()->json([
                'message' => translate('Customer deleted'),
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => translate('Customer not deleted'),
            ], 403);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getSearch(Request $request): JsonResponse
    {
        $limit = $request['limit'] ?? 10;
        $offset = $request['offset'] ?? 1;
        $search = $request->name;
        $result = $this->customer->where('name', 'like', '%' . $search . '%')->orWhere('mobile', 'like', '%' . $search . '%')->latest()->paginate($limit, ['*'], 'page', $offset);
        $data = [
            'total' => $result->total(),
            'limit' => $limit,
            'offset' => $offset,
            'customers' => $result->items(),
        ];
        return response()->json($data, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function dateWiseFilter(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'from' => 'required',
            'to' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $limit = $request['limit'] ?? 10;
        $offset = $request['offset'] ?? 1;

        if (!empty($request->from && $request->to)) {
            $result = $this->customer->when(($request->from && $request->to), function ($query) use ($request) {
                $query->whereBetween('date', [$request->from . ' 00:00:00', $request->to . ' 23:59:59']);
            })->where('tran_type', '=', 'Expense')->latest()->paginate($limit, ['*'], 'page', $offset);
            $data = [
                'total' => $result->total(),
                'limit' => $limit,
                'offset' => $offset,
                'customer' => $result->items(),
            ];
        } else {
            $data = [
                'total' => 0,
                'limit' => $limit,
                'offset' => $offset,
                'customer' => [],
            ];
        }
        return response()->json($data, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function totalTransaction(Request $request): JsonResponse
    {
        $limit = $request['limit'] ?? 10;
        $offset = $request['offset'] ?? 1;
        $result = $this->transection->where('customer_id', $request->customer_id)->with('account')->orderBy('id', 'desc')->paginate($limit, ['*'], 'page', $offset);
        $data = [
            'total' => $result->total(),
            'limit' => $limit,
            'offset' => $offset,
            'transfers' => $result->items(),
        ];
        return response()->json($data, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function transactionFilter(Request $request): JsonResponse
    {
        $limit = $request['limit'] ?? 10;
        $offset = $request['offset'] ?? 1;

        if ($request->account_id) {
            $transactions = $this->transection->where('account_id', $request->account_id)->latest()->paginate($request['limit'], ['*'], 'page', $request['offset']);
        } elseif ($request->transaction_type) {
            $transactions = $this->transection->where('tran_type', $request->transaction_type)->latest()->paginate($request['limit'], ['*'], 'page', $request['offset']);
        } elseif ($request->from && $request->to) {
            $transactions = $this->transection->whereBetween('date', [$request->from . ' 00:00:00', $request->to . ' 23:59:59'])->latest()->paginate($request['limit'], ['*'], 'page', $request['offset']);
        } else {
            $transactions = $this->transection->latest()->paginate($request['limit'], ['*'], 'page', $request['offset']);
        }

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
    public function updateBalance(Request $request): JsonResponse
    {
        $request->validate([
            'customer_id' => 'required',
            'amount' => 'required',
            'account_id' => 'required',
            'date' => 'required',
        ]);
        $customer = $this->customer->find($request->customer_id);

        if ($customer->balance >= 0) {
            $account = $this->account->find(2);
            $transection = $this->transection;
            $transection->tran_type = 'Payable';
            $transection->account_id = $account->id;
            $transection->amount = $request->amount;
            $transection->description = $request->description;
            $transection->debit = 0;
            $transection->credit = 1;
            $transection->balance = $account->balance + $request->amount;
            $transection->date = $request->date;
            $transection->customer_id = $request->customer_id;
            $transection->save();

            $account->total_in = $account->total_in + $request->amount;
            $account->balance = $account->balance + $request->amount;
            $account->save();

            $receiveAccount = $this->account->find($request->account_id);
            $receiveTransaction = $this->transection;
            $receiveTransaction->tran_type = 'Income';
            $receiveTransaction->account_id = $receiveAccount->id;
            $receiveTransaction->amount = $request->amount;
            $receiveTransaction->description = $request->description;
            $receiveTransaction->debit = 0;
            $receiveTransaction->credit = 1;
            $receiveTransaction->balance = $receiveAccount->balance + $request->amount;
            $receiveTransaction->date = $request->date;
            $receiveTransaction->customer_id = $request->customer_id;
            $receiveTransaction->save();

            $receiveAccount->total_in = $receiveAccount->total_in + $request->amount;
            $receiveAccount->balance = $receiveAccount->balance + $request->amount;
            $receiveAccount->save();
        } else {
            $remainingBalance = $customer->balance + $request->amount;

            if ($remainingBalance >= 0) {
                if ($remainingBalance != 0) {
                    $payableAccount = $this->account->find(2);
                    $payableTransaction = $this->transection;
                    $payableTransaction->tran_type = 'Payable';
                    $payableTransaction->account_id = $payableAccount->id;
                    $payableTransaction->amount = $remainingBalance;
                    $payableTransaction->description = $request->description;
                    $payableTransaction->debit = 0;
                    $payableTransaction->credit = 1;
                    $payableTransaction->balance = $payableAccount->balance + $remainingBalance;
                    $payableTransaction->date = $request->date;
                    $payableTransaction->customer_id = $request->customer_id;
                    $payableTransaction->save();

                    $payableAccount->total_in = $payableAccount->total_in + $remainingBalance;
                    $payableAccount->balance = $payableAccount->balance + $remainingBalance;
                    $payableAccount->save();
                }

                $receiveAccount = $this->account->find($request->account_id);
                $receiveTransaction = $this->transection;
                $receiveTransaction->tran_type = 'Income';
                $receiveTransaction->account_id = $request->account_id;
                $receiveTransaction->amount = $request->amount;
                $receiveTransaction->description = $request->description;
                $receiveTransaction->debit = 0;
                $receiveTransaction->credit = 1;
                $receiveTransaction->balance = $receiveAccount->balance + $request->amount;
                $receiveTransaction->date = $request->date;
                $receiveTransaction->customer_id = $request->customer_id;
                $receiveTransaction->save();

                $receiveAccount->total_in = $receiveAccount->total_in + $request->amount;
                $receiveAccount->balance = $receiveAccount->balance + $request->amount;
                $receiveAccount->save();


                $receivableAccount = $this->account->find(3);
                $receivableTransaction = $this->transection;
                $receivableTransaction->tran_type = 'Receivable';
                $receivableTransaction->account_id = $receivableAccount->id;
                $receivableTransaction->amount = -$customer->balance;
                $receivableTransaction->description = 'update customer balance';
                $receivableTransaction->debit = 1;
                $receivableTransaction->credit = 0;
                $receivableTransaction->balance = $receivableAccount->balance + $customer->balance;
                $receivableTransaction->date = $request->date;
                $receivableTransaction->customer_id = $request->customer_id;
                $receivableTransaction->save();

                $receivableAccount->total_out = $receivableAccount->total_out - $customer->balance;
                $receivableAccount->balance = $receivableAccount->balance + $customer->balance;
                $receivableAccount->save();

            } else {

                $receiveAccount = $this->account->find($request->account_id);
                $receiveTransaction = $this->transection;
                $receiveTransaction->tran_type = 'Income';
                $receiveTransaction->account_id = $receiveAccount->id;
                $receiveTransaction->amount = $request->amount;
                $receiveTransaction->description = $request->description;
                $receiveTransaction->debit = 0;
                $receiveTransaction->credit = 1;
                $receiveTransaction->balance = $receiveAccount->balance + $request->amount;
                $receiveTransaction->date = $request->date;
                $receiveTransaction->customer_id = $request->customer_id;
                $receiveTransaction->save();

                $receiveAccount->total_in = $receiveAccount->total_in + $request->amount;
                $receiveAccount->balance = $receiveAccount->balance + $request->amount;
                $receiveAccount->save();

                $receivableAccount = $this->account->find(3);
                $receivableTransaction = new $this->transection;
                $receivableTransaction->tran_type = 'Receivable';
                $receivableTransaction->account_id = $receivableAccount->id;
                $receivableTransaction->amount = $request->amount;
                $receivableTransaction->description = 'update customer balance';
                $receivableTransaction->debit = 1;
                $receivableTransaction->credit = 0;
                $receivableTransaction->balance = $receivableAccount->balance - $request->amount;
                $receivableTransaction->date = $request->date;
                $receivableTransaction->customer_id = $request->customer_id;
                $receivableTransaction->save();

                $receivableAccount->total_out = $receivableAccount->total_out + $request->amount;
                $receivableAccount->balance = $receivableAccount->balance - $request->amount;
                $receivableAccount->save();
            }

        }
        $customer->balance = $customer->balance + $request->amount;
        $customer->save();
        return response()->json([
            'message' => translate('Customer balance updated successfully'),
        ], 200);
    }

}
