<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountTransferRequest;
use App\Models\Account;
use App\Models\AccountTransfer;
use DB;
use Illuminate\Support\Facades\Auth;

class AccountTransfersController extends Controller
{
    public function store(StoreAccountTransferRequest $request)
    {
        $accountFrom = Account::with('currency')->find($request->get('account_from_id'));
        $accountTo = Account::with('currency')->find($request->get('account_to_id'));

        DB::transaction(function () use ($request, $accountFrom, $accountTo) {
            AccountTransfer::create([
                ...$request->validated(),
                'user_id' => Auth::user()->id,
            ]);

            $accountFrom->update([
                'balance' => $accountFrom->balance - $request->get('amount'),
            ]);

            $convertedAmount = $accountFrom->currency->code === $accountTo->currency->code
                ? $request->get('amount')
                : $request->get('converted_amount');

            $accountTo->update([
                'balance' => $accountTo->balance + $convertedAmount,
            ]);
        });

        return redirect()->back();
    }
}
