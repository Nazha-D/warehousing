<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        // Flag لتحديد مستوى التفاصيل
        $detailed = $request->boolean('detailed');

        $data = [
            // ====== Basic fields (always returned) ======
            'id' => $this->id,
            'company_id' => $this->company_id,
            'client_number' => $this->client_number,
            'type' => $this->type,
            'name' => $this->name,

            'client_company_id' => $this->client_company_id,

            'country' => $this->country,
            'city' => $this->city,
            'state' => $this->state,
            'zip' => $this->zip,
            'street' => $this->street,
            'floor_and_building' => $this->floor_and_building,

            'phone_code' => $this->phone_code,
            'phone_number' => $this->phone_number,
            'mobile_code' => $this->mobile_code,
            'mobile_number' => $this->mobile_number,

            'email' => $this->email,
            'website' => $this->website,

            'active' => $this->active,
            'is_blocked' => $this->is_blocked,
            'show_on_pos' => $this->show_on_pos,
            'is_cash_customer' => $this->is_cash_customer,

            'tags' => $this->tags,
            'note' => $this->note,
        ];

        // ====== Detailed fields ======
        if ($detailed) {
            $data += [
                'job_position' => $this->job_position,
                'reference' => $this->reference,
                'title' => $this->title,
                'tax_id' => $this->tax_id,

                'contact_type' => $this->contact_type,
                'contact_name' => $this->contact_name,
                'contact_country' => $this->contact_country,
                'contact_city' => $this->contact_city,
                'contact_state' => $this->contact_state,
                'contact_zip' => $this->contact_zip,
                'contact_street' => $this->contact_street,
                'contact_phone_code' => $this->contact_phone_code,
                'contact_phone_number' => $this->contact_phone_number,
                'contact_mobile_code' => $this->contact_mobile_code,
                'contact_mobile_number' => $this->contact_mobile_number,
                'contact_email' => $this->contact_email,

                'salesperson_id' => $this->salesperson_id,
                'payment_term_id' => $this->payment_term_id,
                'pricelist_id' => $this->pricelist_id,

                'granted_discount' => $this->granted_discount,
                'auto_generated_number' => $this->auto_generated_number,
            ];

            // ====== Relations (loaded only if requested) ======
            $data['salesperson'] = $this->whenLoaded('salesperson');
            $data['company'] = $this->whenLoaded('company');
            $data['client_company'] = $this->whenLoaded('clientCompany');

            $data['addresses'] = $this->whenLoaded('clientAddresses');
            $data['cars'] = $this->whenLoaded('cars');
        }

        return $data;
    }
}
