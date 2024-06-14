<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Order;
use Brian2694\Toastr\Facades\Toastr;
use App\CPU\Helpers;
use App\Models\Account;
use App\Models\Transection;
use function App\CPU\translate;

class CustomerController extends Controller
{
    public function __construct(
        private Customer $customer,
        private Order $order,
        private Account $account,
        private Transection $transection
    ){}

    /**
     * @return Application|Factory|View
     */
    public function index(): View|Factory|Application
    {
        return view('admin-views.customer.index');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required',
            'mobile'=> 'required|unique:customers',
            'image'=>'image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if (!empty($request->file('image'))) {
            $imageName =  Helpers::upload('customer/', 'png', $request->file('image'));
        } else {
            $imageName = 'def.png';
        }

        $customer = $this->customer;
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

        Toastr::success(translate('Customer Added successfully'));
        return back();
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function list(Request $request): View|Factory|Application
    {
        $accounts = $this->account->orderBy('id')->get();
        $queryParam = [];
        $search = $request['search'];
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $customers = $this->customer->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('name', 'like', "%{$value}%")
                        ->orWhere('mobile', 'like', "%{$value}%");
                }
            });

            $queryParam = ['search' => $request['search']];
        } else {
            $customers = $this->customer;
        }

        $customers = $customers->where('id', '!=', '0')->latest('id')->paginate(Helpers::pagination_limit())->appends($queryParam);
        $walkingCustomer = $this->customer->where('id', '=', '0')->first();
        return view('admin-views.customer.list',compact('customers','accounts','search', 'walkingCustomer'));
    }

    /**
     * @param Request $request
     * @param $id
     * @return Application|Factory|View|RedirectResponse
     */
    public function view(Request $request, $id): View|Factory|RedirectResponse|Application
    {
        $customer = $this->customer->where('id',$id)->first();
        if(isset($customer))
        {
            $queryParam = [];
            $search = $request['search'];
            if ($request->has('search')) {
                $key = explode(' ', $request['search']);
                $orders = $this->order->where(['user_id' => $id])
                                    ->where(function ($q) use ($key) {
                                        foreach ($key as $value) {
                                            $q->where('id', 'like', "%{$value}%");
                                        }
                                    });
                $queryParam = ['search' => $request['search']];
            } else {
                $orders = $this->order->where(['user_id' => $id]);
            }

            $orders = $orders->latest()->paginate(Helpers::pagination_limit())->appends($queryParam);
            return view('admin-views.customer.view',compact('customer', 'orders','search'));
        }

        Toastr::error('Customer not found!');
        return back();
    }

    /**
     * @param Request $request
     * @param $id
     * @return Application|Factory|View|RedirectResponse
     */
    public function transactionList(Request $request, $id): View|Factory|RedirectResponse|Application
    {
        $accounts = $this->account->get();
        $customer = $this->customer->where('id',$id)->first();
        if(isset($customer))
        {
            $accId = $request['account_id'];
            $tran_type = $request['tran_type'];
            $orders = $this->order->where(['user_id' => $id])->get();
            $transactions = $this->transection->where(['customer_id' => $id])
                                ->when($accId!=null, function($q) use ($request){
                                    return $q->where('account_id',$request['account_id']);
                                })
                                ->when($tran_type!=null, function($q) use ($request){
                                    return $q->where('tran_type',$request['tran_type']);
                                })->latest()->paginate(Helpers::pagination_limit())
                                ->appends(['account_id' => $request['account_id'],'tran_type'=>$request['tran_type']]);
            return view('admin-views.customer.transaction-list',compact('customer', 'transactions','orders','tran_type','accounts','accId'));
        }

        Toastr::error(translate('Customer not found'));
        return back();
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function edit(Request $request): Factory|View|Application
    {
        $customer = $this->customer->where('id',$request->id)->first();
        return view('admin-views.customer.edit',compact('customer'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $customer = $this->customer->where('id',$request->id)->first();

        $request->validate([
            'name' => 'required',
            'mobile'=> 'required|unique:customers,mobile,'.$customer->id,
            'image'=>'image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $customer->name = $request->name;
        $customer->mobile = $request->mobile;
        $customer->email = $request->email;
        $customer->image = $request->has('image') ? Helpers::update('customer/', $customer->image, 'png', $request->file('image')) : $customer->image;
        $customer->state = $request->state;
        $customer->city = $request->city;
        $customer->zip_code = $request->zip_code;
        $customer->address = $request->address;
        $customer->balance = $request->balance;
        $customer->save();

        Toastr::success(translate('Customer updated successfully'));
        return back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function delete(Request $request): RedirectResponse
    {
        $customer = $this->customer->find($request->id);
        Helpers::delete('customer/' . $customer['image']);
        $customer->delete();

        Toastr::success(translate('Customer removed successfully'));
        return back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateBalance(Request $request): RedirectResponse
    {
        $request->validate([
            'customer_id'=>'required',
            'amount' => 'required',
            'account_id'=> 'required',
            'date' => 'required',
        ]);

        $customer = $this->customer->find($request->customer_id);

        if($customer->balance >= 0)
        {
            $account = Account::find(2);
            $transection = new Transection;
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

            $receiveAccount = Account::find($request->account_id);
            $receiveTransection = new Transection;
            $receiveTransection->tran_type = 'Income';
            $receiveTransection->account_id = $receiveAccount->id;
            $receiveTransection->amount = $request->amount;
            $receiveTransection->description = $request->description;
            $receiveTransection->debit = 0;
            $receiveTransection->credit = 1;
            $receiveTransection->balance = $receiveAccount->balance + $request->amount;
            $receiveTransection->date = $request->date;
            $receiveTransection->customer_id = $request->customer_id;
            $receiveTransection->save();

            $receiveAccount->total_in = $receiveAccount->total_in + $request->amount;
            $receiveAccount->balance = $receiveAccount->balance + $request->amount;
            $receiveAccount->save();
        }else{
            $remainingBalance = $customer->balance + $request->amount;

            if($remainingBalance >= 0)
            {
                if($remainingBalance!=0)
                {
                    $payableAccount = Account::find(2);
                    $payableTransection = new Transection;
                    $payableTransection->tran_type = 'Payable';
                    $payableTransection->account_id = $payableAccount->id;
                    $payableTransection->amount = $remainingBalance;
                    $payableTransection->description = $request->description;
                    $payableTransection->debit = 0;
                    $payableTransection->credit = 1;
                    $payableTransection->balance = $payableAccount->balance + $remainingBalance;
                    $payableTransection->date = $request->date;
                    $payableTransection->customer_id = $request->customer_id;
                    $payableTransection->save();

                    $payableAccount->total_in = $payableAccount->total_in + $remainingBalance;
                    $payableAccount->balance = $payableAccount->balance + $remainingBalance;
                    $payableAccount->save();
                }

                $receiveAccount = Account::find($request->account_id);
                $receiveTransection = new Transection;
                $receiveTransection->tran_type = 'Income';
                $receiveTransection->account_id = $request->account_id;
                $receiveTransection->amount = $request->amount;
                $receiveTransection->description = $request->description;
                $receiveTransection->debit = 0;
                $receiveTransection->credit = 1;
                $receiveTransection->balance = $receiveAccount->balance + $request->amount;
                $receiveTransection->date = $request->date;
                $receiveTransection->customer_id = $request->customer_id;
                $receiveTransection->save();

                $receiveAccount->total_in = $receiveAccount->total_in + $request->amount;
                $receiveAccount->balance = $receiveAccount->balance + $request->amount;
                $receiveAccount->save();


                $receivableAccount = Account::find(3);
                $receivableTransaction = new Transection;
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

            }else{

                $receiveAccount = Account::find($request->account_id);
                $receiveTransection = new Transection;
                $receiveTransection->tran_type = 'Income';
                $receiveTransection->account_id = $receiveAccount->id;
                $receiveTransection->amount = $request->amount;
                $receiveTransection->description = $request->description;
                $receiveTransection->debit = 0;
                $receiveTransection->credit = 1;
                $receiveTransection->balance = $receiveAccount->balance + $request->amount;
                $receiveTransection->date = $request->date;
                $receiveTransection->customer_id = $request->customer_id;
                $receiveTransection->save();

                $receiveAccount->total_in = $receiveAccount->total_in + $request->amount;
                $receiveAccount->balance = $receiveAccount->balance + $request->amount;
                $receiveAccount->save();

                $receivableAccount = Account::find(3);
                $receivableTransaction = new Transection;
                $receivableTransaction->tran_type = 'Receivable';
                $receivableTransaction->account_id = $receivableAccount->id;
                $receivableTransaction->amount = $request->amount;
                $receivableTransaction->description = 'update customer balance';
                $receivableTransaction->debit = 1;
                $receivableTransaction->credit =0;
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

        Toastr::success(translate('Customer balance updated successfully'));
        return back();
    }
}
