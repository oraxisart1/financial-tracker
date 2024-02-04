<?php

namespace App\Models;

use App\Enums\TransactionType;
use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory;
    use SoftDeletes;

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

    public function transfersFrom(): HasMany
    {
        return $this->hasMany(AccountTransfer::class, 'account_from_id');
    }

    public function transfersTo(): HasMany
    {
        return $this->hasMany(AccountTransfer::class, 'account_to_id');
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
