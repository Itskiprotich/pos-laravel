<?php

namespace App\Http\Controllers\Api\V1;

use App\CPU\Helpers;
use App\Models\Account;
use App\Models\Supplier;
use App\Models\Transection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use function App\CPU\translate;

class SupplierController extends Controller
{
    public function __construct(
        private Supplier $supplier,
        private Transection $transection,
        private Account $account,
    ){}

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getIndex(Request $request): JsonResponse
    {
        $limit = $request['limit'] ?? 10;
        $offset = $request['offset'] ?? 1;
        $suppliers = $this->supplier->withCount('products')->latest()->paginate($limit, ['*'], 'page', $offset);
        $data =  [
            'total' => $suppliers->total(),
            'limit' => $limit,
            'offset' => $offset,
            'suppliers' => $suppliers->items()
        ];
        return response()->json($data, 200);
    }

    /**
     * @param Request $request
     * @param Supplier $supplier
     * @return JsonResponse
     */
    public function postStore(Request $request, Supplier $supplier): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'mobile' => 'required|unique:suppliers',
            'email' => 'required|email|unique:suppliers',
            'state' => 'required',
            'city' => 'required',
            'zip_code' => 'required',
            'address' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        if (!empty($request->file('image'))) {
            $imageName =  Helpers::upload('supplier/', 'png', $request->file('image'));
        } else {
            $imageName = 'def.png';
        }
        try {
            $supplier->name = $request->name;
            $supplier->mobile = $request->mobile;
            $supplier->email = $request->email;
            $supplier->image = $imageName;
            $supplier->state = $request->state;
            $supplier->city = $request->city;
            $supplier->zip_code = $request->zip_code;
            $supplier->address = $request->address;
            $supplier->due_amount = $request->due_amount;
            $supplier->save();
            return response()->json([
                'success' => true,
                'message' => translate('Supplier saved successfully'),
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => translate('Supplier not saved')
            ], 403);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDetails(Request $request): JsonResponse
    {
        $supplier = $this->supplier->findOrFail($request->id);
        return response()->json([
            'success' => true,
            'message' => translate('Supplier details'),
            'supplier' => $supplier
        ], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function postUpdate(Request $request): JsonResponse
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'mobile' => 'required|unique:suppliers,mobile,' . $request->id,
            'email' => 'required|email|unique:suppliers,email,' .$request->id,
            'state' => 'required',
            'city' => 'required',
            'zip_code' => 'required',
            'address' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $supplier = $this->supplier->findOrFail($request->id);
        try {
            $supplier->name = $request->name;
            $supplier->mobile = $request->mobile;
            $supplier->email = $request->email;
            $supplier->image = $request->has('image') ? Helpers::update('supplier/', $supplier->image, 'png', $request->file('image')) : $supplier->image;
            $supplier->state = $request->state;
            $supplier->city = $request->city;
            $supplier->zip_code = $request->zip_code;
            $supplier->address = $request->address;
            $supplier->save();
            return response()->json([
                'success' => true,
                'message' => translate('Supplier updated successfully'),
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => true,
                'message' => translate('Supplier not updated'),
            ], 403);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        try {
            $supplier = $this->supplier->findOrFail($request->id);
            Helpers::delete('supplier/' . $supplier['image']);
            $supplier->delete();
            return response()->json([
                'success' => true,
                'message' => translate('Supplier deleted')
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => translate('Supplier not deleted')
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
        $result = $this->supplier
            ->withCount('products')
            ->where('name', 'like', '%' . $search . '%')->orWhere('mobile', 'like', '%' . $search . '%')
            ->latest()->paginate($limit, ['*'], 'page', $offset);
        $data = [
            'total' => $result->total(),
            'limit' => $limit,
            'offset' => $offset,
            'suppliers' => $result->items(),
        ];
        return response()->json($data, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function filterByCity(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'city' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $limit = $request['limit'] ?? 10;
        $offset = $request['offset'] ?? 1;
        if (!empty($request->city)) {
            $result = $this->supplier->where('city', $request->city)->latest()->paginate($limit, ['*'], 'page', $offset);
            $data = [
                'total' => $result->total(),
                'limit' => $limit,
                'offset' => $offset,
                'supplier' => $result->items(),
            ];
        } else {
            $data = [
                'total' => 0,
                'limit' => $limit,
                'offset' => $offset,
                'supplier' => [],
            ];
        }
        return response()->json($data, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function transactions(Request $request): JsonResponse
    {
        $transactions = $this->transection->with('account')->where('supplier_id', $request->supplier_id)->latest()->paginate($request['limit'], ['*'], 'page', $request['offset']);
        $data = [
            'total' => $transactions->total(),
            'limit' => $request['limit'],
            'offset' => $request['offset'],
            'transfers' => $transactions->items()
        ];
        return response()->json($data, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse|void
     */
    public function transactionsDateFilter(Request $request)
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
            $result = $this->transection->when(($request->from && $request->to), function ($query) use ($request) {
                $query->whereBetween('date', [$request->from . ' 00:00:00', $request->to . ' 23:59:59']);
            })->where('supplier_id', '=', $request->supplier_id)->latest()->paginate($limit, ['*'], 'page', $offset);
            $data = [
                'total' => $result->total(),
                'limit' => $limit,
                'offset' => $offset,
                'transfers' => $result->items(),
            ];
            return response()->json($data, 200);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function payment(Request $request): JsonResponse
    { {
            $request->validate([
                'supplier_id' => 'required',
                'total_due_amount' => 'required',
                'pay_amount' => 'required',
                'remaining_due_amount' => 'required',
                'payment_account_id' => 'required',
            ]);

            $paymentAccount = $this->account->find($request->payment_account_id);
            if ($paymentAccount->balance < $request->pay_amount) {
                $data = [
                    'success' => true,
                    'message' => translate('You do not have sufficient balance!')
                ];
                return response()->json($data);
            }

            if ($request->pay_amount > 0) {
                $paymentTransaction = $this->transection;
                $paymentTransaction->tran_type = 'Expense';
                $paymentTransaction->account_id = $paymentAccount->id;
                $paymentTransaction->amount = $request->pay_amount;
                $paymentTransaction->description = 'Supplier due payment';
                $paymentTransaction->debit = 1;
                $paymentTransaction->credit = 0;
                $paymentTransaction->balance = $paymentAccount->balance - $request->pay_amount;
                $paymentTransaction->date = date("Y/m/d");
                $paymentTransaction->supplier_id = $request->supplier_id;
                $paymentTransaction->save();

                $paymentAccount->total_out = $paymentAccount->total_out + $request->pay_amount;
                $paymentAccount->balance = $paymentAccount->balance - $request->pay_amount;
                $paymentAccount->save();

                $payableAccount = $this->account->find(2);
                $payableTransaction = $this->transection;
                $payableTransaction->tran_type = 'Payable';
                $payableTransaction->account_id = $payableAccount->id;
                $payableTransaction->amount = $request->pay_amount;
                $payableTransaction->description = 'Supplier due payment';
                $payableTransaction->debit = 1;
                $payableTransaction->credit = 0;
                $payableTransaction->balance = $payableAccount->balance - $request->pay_amount;
                $payableTransaction->date = date("Y/m/d");
                $payableTransaction->supplier_id = $request->supplier_id;
                $payableTransaction->save();

                $payableAccount->total_out = $payableAccount->total_out + $request->pay_amount;
                $payableAccount->balance = $payableAccount->balance - $request->pay_amount;
                $payableAccount->save();
            }

            $supplier = $this->supplier->find($request->supplier_id);
            $supplier->due_amount = $supplier->due_amount - $request->pay_amount;
            $supplier->save();

            $data = [
                'success' => true,
                'message' => translate('Supplier payment successfully')
            ];
            return response()->json($data);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function newPurchase(Request $request): JsonResponse
    {

        $request->validate([
            'supplier_id' => 'required',
            'purchased_amount' => 'required',
            'paid_amount' => 'required',
            'due_amount' => 'required',
            'payment_account_id' => 'required',
        ]);

        $paymentAccount = $this->account->find($request->payment_account_id);

        if ($paymentAccount->balance < $request->paid_amount) {
            $data = [
                'success' => true,
                'message' => translate('You do not have sufficient balance!')
            ];
            return response()->json($data);
        }
        if ($request->paid_amount > 0) {
            $paymentTransaction = $this->transection;
            $paymentTransaction->tran_type = 'Expense';
            $paymentTransaction->account_id = $paymentAccount->id;
            $paymentTransaction->amount = $request->paid_amount;
            $paymentTransaction->description = 'Supplier payment';
            $paymentTransaction->debit = 1;
            $paymentTransaction->credit = 0;
            $paymentTransaction->balance = $paymentAccount->balance - $request->paid_amount;
            $paymentTransaction->date = date("Y/m/d");
            $paymentTransaction->supplier_id = $request->supplier_id;
            $paymentTransaction->save();

            $paymentAccount->total_out = $paymentAccount->total_out + $request->paid_amount;
            $paymentAccount->balance = $paymentAccount->balance - $request->paid_amount;
            $paymentAccount->save();
        }

        if ($request->due_amount > 0) {
            $payableAccount = $this->account->find(2);
            $payableTransaction = $this->transection;
            $payableTransaction->tran_type = 'Payable';
            $payableTransaction->account_id = $payableAccount->id;
            $payableTransaction->amount = $request->due_amount;
            $payableTransaction->description = 'Supplier payment';
            $payableTransaction->debit = 0;
            $payableTransaction->credit = 1;
            $payableTransaction->balance = $payableAccount->balance + $request->due_amount;
            $payableTransaction->date = date("Y/m/d");
            $payableTransaction->supplier_id = $request->supplier_id;
            $payableTransaction->save();

            $payableAccount->total_in = $payableAccount->total_in + $request->due_amount;
            $payableAccount->balance = $payableAccount->balance + $request->due_amount;
            $payableAccount->save();

            $supplier = $this->supplier->find($request->supplier_id);
            $supplier->due_amount = $supplier->due_amount + $request->due_amount;
            $supplier->save();
        }
        $data = [
            'success' => true,
            'message' => translate('Supplier new purchase added successfully')
        ];
        return response()->json($data);
    }
}
