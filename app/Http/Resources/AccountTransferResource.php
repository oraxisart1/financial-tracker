<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountTransferResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'account_from' => $this->accountFrom,
            'account_to' => $this->accountTo,
            'amount' => $this->amount,
            'converted_amount' => $this->converted_amount,
            'date' => $this->date,
        ];
    }
}
