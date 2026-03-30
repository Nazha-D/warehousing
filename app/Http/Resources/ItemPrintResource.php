<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemPrintResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $baseUrl = env('APP_URL').'storage/app/public/';
        return [
            'id' => $this->id,
            'item_name' => $this->item_name,
            'main_code' => $this->main_code,
            'main_description' => $this->main_description,
            'main_image_url' => $this->itemImages->first()
                ? $baseUrl . $this->itemImages->first()->path
                : null,
            'item_groups'=>$this->itemGroups
        ];
    }
}
