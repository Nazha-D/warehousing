<?php

namespace App\Http\Resources;

use App\Models\Client;
use App\Models\PriceList;
use App\Services\Pricing\PriceCalculator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        // Flag لتحديد ما إذا نريد التفاصيل الكاملة أم لا
        $detailed = $request->boolean('detailed');
        $baseUrl = env('APP_URL').'storage/app/public/';
        $priceListId = $request->query('price_list_id');
        $priceList = $priceListId ? \App\Models\PriceList::find($priceListId) : null;

       // $calculator = new PriceCalculator();
     //   $resolvedPrice = $calculator->calculate($this, new Client(), $priceList);


        $data = [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'main_code'  => $this->main_code,
            'main_description' => $this->main_description,
            'item_name' => $this->item_name,
            'short_description'=>$this->short_description,
            'second_language_description'=>$this->second_language_description,
            'can_be_sold'=>$this->can_be_sold,
            'can_be_purchased'=>$this->can_be_purchased,
            'warranty'=>$this->warranty,
            'last_allowed_purchase_date'=>$this->last_allowed_purchase_date,
            'blocked'=>$this->blocked,
            'show_on_pos' => $this->show_on_pos,
            'category' => $this->category,
            'item_type' => $this->itemType,
            'unit_cost' => $this->unit_cost,
            'unit_price' => $this->unit_price,
            //'final_price' => $resolvedPrice->unitPrice,
            //'applied_rules' => $resolvedPrice->appliedRules,
            //'price_list_id' => $resolvedPrice->priceList?->id,

            'quantity' => $this->quantity,
            'weight'=>$this->weight,
            'volume'=>$this->volume,
            'active' => $this->active,
            'currency'=>$this->currency,
            'price_currency'=>$this->priceCurrency,
            'pos_currency'=>$this->posCurrency,
             'default_transaction_package'=>$this->defaultTransactionPackage,
            'subref'=>$this->subref->name ?? '',
            'main_image_url' => $this->itemImages->first()
                ? $baseUrl . $this->itemImages->first()->path
                : null,
        ];
        $data['item_images']=$this->whenLoaded('itemImages', function () use ($baseUrl) {
            return $this->itemImages->map(function ($image) use ($baseUrl) {
                return [
                    'id' => $image->id,
                    'url' => $baseUrl . $image->path,

                ];
            });
        });
        if ($detailed)
        {
            $data['barcodes']=$this->barCodes()->get();
            $data['supplier_codes']=$this->supplierCodes()->get();
            $data['alternative_codes']=$this->alternativeCodes()->get();
            $data['taxation_group'] = $this->taxationGroup;
            $data['item_groups'] = $this->itemGroups;
            $data['subrefObject'] = $this->subref;
            $data['package'] = [
                'type' => $this->package_id,
                'unit_name' => $this->package_unit_name,
                'unit_quantity' => $this->package_unit_quantity,
                'set_name' => $this->package_set_name,
                'set_quantity' => $this->package_set_quantity,
                'superset_name' => $this->package_superset_name,
                'superset_quantity' => $this->package_superset_quantity,
                'palette_name' => $this->package_palette_name,
                'palette_quantity' => $this->package_palette_quantity,
                'container_name' => $this->package_container_name,
                'container_quantity' => $this->package_container_quantity,

            ];
            $data['qty_on_hand'] = $qty = app(\App\Services\StockService::class)
                ->getItemQtyOnHand($this->id);

            $data['qty_on_hand'] = empty($qty) ? null : $qty;
        }
        $priceList = null;

        if ($request->filled('price_list_id')) {
            $priceList = PriceList::query()
                ->where('company_id', $this->company_id)
                ->find($request->price_list_id);


        /** @var PriceCalculator $calculator */
        $calculator = app(PriceCalculator::class);

        $resolvedPrice = $calculator->calculate(
             $this->resource,
        null,
        $priceList,
        true
    );

    $data['pricing'] = [
        'unit_price'   => $resolvedPrice->unitPrice,
        'currency_id'  => $resolvedPrice->currencyId,
        'source'       => $resolvedPrice->source->value,
        'price_list_id'=> $resolvedPrice->priceList?->id,
        'is_fallback'  => $resolvedPrice->isFallback,
    ];

     }
        return $data;
    }
}
