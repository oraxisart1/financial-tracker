<?php

namespace App\Enums;

use App\Traits\EnumValues;

enum TransactionType: string
{
    use EnumValues;

    case INCOME = 'income';

    case EXPENSE = 'expense';
}
