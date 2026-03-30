<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotationLineResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $baseUrl = env('APP_URL').'storage/app/public/';

     $data=[
            'id' => $this->id,
            'quotation_id'=>$this->quotation_id,
            'line_type_id'=>$this->line_type_id,
            'order_index'=>$this->order_index,
            'item_id'=>$this->item_id,
            'package_id'=>$this->package_id,
            'combo_id'=>$this->combo_id,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'description' => $this->description,
            'discount' => $this->discount,
            'total' => $this->total,
            'item' => new ItemPrintResource($this->item),
            'combo' => new ComboPrintResource($this->combo),
             'note'=>$this->note ?? null,
            'image'=>$this->image_path ? $baseUrl.$this->image_path :null,
            'title'=>$this->title,
            'created_at'=>$this->created_at,
            'updated_at'=>$this->updated_at,

        ];
return  $data;
    }
}
