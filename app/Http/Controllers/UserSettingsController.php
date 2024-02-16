<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserSettingsRequest;
use App\Models\Currency;
use Illuminate\Support\Facades\Auth;

class UserSettingsController extends Controller
{
    public function update(UpdateUserSettingsRequest $request)
    {
        Auth::user()->settings->update([
            'default_currency_id' => Currency::findByCode($request->input('currency'))->id,
        ]);

        return redirect()->back();
    }
}
