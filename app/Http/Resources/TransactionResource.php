<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'date' => $this->date->format('Y-m-d'),
            'description' => $this->description,
            'type' => $this->type,
            'amount' => $this->amount,
            'category' => $this->category,
            'currency' => $this->currency,
            'account' => $this->account,
        ];
    }
}
