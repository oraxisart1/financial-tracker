<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Models\Account;
use App\Models\Currency;
use Auth;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class AccountsController extends Controller
{
    public function store(StoreAccountRequest $request)
    {
        Account::create([
            ...$request->validated(),
            'user_id' => Auth::user()->id,
            'currency_id' => Currency::findByCode($request->get('currency'))->id,
        ]);

        return redirect()->route('accounts.index');
    }

    public function index()
    {
        return Inertia::render(
            'Accounts',
            [
                'accounts' => Auth::user()
                    ->accounts()
                    ->with(['currency'])
                    ->get(),
            ]
        );
    }

    public function update(UpdateAccountRequest $request, Account $account): RedirectResponse
    {
        $account->update([
            ...$request->validated(),
            'currency_id' => Currency::findByCode($request->get('currency'))->id,
        ]);

        return redirect()->back();
    }
}
