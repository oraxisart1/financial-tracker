<?php

namespace App\Http\Requests;

use App\Models\Account;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateAccountTransferRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'account_from_id' => [
                'required',
                Rule::exists(Account::class, 'id')
                    ->where('user_id', Auth::user()->id),
            ],
            'account_to_id' => [
                'required',
                Rule::exists(Account::class, 'id')
                    ->where('user_id', Auth::user()->id),
            ],
            'amount' => [
                'required',
                'numeric',
                'gt:0',
            ],
            'converted_amount' => [
                'nullable',
                Rule::requiredIf(function () {
                    $accountFrom = Account::find($this->get('account_from_id'));
                    $accountTo = Account::find($this->get('account_to_id'));

                    return $accountFrom?->currency->code !== $accountTo?->currency->code;
                }),
                'numeric',
                'gt:0',
            ],
            'date' => [
                'required',
                'date',
            ],
            'description' => [
                'nullable',
                'string',
            ],
        ];
    }
}
