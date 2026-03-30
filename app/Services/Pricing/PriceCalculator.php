<?php

namespace App\Services\Pricing;

use App\DTO\ResolvedPrice;
use App\Models\Item;
use App\Models\Client;
use App\Models\PriceList;
use App\Enums\Pricing\PriceSourceEnum;
use App\Enums\Pricing\BaseSourceEnum;
use App\Enums\Pricing\RuleScopeEnum;
final class PriceCalculator implements PriceCalculatorInterface
{
    public function calculate(
        Item $item,
        Client $client = null,
        ?PriceList $priceList = null,
        bool $useCache = true
    ): ResolvedPrice {

        $priceList = $priceList ?? $client?->activePriceList;
        $priceList?->loadMissing('parent', 'rules');
$directPrice = $this->resolveFromPriceListItems($item, $priceList);

if ($directPrice) {
    return $directPrice;
}
        if (!$priceList) {
            return new ResolvedPrice(
                $item,
                round($item->unit_price, 2),
                $item->currency->id,
                PriceSourceEnum::BASE_PRICE,
                null,
                [],
                true
            );
        }

        $cacheKey = "pricelist_{$priceList->id}_item_{$item->id}";
        if ($useCache && cache()->has($cacheKey)) {
            return cache()->get($cacheKey);
        }

        /**
         * 1️⃣ احسب السعر الأساسي:
         * - إما من parent chain
         * - أو من base item price
         */
        $unitPrice = $priceList->parent
            ? $this->calculateParentPrice($item, $priceList->parent)
            : $item->unit_price;

        /**
         * 2️⃣ طبّق فقط قواعد هذه الـ PriceList
         */
        $rules = RuleResolver::getApplicableRules($item, $priceList);
        $appliedRules = [];

        foreach ($rules as $rule) {

            if ($rule->computation_method === 'manual') {
                $unitPrice = $rule->value;
                $appliedRules[] = $rule->id;
                break;
            }

            $unitPrice = $this->applyRule($unitPrice, $rule);
            $appliedRules[] = $rule->id;
        }

        $resolved = new ResolvedPrice(
            $item,
            round($unitPrice, 2),
            $item->currency->id,
            PriceSourceEnum::PRICE_LIST,
            $priceList,
            $appliedRules,
            empty($appliedRules)
        );

        if ($useCache) {
            cache()->put($cacheKey, $resolved, 3600);
        }

        return $resolved;
    }

    private function applyRule(float $price, $rule): float
    {
        return match ($rule->computation_method) {
        'percentage'   => $price * (1 + $rule->value / 100),
            'fixed_amount' => $price + $rule->value,
            'fixed_price'  => $rule->value,
            default        => $price,
        };
    }

    /**
     * 🔁 يبني السعر من أعلى Parent إلى أسفل
     */
    private function calculateParentPrice(
        Item $item,
        PriceList $priceList
    ): float {
        $priceList->loadMissing('parent', 'rules');

        $price = $priceList->parent
            ? $this->calculateParentPrice($item, $priceList->parent)
            : $item->unit_price;

        foreach ($priceList->rules as $rule) {

            if (
                $rule->apply_on === RuleScopeEnum::ITEM &&
                $rule->item_id !== $item->id
            ) {
                continue;
            }

            if (
                $rule->apply_on === RuleScopeEnum::CATEGORY &&
                $rule->category_id !== $item->category_id
            ) {
                continue;
            }

            // 👈 تطبّق كل قواعد هذه الـ price list
            $price = $this->applyRule($price, $rule);
        }

        return $price;
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

}
