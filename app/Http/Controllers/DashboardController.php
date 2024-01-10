<?php

namespace App\Http\Controllers;

use App\Enums\CategoryType;
use App\Enums\TransactionType;
use App\Http\Resources\TransactionResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $transactionsType = $request->get('transaction_type');
        if ($transactionsType) {
            $transactionsType = TransactionType::tryFrom($transactionsType);
        }

        if (!$transactionsType) {
            return redirect()->route('dashboard', ['transaction_type' => TransactionType::EXPENSE->value]);
        }

        $transactionsQuery = Auth::user()
            ->transactions()
            ->with(['category', 'account', 'currency'])
            ->ofType($transactionsType);

        if ($request->get('category')) {
            $transactionsQuery->where('category_id', $request->get('category'));
        }

        $transactionsQuery
            ->orderBy('date', 'desc');

        $transactions = $transactionsQuery->get();

        $categories = Auth::user()
            ->categories()
            ->where('type', CategoryType::fromTransactionType($transactionsType))
            ->get();

        return Inertia::render(
            'Dashboard',
            [
                'transactions' => TransactionResource::collection($transactions),
                'categories' => $categories,
            ]
        );
    }
}
