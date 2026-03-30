<?php

namespace App\Http\Resources;

use App\Helpers\GeneralHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesInvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $client = $this->client;
        $salesOrder = $this->salesOrder;
        $pricelist = $this->pricelist;
        $paymentTerm = $this->paymentTerm;
        $commissionMethod = $this->commissionMethod;
        $cashingMethod = $this->cashingMethod;
        $salesperson = $this->salesperson;
        $detailed = $request->boolean('detailed');

        $data = [
            'id' => $this->id,
            'sales_invoice_number' => $this->sales_invoice_number,
            'input_date' => $this->input_date,
            'invoice_delivery_date' => $this->invoice_delivery_date,
            'value_date' => $this->value_date,
            'status' => $this->status,
            'total' => $this->total,
            'currency' => $this->currency,
            'delivered_from_warehouse'=>$this->warehouse,
            'created_at' => $this->created_at,
        ];

        $data['client'] = $client ?? null;
        $data['sales_order'] = $salesOrder ?? null;

        if ($detailed) {
            $data = array_merge($data, [
                'company' => $this->company,
                'reference' => $this->reference,
                'terms_and_conditions' => $this->terms_and_conditions,
                'terms_and_conditions_object' => $this->termsAndCondition ?? null,
                'commission_rate' => $this->commission_rate,
                'commission_total' => $this->commission_total,
                'special_discount' => $this->special_discount,
                'special_discount_amount' => $this->special_discount_amount,
                'global_discount' => $this->global_discount,
                'global_discount_amount' => $this->global_discount_amount,
                'vat' => $this->vat,
                'vat_lebanese' => $this->vat_lebanese,
                'total_before_vat' => $this->total_before_vat,
                'vat_exempt' => $this->vat_exempt,
                'not_printed' => $this->not_printed,
                'printed_as_vat_exempt' => $this->printed_as_vat_exempt,
                'printed_as_percentage' => $this->printed_as_percentage,
                'vat_inclusive_prices' => $this->vat_inclusive_prices,
                'before_vat_prices' => $this->before_vat_prices,
                'code' => $this->code,
                'title' => $this->title,
                //'delivered_from_warehouse_id' => $this->delivered_from_warehouse_id,
                'company_header' => $this->companyHeader ?? null,
                'invoice_type' => $this->invoice_type,
                'car_id' => $this->car_id,
                'terms_and_condition_id' => $this->terms_and_condition_id,
                'sales_invoice_lines' => SalesInvoiceLineResource::collection($this->lines),
                'updated_at' => $this->updated_at,
            ]);

            if ($paymentTerm) {
                $data['payment_term'] = [
                    'id' => $paymentTerm->id,
                    'title' => $paymentTerm->title,
                ];
            } else {
                $data['payment_term'] = null;
            }

            if ($pricelist) {
                $data['price_list'] = [
                    'id' => $pricelist->id,
                    'title' => $pricelist->title,
                    'code' => $pricelist->code,
                ];
            } else {
                $data['price_list'] = null;
            }

            if ($commissionMethod) {
                $data['commission_method'] = [
                    'id' => $commissionMethod->id,
                    'title' => $commissionMethod->title,
                ];
            } else {
                $data['commission_method'] = null;
            }

            if ($cashingMethod) {
                $data['cashing_method'] = [
                    'id' => $cashingMethod->id,
                    'title' => $cashingMethod->title,
                ];
            } else {
                $data['cashing_method'] = null;
            }

            if ($salesperson) {
                $data['salesperson'] = [
                    'id' => $salesperson->id,
                    'name' => $salesperson->name,
                ];
            } else {
                $data['salesperson'] = null;
            }
        }

        return $data;
    }
}
