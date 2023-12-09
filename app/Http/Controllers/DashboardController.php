<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $transactions = Auth::user()->transactions;

        return Inertia::render('Dashboard', compact('transactions'));
    }
}
