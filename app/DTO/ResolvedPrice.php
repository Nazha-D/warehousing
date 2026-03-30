<?php
namespace App\DTO;

use App\Enums\Pricing\PriceSourceEnum;
use App\Models\PriceList;
use App\Models\Item;
use App\Models\PriceListItem;


final class ResolvedPrice
{
    public function __construct(
        public readonly Item $item,
        public readonly float $unitPrice,
        public readonly int $currencyId,
        public readonly PriceSourceEnum $source,
        public readonly ?PriceList $priceList = null,
        public readonly array $appliedRules = [],
        public readonly bool $isFallback = false
    ) {

    }
public static function fromDirectItem(
    PriceListItem $priceListItem,
    PriceList $priceList
): self {
    return new self(

       $priceListItem->item,                 // ✅ Item model
         (float) $priceListItem->manual_price,
         $priceList->currency_id,        // أو item->currency_id حسب التصميم
        PriceSourceEnum::MANUAL_OVERRIDE,   // ✅ Enum
         $priceList,
         [],
         false
    );
}
private function resolveFromPriceListItems(
    Item $item,
    PriceList $priceList
): ?ResolvedPrice {
    $priceList->loadMissing('items', 'parent');

    $direct = $priceList->items
        ->firstWhere('item_id', $item->id);

    if ($direct) {
        return ResolvedPrice::fromDirectItem($direct, $priceList);
    }

    return $priceList->parent
        ? $this->resolveFromPriceListItems($item, $priceList->parent)
        : null;
}
public static function fromBasePrice(Item $item): self
{
    return new self(
        $item,
        round($item->unit_price, 2),
        $item->currency->id,
        PriceSourceEnum::BASE_PRICE,
        null,
        [],
        true
    );
}
}
