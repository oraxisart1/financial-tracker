<?php

namespace App\Enums;

use App\Traits\EnumValues;

enum CategoryType: string
{
    use EnumValues;

    case INCOME = 'income';

    case EXPENSE = 'expense';
}
