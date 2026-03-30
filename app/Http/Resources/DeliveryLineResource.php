<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryLineResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return
            ['id' => $this->id,
                'delivery_id'=>$this->sales_invoice_id,
                'line_type_id'=>$this->line_type_id,
                'order_index'=>$this->order_index,
                'item_id'=>$this->item_id,
                'package_id'=>$this->package_id,
                'combo_id'=>$this->combo_id,
                'quantity' => $this->qty,
                'unit_price' => $this->unit_price,
                'warehouse_id'=>$this->warehouse,
                'description' => $this->description,
                'discount' => $this->discount,
                'total' => $this->total,
                'item' => new ItemPrintResource($this->item),
                'combo' => new ComboPrintResource($this->combo),
                'note'=>$this->note ?? null,
                'image'=>$this->image_path ? $baseUrl.$this->image_path :null,
                'title'=>$this->title,
                'sales_order_line'=>$this->reservation->source,
                'created_at'=>$this->created_at,
                'updated_at'=>$this->updated_at,
            ];
    }
}
