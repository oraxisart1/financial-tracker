<?php

namespace App\Models;

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
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
