<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'amount',
        'description',
        'user_id',
        'type',
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
}
