<?php

namespace App\Http\Resources;

use App\Helpers\GeneralHelper;
use App\Nova\Item;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComboResource extends JsonResource
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
            'company' => $this->company,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'total' => $this->total,

            'items' =>$this->items,
             'comboItems'=>$this->items()->get(),
            'currency'=>$this->currency,
            'brand'=>$this->brand,
            'image'=> $baseUrl.$this->img_path,
            'created_at' =>$this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
