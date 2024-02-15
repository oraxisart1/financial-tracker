<?php

namespace App\DTO;

use App\Models\Currency;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionDTO
{
    public function __construct(
        public Carbon $date,
        public float $amount,
        public string $currency,
        public int $categoryId,
        public int $accountId,
        public int $userId,
        public ?string $description = null
    ) {
    }

    public static function fromRequest(Request $request): TransactionDTO
    {
        return new static(
            Carbon::parse($request->input('date')),
            $request->input('amount'),
            $request->input('currency'),
            $request->input('category_id'),
            $request->input('account_id'),
            Auth::user()->id,
            $request->input('description')
        );
    }

    public function toArray()
    {
        return [
            'date' => $this->date,
            'amount' => $this->amount,
            'currency_id' => Currency::findByCode($this->currency)->id,
            'category_id' => $this->categoryId,
            'account_id' => $this->accountId,
            'description' => $this->description,
            'user_id' => $this->userId,
        ];
    }
}
