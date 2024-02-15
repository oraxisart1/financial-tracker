<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionsController extends Controller
{
    public function index(Request $request)
    {
        $transactionQuery = Auth::user()
            ->transactions()
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc');

        if ($request->get('type')) {
            $transactionQuery->whereRelation('category', 'type', $request->get('type'));
        }

        if ($request->get('category_id')) {
            $transactionQuery->where('category_id', $request->get('category_id'));
        }

        if ($request->get('date_from')) {
            $transactionQuery->whereDate('date', '>=', $request->get('date_from'));
        }

        if ($request->get('date_to')) {
            $transactionQuery->whereDate('date', '<=', $request->get('date_to'));
        }

        $paginator = $transactionQuery
            ->paginate($request->perPage())
            ->withQueryString();

        return response()->json([
            'transactions' => TransactionResource::collection($paginator),
            'nextPageUrl' => $paginator->nextPageUrl(),
        ]);
    }
}
