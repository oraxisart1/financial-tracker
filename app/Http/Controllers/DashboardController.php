<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $transactionsType = $request->get('transaction_type');
        if ($transactionsType) {
            $transactionsType = TransactionType::tryFrom($transactionsType) ?: TransactionType::EXPENSE;
        } else {
            $transactionsType = TransactionType::EXPENSE;
        }

        $transactions = Auth::user()
            ->transactions()
            ->ofType($transactionsType)
            ->get();

        return Inertia::render('Dashboard', compact('transactions'));
    }
}
