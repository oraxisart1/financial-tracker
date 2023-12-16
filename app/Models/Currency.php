<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    public static function findByCode(string $code)
    {
        return static::where('code', $code)->firstOrFail();
    }
}
