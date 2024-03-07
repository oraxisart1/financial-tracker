<?php

namespace App\Http\Controllers;

use App\DTO\AccountTransferDTO;
use App\Http\Requests\StoreAccountTransferRequest;
use App\Http\Requests\UpdateAccountTransferRequest;
use App\Models\Account;
use App\Models\AccountTransfer;
use App\Services\AccountTransferService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AccountTransferController extends Controller
{
    public function __construct(private readonly AccountTransferService $accountTransferService)
    {
        $this->authorizeResource(AccountTransfer::class);
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

    public function update(UpdateAccountTransferRequest $request, AccountTransfer $accountTransfer)
    {
        $convertedAmount = $accountTransfer->accountFrom->currency->code === $accountTransfer->accountTo->currency->code
            ? $request->get('amount')
            : $request->get('converted_amount');

        $this->accountTransferService->update(
            $accountTransfer,
            new AccountTransferDTO(
                $request->get('account_from_id'),
                $request->get('account_to_id'),
                $request->get('amount'),
                $convertedAmount,
                Carbon::parse($request->get('date')),
                $request->get('description'),
            )
        );

        return redirect()->back();
    }


    public function destroy(AccountTransfer $accountTransfer)
    {
        $this->accountTransferService->delete($accountTransfer);

        return redirect()->back();
    }
}
