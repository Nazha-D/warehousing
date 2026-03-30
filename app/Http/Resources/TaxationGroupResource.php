<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaxationGroupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'company_id'=>$this->company_id,
            'company' => $this->company,
            'code' => $this->code,
            'name' => $this->name,
            'active' => (bool) $this->active,
            'tax_rates' => $this->taxRates,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];

        return $data;
    }
}
