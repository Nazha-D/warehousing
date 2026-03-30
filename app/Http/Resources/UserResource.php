<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Flag لتحديد إذا تم طلب التفاصيل الكاملة
        $detailed = $request->boolean('detailed');

        $data = [
            'id' => $this->id,
            'company' => $this->company ?? null,
            'name' => $this->name,
            'email' => $this->email,
            'is_salesperson' => (bool) $this->is_salesperson,
            'roles' => $this->roles,
           // 'permissions' => $this->getAllPermissions()->pluck('name'),
            'active' => (bool) $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];


        if ($detailed && $this->is_salesperson) {
            $data['cashing_method'] = $this->userSalespersonConfiguration?->cashingMethod;
            $data['commission_method'] = $this->userSalespersonConfiguration?->commissionMethod;
            $data['commission'] = $this->userSalespersonConfiguration?->commission;


        }

        // إمكانية إضافة بيانات أخرى لاحقًا إذا احتجنا (مثل last_session, etc.)
        // $data['last_session'] = $this->currentSession;

        return $data;
    }
}
