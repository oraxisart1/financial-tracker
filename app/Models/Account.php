<?php

namespace App\Models;

use App\Enums\TransactionType;
use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'currency_id',
        'balance',
        'color',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function addTransaction(Transaction $transaction)
    {
        DB::transaction(function () use ($transaction) {
            $this->transactions()->save($transaction);

            $multiplier = $transaction->type === TransactionType::INCOME ? 1 : -1;
            $amount = $multiplier * $transaction->amount;
            $this->update([
                'balance' => $this->balance + $amount,
            ]);
        });
    }
}
