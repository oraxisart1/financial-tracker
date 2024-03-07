<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Auth;
use Illuminate\Http\RedirectResponse;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Category::class);
    }

    public function store(StoreCategoryRequest $request)
    {
        Category::create([
            ...$request->validated(),
            'user_id' => Auth::user()->id,
        ]);
        return redirect()->back();
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()->back();
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return redirect()->back();
    }
}
