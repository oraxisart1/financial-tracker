<?php

namespace App\DTO;

use Carbon\Carbon;

class AccountTransferDTO
{
    public function __construct(
        public int $accountFromId,
        public int $accountToId,
        public float $amount,
        public float $convertedAmount,
        public Carbon $date,
        public ?string $description
    ) {
    }

    public function toArray(): array
    {
        return [
            'account_from_id' => $this->accountFromId,
            'account_to_id' => $this->accountToId,
            'amount' => $this->amount,
            'converted_amount' => $this->convertedAmount,
            'date' => $this->date,
            'description' => $this->description,
        ];
    }
}
