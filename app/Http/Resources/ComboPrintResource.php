<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComboPrintResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {        $baseUrl = env('APP_URL').'storage/app/public/';

        return [
            'id'=>$this->id,
            'brand'=>$this->brand,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'image_url'=> $baseUrl.$this->img_path,
        ];
    }
}
