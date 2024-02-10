<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AccountTransferResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountTransfersController extends Controller
{
    public function index(Request $request)
    {
        $transfersQuery = Auth::user()
            ->accountTransfers()
            ->with(['accountFrom', 'accountTo', 'accountFrom.currency', 'accountTo.currency']);

        $accountId = $request->get('account_id');
        if ($accountId) {
            $transfersQuery->where('account_from_id', $accountId)
                ->orWhere('account_to_id', $accountId);
        }

        $paginator = $transfersQuery
            ->paginate(10)
            ->withQueryString();
        return response()->json([
            'accountTransfers' => AccountTransferResource::collection($paginator),
            'nextPageUrl' => $paginator->nextPageUrl(),
        ]);
    }
}
