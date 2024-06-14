<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Transection;
use App\CPU\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use function App\CPU\translate;

class ReceivableController extends Controller
{
    public function __construct(
        private Transection $transection,
        private Account $account,
    ){}

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function add(Request $request): View|Factory|Application
    {
        $accounts = $this->account->orderBy('id')->get();
        $search = $request['search'];
        $from = $request->from;
        $to = $request->to;
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $query = $this->transection->where('tran_type','Receivable')->
                    where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->orWhere('description', 'like', "%{$value}%");
                        }
                });
            $queryParam = ['search' => $request['search']];
        }else
         {
            $query = $this->transection->where('tran_type','Receivable')
                                ->when($from!=null, function($q) use ($request){
                                     return $q->whereBetween('date', [$request['from'], $request['to']]);
            });

         }
        $receivables = $query->latest()->paginate(Helpers::pagination_limit());
        return view('admin-views.account-receivable.add',compact('accounts','receivables','search','from','to'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'account_id' => 'required',
            'description'=> 'required',
            'amount' => 'required',
            'date' =>'required',
        ]);

        $account = $this->account->find($request->account_id);

        $transection = $this->transection;
        $transection->tran_type = 'Receivable';
        $transection->account_id = $request->account_id;
        $transection->amount = $request->amount;
        $transection->description = $request->description;
        $transection->debit = 0;
        $transection->credit = 1;
        $transection->balance =  $account->balance + $request->amount;
        $transection->date = $request->date;
        $transection->save();

        $account->total_in = $account->total_in + $request->amount;
        $account->balance = $account->balance + $request->amount;
        $account->save();

        Toastr::success(translate('Receivable Balance Added successfully'));
        return back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function transfer(Request $request): RedirectResponse
    {
        $receivableAccount = $this->account->find($request->account_id);
        $receivableTransection = $this->transection->find($request->transection_id);
        $balance = $receivableTransection->amount - $request->amount;
        if($balance < 0){

            Toastr::warning(translate('You have not sufficient balance for this transaction'));
            return back();
        }

        $receivableTransection->amount = $balance;
        $receivableTransection->balance = $receivableTransection->balance - $request->amount;
        $receivableTransection->save();

        $receivableAccount->total_out = $receivableAccount->total_out + $request->amount;
        $receivableAccount->balance = $receivableAccount->balance - $request->amount;
        $receivableAccount->save();

        $receiveAccount = $this->account->find($request->receive_account_id);

        $transection = $this->transection;
        $transection->tran_type = 'Income';
        $transection->account_id = $request->receive_account_id;
        $transection->amount = $request->amount;
        $transection->description = $request->description;
        $transection->debit = 0;
        $transection->credit = 1;
        $transection->balance =  $receiveAccount->balance + $request->amount;
        $transection->date = $request->date;
        $transection->save();

        $receiveAccount->total_in = $receiveAccount->total_in + $request->amount;
        $receiveAccount->balance = $receiveAccount->balance + $request->amount;
        $receiveAccount->save();

        Toastr::success(translate('Payable Balance pay successfully'));
        return back();

    }
}
