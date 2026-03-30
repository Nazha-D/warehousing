<?php

namespace App\Http\Resources;

use App\Helpers\GeneralHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $client = $this->client;
        $paymentTerm = $this->paymentTerm;
        $pricelist = $this->priceList;
        $commissionMethod = $this->commissionMethod;
        $cashingMethod = $this->cashingMethod;
        $salesperson = $this->salesperson;

        $detailed = $request->boolean('detailed');

        $data = [
            'id' => $this->id,
            'sales_order_number' => $this->sales_order_number,
            'input_date' => $this->input_date,
            'created_at_date' => GeneralHelper::changeDateFormatTodmY($this->created_at),
            'created_at' => $this->created_at,
            'status' => $this->status,
            'total' => $this->total,
            'currency' => $this->currency,
            'quotation' => $this->quotation
        ];

        if ($client != null) {
            $data['client'] = $client;
        } else {
            $data['client'] = null;
        }

        if ($salesperson != null) {
            $data['salesperson'] = $salesperson;
        } else {
            $data['salesperson'] = null;
        }

        if ($detailed) {
            $data = array_merge($data, [

                'company' => $this->company,
                'quotation_id' => $this->quotation_id,
                'drafted_by' => $this->createdBy ?? null,

                'reference' => $this->reference,
                'validity' => $this->validity,

                'terms_and_conditions' => $this->terms_and_conditions,

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

                'updated_at' => $this->updated_at,

                'order_lines' => SalesOrderLineResource::collection($this->lines),

                'company_header' => $this->companyHeader ?? null,
            ]);

            if ($paymentTerm != null) {
                $data['payment_term'] = [
                    'id' => $paymentTerm->id,
                    'title' => $paymentTerm->title,
                ];
            } else {
                $data['payment_term'] = null;
            }

            if ($pricelist != null) {
                $data['price_list'] = [
                    'id' => $pricelist->id,
                    'title' => $pricelist->title,
                    'code' => $pricelist->code,
                ];
            } else {
                $data['price_list'] = null;
            }

            if ($commissionMethod != null) {
                $data['commission_method'] = [
                    'id' => $commissionMethod->id,
                    'title' => $commissionMethod->title,
                ];
            } else {
                $data['commission_method'] = null;
            }

            if ($cashingMethod != null) {
                $data['cashing_method'] = [
                    'id' => $cashingMethod->id,
                    'title' => $cashingMethod->title,
                ];
            } else {
                $data['cashing_method'] = null;
            }
        }

        return $data;
    }
}
