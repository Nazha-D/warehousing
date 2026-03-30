<?php

namespace App\Services\Pricing;

use App\Models\Item;
use App\Models\PriceList;
use App\Models\Client;
use App\DTO\ResolvedPrice;

class PreviewPriceService
{
    protected PriceCalculator $priceCalculator;
    public function __construct()
    {
        $this->priceCalculator=new PriceCalculator();
    }
//
//public function preview(
//    PriceList $priceList,
//    int $itemId,
//    Client $client=null,
//    bool $useCache = false
//): ResolvedPrice {
//    $item = Item::query()
//        ->where('company_id', $priceList->company_id)
//        ->findOrFail($itemId);
//
//    return $this->priceCalculator->calculate(
//         $item,
//        $client,
//        $priceList,
//       $useCache
//        );
//    }
    public function preview(
        PriceList $priceList,
        ?array $itemIds = null,
        ?Client $client = null,
        bool $useCache = false
    ): array {

        $query = Item::query()
            ->where('company_id', $priceList->company_id)

            ->with('currency');

        // إذا تم تمرير IDs
        if (!empty($itemIds)) {
            $query->whereIn('id', $itemIds);
        }

        $items = $query->get();

        $resolvedPrices = $items->map(function ($item) use ($priceList, $client, $useCache) {

            $resolvedPrice = $this->priceCalculator->calculate(
                $item,
                $client,
                $priceList,
                $useCache
            );

            // fallback إذا لم تنطبق أي Rule
            if ($resolvedPrice->isFallback) {
                return ResolvedPrice::fromBasePrice($item);
            }

            return $resolvedPrice;
        });

        return $resolvedPrices->all();
    }



}
