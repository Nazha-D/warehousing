<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'company_id'=>$this->company_id,
            'name'=>$this->name,
            'symbol'=>$this->symbol,
            'active'=>$this->active,
            'latest_rate'=>$this->latest_rate,
            'exchange_rates'=>$this->exchangeRates()->get()

        ];
    }
}
