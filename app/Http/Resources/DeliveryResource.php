<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

       $data=[
            'id'=>$this->id,
            'delivery_number'=>$this->delivery_number,
            'status'=>$this->status,
            'date'=>$this->date,
            'expected_delivery'=>$this->expected_delivery,
            'client'=>$this->client,
            'driver'=>$this->driver
        ];
       if ($request->detailed==true)
       {
           $data['lines'] =  DeliveryLineResource::collection($this->deliveryLines);

       }
    return $data;
    }
}
