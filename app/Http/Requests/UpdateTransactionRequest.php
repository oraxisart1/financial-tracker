<?php

namespace App\Http\Requests;

use App\Models\Account;
use App\Models\Category;
use App\Models\Currency;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransactionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'description' => ['nullable'],
            'currency' => ['required', Rule::exists(Currency::class, 'code')],
            'category_id' => ['required', Rule::exists(Category::class, 'id')],
            'account_id' => ['required', Rule::exists(Account::class, 'id')],
        ];
    }
}
