<?php

namespace App\Http\Controllers;

use App\Events\AccountDeleted;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Http\Resources\AccountTransferResource;
use App\Http\Resources\CurrencyResource;
use App\Models\Account;
use App\Models\Currency;
use App\Services\AccountService;
use Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AccountController extends Controller
{
    public function __construct(private readonly AccountService $accountService)
    {
        $this->authorizeResource(Account::class);
    }

    public function store(StoreAccountRequest $request)
    {
        Account::create([
            ...$request->validated(),
            'user_id' => Auth::user()->id,
            'currency_id' => Currency::findByCode($request->get('currency'))->id,
            'balance' => $request->get('balance') ?: 0,
        ]);

        return redirect()->route('accounts.index');
    }

    public function index(Request $request)
    {
        $accountId = $request->get('account_id');

        $transfersQuery = Auth::user()
            ->accountTransfers()
            ->with(['accountFrom', 'accountTo', 'accountFrom.currency', 'accountTo.currency'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc');
        if ($accountId) {
            $transfersQuery
                ->where('account_from_id', $accountId)
                ->orWhere('account_to_id', $accountId);
        }

        $transfersPaginator = $transfersQuery
            ->paginate(config('app.pagination_size'))
            ->withQueryString();

        return Inertia::render(
            'Accounts',
            [
                'accounts' => Auth::user()
                    ->accounts()
                    ->orderBy('created_at', 'DESC')
                    ->with(['currency'])
                    ->get(),
                'currencies' => CurrencyResource::collection(Currency::all()),
                'transfers' => AccountTransferResource::collection(
                    $transfersPaginator
                ),
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

    public function destroy(Account $account, string $mode = AccountService::DELETE_CASCADE_MODE)
    {
        $this->accountService->delete($account, $mode);

        return redirect()->back();
    }

    public function toggle(Account $account)
    {
        $account->toggleState();

        return redirect()->back();
    }
}
