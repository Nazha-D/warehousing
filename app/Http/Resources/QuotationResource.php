<?php

namespace App\Http\Resources;

use App\Helpers\GeneralHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotationResource extends JsonResource
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
        $pricelist = $this->pricelist;
        $commissionMethod = $this->commissionMethod;
        $cashingMethod = $this->cashingMethod;
        $salesperson = $this->salesperson;
        $detailed = $request->boolean('detailed');
        $data = ['id' => $this->id,
            'quotation_number' => $this->quotation_number,
            'input_date' => $this->input_date,
            'created_at_date' => GeneralHelper::changeDateFormatTodmY($this->created_at),
            'created_at' => $this->created_at,
            'chance' => $this->chance,
            'status' => $this->status->value,
            'total' => $this->total,
            'currency' => $this->currency,
        ];
        if ($client != null) {
            $data['client'] =$this->client;
        } else {
            $data['client'] = null;
        }
        if ($salesperson != null) {
            $data['salesperson'] = $this->salesperson;
        } else {
            $data['salesperson'] = null;
        }
        if ($detailed)
        {
            $data =array_merge($data, [

                'company' => $this->company,
                'drafted_by' => $this->createdBy ?? null,

                'reference' => $this->reference,
                'validity' => $this->validity,

                'terms_and_conditions_object' => $this->termsAndConditions ?? null,
                //'termsAndConditionsHistory' =>  optional($this->termsAndConditions()->get()),
                'terms_and_conditions' => $this->terms_and_conditions,
                'commission_rate' => $this->commission_rate,
                'commission_total' => $this->commission_total,
                // 'tasks'=>TaskResource::collection($this->tasks()->get()),
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
                'delivery_term' => $this->deliveryTerm,
                'cancellation_reason' => $this->cancellation_reason,
                'updated_at' => $this->updated_at,
//            'order_lines' => $this->quotationLines,
                'order_lines' => QuotationLineResource::collection($this->quotationLines),
                'company_header' => $this->companyHeader ?? null,
                'terms_and_condition' => $this->termsAndCondition ?? null
            ]);


            if ($paymentTerm != null) {
                $data['paymentTerm'] = [
                    'id' => $paymentTerm->id,
                    'title' => $paymentTerm->title,
                ];
            } else {
                $data['payment_term'] = null;
            }

            if ($pricelist != null) {
                $data['price_list'] = [
                    'id' => $pricelist->id,
                    'name' => $pricelist->name
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
