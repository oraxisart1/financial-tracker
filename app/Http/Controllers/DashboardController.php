<?php

namespace App\Http\Controllers;

use App\Enums\CategoryType;
use App\Enums\TransactionType;
use App\Http\Resources\TransactionResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $transactionsType = $request->get('transaction_type');
        if ($transactionsType) {
            $transactionsType = CategoryType::tryFrom($transactionsType);
        }

        if (!$transactionsType) {
            return redirect()->route('dashboard', ['transaction_type' => CategoryType::EXPENSE->value]);
        }

        $transactionsQuery = $this->buildTransactionsQuery($request);

        $transactions = $transactionsQuery
            ->paginate(10)
            ->withQueryString();

        $categories = Auth::user()
            ->categories()
            ->where('type', $transactionsType)
            ->get();

        $accounts = Auth::user()
            ->accounts()
            ->with(['currency'])
            ->get();

        return Inertia::render(
            'Dashboard',
            [
                'transactions' => TransactionResource::collection($transactions),
                'categories' => $categories,
                'accounts' => $accounts,
                'totalAmount' => $transactionsQuery
                    ->sum('amount'),
            ]
        );
    }

    public function loadTransactions(Request $request)
    {
        $paginator = $this->buildTransactionsQuery($request)
            ->paginate(10)
            ->withQueryString();
        $transactions = TransactionResource::collection($paginator);

        return response()->json([
            'transactions' => $transactions,
            'nextPageUrl' => $paginator->nextPageUrl(),
        ]);
    }

    private function buildTransactionsQuery(Request $request)
    {
        $transactionsType = CategoryType::from($request->get('transaction_type'));

        $transactionsQuery = Auth::user()
            ->transactions()
            ->with(['category', 'account', 'currency'])
            ->ofType($transactionsType);

        if (!$request->get('all_time')) {
            $dateFrom = $request->get('date_from')
                ? Carbon::parse($request->get('date_from'))
                : Carbon::parse(date('Y-m-01'));

            $dateTo = $request->get('date_to')
                ? Carbon::parse($request->get('date_to'))
                : Carbon::parse(date('Y-m-t'));

            $transactionsQuery
                ->where('date', '>=', $dateFrom)
                ->where('date', '<=', $dateTo);
        }

        if ($request->get('category_id')) {
            $transactionsQuery->where('category_id', $request->get('category_id'));
        }

        return $transactionsQuery
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc');
    }
}
