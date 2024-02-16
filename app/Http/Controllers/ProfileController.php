<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = Auth::user();

        $attributes = [
            'name' => $request->input('name'),
        ];

        if ($request->input('new_password')) {
            $attributes['password'] = bcrypt($request->input('new_password'));
        }

        $user->update($attributes);

        return redirect()->back();
    }
}
