<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'amount',
        'description',
        'user_id',
        'type',
        'currency_id',
        'category_id',
        'account_id',
    ];

    protected $casts = [
        'date' => 'date',
        'type' => TransactionType::class,
    ];

    /**
     * Scope a query to only include users of a given type.
     */
    public function scopeOfType(Builder $query, TransactionType $type): void
    {
        $query->where('type', $type);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class)->withTrashed();
    }
}
