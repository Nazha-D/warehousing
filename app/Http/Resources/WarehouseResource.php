<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $detailed = $request->boolean('detailed');
        $data= [
            'id'=>$this->id,
            'company'=>$this->company,
            'warehouse_number'=>$this->warehouse_number,
            'name'=>$this->name,
            'type'=>$this->type,
            'address'=>$this->address,
            'blocked'=>$this->blocked,
            'active'=>$this->active,
        ];
        if($request->detailed){
            $data['items']=$this->items;
        }
        return  $data;
    }
}
