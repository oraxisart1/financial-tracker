<?php

namespace App\Http\Controllers;

use App\Enums\CategoryType;
use Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $categoryType = $request->get('category_type', CategoryType::EXPENSE->value);

        return Inertia::render(
            'Settings',
            [
                'categories' => Auth::user()
                    ->categories()
                    ->where('type', $categoryType)
                    ->get(),
            ]
        );
    }
}
