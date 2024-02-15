<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Auth;

class CategoriesController extends Controller
{
    public function store(StoreCategoryRequest $request)
    {
        Category::create([
            ...$request->validated(),
            'user_id' => Auth::user()->id,
        ]);
        return redirect()->back();
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

        return redirect()->back();
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->back();
    }
}
