<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PosInvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $detailed = $request->boolean('detailed');

        return [

            /*
            |--------------------------------------------------------------------------
            | Basic Fields (Always Returned)
            |--------------------------------------------------------------------------
            */

            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'status' => $this->status,

            'subtotal' => $this->subtotal,
            'tax_total' => $this->tax_total,
            'discount_total' => $this->discount_total,
            'custom_discount_total' => $this->custom_discount_total,
            'grand_total' => $this->grand_total,

            'paid_total' => $this->paid_total,
            'remaining_total' => $this->remaining_total,
            'change_total' => $this->change_total,

            'currency' => $this->currency,

            'client' => $this->whenLoaded('client'),

            'user' => $this->whenLoaded('user'),
             'finished_by_user'=>$this->whenLoaded('finishedByUser'),
            'car'=>$this->whenLoaded('car'),
                'note'=>$this->note,
            'opened_at' => $this->opened_at,
            'closed_at' => $this->closed_at,

            /*
            |--------------------------------------------------------------------------
            | Detailed Section (Optional)
            |--------------------------------------------------------------------------
            */

            'lines' => $this->when(
        $detailed,

            $this->whenLoaded('lines')

    ),

            'payments' => $this->when(
        $detailed,
        fn () => $this->payments->map(function ($payment) {

            return [
                'id' => $payment->id,
                'type' => $payment->type,
                'amount' => $payment->amount,
                'exchange_rate' => $payment->exchange_rate,
                'amount_in_invoice_currency' => $payment->amount_in_invoice_currency,

                'currency' => [
                    'id' => $payment->currency?->id,
                            'code' => $payment->currency?->code,
                        ],

                        'cashing_method' => [
                'id' => $payment->cashingMethod?->id,
                            'title' => $payment->cashingMethod?->title,
                        ],

                        'created_at' => $payment->created_at,
                    ];
                })
    ),
        ];
    }
}
