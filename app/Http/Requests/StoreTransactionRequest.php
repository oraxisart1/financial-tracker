<?php

namespace App\Http\Requests;

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Currency;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
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
            'type' => ['required', Rule::enum(TransactionType::class)],
            'currency' => ['required', Rule::exists(Currency::class, 'code')],
            'category_id' => ['required', Rule::exists(Category::class, 'id')],
        ];
    }
}
