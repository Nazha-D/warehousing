<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemGroupResource extends JsonResource
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
            'company' => $this->company,
            'code' => $this->code,
            'name' => $this->name,
            'show_name'=>$this->show_name,
            'active' => (bool)$this->active,
            'children_recursive'=>$this->childrenRecursive()->get(),
            //'items' => $this->items,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];

        return $data;
    }
}
