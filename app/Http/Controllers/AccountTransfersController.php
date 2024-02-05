<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountTransferRequest;
use App\Models\Account;
use App\Models\AccountTransfer;
use App\Services\AccountTransferService;
use DB;
use Illuminate\Support\Facades\Auth;

class AccountTransfersController extends Controller
{
    public function __construct(private readonly AccountTransferService $accountTransferService)
    {
    }

    public function store(StoreAccountTransferRequest $request)
    {
        $accountFrom = Account::with('currency')->find($request->get('account_from_id'));
        $accountTo = Account::with('currency')->find($request->get('account_to_id'));
        $convertedAmount = $accountFrom->currency->code === $accountTo->currency->code
            ? $request->get('amount')
            : $request->get('converted_amount');

        $transfer = AccountTransfer::make([
            ...$request->validated(),
            'user_id' => Auth::user()->id,
            'converted_amount' => $convertedAmount,
        ]);

        $this->accountTransferService->transferBetweenAccounts($accountFrom, $accountTo, $transfer);

        return redirect()->back();
    }


    public function destroy(AccountTransfer $accountTransfer)
    {
        $this->accountTransferService->delete($accountTransfer);

        return redirect()->back();
    }
}
