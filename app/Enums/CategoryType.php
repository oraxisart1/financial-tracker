<?php

namespace App\Enums;

use App\Traits\EnumValues;

enum CategoryType: string
{
    use EnumValues;

    case INCOME = 'income';

    case EXPENSE = 'expense';

    public static function fromTransactionType(TransactionType $transactionsType): CategoryType
    {
        return match ($transactionsType) {
            TransactionType::EXPENSE => self::EXPENSE,
            TransactionType::INCOME => self::INCOME,
        };
    }
}
